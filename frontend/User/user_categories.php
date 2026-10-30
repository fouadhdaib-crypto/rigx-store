<?php
require_once __DIR__ . '/../../backend/classes/CategorieClass.php';

$search = trim($_GET['q'] ?? '');
$categories = [];
$error_msg = '';

try {
    $categoryManager = new Categorie();
    $categories = $categoryManager->getAll($search);
} catch (\Throwable $e) {
    $error_msg = $e->getMessage();
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#090a0c">
    <title>Categories | REGIX Store</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/user_categories.css">

</head>

<body class="store-categories">
    <header class="store-header">
        <div class="store-header-inner">
            <a class="store-brand" href="index.php" aria-label="REGIX Store home">
                <img src="../assets/regix-logo.svg" alt="REGIX Store" width="220" height="53">
            </a>
            <nav class="store-nav" aria-label="Store navigation">
                <a href="index.php">Home</a>
                <a href="user_categories.php" aria-current="page">Categories</a>
            </nav>
            <a class="store-home" href="index.php">Storefront</a>
        </div>
    </header>

    <main class="store-main">
        <section class="store-heading">
            <div>
                <div class="store-kicker">YOUR STORE CATALOG</div>
                <h1>Explore categories<span>.</span></h1>
                <p>Browse gaming hardware, components, and accessories selected for your next build.</p>
            </div>
        </section>

        <?php if ($error_msg !== ''): ?>
            <div class="store-alert" role="alert">
                DB_conn Error: <?= htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="category-tools" aria-label="Category search and count">
            <span class="category-count">
                <?= count($categories) ?>
                <?= count($categories) === 1 ? 'category' : 'categories' ?><?= $search !== '' ? ' found' : '' ?>
            </span>
            <form class="category-search" method="get" action="user_categories.php" role="search">
                <label class="visually-hidden" for="categorySearch">Search categories by name</label>
                <input id="categorySearch" type="search" name="q"
                    value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Search by category name...">
                <?php if ($search !== ''): ?>
                    <a class="clear-search" href="user_categories.php">Clear</a>
                <?php endif; ?>
                <button type="submit">Search</button>
            </form>
        </section>

        <section class="category-grid" aria-label="Product categories">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <?php
                    $image = $category['category_img'] ?? $category['image'] ?? '';
                    $imageFile = basename($image);
                    $imagePath = $imageFile !== '' && is_file(__DIR__ . '/../assets/category/' . $imageFile)
                        ? '../assets/category/' . rawurlencode($imageFile)
                        : '';
                    ?>
                    <article class="store-category">
                        <a class="category-image" href="product.php?category_id=<?= (int) $category['id'] ?>"
                            aria-label="Browse <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?> products">
                            <?php if ($imagePath !== ''): ?>
                                <img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <?php else: ?>
                                <img class="category-placeholder-mark" src="../assets/regix-mark.svg" alt="" aria-hidden="true">
                            <?php endif; ?>
                            <span class="category-image-label">REGIX CATEGORY</span>
                        </a>
                        <div class="category-copy">
                            <h2><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <?php $description = trim($category['description'] ?? 'Browse products available in this category.'); ?>
                            <p class="category-description"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
                            <button class="description-toggle" type="button" aria-expanded="false" hidden>See more</button>
                            <a class="category-link" href="product.php?category_id=<?= (int) $category['id'] ?>">
                                Browse products <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="category-empty">
                    <h2><?= $search !== '' ? 'No matching categories' : 'No categories yet' ?></h2>
                    <p><?= $search !== '' ? 'Try another category name or clear your search.' : 'Check back soon for the latest gaming hardware and accessories.' ?>
                    </p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="store-footer">
        <strong>REGIX<span>.</span></strong>
        <span>&copy; <?= date('Y') ?> REGIX Store</span>
    </footer>
    <script>
        function updateDescriptionToggles() {
            document.querySelectorAll('.store-category').forEach(card => {
                const description = card.querySelector('.category-description');
                const button = card.querySelector('.description-toggle');
                const expanded = description.classList.contains('is-expanded');

                description.classList.remove('is-expanded');
                button.hidden = description.scrollHeight <= description.clientHeight + 1;
                description.classList.toggle('is-expanded', expanded);
            });
        }

        document.querySelectorAll('.description-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const description = button.previousElementSibling;
                const expanded = description.classList.toggle('is-expanded');

                button.textContent = expanded ? 'See less' : 'See more';
                button.setAttribute('aria-expanded', String(expanded));
            });
        });

        updateDescriptionToggles();
        window.addEventListener('resize', updateDescriptionToggles);
    </script>
</body>

</html>