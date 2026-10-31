<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/classes/ModelFactory.php';

/**
 * Escape output for safe HTML rendering.
 */
function pc_builder_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Format store prices. The REGIX store uses JOD pricing.
 */
function pc_builder_money($value): string
{
    return number_format((float) $value, 2) . ' JOD';
}

/**
 * Convert technical purpose values into customer-facing labels.
 */
function pc_builder_purpose_label(string $purpose): string
{
    $labels = [
        'gaming' => 'Gaming',
        'workstation' => 'Workstation',
        'general_use' => 'Everyday use'
    ];

    return $labels[$purpose] ?? 'Custom PC';
}

/**
 * Convert internal compatibility states into clear customer messages.
 */
function pc_builder_compatibility_label(string $status): string
{
    if ($status === 'compatible') {
        return 'Compatibility checks passed';
    }

    if ($status === 'incompatible') {
        return 'Compatibility conflict found';
    }

    return 'Needs verification before purchase';
}

/**
 * Convert internal statuses into customer-facing labels.
 */
function pc_builder_status_label(string $status): string
{
    $labels = [
        'compatible' => 'Passed',
        'incompatible' => 'Conflict',
        'needs_verification' => 'Needs verification',
        'verified' => 'Verified',
        'incomplete' => 'Incomplete',
        'minimum_met' => 'Minimum met',
        'recommended_met' => 'Recommended capacity met'
    ];

    return $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
}

/*
|--------------------------------------------------------------------------
| Form Configuration
|--------------------------------------------------------------------------
*/

$allowedPurposes = [
    'gaming',
    'workstation',
    'general_use'
];

$allowedResolutions = [
    '1080p',
    '1440p',
    '4k'
];

$formValues = [
    'budget' => '1200',
    'purpose' => 'gaming',
    'resolution' => '1440p'
];

$formErrors = [];
$result = null;

$hasSubmitted = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';

/*
|--------------------------------------------------------------------------
| Form Processing
|--------------------------------------------------------------------------
*/

if ($hasSubmitted) {

    $budgetInput = $_POST['budget'] ?? '';
    $purposeInput = $_POST['purpose'] ?? 'gaming';
    $resolutionInput = $_POST['resolution'] ?? '1440p';

    $formValues['budget'] = is_scalar($budgetInput)
        ? trim((string) $budgetInput)
        : '';

    $formValues['purpose'] = is_string($purposeInput)
        ? $purposeInput
        : 'gaming';

    $formValues['resolution'] = is_string($resolutionInput)
        ? $resolutionInput
        : '1440p';

    // Validate budget.
    if (
        $formValues['budget'] === '' ||
        !is_numeric($formValues['budget']) ||
        (float) $formValues['budget'] <= 0
    ) {
        $formErrors[] = 'Enter a valid budget greater than zero.';
    }

    // Validate PC purpose.
    if (!in_array($formValues['purpose'], $allowedPurposes, true)) {
        $formErrors[] = 'Choose a valid PC usage type.';
    }

    // Validate gaming resolution.
    if (!in_array($formValues['resolution'], $allowedResolutions, true)) {
        $formErrors[] = 'Choose a valid gaming resolution.';
    }

    /*
    |--------------------------------------------------------------------------
    | Generate PC Build
    |--------------------------------------------------------------------------
    */

    if (empty($formErrors)) {

        try {

            $builder = ModelFactory::pcBuilder();

            $result = $builder->generateBuild(
                (float) $formValues['budget'],
                $formValues['purpose'],
                3,
                $formValues['resolution']
            );

        } catch (Throwable $exception) {

            error_log(
                '[REGIX PC Builder] ' . $exception->getMessage()
            );

            $result = [
                'success' => false,
                'status' => 'server_error',
                'message' => 'We could not generate a PC build right now. Please try again.'
            ];
        }
    }
}

/*
|--------------------------------------------------------------------------
| Process Build Results
|--------------------------------------------------------------------------
*/

$recommendedBuild = (
    is_array($result) &&
    ($result['success'] ?? false) === true &&
    isset($result['recommended_build']) &&
    is_array($result['recommended_build'])
)
    ? $result['recommended_build']
    : null;

$alternatives = (
    is_array($result) &&
    isset($result['alternatives']) &&
    is_array($result['alternatives'])
)
    ? $result['alternatives']
    : [];

/*
|--------------------------------------------------------------------------
| Component Labels
|--------------------------------------------------------------------------
*/

$componentLabels = [
    'cpu' => 'Processor',
    'motherboard' => 'Motherboard',
    'ram' => 'Memory',
    'gpu' => 'Graphics card',
    'storage' => 'Primary storage',
    'psu' => 'Power supply',
    'case' => 'PC case',
    'cooler' => 'CPU cooling'
];

$componentIcons = [
    'cpu' => 'CPU',
    'motherboard' => 'MB',
    'ram' => 'RAM',
    'gpu' => 'GPU',
    'storage' => 'SSD',
    'psu' => 'PSU',
    'case' => 'CASE',
    'cooler' => 'COOL'
];

/*
|--------------------------------------------------------------------------
| Load Existing Customer Layout
|--------------------------------------------------------------------------
|
| No changes to frontend/User/layout.php are required.
|
*/

require_once __DIR__ . '/layout.php';

/*
|--------------------------------------------------------------------------
| Load PC Builder CSS and JavaScript
|--------------------------------------------------------------------------
|
| Assets are injected into the <head> generated by store_page_start().
| This avoids changing the existing layout function.
|
*/

$pcBuilderCssPath = __DIR__ . '/../css/pc_builder.css';
$pcBuilderJsPath = __DIR__ . '/../js/pc_builder.js';

$pcBuilderCssVersion = is_file($pcBuilderCssPath)
    ? (string) filemtime($pcBuilderCssPath)
    : '1';

$pcBuilderJsVersion = is_file($pcBuilderJsPath)
    ? (string) filemtime($pcBuilderJsPath)
    : '1';

$pcBuilderAssets =
    '<link rel="stylesheet" href="../css/pc_builder.css?v='
    . $pcBuilderCssVersion . '">' . PHP_EOL .

    '<script src="../js/pc_builder.js?v='
    . $pcBuilderJsVersion . '" defer></script>' . PHP_EOL;

/*
|--------------------------------------------------------------------------
| Render Existing Store Header and Main Opening
|--------------------------------------------------------------------------
*/

ob_start();

store_page_start('PC Builder');

$storePageStart = (string) ob_get_clean();

/*
|--------------------------------------------------------------------------
| Inject Page-Specific Assets into Existing Head
|--------------------------------------------------------------------------
*/

$headPosition = stripos($storePageStart, '</head>');

if ($headPosition !== false) {

    $storePageStart = substr_replace(
        $storePageStart,
        $pcBuilderAssets,
        $headPosition,
        0
    );
}

echo $storePageStart;

?>

<!-- =========================================================
     PC BUILDER PAGE
========================================================= -->

<div class="pc-builder-page">

    <div class="pb-main">

        <!-- =====================================================
             HERO SECTION
        ====================================================== -->

        <section class="pb-hero">

            <div class="pb-hero-copy">

                <div class="pb-eyebrow">
                    <span class="pb-eyebrow-dot"></span>
                    REGIX CUSTOM BUILDER
                </div>

                <h1>
                    Build a PC that<br>
                    <span>fits your purpose.</span>
                </h1>

                <p>
                    Set your budget, choose what you use your PC for,
                    and get a build assembled from available REGIX components.
                </p>

                <div class="pb-hero-points">

                    <span>
                        <i>✓</i>
                        Live store availability
                    </span>

                    <span>
                        <i>✓</i>
                        Compatibility checks
                    </span>

                    <span>
                        <i>✓</i>
                        Performance-based ranking
                    </span>

                </div>

            </div>

            <div class="pb-hero-visual" aria-hidden="true">

                <div class="pb-visual-glow"></div>

                <div class="pb-visual-orbit pb-orbit-one"></div>

                <div class="pb-visual-orbit pb-orbit-two"></div>

                <div class="pb-visual-chip">

                    <span class="pb-chip-corner top-left"></span>
                    <span class="pb-chip-corner top-right"></span>
                    <span class="pb-chip-corner bottom-left"></span>
                    <span class="pb-chip-corner bottom-right"></span>

                    <div class="pb-chip-inner">

                        <span class="pb-chip-mark">R</span>

                        <strong>REGIX</strong>

                        <small>PC BUILDER</small>

                    </div>

                </div>

                <span class="pb-floating-tag tag-performance">
                    <span></span>
                    PERFORMANCE FIRST
                </span>

                <span class="pb-floating-tag tag-compatible">
                    ✓ COMPATIBILITY CHECKS
                </span>

            </div>

        </section>


        <!-- =====================================================
             BUILDER FORM SECTION
        ====================================================== -->

        <section class="pb-builder-layout" id="builder-form">

            <!-- Form Column -->

            <div class="pb-form-column">

                <div class="pb-section-heading">

                    <div>

                        <span class="pb-section-kicker">
                            START YOUR BUILD
                        </span>

                        <h2>Tell us what you need.</h2>

                        <p>
                            We’ll match your requirements with products currently in stock.
                        </p>

                    </div>

                    <span class="pb-step-indicator">
                        <b>01</b> / 02
                    </span>

                </div>


                <!-- Form Validation Errors -->

                <?php if (!empty($formErrors)): ?>

                    <div class="pb-alert pb-alert-error" role="alert">

                        <strong>Please check your choices.</strong>

                        <?php foreach ($formErrors as $error): ?>

                            <span>
                                <?= pc_builder_e($error) ?>
                            </span>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>


                <!-- PC Builder Form -->

                <form method="POST" action="pc_builder.php#build-results" class="pb-form" id="pcBuilderForm">

                    <!-- Budget -->

                    <div class="pb-form-block">

                        <div class="pb-field-heading">

                            <span class="pb-field-number">01</span>

                            <div>

                                <label for="pbBudget">
                                    What’s your budget?
                                </label>

                                <small>
                                    Enter the maximum amount you want to spend.
                                </small>

                            </div>

                        </div>

                        <div class="pb-budget-input-wrap">

                            <span class="pb-currency">
                                JOD
                            </span>

                            <input type="number" id="pbBudget" name="budget" min="1" step="0.01" max="1000000"
                                value="<?= pc_builder_e($formValues['budget']) ?>" placeholder="e.g. 1200" required>

                        </div>

                        <p class="pb-field-hint">
                            The recommended build and alternatives will stay within this budget.
                        </p>

                    </div>


                    <div class="pb-form-divider"></div>


                    <!-- PC Purpose -->

                    <fieldset class="pb-form-block pb-purpose-fieldset">

                        <legend class="pb-field-heading">

                            <span class="pb-field-number">02</span>

                            <span>

                                <strong>
                                    What will you use it for?
                                </strong>

                                <small>
                                    Choose the primary purpose of your PC.
                                </small>

                            </span>

                        </legend>


                        <div class="pb-purpose-options">

                            <!-- Gaming -->

                            <label
                                class="pb-purpose-option <?= $formValues['purpose'] === 'gaming' ? 'selected' : '' ?>">

                                <input type="radio" name="purpose" value="gaming" <?= $formValues['purpose'] === 'gaming' ? 'checked' : '' ?> required>

                                <span class="pb-purpose-icon" aria-hidden="true">
                                    Ｇ
                                </span>

                                <span class="pb-purpose-copy">

                                    <strong>Gaming</strong>

                                    <small>
                                        Games and high frame rates
                                    </small>

                                </span>

                                <span class="pb-radio-mark"></span>

                            </label>


                            <!-- Workstation -->

                            <label
                                class="pb-purpose-option <?= $formValues['purpose'] === 'workstation' ? 'selected' : '' ?>">

                                <input type="radio" name="purpose" value="workstation"
                                    <?= $formValues['purpose'] === 'workstation' ? 'checked' : '' ?>>

                                <span class="pb-purpose-icon" aria-hidden="true">
                                    ⌘
                                </span>

                                <span class="pb-purpose-copy">

                                    <strong>Workstation</strong>

                                    <small>
                                        Heavy workloads and multitasking
                                    </small>

                                </span>

                                <span class="pb-radio-mark"></span>

                            </label>


                            <!-- Everyday Use -->

                            <label
                                class="pb-purpose-option <?= $formValues['purpose'] === 'general_use' ? 'selected' : '' ?>">

                                <input type="radio" name="purpose" value="general_use"
                                    <?= $formValues['purpose'] === 'general_use' ? 'checked' : '' ?>>

                                <span class="pb-purpose-icon" aria-hidden="true">
                                    ▦
                                </span>

                                <span class="pb-purpose-copy">

                                    <strong>Everyday use</strong>

                                    <small>
                                        Study, browsing and daily tasks
                                    </small>

                                </span>

                                <span class="pb-radio-mark"></span>

                            </label>

                        </div>

                    </fieldset>


                    <!-- Gaming Resolution -->

                    <div class="pb-resolution-group" id="pbResolutionGroup" <?= $formValues['purpose'] !== 'gaming' ? 'hidden' : '' ?>>

                        <label for="pbResolution">
                            Preferred gaming resolution
                        </label>

                        <div class="pb-resolution-options">

                            <label>

                                <input type="radio" name="resolution" value="1080p"
                                    <?= $formValues['resolution'] === '1080p' ? 'checked' : '' ?>>

                                <span>1080p</span>

                            </label>


                            <label>

                                <input type="radio" name="resolution" value="1440p"
                                    <?= $formValues['resolution'] === '1440p' ? 'checked' : '' ?>>

                                <span>1440p</span>

                            </label>


                            <label>

                                <input type="radio" name="resolution" value="4k" <?= $formValues['resolution'] === '4k' ? 'checked' : '' ?>>

                                <span>4K</span>

                            </label>

                        </div>

                        <p class="pb-field-hint">
                            Resolution changes which GPU gaming benchmark is used for ranking.
                        </p>

                    </div>


                    <!-- Submit -->

                    <button type="submit" class="pb-submit-button" id="pbGenerateButton">

                        <span>
                            Generate my PC build
                        </span>

                        <span class="pb-submit-arrow" aria-hidden="true">
                            →
                        </span>

                    </button>

                    <p class="pb-submit-note">

                        <span>✦</span>

                        Builds are generated from available store inventory
                        and checked for known compatibility conflicts.

                    </p>

                </form>

            </div>


            <!-- =================================================
                 INFORMATION COLUMN
            ================================================== -->

            <aside class="pb-info-column">

                <!-- How it works -->

                <div class="pb-info-card pb-process-card">

                    <span class="pb-info-label">
                        HOW IT WORKS
                    </span>

                    <h3>
                        More than a parts list.
                    </h3>

                    <p>
                        Your build is assembled through a set of checks and ranking steps.
                    </p>


                    <div class="pb-process-list">

                        <div class="pb-process-item">

                            <span class="pb-process-icon">
                                01
                            </span>

                            <div>

                                <strong>
                                    Find available parts
                                </strong>

                                <small>
                                    Only products currently in stock.
                                </small>

                            </div>

                        </div>


                        <div class="pb-process-item">

                            <span class="pb-process-icon">
                                02
                            </span>

                            <div>

                                <strong>
                                    Check compatibility
                                </strong>

                                <small>
                                    Known conflicts are excluded.
                                </small>

                            </div>

                        </div>


                        <div class="pb-process-item">

                            <span class="pb-process-icon">
                                03
                            </span>

                            <div>

                                <strong>
                                    Rank performance
                                </strong>

                                <small>
                                    Using available benchmark data.
                                </small>

                            </div>

                        </div>


                        <div class="pb-process-item">

                            <span class="pb-process-icon">
                                04
                            </span>

                            <div>

                                <strong>
                                    Respect your budget
                                </strong>

                                <small>
                                    Compare suitable build alternatives.
                                </small>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Inventory Information -->

                <div class="pb-info-card pb-inventory-card">

                    <div class="pb-inventory-icon">
                        ✳
                    </div>

                    <div>

                        <strong>
                            Built from REGIX inventory
                        </strong>

                        <p>
                            Component availability and pricing are read from
                            the live store catalog when you generate a build.
                        </p>

                    </div>

                </div>


                <!-- Disclaimer -->

                <div class="pb-disclaimer">

                    <span>ⓘ</span>

                    <p>
                        Some products may need specification verification.
                        Always review compatibility notes before purchasing.
                    </p>

                </div>

            </aside>

        </section>


        <!-- =====================================================
             BUILD RESULTS
        ====================================================== -->

        <?php if ($hasSubmitted && $result !== null): ?>

            <section class="pb-results-section" id="build-results" aria-live="polite">

                <!-- Results Heading -->

                <div class="pb-results-heading">

                    <div>

                        <span class="pb-section-kicker">
                            YOUR PC BUILD RESULTS
                        </span>

                        <h2>
                            <?= ($result['success'] ?? false) === true
                                ? 'Your recommended build.'
                                : 'We couldn’t complete this build.' ?>
                        </h2>

                        <p>

                            <?= pc_builder_e(
                                pc_builder_purpose_label($formValues['purpose'])
                            ) ?>

                            <?php if ($formValues['purpose'] === 'gaming'): ?>

                                · <?= pc_builder_e(
                                    strtoupper($formValues['resolution'])
                                ) ?>

                            <?php endif; ?>

                            · Budget
                            <?= pc_builder_e(
                                pc_builder_money($formValues['budget'])
                            ) ?>

                        </p>

                    </div>

                    <a href="pc_builder.php#builder-form" class="pb-edit-link">
                        Edit requirements <span>↗</span>
                    </a>

                </div>


                <!-- Error Result -->

                <?php if (!empty($formErrors)): ?>

                    <div class="pb-alert pb-alert-error" role="alert">
                        Please correct the form values above.
                    </div>


                <?php elseif (($result['success'] ?? false) !== true): ?>

                    <div class="pb-result-error" role="status">

                        <div class="pb-result-error-icon">
                            !
                        </div>

                        <div>

                            <strong>
                                <?= pc_builder_e(
                                    $result['message'] ?? 'No build could be generated.'
                                ) ?>
                            </strong>


                            <?php if (isset($result['minimum_listed_budget'])): ?>

                                <p>
                                    Minimum listed budget for the current requirements:

                                    <b>
                                        <?= pc_builder_e(
                                            pc_builder_money($result['minimum_listed_budget'])
                                        ) ?>
                                    </b>
                                </p>

                            <?php endif; ?>


                            <?php if (!empty($result['missing_component_types'])): ?>

                                <p>
                                    Unavailable or missing categories:

                                    <?= pc_builder_e(
                                        implode(', ', $result['missing_component_types'])
                                    ) ?>
                                </p>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- Successful Result -->

                <?php elseif ($recommendedBuild !== null): ?>

                    <?php

                    $recommendedComponents = $recommendedBuild['components'] ?? [];

                    $performanceScore = $recommendedBuild['performance_score'] ?? null;

                    $recommendationScore = $recommendedBuild['recommendation_score'] ?? null;

                    $compatibilityStatus = (string) (
                        $recommendedBuild['compatibility_status'] ?? 'needs_verification'
                    );

                    $ramRequirements = $recommendedBuild['purpose_requirements'] ?? [];

                    $compatibilityChecks = $recommendedBuild['compatibility_checks'] ?? [];

                    $componentsNeedingVerification =
                        $recommendedBuild['components_needing_verification'] ?? [];

                    $performanceMetrics = $recommendedBuild['performance_metrics'] ?? [];

                    $storagePolicy = $result['storage_policy'] ?? null;

                    ?>


                    <!-- Recommended Build Card -->

                    <article class="pb-recommended-card">

                        <div class="pb-recommended-topline">

                            <span class="pb-recommended-badge">
                                <i></i>
                                RECOMMENDED BUILD
                            </span>

                            <span class="pb-build-id">
                                BUILD #
                                <?= pc_builder_e($recommendedBuild['build_id'] ?? '—') ?>
                            </span>

                        </div>


                        <!-- Build Overview -->

                        <div class="pb-build-overview">

                            <div class="pb-build-overview-copy">

                                <h3>
                                    <?= pc_builder_e(
                                        pc_builder_purpose_label($formValues['purpose'])
                                    ) ?>
                                    PC
                                </h3>

                                <p>
                                    Selected from available products based on your
                                    budget, requirements and benchmark data.
                                </p>

                            </div>


                            <div class="pb-build-price">

                                <span>
                                    BUILD TOTAL
                                </span>

                                <strong>
                                    <?= pc_builder_e(
                                        pc_builder_money($recommendedBuild['total_price'] ?? 0)
                                    ) ?>
                                </strong>

                                <small>
                                    <?= pc_builder_e(
                                        pc_builder_money($recommendedBuild['remaining_budget'] ?? 0)
                                    ) ?>
                                    remaining
                                </small>

                            </div>

                        </div>


                        <!-- Build Metrics -->

                        <div class="pb-build-metrics">

                            <div class="pb-metric-card">

                                <span>
                                    Performance score
                                </span>

                                <strong>
                                    <?= is_numeric($performanceScore)
                                        ? pc_builder_e(
                                            number_format((float) $performanceScore, 2)
                                        ) . '<small>/100</small>'
                                        : 'Not available' ?>
                                </strong>

                                <small>
                                    <?= pc_builder_e(
                                        $recommendedBuild['performance_scope']
                                        ?? 'Relative benchmark ranking'
                                    ) ?>
                                </small>

                            </div>


                            <div class="pb-metric-card">

                                <span>
                                    Recommendation score
                                </span>

                                <strong>
                                    <?= is_numeric($recommendationScore)
                                        ? pc_builder_e(
                                            number_format((float) $recommendationScore, 2)
                                        ) . '<small>/100</small>'
                                        : 'Not available' ?>
                                </strong>

                                <small>
                                    Performance, memory and budget fit
                                </small>

                            </div>


                            <div class="pb-metric-card">

                                <span>
                                    Compatibility
                                </span>

                                <strong
                                    class="pb-status-text <?= $compatibilityStatus === 'compatible' ? 'is-good' : 'is-warning' ?>">

                                    <?= pc_builder_e(
                                        pc_builder_status_label($compatibilityStatus)
                                    ) ?>

                                </strong>

                                <small>
                                    <?= pc_builder_e(
                                        pc_builder_compatibility_label($compatibilityStatus)
                                    ) ?>
                                </small>

                            </div>


                            <div class="pb-metric-card">

                                <span>
                                    RAM capacity
                                </span>

                                <strong>

                                    <?= pc_builder_e(
                                        $ramRequirements['actual_ram_gb']
                                        ?? (
                                            $recommendedComponents['ram']['specifications']['capacity_gb']
                                            ?? '—'
                                        )
                                    ) ?>

                                    <small>GB</small>

                                </strong>

                                <small>
                                    <?= pc_builder_e(
                                        pc_builder_status_label(
                                            (string) ($ramRequirements['ram_status'] ?? 'unknown')
                                        )
                                    ) ?>
                                </small>

                            </div>

                        </div>


                        <!-- Selected Components -->

                        <div class="pb-components-heading">

                            <div>

                                <span class="pb-section-kicker">
                                    THE COMPONENTS
                                </span>

                                <h4>
                                    Your selected parts
                                </h4>

                            </div>

                            <span>
                                <?= count($recommendedComponents) ?> components
                            </span>

                        </div>


                        <div class="pb-components-grid">

                            <?php foreach ($componentLabels as $type => $label): ?>

                                <?php if (!isset($recommendedComponents[$type])) {
                                    continue;
                                } ?>

                                <?php $component = $recommendedComponents[$type]; ?>


                                <article class="pb-component-card">

                                    <div class="pb-component-card-top">

                                        <span class="pb-component-icon">
                                            <?= pc_builder_e($componentIcons[$type]) ?>
                                        </span>

                                        <span class="pb-component-type">
                                            <?= pc_builder_e($label) ?>
                                        </span>


                                        <?php if (($component['spec_status'] ?? '') === 'verified'): ?>

                                            <span class="pb-mini-check" title="Specifications verified">
                                                ✓
                                            </span>

                                        <?php else: ?>

                                            <span class="pb-mini-warning" title="Specifications need verification">
                                                !
                                            </span>

                                        <?php endif; ?>

                                    </div>


                                    <h5>
                                        <?= pc_builder_e(
                                            $component['name'] ?? 'Unknown component'
                                        ) ?>
                                    </h5>


                                    <div class="pb-component-meta">

                                        <span>
                                            <?= pc_builder_e($component['brand'] ?? '') ?>
                                        </span>

                                        <span>
                                            <?= pc_builder_e(
                                                ucfirst((string) ($component['condition_type'] ?? ''))
                                            ) ?>
                                        </span>

                                    </div>


                                    <div class="pb-component-price">
                                        <?= pc_builder_e(
                                            pc_builder_money($component['price'] ?? 0)
                                        ) ?>
                                    </div>

                                </article>

                            <?php endforeach; ?>

                        </div>


                        <!-- Components Needing Verification -->

                        <?php if (!empty($componentsNeedingVerification)): ?>

                            <div class="pb-verification-notice">

                                <div class="pb-notice-symbol">
                                    !
                                </div>

                                <div>

                                    <strong>
                                        Some specifications need verification
                                    </strong>

                                    <p>
                                        These products have incomplete specification
                                        records. Review them before purchasing.
                                    </p>

                                    <ul>

                                        <?php foreach ($componentsNeedingVerification as $item): ?>

                                            <li>

                                                <?= pc_builder_e($item['name'] ?? 'Component') ?>

                                                —

                                                <?= pc_builder_e(
                                                    ucwords(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            (string) ($item['component_type'] ?? '')
                                                        )
                                                    )
                                                ) ?>

                                            </li>

                                        <?php endforeach; ?>

                                    </ul>

                                </div>

                            </div>

                        <?php endif; ?>


                        <!-- Compatibility Checks -->

                        <?php if (!empty($compatibilityChecks)): ?>

                            <details class="pb-checks-details">

                                <summary>

                                    View compatibility check details

                                    <span>
                                        <?= count($compatibilityChecks) ?> checks
                                    </span>

                                </summary>


                                <div class="pb-checks-list">

                                    <?php foreach ($compatibilityChecks as $check): ?>

                                        <?php
                                        $checkStatus = (string) (
                                            $check['status'] ?? 'needs_verification'
                                        );
                                        ?>

                                        <div class="pb-check-row">

                                            <span class="pb-check-indicator <?= pc_builder_e($checkStatus) ?>"></span>

                                            <span class="pb-check-name">

                                                <?= pc_builder_e(
                                                    ucwords(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            (string) ($check['code'] ?? 'Compatibility check')
                                                        )
                                                    )
                                                ) ?>

                                            </span>

                                            <span class="pb-check-message">
                                                <?= pc_builder_e($check['message'] ?? '') ?>
                                            </span>

                                            <span class="pb-check-status <?= pc_builder_e($checkStatus) ?>">

                                                <?= pc_builder_e(
                                                    pc_builder_status_label($checkStatus)
                                                ) ?>

                                            </span>

                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            </details>

                        <?php endif; ?>


                        <!-- Storage Policy -->

                        <?php if (is_array($storagePolicy)): ?>

                            <p class="pb-policy-note">
                                Primary storage policy: SSD selected.
                                Secondary HDD selection is not part of this build step yet.
                            </p>

                        <?php endif; ?>

                    </article>


                    <!-- =================================================
                         ALTERNATIVE BUILDS
                    ================================================== -->

                    <?php if (!empty($alternatives)): ?>

                        <div class="pb-alternatives-heading">

                            <div>

                                <span class="pb-section-kicker">
                                    OTHER OPTIONS
                                </span>

                                <h3>
                                    Compare alternatives.
                                </h3>

                                <p>
                                    Different component combinations that also fit your requirements.
                                </p>

                            </div>

                            <span class="pb-alternative-count">
                                <?= count($alternatives) ?> alternatives
                            </span>

                        </div>


                        <div class="pb-alternatives-grid">

                            <?php foreach ($alternatives as $alternativeIndex => $alternative): ?>

                                <?php

                                $alternativeComponents = $alternative['components'] ?? [];

                                $alternativeRam = $alternative['purpose_requirements'] ?? [];

                                ?>


                                <article class="pb-alternative-card">

                                    <div class="pb-alternative-top">

                                        <span>
                                            OPTION <?= (int) ($alternativeIndex + 1) ?>
                                        </span>

                                        <strong>

                                            <?= is_numeric($alternative['recommendation_score'] ?? null)
                                                ? pc_builder_e(
                                                    number_format(
                                                        (float) $alternative['recommendation_score'],
                                                        2
                                                    )
                                                )
                                                : '—' ?>

                                            <small>
                                                score
                                            </small>

                                        </strong>

                                    </div>


                                    <div class="pb-alternative-price">

                                        <?= pc_builder_e(
                                            pc_builder_money($alternative['total_price'] ?? 0)
                                        ) ?>

                                    </div>


                                    <div class="pb-alternative-specs">

                                        <div>

                                            <span>
                                                Processor
                                            </span>

                                            <strong>
                                                <?= pc_builder_e(
                                                    $alternativeComponents['cpu']['name'] ?? '—'
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Graphics
                                            </span>

                                            <strong>
                                                <?= pc_builder_e(
                                                    $alternativeComponents['gpu']['name'] ?? '—'
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Memory
                                            </span>

                                            <strong>

                                                <?= pc_builder_e(
                                                    $alternativeRam['actual_ram_gb']
                                                    ?? (
                                                        $alternativeComponents['ram']['specifications']['capacity_gb']
                                                        ?? '—'
                                                    )
                                                ) ?>GB

                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Storage
                                            </span>

                                            <strong>
                                                <?= pc_builder_e(
                                                    $alternativeComponents['storage']['name'] ?? '—'
                                                ) ?>
                                            </strong>

                                        </div>

                                    </div>


                                    <div class="pb-alternative-footer">

                                        <span
                                            class="<?= ($alternative['compatibility_status'] ?? '') === 'compatible' ? 'is-good' : 'is-warning' ?>">

                                            <?= pc_builder_e(
                                                pc_builder_status_label(
                                                    (string) (
                                                        $alternative['compatibility_status']
                                                        ?? 'needs_verification'
                                                    )
                                                )
                                            ) ?>

                                        </span>

                                        <span>
                                            <?= (int) ($alternative['verified_components'] ?? 0) ?>/8 verified
                                        </span>

                                    </div>

                                </article>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>


                    <!-- Results Disclaimer -->

                    <div class="pb-results-footnote">

                        <span>ⓘ</span>

                        <p>
                            Performance scores are relative rankings based on the benchmark
                            data currently available. They are not predicted FPS for the
                            complete PC. A build marked “Needs verification” should be
                            reviewed before purchase.
                        </p>

                    </div>

                <?php endif; ?>

            </section>

        <?php endif; ?>


        <!-- =====================================================
             BOTTOM BANNER
        ====================================================== -->

        <section class="pb-bottom-banner">

            <div>

                <span class="pb-section-kicker">
                    NEED A DIFFERENT CONFIGURATION?
                </span>

                <h2>
                    Your build should work for you.
                </h2>

                <p>
                    Adjust your budget or purpose and generate another set of recommendations.
                </p>

            </div>

            <a href="pc_builder.php#builder-form">
                Start again <span>→</span>
            </a>

        </section>

    </div><!-- /.pb-main -->

</div><!-- /.pc-builder-page -->


<?php

/*
|--------------------------------------------------------------------------
| Close Existing Customer Layout
|--------------------------------------------------------------------------
|
| This closes the main element and renders the existing store dialogs,
| footer and closing HTML tags.
|
*/

store_page_end();

?>