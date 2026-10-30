<?php

declare(strict_types=1);
require_once __DIR__ . '/layout.php';

$categories = [
    ['Processors', 'The brains behind every frame.', 'CPU', 'Processors'],
    ['Graphics cards', 'Make every pixel count.', 'GPU', 'Graphics'],
    ['RAM & memory', 'More speed. Less waiting.', 'RAM', 'Memory'],
    ['Storage', 'Load faster. Play longer.', 'SSD', 'Storage'],
];
$rigs = [
    [
        'Titan Ultra',
        'For the uncompromising.',
        '4K ENTHUSIAST',
        'Intel Core i9-14900K',
        'RTX 4090 · 24GB',
        '64GB DDR5',
        'photo-1587202372775-e229f172b9d7',
        'ultra',
        'cyan',
    ],
    [
        'Apex AMD',
        'Find your competitive edge.',
        'COMPETITIVE GAMING',
        'AMD Ryzen 7 7800X3D',
        'RTX 4080 Super · 16GB',
        '32GB DDR5',
        'photo-1591488320449-011701bb6704',
        'apex',
        'purple',
    ],
    [
        'Phantom',
        'Your next chapter starts here.',
        'EVERYDAY GAMING',
        'Intel Core i5-14600K',
        'RTX 4070 Super · 12GB',
        '32GB DDR5',
        'photo-1547082299-de196ea013d6',
        'phantom',
        'pink',
    ],
];

store_page_start('Level up your setup', '', true);
?>
<section class="gaming-hero" aria-labelledby="gaming-title">
    <div class="gaming-hero-copy">
        <span class="gaming-kicker">
            <span class="status-dot">
            </span>
            YOUR NEXT LEVEL STARTS HERE
        </span>
        <h1 id="gaming-title">
            UNLEASH YOUR
            <br>
            <span>
                GAMING BEAST.
            </span>
        </h1>
        <p>
            Big ideas need serious hardware. Discover gaming rigs, powerful components, and the finishing touches for a setup that’s unmistakably yours.
        </p>
        <div class="gaming-actions">
            <a class="button neon-button" href="#builder">
                Plan your build <?= store_icon('arrow') ?>
            </a>
            <a class="button glass-button" href="#rigs">
                Explore builds
            </a>
        </div>
        <div class="hero-specs">
            <div>
                <strong>
                    01 / DISCOVER
                </strong>
                <span>
                    Find your components
                </span>
            </div>
            <div>
                <strong>
                    02 / COMBINE
                </strong>
                <span>
                    Plan your dream setup
                </span>
            </div>
            <div>
                <strong>
                    03 / LEVEL UP
                </strong>
                <span>
                    Make it your own
                </span>
            </div>
        </div>
    </div>
    <div class="gaming-visual">
        <div class="visual-grid" aria-hidden="true">
        </div>
        <figure class="pc-frame">
            <img
                src="https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=900&q=85"
                alt="Gaming desktop with illuminated components"
                width="600"
                height="640"
                fetchpriority="high"
            >
            <figcaption>
                <span class="status-dot">
                </span>
                BUILT FOR YOUR NEXT LEVEL
                <span>
                    REGIX / 01
                </span>
            </figcaption>
        </figure>
        <div class="floating-spec spec-top">
            <?= store_icon('box') ?>
            <div>
                <small>
                    THE POWER TO PLAY
                </small>
                <strong>
                    Graphics. Reimagined.
                </strong>
            </div>
        </div>
        <div class="floating-spec spec-bottom">
            <span class="cooling-symbol" aria-hidden="true">
                ✳
            </span>
            <div>
                <small>
                    KEEP YOUR COOL
                </small>
                <strong>
                    Performance in balance.
                </strong>
            </div>
        </div>
    </div>
</section>
<div class="hardware-strip" aria-label="Hardware focus">
    <span>
        THE FOUNDATION OF A GREAT SETUP
    </span>
    <strong>
        PROCESSORS
    </strong>
    <i>
        +
    </i>
    <strong>
        GRAPHICS
    </strong>
    <i>
        +
    </i>
    <strong>
        MEMORY
    </strong>
    <i>
        +
    </i>
    <strong>
        STORAGE
    </strong>
</div>
<section class="gaming-section" id="categories" aria-labelledby="category-title">
    <div class="gaming-section-heading">
        <div>
            <span class="gaming-kicker">
                EVERY COMPONENT COUNTS
            </span>
            <h2 id="category-title">
                Small parts.
                <span>
                    Big possibilities.
                </span>
            </h2>
        </div>
        <a class="gaming-text-link" href="#categories" data-clear-category-search>
            All categories <?= store_icon('arrow') ?>
        </a>
    </div>
    <p class="landing-category-feedback" id="landing-category-feedback" role="status" hidden></p>
    <div class="hardware-categories">
        <?php foreach ($categories as [$name, $description, $symbol, $query]): ?>
            <a class="hardware-category" href="#builder">
                <span class="component-icon" aria-hidden="true">
                    <span>
                        <?= e($symbol) ?>
                    </span>
                </span>
                <h3>
                    <?= e($name) ?>
                </h3>
                <p>
                    <?= e($description) ?>
                </p>
                <span class="category-arrow">
                    <?= store_icon('arrow') ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<section class="gaming-section" id="rigs" aria-labelledby="rig-title">
    <div class="gaming-section-heading">
        <div>
            <span class="gaming-kicker">
                FIND YOUR PLAYSTYLE
            </span>
            <h2 id="rig-title">
                Meet your next
                <span>
                    battle station.
                </span>
            </h2>
            <p>
                Three build concepts to inspire your next setup.
            </p>
        </div>
        <a class="gaming-text-link" href="#builder">
            Plan your setup <?= store_icon('arrow') ?>
        </a>
    </div>
    <div class="rig-grid">
        <?php foreach ($rigs as [$name, $description, $badge, $cpu, $gpu, $ram, $photo, $preset, $accent]): ?>
            <article class="rig-card accent-<?= e($accent) ?>">
                <div class="rig-image">
                    <img
                        src="https://images.unsplash.com/<?= e($photo) ?>?auto=format&fit=crop&w=650&q=80"
                        alt="<?= e($name) ?> gaming setup inspiration"
                        width="650"
                        height="430"
                        loading="lazy"
                    >
                    <span class="rig-badge">
                        <?= e($badge) ?>
                    </span>
                    <span class="rig-number">
                        0<?= array_search($preset, ['ultra', 'apex', 'phantom'], true) + 1 ?>
                    </span>
                </div>
                <div class="rig-content">
                    <h3>
                        <?= e($name) ?>
                    </h3>
                    <p>
                        <?= e($description) ?>
                    </p>
                    <ul>
                        <li>
                            <span>
                                CPU
                            </span>
                            <?= e($cpu) ?>
                        </li>
                        <li>
                            <span>
                                GPU
                            </span>
                            <?= e($gpu) ?>
                        </li>
                        <li>
                            <span>
                                RAM
                            </span>
                            <?= e($ram) ?>
                        </li>
                    </ul>
                    <div class="rig-bottom">
                        <small>
                            CONCEPT BUILD
                            <br>
                            <span>
                                Explore the possibilities
                            </span>
                        </small>
                        <a class="button rig-button" data-preset="<?= e($preset) ?>" href="#builder">
                            Customize <?= store_icon('arrow') ?>
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<section class="build-planner gaming-section" id="builder" aria-labelledby="builder-title">
    <div class="planner-copy">
        <span class="gaming-kicker">
            YOUR SETUP. YOUR RULES.
        </span>
        <h2 id="builder-title">
            Dream it.
            <br>
            <span>
                Piece it together.
            </span>
        </h2>
        <p>
            Start with the essentials. Explore component combinations and get a feel for your build budget.
        </p>
        <a class="gaming-text-link" href="#rigs">
            Explore build concepts <?= store_icon('arrow') ?>
        </a>
        <div class="planner-note">
            <?= store_icon('box') ?>
            <p>
                This is a planning preview. Prices are illustrative estimates in JOD; availability and compatibility must be checked before buying.
            </p>
        </div>
    </div>
    <div class="planner-panel">
        <div class="planner-panel-top">
            <h3>
                Build planner
            </h3>
            <span>
                01 / CORE COMPONENTS
            </span>
        </div>
        <label for="build-cpu">
            <span>
                01
            </span>
            PROCESSOR
        </label>
        <select id="build-cpu">
            <option value="250">
                Intel Core i5-14600K — 250 JOD
            </option>
            <option value="320">
                AMD Ryzen 7 7800X3D — 320 JOD
            </option>
            <option value="420">
                Intel Core i9-14900K — 420 JOD
            </option>
        </select>
        <label for="build-gpu">
            <span>
                02
            </span>
            GRAPHICS CARD
        </label>
        <select id="build-gpu">
            <option value="450">
                RTX 4070 Super · 12GB — 450 JOD
            </option>
            <option value="750">
                RTX 4080 Super · 16GB — 750 JOD
            </option>
            <option value="1300">
                RTX 4090 · 24GB — 1,300 JOD
            </option>
        </select>
        <label for="build-ram">
            <span>
                03
            </span>
            MEMORY
        </label>
        <select id="build-ram">
            <option value="85">
                32GB DDR5 — 85 JOD
            </option>
            <option value="165">
                64GB DDR5 — 165 JOD
            </option>
        </select>
        <div class="planner-total">
            <div>
                <span>
                    ESTIMATED COMPONENT SUBTOTAL
                </span>
                <strong>
                    <output id="build-total" aria-live="polite">
                        785
                    </output>
                    <small>
                        JOD
                    </small>
                </strong>
            </div>
            <?= store_icon('arrow') ?>
        </div>
        <p class="total-note">
            CPU + GPU + RAM only. Other parts and delivery excluded.
        </p>
        <noscript>
            <p>
                Enable JavaScript to update your build estimate.
            </p>
        </noscript>
    </div>
</section>
<section class="gaming-final">
    <div>
        <span class="gaming-kicker">
            READY WHEN YOU ARE
        </span>
        <h2>
            Your next upgrade
            <span>
                is out there.
            </span>
        </h2>
    </div>
    <a class="button neon-button" href="#builder">
        Start your build <?= store_icon('arrow') ?>
    </a>
</section>
<?php store_page_end(); ?>
