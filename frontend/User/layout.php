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
        'cart' => '<path d="M3 3h2l3 12h11l2-8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'chevron' => '<path d="m8 10 4 4 4-4"/>',
    ];

    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        . ' stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"'
        . ' aria-hidden="true">'
        . ($paths[$name] ?? $paths['box'])
        . '</svg>';
}

function store_cart(): array
{
    static $cart = null;
    if ($cart !== null) {
        return $cart;
    }

    $cart = ['items' => [], 'count' => 0, 'total' => 0.0, 'error' => false];
    if (empty($_SESSION['email'])) {
        return $cart;
    }

    try {
        require_once __DIR__ . '/../../backend/config/db.php';
        $db = DB_conn::getConnection();
        $statement = $db->prepare(
            "SELECT c.id FROM carts c JOIN users u ON u.id = c.user_id
             WHERE u.email = ? AND u.deleted_at IS NULL
               AND c.status = 'active' AND c.deleted_at IS NULL
             ORDER BY c.created_at DESC, c.id DESC LIMIT 1"
        );
        $statement->execute([$_SESSION['email']]);
        $id = $statement->fetchColumn();
        if ($id) {
            $statement = $db->prepare(
                'SELECT p.name, p.price, ci.quantity
                 FROM cart_items ci JOIN products p ON p.id = ci.product_id
                 WHERE ci.cart_id = ? AND ci.deleted_at IS NULL AND ci.quantity > 0
                 ORDER BY ci.id'
            );
            $statement->execute([$id]);
            $cart['items'] = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cart['items'] as $item) {
                $cart['count'] += (int) $item['quantity'];
                $cart['total'] += (float) $item['price'] * (int) $item['quantity'];
            }
        }
    } catch (Throwable $error) {
        error_log('Store cart: ' . $error->getMessage());
        $cart['error'] = true;
    }
    return $cart;
}

function store_page_start(
    string $title = 'Discover your next find',
    string $search = '',
    bool $landing = false
): void {
    $cart = store_cart();
    $signedIn = !empty($_SESSION['email']);
    ?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#090a0c">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Oxanium:wght@500;600;700;800&display=swap" rel="stylesheet">
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
        <link rel="stylesheet" href="../css/storefront.css?v=<?= filemtime(__DIR__ . '/../css/storefront.css') ?>">
        <link rel="stylesheet" href="../css/typography.css?v=<?= filemtime(__DIR__ . '/../css/typography.css') ?>">
        <script src="../js/store.js?v=<?= filemtime(__DIR__ . '/../js/store.js') ?>" defer></script>
    </head>
    <body<?= $landing ? ' class="gaming-page"' : '' ?>>
        <div class="store-ambient" aria-hidden="true"><span class="ambient-glow ambient-glow-one"></span><span class="ambient-glow ambient-glow-two"></span><span class="ambient-grid"></span><span class="ambient-stars"></span></div>
        <a class="skip-link" href="#main-content">
            Skip to content
        </a>
        <div class="announcement">
            <span>
                NEXT LEVEL HARDWARE. EVERYDAY POSSIBILITIES.
            </span>
            <span>
                <a href="index.php#categories">Find your next upgrade <?= store_icon('arrow') ?></a>
            </span>
        </div>
        <header class="store-header">
            <div class="header-main wrap">
                <a class="store-brand" href="index.php" aria-label="REGIX home">
                    <img src="../assets/regix-logo.svg" alt="REGIX — Next level shopping" width="180" height="44">
                </a>
                <nav class="store-nav" id="store-nav" aria-label="Store navigation">
                <a class="store-home-link" href="index.php" <?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php' ? 'aria-current="page"' : '' ?>>Home</a>
                <a href="store.php" <?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'store.php' ? 'aria-current="page"' : '' ?>>Store</a>
                <a href="index.php#categories">Categories</a>
                <a href="index.php#rigs">Featured rigs</a>
                <a href="index.php#builder">Build planner</a>
                </nav>
                <div class="store-header-actions">
                    <button class="store-icon-button" type="button" data-open-store-dialog="store-search-dialog" aria-label="Search categories" title="Search categories"><?= store_icon('search') ?></button>
                    <?php if ($signedIn): ?>
                        <details class="store-account">
                            <summary aria-label="Your account"><?= store_icon('user') ?><span class="account-name"><?= e((string) ($_SESSION['user_name'] ?? 'Account')) ?></span><?= store_icon('chevron') ?></summary>
                            <div class="store-account-panel"><small>WELCOME BACK</small><strong><?= e((string) ($_SESSION['user_name'] ?? 'Your account')) ?></strong><?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?><a href="../admin/dashboard.php">Admin dashboard <?= store_icon('arrow') ?></a><?php endif; ?><a href="../../backend/auth/logout.php">Sign out</a></div>
                        </details>
                    <?php else: ?>
                        <a class="store-icon-button store-sign-in" href="../../backend/auth/login.php" aria-label="Sign in"><?= store_icon('user') ?><span>Sign in</span></a>
                    <?php endif; ?>
                    <button class="store-cart-button" type="button" data-open-store-dialog="store-cart-dialog" aria-label="Open cart, <?= (int) $cart['count'] ?> items">
                        <?= store_icon('cart') ?><span class="cart-button-label">Cart</span><span class="store-cart-count"><?= (int) $cart['count'] ?></span>
                    </button>
                    <button class="menu-button" type="button" aria-controls="store-nav" aria-expanded="false" aria-label="Toggle navigation"><?= store_icon('menu') ?></button>
                </div>
            </div>
        </header>
        <main id="main-content" class="wrap store-main">
            <?php
}

function store_page_end(): void
{
    $cart = store_cart();
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
        <dialog id="store-search-dialog" class="store-search-dialog" aria-labelledby="store-search-title">
            <div class="store-dialog-heading"><div><span class="gaming-kicker">FIND YOUR NEXT UPGRADE</span><h2 id="store-search-title">What’s on your wishlist?</h2></div><button type="button" class="store-icon-button" data-close-store-dialog aria-label="Close search"><?= store_icon('close') ?></button></div>
            <form action="index.php#categories" method="get" class="store-search-form" id="landing-category-search" role="search"><label class="sr-only" for="header-search">Search categories</label><?= store_icon('search') ?><input id="header-search" name="q" type="search" maxlength="100" placeholder="Search hardware categories…" required><button type="submit" aria-label="Search"><?= store_icon('arrow') ?></button></form>
            <p>Start with a category, then explore what fits your setup.</p><div class="store-search-suggestions"><a href="index.php?q=Memory#categories">Memory</a><a href="index.php?q=Storage#categories">Storage</a><a href="index.php?q=Graphics#categories">Graphics cards</a></div>
        </dialog>
        <dialog id="store-cart-dialog" class="store-cart-dialog" aria-labelledby="store-cart-title">
            <div class="store-dialog-heading"><div><span class="gaming-kicker">YOUR NEXT SETUP</span><h2 id="store-cart-title">Your cart <span><?= (int) $cart['count'] ?></span></h2></div><button type="button" class="store-icon-button" data-close-store-dialog aria-label="Close cart"><?= store_icon('close') ?></button></div>
            <?php if ($cart['error']): ?>
                <div class="store-cart-empty"><?= store_icon('box') ?><h3>We couldn’t load your cart.</h3><p>Please refresh the page and try again.</p></div>
            <?php elseif (!$cart['items']): ?>
                <div class="store-cart-empty"><span class="cart-empty-orbit"><?= store_icon('cart') ?></span><span class="gaming-kicker">ROOM FOR SOMETHING GREAT</span><h3>Your next upgrade starts here.</h3><p><?= empty($_SESSION['email']) ? 'Sign in to see your saved cart, or explore the collection for your next find.' : 'Your cart is empty. Discover the components that make your setup yours.' ?></p><a class="button neon-button" href="<?= empty($_SESSION['email']) ? '../../backend/auth/login.php' : 'index.php#categories' ?>"><?= empty($_SESSION['email']) ? 'Sign in to your account' : 'Explore categories' ?> <?= store_icon('arrow') ?></a></div>
            <?php else: ?>
                <div class="store-cart-items"><?php foreach ($cart['items'] as $item): ?><article class="store-cart-item"><span class="cart-item-icon"><?= store_icon('box') ?></span><div><h3><?= e($item['name']) ?></h3><p>Qty <?= (int) $item['quantity'] ?> · <?= number_format((float) $item['price'], 2) ?> JOD each</p></div><strong><?= number_format((float) $item['price'] * (int) $item['quantity'], 2) ?><small>JOD</small></strong></article><?php endforeach; ?></div>
                <div class="store-cart-summary"><span>Subtotal</span><strong><?= number_format($cart['total'], 2) ?> JOD</strong><p>Before any discounts or delivery charges.</p></div>
            <?php endif; ?>
            <div class="store-cart-bottom"><button class="button glass-button" type="button" data-close-store-dialog>Continue exploring <?= store_icon('arrow') ?></button><span><?= store_icon('check') ?> Your setup, one great find at a time.</span></div>
        </dialog>
        </body>

    </html>
    <?php
}
