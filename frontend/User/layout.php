<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/config.php';

function store_icon(string $name): string
{
    $paths = [
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/>',
        'arrow' => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
        'box' => '<path d="m3 7 9-4 9 4v10l-9 4-9-4ZM3 7l9 4 9-4M12 11v10M7 5l10 4"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
    ];

    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        . ' stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"'
        . ' aria-hidden="true">'
        . ($paths[$name] ?? $paths['box'])
        . '</svg>';
}

function store_page_start(
    string $title = 'Discover your next find',
    string $search = '',
    bool $landing = false
): void {
    ?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#090a0c">
        <meta name="description"
            content="Discover new and pre-loved products at REGIX. Browse categories and find your next everyday essential.">
        <title>
            <?= e($title) ?>
            · REGIX
        </title>
        <link rel="icon" href="../assets/favicon.svg" type="image/svg+xml">
        <link rel="stylesheet" href="../css/store.css">
        <?php if ($landing): ?>
            <link rel="stylesheet" href="../css/landing.css">
            <script src="../js/landing.js" defer></script>
        <?php endif; ?>
        <script src="../js/store.js" defer></script>
    </head>
    <body<?= $landing ? ' class="gaming-page"' : '' ?>>
        <a class="skip-link" href="#main-content">
            Skip to content
        </a>
        <div class="announcement">
            <span>
                NEW FINDS. MORE POSSIBILITIES.
            </span>
            <span>
                New & pre-loved, all in one place <?= store_icon('arrow') ?>
            </span>
        </div>
        <header class="store-header">
            <div class="header-main wrap">
                <a class="store-brand" href="index.php" aria-label="REGIX home">
                    <img src="../assets/regix-logo.svg" alt="REGIX — Next level shopping" width="180" height="44">
                </a>
                <a class="button header-explore" href="index.php#rigs">
                    Explore builds <?= store_icon('arrow') ?>
                </a>
                <a class="account-link" href="../../backend/auth/login.php">
                    <?= store_icon('user') ?>
                    <span>
                        <?= e((string) ($_SESSION['user_name'] ?? 'Sign in')) ?>
                    </span>
                </a>
                <button class="menu-button" type="button" aria-controls="store-nav" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <?= store_icon('menu') ?>
                </button>
            </div>
            <nav class="store-nav wrap" id="store-nav" aria-label="Store navigation">
                <a href="index.php#categories">Categories</a>
                <a href="index.php#rigs">Featured rigs</a>
                <a href="index.php#builder">Build planner</a>
                <span class="nav-note">
                    <span class="status-dot"></span>
                    A little discovery goes a long way
                </span>
            </nav>
        </header>
        <main id="main-content" class="wrap store-main">
            <?php
}

function store_page_end(): void
{
    ?>
        </main>
        <footer class="store-footer">
            <div class="wrap footer-inner">
                <div>
                    <a class="footer-logo" href="index.php">
                        REGIX
                        <span>
                            .
                        </span>
                    </a>
                    <p>
                        Next level shopping. Everyday discoveries.
                    </p>
                </div>
                <nav aria-label="Footer navigation">
                    <a href="index.php#rigs">
                        Featured builds
                    </a>
                    <a href="index.php#categories">
                        Categories
                    </a>
                    <a href="../../backend/auth/login.php">
                        Your account
                    </a>
                </nav>
                <small>
                    &copy; <?= date('Y') ?> REGIX
                </small>
            </div>
        </footer>
        </body>

    </html>
    <?php
}