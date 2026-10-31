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

store_page_start('Next level. No limits.', '', true);
?>
<div class="regix-intro" id="regix-intro" hidden aria-hidden="true"><span>REGIX / NEXT LEVEL SHOPPING</span><button type="button" id="skip-regix-intro">Skip intro ↗</button><strong><span id="regix-load-count">0</span><em>%</em></strong></div>
<section class="campaign-hero" id="top" aria-labelledby="campaign-title">
    <div class="campaign-hero-top">
        <p class="campaign-roles"><span>PERFORMANCE.</span><span>PRECISION.</span><span>POSSIBILITY.</span></p>
        <div class="campaign-hero-note"><span class="campaign-label">HARDWARE FOR WHAT’S NEXT</span><p>For the late-night players. The big-idea creators. The ones who never settle for the ordinary.</p><a href="#rigs" class="campaign-link">Find your next level <?= store_icon('arrow') ?></a></div>
    </div>
    <h1 class="campaign-hero-title" id="campaign-title" aria-label="Next level."><span class="hero-word" aria-hidden="true"><span>N</span><span>E</span><span>X</span><span>T</span></span><span class="hero-word" aria-hidden="true"><span>L</span><span>E</span><span>V</span><span>E</span><span>L</span><span>.</span></span></h1>
    <figure class="campaign-hero-product campaign-liquid" data-bare><img src="../assets/regix-hero-tower.png" alt="Black gaming tower with lime illuminated cooling fans" width="1024" height="1536" fetchpriority="high"></figure>
    <div class="campaign-hero-base" aria-hidden="true"></div>
    <div class="campaign-hero-bottom"><span>REGIX — HARDWARE WITH INTENT.</span><a href="#story">SCROLL TO EXPLORE <span>↓</span></a><span>AMMAN, JO / JOD</span></div>
</section>
<section class="campaign-marquee" aria-label="Play. Create. Go beyond."><div class="marquee-track" aria-hidden="true"><?php for ($i = 0; $i < 2; $i++): ?><div><span>PLAY.</span><span class="marquee-outline">CREATE.</span><span>GO BEYOND.</span><span class="marquee-star">✳</span></div><?php endfor; ?></div><button type="button" id="campaign-motion-toggle" aria-pressed="false">Pause motion Ⅱ</button></section>
<section class="campaign-story campaign-section" id="story">
    <div class="campaign-section-heading campaign-reveal"><span class="campaign-label">01 / THE REGIX MINDSET</span><h2>GREAT SETUPS<br>DON’T HAPPEN<br><span>BY ACCIDENT.</span></h2></div>
    <div class="campaign-story-copy campaign-reveal"><p class="campaign-lead">Every great session starts long before you press play.</p><p>It starts with the right parts. A considered choice. A setup that feels like an extension of you. REGIX brings the possibilities together, from your first upgrade to your next ambitious build.</p><div class="campaign-principles"><article><span>01</span><div><h3>Choose with intent.</h3><p>Build around what you actually do. Play, create, work, or a little of everything.</p></div></article><article><span>02</span><div><h3>Make room for more.</h3><p>Find components that fit your plans today and leave possibilities for tomorrow.</p></div></article><article><span>03</span><div><h3>Make it yours.</h3><p>New hardware or a pre-loved find. Your next setup doesn’t have to look like anyone else’s.</p></div></article></div></div>
</section>
<section class="campaign-categories campaign-section" id="categories" aria-labelledby="category-title">
    <div class="campaign-section-heading campaign-reveal"><div><span class="campaign-label">02 / THE BUILDING BLOCKS</span><h2 id="category-title">SMALL PARTS.<br><span>BIG POSSIBILITIES.</span></h2></div><a class="campaign-link" href="#categories" data-clear-category-search>All categories <?= store_icon('arrow') ?></a></div>
    <p class="landing-category-feedback" id="landing-category-feedback" role="status" hidden></p>
    <div class="campaign-category-list"><?php foreach ($categories as $i => [$name, $description, $symbol, $query]): ?><a class="hardware-category campaign-reveal" href="#builder"><span class="campaign-category-number">0<?= $i + 1 ?></span><span class="campaign-category-name"><?= e($name) ?></span><span class="campaign-category-description"><?= e($description) ?></span><span class="campaign-category-symbol"><?= e($symbol) ?></span><?= store_icon('arrow') ?></a><?php endforeach; ?></div>
</section>
<section class="campaign-rigs campaign-section" id="rigs" aria-labelledby="rig-title">
    <div class="campaign-section-heading campaign-reveal"><div><span class="campaign-label">03 / SELECTED BUILD CONCEPTS</span><h2 id="rig-title">THE NEXT THING.<br><span>YOUR THING.</span></h2></div><p>03 concepts. Countless possibilities.<br>Illustrative configurations to spark your next build.</p></div>
    <div class="campaign-rig-grid"><?php foreach ($rigs as $i => [$name, $description, $badge, $cpu, $gpu, $ram, $photo, $preset, $accent]): ?><article class="campaign-rig campaign-reveal"><a class="campaign-rig-image campaign-liquid" href="#builder" data-preset="<?= e($preset) ?>" aria-label="Plan the <?= e($name) ?> build"><img src="https://images.unsplash.com/<?= e($photo) ?>?auto=format&fit=crop&w=1100&q=85" alt="<?= e($name) ?> gaming setup inspiration" width="1100" height="760" loading="lazy"><span class="campaign-image-index">0<?= $i + 1 ?> / REGIX CONCEPT</span><span class="campaign-image-arrow"><?= store_icon('arrow') ?></span></a><div class="campaign-rig-caption"><div><h3><?= e($name) ?></h3><p><?= e($description) ?></p></div><span><?= e($badge) ?></span></div><p class="campaign-rig-spec"><?= e($cpu) ?> / <?= e($gpu) ?> / <?= e($ram) ?></p></article><?php endforeach; ?><article class="campaign-custom-rig campaign-reveal"><span class="campaign-label">THE MOST IMPORTANT BUILD IS YOURS.</span><h3>YOUR RULES.<br>YOUR RIG.</h3><p>Start with a concept. Make it personal.</p><a href="#builder" class="campaign-link">Open the build planner <?= store_icon('arrow') ?></a><span class="campaign-custom-cross" aria-hidden="true">✳</span></article></div>
</section>
<section class="campaign-playstyles campaign-section" id="playstyles">
    <div class="campaign-section-heading campaign-reveal"><div><span class="campaign-label">04 / FIND YOUR FOCUS</span><h2>WHAT DRIVES<br><span>YOUR SETUP?</span></h2></div></div>
    <div class="campaign-playstyle-grid campaign-reveal"><div class="campaign-playstyle-tabs" role="tablist" aria-label="Choose your setup focus"><button type="button" role="tab" id="focus-play" aria-selected="true" aria-controls="focus-panel" data-focus="play"><span>01</span><span>THE PLAYER<small>FRAMES. FOCUS. FLOW.</small></span><i>↗</i></button><button type="button" role="tab" id="focus-create" aria-selected="false" aria-controls="focus-panel" tabindex="-1" data-focus="create"><span>02</span><span>THE CREATOR<small>IDEAS INTO REALITY.</small></span><i>↗</i></button><button type="button" role="tab" id="focus-everyday" aria-selected="false" aria-controls="focus-panel" tabindex="-1" data-focus="everyday"><span>03</span><span>THE EVERYDAY<small>LESS WAITING. MORE DOING.</small></span><i>↗</i></button></div><div class="campaign-focus-panel" role="tabpanel" id="focus-panel" aria-labelledby="focus-play" tabindex="0"><span class="campaign-focus-glyph" aria-hidden="true">↗</span><p id="focus-copy">Stay in the moment. Start with graphics performance, a balanced processor, and the responsiveness that makes every session feel right.</p><a href="#builder" class="campaign-link">Plan your setup <?= store_icon('arrow') ?></a></div></div>
</section>
<section class="build-planner gaming-section campaign-reveal" id="builder" aria-labelledby="builder-title">
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

<section class="campaign-contact campaign-section" id="contact"><span class="campaign-label campaign-reveal">YOUR NEXT CHAPTER STARTS HERE.</span><h2 class="campaign-reveal">MAKE IT<br><span>YOURS.</span></h2><div class="campaign-contact-bottom campaign-reveal"><p>One component. A whole new setup.<br>There’s always a next level.</p><a href="#builder" class="campaign-contact-link">LET’S BUILD <?= store_icon('arrow') ?></a><a href="store.php" class="campaign-link">Explore the store ↗</a></div></section>
<?php store_page_end(); ?>
