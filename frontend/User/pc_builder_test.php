<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/classes/ModelFactory.php';

header('Content-Type: application/json; charset=utf-8');

try {

    $builder = ModelFactory::pcBuilder();

    // Existing product IDs from regix_db
    $selectedIds = [
        1,  // AMD Ryzen 5 7600
        8,  // MSI MAG B650 Tomahawk WiFi
        11, // Kingston Fury Beast 32GB DDR5
        6,  // MSI RTX 4060 Ventus 2X
        14, // Samsung 990 PRO 1TB
        18, // Corsair RM750e 750W
        21, // NZXT H5 Flow
        23  // DeepCool AK400
    ];

    // Load selected products
    $loadedComponents = $builder->getComponents($selectedIds);

    $componentsForCheck = [];
    $componentDetails = [];
    $missingIds = [];

    foreach ($selectedIds as $id) {

        if (!isset($loadedComponents[$id])) {
            $missingIds[] = $id;
            continue;
        }

        $component = $loadedComponents[$id];

        $type = $component['component_type'];

        $componentsForCheck[$type] = $component;

        $componentDetails[] = [
            'id' => $component['product_id'],
            'name' => $component['name'],
            'type' => $type,
            'price' => $component['price'],
            'quantity' => $component['quantity'],
            'spec_status' => $component['spec_status'],
            'spec_data_valid' => $component['spec_data_valid']
        ];
    }

    // Test unavailable product (ID 10 is sold)
    $unavailableProduct = $builder->getComponent(10);

    // Get available catalog
    $catalog = $builder->getCatalog();

    $catalogCounts = [];

    foreach ($catalog as $type => $items) {
        $catalogCounts[$type] = count($items);
    }

    // Run compatibility checks
    $compatibility = $builder->checkCompatibility(
        $componentsForCheck
    );

    echo json_encode([
        'success' => true,

        'catalog_counts' => $catalogCounts,

        'selected_products' => [
            'requested_ids' => $selectedIds,
            'loaded_count' => count($componentDetails),
            'missing_or_unavailable_ids' => $missingIds,
            'components' => $componentDetails
        ],

        'unavailable_product_test' => [
            'product_id' => 10,
            'correctly_rejected' => $unavailableProduct === null
        ],

        'compatibility' => $compatibility

    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Throwable $exception) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $exception->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}