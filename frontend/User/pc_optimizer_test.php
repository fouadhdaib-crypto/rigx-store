<?php
declare(strict_types=1);

/**
 * REGIX PC Builder - Integration Test Runner
 *
 * Location in project:
 * frontend/User/pc_optimizer_tests.php
 *
 * Runs four scenarios against the real database catalog through:
 * ModelFactory -> PCBuilderBAL -> PCBuilderDAL -> BuildOptimizerClass.
 */

// Keep this diagnostic page local during development.
$remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

if (
    PHP_SAPI !== 'cli' &&
    !in_array($remoteAddress, ['127.0.0.1', '::1'], true)
) {
    http_response_code(403);
    exit('This test runner is available only from localhost.');
}

require_once dirname(__DIR__, 2) . '/backend/classes/ModelFactory.php';

if (PHP_SAPI !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
}

/**
 * Read the selected RAM capacity from the returned build.
 */
$getRamCapacity = static function (array $build): ?float {
    $capacity = $build['purpose_requirements']['actual_ram_gb']
        ?? $build['components']['ram']['specifications']['capacity_gb']
        ?? null;

    return is_numeric($capacity) ? (float) $capacity : null;
};

/**
 * Count how many component product IDs differ between two builds.
 */
$countBuildDifferences = static function (array $first, array $second): int {
    $componentTypes = [
        'cpu',
        'motherboard',
        'ram',
        'gpu',
        'storage',
        'psu',
        'case',
        'cooler'
    ];

    $differences = 0;

    foreach ($componentTypes as $type) {
        $firstId = $first['components'][$type]['product_id'] ?? null;
        $secondId = $second['components'][$type]['product_id'] ?? null;

        if ($firstId !== $secondId) {
            $differences++;
        }
    }

    return $differences;
};

/**
 * Validate one successful optimizer response.
 */
$validateSuccessfulResult = static function (array $result, array $test, callable $getRamCapacity, callable $countBuildDifferences): array {
    $checks = [];
    $budget = (float) $test['budget'];
    $minimumRam = (float) $test['minimum_ram_gb'];

    $recommended = $result['recommended_build'] ?? null;
    $alternatives = $result['alternatives'] ?? [];

    $addCheck = static function (string $name, bool $passed, $actual = null, $expected = null) use (&$checks): void {
        $checks[] = [
            'name' => $name,
            'passed' => $passed,
            'actual' => $actual,
            'expected' => $expected
        ];
    };

    $addCheck(
        'Optimizer returned success',
        ($result['success'] ?? false) === true,
        $result['success'] ?? false,
        true
    );

    $addCheck(
        'Returned purpose matches request',
        ($result['purpose'] ?? null) === $test['purpose'],
        $result['purpose'] ?? null,
        $test['purpose']
    );

    $addCheck(
        'Recommended build exists',
        is_array($recommended),
        is_array($recommended),
        true
    );

    if (!is_array($recommended)) {
        return $checks;
    }

    $builds = array_merge([$recommended], is_array($alternatives) ? $alternatives : []);

    $addCheck(
        'At least one result was returned',
        count($builds) >= 1,
        count($builds),
        '>= 1'
    );

    foreach ($builds as $index => $build) {
        $label = $index === 0 ? 'Recommended build' : 'Alternative ' . $index;
        $components = $build['components'] ?? [];
        $price = $build['total_price'] ?? null;
        $ramCapacity = $getRamCapacity($build);
        $compatibilityStatus = $build['compatibility_status'] ?? null;
        $performanceStatus = $build['performance_data_status'] ?? null;
        $performanceScore = $build['performance_score'] ?? null;

        $addCheck(
            $label . ': all eight component types are present',
            count($components) === 8,
            count($components),
            8
        );

        $addCheck(
            $label . ': price does not exceed budget',
            is_numeric($price) && (float) $price <= $budget,
            $price,
            '<= ' . $budget
        );

        $addCheck(
            $label . ': RAM meets minimum purpose requirement',
            $ramCapacity !== null && $ramCapacity >= $minimumRam,
            $ramCapacity,
            '>= ' . $minimumRam . ' GB'
        );

        $addCheck(
            $label . ': no explicit compatibility conflict',
            $compatibilityStatus !== 'incompatible',
            $compatibilityStatus,
            'compatible or needs_verification'
        );

        $addCheck(
            $label . ': performance benchmark ranking exists',
            $performanceStatus === 'ranked' && is_numeric($performanceScore),
            [
                'status' => $performanceStatus,
                'score' => $performanceScore
            ],
            'ranked with numeric score'
        );

        if (isset($test['resolution']) && $test['purpose'] === 'gaming') {
            $addCheck(
                $label . ': requested gaming resolution is used',
                ($build['performance_resolution'] ?? null) === $test['resolution'],
                $build['performance_resolution'] ?? null,
                $test['resolution']
            );
        }

        $ramStatus = $build['purpose_requirements']['ram_status'] ?? null;
        $addCheck(
            $label . ': RAM status is reported',
            in_array($ramStatus, ['minimum_met', 'recommended_met'], true),
            $ramStatus,
            'minimum_met or recommended_met'
        );
    }

    // Check that diversity is attempted when multiple results are returned.
    if (count($builds) > 1) {
        $minimumDifferences = null;

        for ($i = 0; $i < count($builds); $i++) {
            for ($j = $i + 1; $j < count($builds); $j++) {
                $differenceCount = $countBuildDifferences(
                    $builds[$i],
                    $builds[$j]
                );

                $minimumDifferences = $minimumDifferences === null
                    ? $differenceCount
                    : min($minimumDifferences, $differenceCount);
            }
        }

        // Report diversity diagnostically; do not fail if the catalog has too few options.
        $checks[] = [
            'name' => 'Alternatives diversity (diagnostic)',
            'passed' => $minimumDifferences >= 2,
            'diagnostic_only' => true,
            'actual_minimum_component_differences' => $minimumDifferences,
            'expected_preference' => '>= 2 when enough distinct candidates exist'
        ];
    }

    return $checks;
};

$tests = [
    [
        'name' => 'Gaming - budget 1200 - 1440p',
        'budget' => 1200.00,
        'purpose' => 'gaming',
        'resolution' => '1440p',
        'limit' => 3,
        'minimum_ram_gb' => 16,
        'expected_outcome' => 'success'
    ],
    [
        'name' => 'Workstation - budget 2000',
        'budget' => 2000.00,
        'purpose' => 'workstation',
        'resolution' => '1440p',
        'limit' => 3,
        'minimum_ram_gb' => 32,
        'expected_outcome' => 'success'
    ],
    [
        'name' => 'General Use - budget 1200',
        'budget' => 1200.00,
        'purpose' => 'general_use',
        'resolution' => '1440p',
        'limit' => 3,
        'minimum_ram_gb' => 16,
        'expected_outcome' => 'success'
    ],
    [
        'name' => 'Workstation - insufficient budget 100',
        'budget' => 100.00,
        'purpose' => 'workstation',
        'resolution' => '1440p',
        'limit' => 3,
        'minimum_ram_gb' => 32,
        'expected_outcome' => 'budget_insufficient'
    ]
];

$testResults = [];
$passedTests = 0;
$failedTests = 0;

try {
    // Instantiate once to verify the complete application dependency wiring.
    $builder = ModelFactory::pcBuilder();

    foreach ($tests as $test) {
        $testResult = [
            'name' => $test['name'],
            'passed' => false,
            'checks' => [],
            'summary' => null
        ];

        try {
            $result = $builder->generateBuild(
                $test['budget'],
                $test['purpose'],
                $test['limit'],
                $test['resolution']
            );

            $actualStatus = $result['status'] ?? 'unknown';

            if ($test['expected_outcome'] === 'success') {
                $checks = $validateSuccessfulResult(
                    $result,
                    $test,
                    $getRamCapacity,
                    $countBuildDifferences
                );

                $blockingChecks = array_filter(
                    $checks,
                    static fn(array $check): bool =>
                        ($check['diagnostic_only'] ?? false) !== true &&
                        ($check['passed'] ?? false) !== true
                );

                $testResult['checks'] = $checks;
                $testResult['passed'] = empty($blockingChecks);

                if (!empty($result['recommended_build'])) {
                    $recommended = $result['recommended_build'];
                    $ramCapacity = $getRamCapacity($recommended);

                    $testResult['summary'] = [
                        'actual_status' => $actualStatus,
                        'build_id' => $recommended['build_id'] ?? null,
                        'total_price' => $recommended['total_price'] ?? null,
                        'remaining_budget' => $recommended['remaining_budget'] ?? null,
                        'cpu' => $recommended['components']['cpu']['name'] ?? null,
                        'gpu' => $recommended['components']['gpu']['name'] ?? null,
                        'ram_gb' => $ramCapacity,
                        'performance_score' => $recommended['performance_score'] ?? null,
                        'performance_rank' => $recommended['performance_rank'] ?? null,
                        'compatibility_status' => $recommended['compatibility_status'] ?? null,
                        'alternatives_count' => count($result['alternatives'] ?? [])
                    ];
                }
            } else {
                $minimumBudget = $result['minimum_listed_budget'] ?? null;
                $isExpectedStatus = $actualStatus === $test['expected_outcome'];
                $minimumBudgetIsHigher = is_numeric($minimumBudget)
                    && (float) $minimumBudget > $test['budget'];

                $testResult['checks'] = [
                    [
                        'name' => 'Optimizer returns expected failure status',
                        'passed' => $isExpectedStatus,
                        'actual' => $actualStatus,
                        'expected' => $test['expected_outcome']
                    ],
                    [
                        'name' => 'Minimum listed budget exceeds requested budget',
                        'passed' => $minimumBudgetIsHigher,
                        'actual' => $minimumBudget,
                        'expected' => '> ' . $test['budget']
                    ],
                    [
                        'name' => 'No recommended build is returned',
                        'passed' => empty($result['recommended_build']),
                        'actual' => isset($result['recommended_build']),
                        'expected' => false
                    ]
                ];

                $testResult['passed'] = $isExpectedStatus
                    && $minimumBudgetIsHigher
                    && empty($result['recommended_build']);

                $testResult['summary'] = [
                    'actual_status' => $actualStatus,
                    'minimum_listed_budget' => $minimumBudget,
                    'requested_budget' => $test['budget'],
                    'missing_component_types' => $result['missing_component_types'] ?? []
                ];
            }
        } catch (Throwable $exception) {
            $testResult['passed'] = false;
            $testResult['error'] = get_class($exception) . ': ' . $exception->getMessage();
        }

        if ($testResult['passed']) {
            $passedTests++;
        } else {
            $failedTests++;
        }

        $testResults[] = $testResult;
    }

    $output = [
        'success' => $failedTests === 0,
        'test_suite' => 'REGIX PC Builder Integration Tests',
        'executed_at' => date(DATE_ATOM),
        'summary' => [
            'total_tests' => count($tests),
            'passed' => $passedTests,
            'failed' => $failedTests
        ],
        'tests' => $testResults,
        'notes' => [
            'Tests use the real available product catalog through ModelFactory.',
            'Workstation RAM minimum is 32GB; Gaming and General Use minimum is 16GB.',
            'Compatibility status needs_verification is allowed; incompatible is not.',
            'Performance scores are relative benchmark rankings, not predicted FPS.',
            'Alternative diversity is diagnostic only and does not fail the test suite.'
        ]
    ];

    if ($failedTests > 0) {
        http_response_code(422);
    }

    echo json_encode(
        $output,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES |
        JSON_INVALID_UTF8_SUBSTITUTE |
        JSON_THROW_ON_ERROR
    );
} catch (Throwable $exception) {
    http_response_code(500);

    echo json_encode(
        [
            'success' => false,
            'test_suite' => 'REGIX PC Builder Integration Tests',
            'error' => get_class($exception) . ': ' . $exception->getMessage()
        ],
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );
}