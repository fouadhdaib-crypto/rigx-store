<?php

declare(strict_types=1);

require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../../backend/config/db.php';

$db = DB_conn::getConnection();
$categories = $db->query(
    "SELECT id, name
     FROM categories
     WHERE deleted_at IS NULL
     ORDER BY name"
)->fetchAll(PDO::FETCH_ASSOC);

$search = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
$search = mb_substr($search, 0, 100);
$categoryId = filter_var(
    $_GET['category_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$categoryId = $categoryId === false ? null : $categoryId;

$conditions = [
    "p.deleted_at IS NULL",
    "p.listing_status = 'available'",
    'p.quantity > 0',
    'c.deleted_at IS NULL',
];
$parameters = [];

if ($search !== '') {
    $conditions[] = '(p.name LIKE :name_search OR p.brand LIKE :brand_search OR p.description LIKE :description_search)';
    $searchTerm = '%' . $search . '%';
    $parameters[':name_search'] = $searchTerm;
    $parameters[':brand_search'] = $searchTerm;
    $parameters[':description_search'] = $searchTerm;
}

if ($categoryId !== null) {
    $conditions[] = 'p.category_id = :category_id';
    $parameters[':category_id'] = $categoryId;
}

$statement = $db->prepare(
    'SELECT p.id, p.category_id, p.name, p.brand, p.description, p.price,
            p.quantity, p.condition_type, p.product_img, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE ' . implode(' AND ', $conditions) . '
     ORDER BY p.created_at DESC, p.id DESC'
);
$statement->execute($parameters);
$products = $statement->fetchAll(PDO::FETCH_ASSOC);

$cartNotice = (string) ($_SESSION['cart_notice'] ?? '');
unset($_SESSION['cart_notice']);

store_page_start('Store', $search);
?>
<section class="store-catalog" aria-labelledby="store-title">
    <div class="store-catalog-heading">
        <span class="campaign-label">REGIX STORE</span>
        <h1 id="store-title">Browse products</h1>
        <p>Find your next upgrade from the products currently available.</p>
    </div>

    <?php if ($cartNotice !== ''): ?>
        <p class="landing-category-feedback" role="status"><?= e($cartNotice) ?></p>
    <?php endif; ?>

    <form class="store-catalog-filters" action="store.php" method="get">
        <label>
            <span>Search products</span>
            <input type="search" name="q" value="<?= e($search) ?>" placeholder="Name, brand, or description">
        </label>
        <label>
            <span>Category</span>
            <select name="category_id">
                <option value="">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>>
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Search</button>
        <?php if ($search !== '' || $categoryId !== null): ?>
            <a href="store.php">Clear filters</a>
        <?php endif; ?>
    </form>

    <div class="store-catalog-results">
        <p><?= count($products) ?> <?= count($products) === 1 ? 'product' : 'products' ?> available</p>
    </div>

    <?php if ($products === []): ?>
        <p class="landing-products-empty">No products match your search yet.</p>
    <?php else: ?>
        <div class="landing-products-grid">
            <?php foreach ($products as $product): ?>
                <?php
                $image = (string) ($product['product_img'] ?? '');
                $imageIsValid = preg_match('#\Aassets/products/[A-Za-z0-9._-]+\z#', $image) === 1;
                ?>
                <article class="landing-product-card">
                    <div class="landing-product-image">
                        <img src="<?= $imageIsValid ? '../' . e($image) : '../assets/regix-hero-tower.png' ?>"
                            alt="<?= e($product['name']) ?>" loading="lazy">
                        <span><?= e(strtoupper((string) $product['condition_type'])) ?></span>
                    </div>
                    <div class="landing-product-copy">
                        <span><?= e((string) ($product['brand'] ?: $product['category_name'])) ?> ·
                            <?= e($product['category_name']) ?></span>
                        <h4><?= e($product['name']) ?></h4>
                        <?php if ((string) $product['description'] !== ''): ?>
                            <p><?= e($product['description']) ?></p>
                        <?php endif; ?>
                        <div class="landing-product-buy">
                            <strong><?= number_format((float) $product['price'], 2) ?> JOD</strong>
                            <?php if (!empty($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'customer'): ?>
                                <form method="post" action="cart_action.php">
                                    <input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['cart_csrf']) ?>">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                    <input type="hidden" name="return_to" value="store">
                                    <button type="submit">Add to cart</button>
                                </form>
                            <?php else: ?>
                                <a href="../../backend/auth/login.php">Sign in to buy</a>
                            <?php endif; ?>
                        </div>
                        <small><?= (int) $product['quantity'] ?> in stock</small>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php store_page_end(); ?>