<?php
declare(strict_types=1);

/**
 * REGIX PC Builder - RAM Recommendation Scoring Integration Tests
 *
 * Save to: frontend/User/pc_optimizer_ram_recommendation_test.php
 * Run only from localhost during development.
 */

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

$tests = [];
$passed = 0;
$failed = 0;

$addTest = static function (
    string $name,
    bool $success,
    array $details = []
) use (&$tests, &$passed, &$failed): void {
    $tests[] = [
        'name' => $name,
        'passed' => $success,
        'details' => $details
    ];

    if ($success) {
        $passed++;
    } else {
        $failed++;
    }
};

$expectedRamScore = static function (
    float $actualRam,
    float $minimumRam,
    float $recommendedRam
): float {
    if ($actualRam < $minimumRam) {
        return 0.0;
    }

    if ($actualRam >= $recommendedRam) {
        return 100.0;
    }

    if ($recommendedRam <= $minimumRam) {
        return 100.0;
    }

    return 50.0 + (
        (($actualRam - $minimumRam) / ($recommendedRam - $minimumRam)) * 50.0
    );
};

$runBuildCase = static function (
    $builder,
    string $name,
    float $budget,
    string $purpose,
    ?string $resolution = null
) use ($addTest, $expectedRamScore): array {

    $result = $builder->generateBuild(
        $budget,
        $purpose,
        3,
        $resolution ?? '1440p'
    );

    $addTest(
        $name . ': optimizer returned success',
        ($result['success'] ?? false) === true,
        [
            'actual_success' => $result['success'] ?? false,
            'actual_status' => $result['status'] ?? null,
            'message' => $result['message'] ?? null
        ]
    );

    if (($result['success'] ?? false) !== true) {
        return $result;
    }

    $addTest(
        $name . ': purpose matches request',
        ($result['purpose'] ?? null) === $purpose,
        [
            'actual' => $result['purpose'] ?? null,
            'expected' => $purpose
        ]
    );

    $builds = [];

    if (isset($result['recommended_build']) && is_array($result['recommended_build'])) {
        $builds[] = $result['recommended_build'];
    }

    foreach (($result['alternatives'] ?? []) as $alternative) {
        if (is_array($alternative)) {
            $builds[] = $alternative;
        }
    }

    $requirements = $result['purpose_requirements'] ?? [];
    $minimumRam = (float) ($requirements['minimum_ram_gb'] ?? 0);
    $recommendedRam = (float) ($requirements['recommended_ram_gb'] ?? 0);
    $weights = $result['recommendation_ranking']['weights_percent'] ?? [];
    $weightTotal = (int) ($weights['performance'] ?? 0)
        + (int) ($weights['ram'] ?? 0)
        + (int) ($weights['budget'] ?? 0);

    $addTest(
        $name . ': recommendation weights total 100%',
        $weightTotal === 100,
        [
            'weights' => $weights,
            'actual_total' => $weightTotal,
            'expected_total' => 100
        ]
    );

    $allBuildsHaveRequiredComponents = !empty($builds);
    $allWithinBudget = !empty($builds);
    $allMeetMinimumRam = !empty($builds);
    $allHaveRamScores = !empty($builds);
    $allHaveRecommendationScores = !empty($builds);
    $allHaveValidRecommendationFormula = !empty($builds);
    $allHaveSsdPrimary = !empty($builds);
    $allHaveNoKnownConflict = !empty($builds);
    $scoresInDescendingOrder = true;
    $buildDetails = [];
    $previousScore = null;

    foreach ($builds as $index => $build) {
        $components = $build['components'] ?? [];
        $ram = $components['ram'] ?? [];
        $storage = $components['storage'] ?? [];
        $ramSpecs = $ram['specifications'] ?? [];
        $actualRam = is_numeric($ramSpecs['capacity_gb'] ?? null)
            ? (float) $ramSpecs['capacity_gb']
            : null;

        if (count(array_intersect(
            ['cpu', 'motherboard', 'ram', 'gpu', 'storage', 'psu', 'case', 'cooler'],
            array_keys($components)
        )) !== 8) {
            $allBuildsHaveRequiredComponents = false;
        }

        if ((float) ($build['total_price'] ?? INF) > $budget) {
            $allWithinBudget = false;
        }

        if ($actualRam === null || $actualRam < $minimumRam) {
            $allMeetMinimumRam = false;
        }

        $ramScore = $build['ram_recommendation_score'] ?? null;
        if (!is_numeric($ramScore)) {
            $allHaveRamScores = false;
        } elseif ($actualRam !== null) {
            $expected = $expectedRamScore(
                $actualRam,
                $minimumRam,
                $recommendedRam
            );

            if (abs((float) $ramScore - $expected) > 0.01) {
                $allHaveValidRecommendationFormula = false;
            }
        }

        $recommendationScore = $build['recommendation_score'] ?? null;
        $performanceScore = $build['performance_score'] ?? null;
        $budgetFitScore = $build['budget_fit_score'] ?? null;

        if (
            !is_numeric($recommendationScore) ||
            !is_numeric($performanceScore) ||
            !is_numeric($budgetFitScore) ||
            !is_numeric($ramScore)
        ) {
            $allHaveRecommendationScores = false;
            $allHaveValidRecommendationFormula = false;
        } else {
            $expectedRecommendationScore = (
                ((float) $performanceScore * (float) $weights['performance']) +
                ((float) $ramScore * (float) $weights['ram']) +
                ((float) $budgetFitScore * (float) $weights['budget'])
            ) / 100;

            if (abs((float) $recommendationScore - $expectedRecommendationScore) > 0.02) {
                $allHaveValidRecommendationFormula = false;
            }

            if (
                $previousScore !== null &&
                (float) $recommendationScore > $previousScore + 0.001
            ) {
                $scoresInDescendingOrder = false;
            }

            $previousScore = (float) $recommendationScore;
        }

        $driveType = strtoupper(trim((string) (
            $storage['specifications']['drive_type'] ?? ''
        )));

        if (
            $driveType !== 'SSD' ||
            ($storage['storage_role'] ?? null) !== 'primary'
        ) {
            $allHaveSsdPrimary = false;
        }

        if (($build['compatibility_status'] ?? null) === 'incompatible') {
            $allHaveNoKnownConflict = false;
        }

        $buildDetails[] = [
            'position' => $index + 1,
            'build_id' => $build['build_id'] ?? null,
            'total_price' => $build['total_price'] ?? null,
            'ram_gb' => $actualRam,
            'ram_status' => $build['purpose_requirements']['ram_status'] ?? null,
            'ram_recommendation_score' => $ramScore,
            'performance_score' => $performanceScore,
            'budget_fit_score' => $budgetFitScore,
            'recommendation_score' => $recommendationScore,
            'recommendation_rank' => $build['recommendation_rank'] ?? null,
            'performance_rank' => $build['performance_rank'] ?? null,
            'compatibility_status' => $build['compatibility_status'] ?? null,
            'storage' => $storage['name'] ?? null,
            'storage_drive_type' => $driveType
        ];
    }

    $addTest(
        $name . ': all builds contain the eight required component types',
        $allBuildsHaveRequiredComponents,
        ['builds_checked' => count($builds)]
    );

    $addTest(
        $name . ': every build stays within budget',
        $allWithinBudget,
        ['budget' => $budget]
    );

    $addTest(
        $name . ': every build meets minimum RAM requirement',
        $allMeetMinimumRam,
        ['minimum_ram_gb' => $minimumRam]
    );

    $addTest(
        $name . ': every build has a RAM recommendation score',
        $allHaveRamScores,
        ['expected_scale' => 'minimum=50, recommended=100']
    );

    $addTest(
        $name . ': recommendation score follows configured weights',
        $allHaveRecommendationScores && $allHaveValidRecommendationFormula,
        ['weights' => $weights]
    );

    $addTest(
        $name . ': results are ordered by recommendation score',
        $scoresInDescendingOrder,
        ['builds' => $buildDetails]
    );

    $addTest(
        $name . ': all builds use an SSD as primary storage',
        $allHaveSsdPrimary,
        ['builds' => $buildDetails]
    );

    $addTest(
        $name . ': no build has an explicit compatibility conflict',
        $allHaveNoKnownConflict,
        ['builds' => $buildDetails]
    );

    if ($purpose === 'gaming' && $resolution !== null) {
        $addTest(
            $name . ': requested gaming resolution is preserved',
            ($result['performance_ranking']['resolution'] ?? null) === $resolution,
            [
                'actual' => $result['performance_ranking']['resolution'] ?? null,
                'expected' => $resolution
            ]
        );
    }

    $result['_test_build_details'] = $buildDetails;

    return $result;
};

try {
    $builder = ModelFactory::pcBuilder();

    $gaming = $runBuildCase(
        $builder,
        'Gaming - budget 1200 - 1440p',
        1200.00,
        'gaming',
        '1440p'
    );

    $workstation = $runBuildCase(
        $builder,
        'Workstation - budget 2000',
        2000.00,
        'workstation'
    );

    $generalUse = $runBuildCase(
        $builder,
        'General Use - budget 1200',
        1200.00,
        'general_use'
    );

    // Verify that a very low budget returns a clear failure instead of an incomplete build.
    $lowBudget = $builder->generateBuild(
        100.00,
        'workstation',
        3,
        '1440p'
    );

    $addTest(
        'Workstation - insufficient budget returns budget_insufficient',
        ($lowBudget['success'] ?? true) === false &&
        ($lowBudget['status'] ?? null) === 'budget_insufficient' &&
        empty($lowBudget['recommended_build']),
        [
            'actual_status' => $lowBudget['status'] ?? null,
            'minimum_listed_budget' => $lowBudget['minimum_listed_budget'] ?? null,
            'requested_budget' => 100
        ]
    );

    $output = [
        'success' => $failed === 0,
        'test_suite' => 'REGIX PC Builder - RAM Recommendation Scoring',
        'executed_at' => date(DATE_ATOM),
        'summary' => [
            'total_tests' => count($tests),
            'passed' => $passed,
            'failed' => $failed
        ],
        'ram_scoring_policy' => [
            'gaming' => [
                'minimum_ram_gb' => 16,
                'recommended_ram_gb' => 32,
                'minimum_score' => 50,
                'recommended_score' => 100
            ],
            'workstation' => [
                'minimum_ram_gb' => 32,
                'recommended_ram_gb' => 64,
                'minimum_score' => 50,
                'recommended_score' => 100
            ],
            'general_use' => [
                'minimum_ram_gb' => 16,
                'recommended_ram_gb' => 32,
                'minimum_score' => 50,
                'recommended_score' => 100
            ]
        ],
        'case_summaries' => [
            'gaming' => $gaming['_test_build_details'] ?? [],
            'workstation' => $workstation['_test_build_details'] ?? [],
            'general_use' => $generalUse['_test_build_details'] ?? [],
            'insufficient_budget' => [
                'status' => $lowBudget['status'] ?? null,
                'minimum_listed_budget' => $lowBudget['minimum_listed_budget'] ?? null
            ]
        ],
        'tests' => $tests,
        'notes' => [
            'Tests use the real available product catalog through ModelFactory.',
            'The purpose minimum RAM is a hard filter; recommended RAM is a soft scoring preference.',
            'Recommendation scores combine relative performance, RAM score and budget fit using the weights returned by the optimizer.',
            'This test runner does not modify the database.'
        ]
    ];

    if ($failed > 0) {
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
            'test_suite' => 'REGIX PC Builder - RAM Recommendation Scoring',
            'error' => get_class($exception) . ': ' . $exception->getMessage()
        ],
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );
}
