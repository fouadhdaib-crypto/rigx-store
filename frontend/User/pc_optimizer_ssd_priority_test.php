<?php
declare(strict_types=1);

/**
 * REGIX PC Builder - SSD Primary Storage Policy Test
 *
 * Save to: frontend/User/pc_optimizer_ssd_priority_test.php
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

try {
    // Use the real DAL/BAL catalog through ModelFactory.
    $builder = ModelFactory::pcBuilder();
    $result = $builder->generateBuild(
        2000.00,
        'gaming',
        3,
        '1440p'
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

    $policy = $result['storage_policy'] ?? [];
    $availableSsdCount = (int) ($policy['available_ssd_count'] ?? 0);
    $availableHddCount = (int) ($policy['available_hdd_count'] ?? 0);

    $addTest(
        'A successful build result includes the SSD-primary policy',
        ($result['success'] ?? false) === true &&
        ($policy['primary_drive_type'] ?? null) === 'SSD' &&
        ($policy['selection_mode'] ?? null) === 'ssd_only',
        [
            'success' => $result['success'] ?? false,
            'storage_policy' => $policy
        ]
    );

    $addTest(
        'At least one available SSD exists in the current catalog',
        $availableSsdCount > 0,
        [
            'available_ssd_count' => $availableSsdCount,
            'available_hdd_count' => $availableHddCount
        ]
    );

    $allSelectedStorageAreSsd = !empty($builds);
    $selectedStorageDetails = [];

    foreach ($builds as $index => $build) {
        $storage = $build['components']['storage'] ?? [];
        $driveType = strtoupper(trim((string) (
            $storage['specifications']['drive_type'] ?? ''
        )));
        $storageRole = $storage['storage_role'] ?? null;

        $selectedStorageDetails[] = [
            'build_position' => $index + 1,
            'product_id' => $storage['product_id'] ?? null,
            'name' => $storage['name'] ?? null,
            'drive_type' => $driveType,
            'storage_role' => $storageRole
        ];

        if ($driveType !== 'SSD' || $storageRole !== 'primary') {
            $allSelectedStorageAreSsd = false;
        }
    }

    $addTest(
        'Every returned build uses an SSD as its primary storage',
        $allSelectedStorageAreSsd,
        [
            'builds_checked' => count($builds),
            'selected_storage' => $selectedStorageDetails
        ]
    );

    // Confirm that HDD products are still present in the original store catalog.
    $originalCatalog = $builder->getCatalog();
    $originalHddProducts = array_values(array_filter(
        $originalCatalog['storage'] ?? [],
        static function (array $storage): bool {
            $type = strtolower(trim((string) (
                $storage['specifications']['drive_type'] ?? ''
            )));

            return in_array($type, ['hdd', 'hard disk drive', 'hard-disk drive'], true);
        }
    ));

    $addTest(
        'HDD products remain in the original catalog',
        count($originalHddProducts) > 0 && $availableHddCount === count($originalHddProducts),
        [
            'hdd_products_in_original_catalog' => count($originalHddProducts),
            'hdd_products_reported_by_policy' => $availableHddCount
        ]
    );

    // Synthetic in-memory scenario: HDD exists but no SSD exists.
    // This does not update or mutate the database.
    $hddOnlyCatalog = $originalCatalog;
    $hddOnlyCatalog['storage'] = $originalHddProducts;

    $compatibility = new CompatibilityClass();
    $performanceRanking = new PerformanceRankingClass();
    $optimizer = new BuildOptimizerClass(
        $compatibility,
        $performanceRanking
    );

    $hddOnlyResult = $optimizer->generate(
        $hddOnlyCatalog,
        2000.00,
        'gaming',
        1,
        '1440p'
    );

    $addTest(
        'HDD-only catalog is rejected as a primary-storage build',
        ($hddOnlyResult['success'] ?? true) === false &&
        ($hddOnlyResult['status'] ?? null) === 'primary_ssd_unavailable' &&
        empty($hddOnlyResult['recommended_build']),
        [
            'actual_status' => $hddOnlyResult['status'] ?? null,
            'message' => $hddOnlyResult['message'] ?? null,
            'storage_policy' => $hddOnlyResult['storage_policy'] ?? null
        ]
    );

    $output = [
        'success' => $failed === 0,
        'test_suite' => 'REGIX PC Builder - SSD Primary Storage Policy',
        'executed_at' => date(DATE_ATOM),
        'summary' => [
            'total_tests' => count($tests),
            'passed' => $passed,
            'failed' => $failed
        ],
        'tests' => $tests,
        'notes' => [
            'Normal test uses the real catalog through ModelFactory.',
            'HDD-only test uses an in-memory catalog copy and does not modify the database.',
            'The current builder supports one primary storage slot; secondary HDD selection is a future feature.'
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
            'test_suite' => 'REGIX PC Builder - SSD Primary Storage Policy',
            'error' => get_class($exception) . ': ' . $exception->getMessage()
        ],
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );
}
