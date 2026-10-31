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

$products = $db->query(
    "SELECT p.id, p.category_id, p.name, p.brand, p.description, p.price,
            p.quantity, p.condition_type, p.product_img, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.deleted_at IS NULL
       AND p.listing_status = 'available'
       AND p.quantity > 0
       AND c.deleted_at IS NULL
     ORDER BY p.created_at DESC, p.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$cartNotice = (string) ($_SESSION['cart_notice'] ?? '');
unset($_SESSION['cart_notice']);

store_page_start('Store', $search);
?>
<style>
    .store-catalog .store-catalog-filters {
        display: grid;
        grid-template-columns: minmax(240px, 2fr) repeat(2, minmax(150px, 1fr)) auto;
        align-items: end;
        gap: 14px;
        padding: 18px;
        border: 1px solid var(--line);
        border-radius: 16px;
        background: var(--surface);
    }

    .store-catalog .store-catalog-filters label {
        display: grid;
        gap: 8px;
        min-width: 0;
        margin: 0;
    }

    .store-catalog .store-catalog-filters label > span {
        color: var(--muted);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .store-catalog .store-catalog-filters label input,
    .store-catalog .store-catalog-filters label select {
        display: block;
        width: 100%;
        height: 46px;
        min-height: 46px;
        margin: 0;
        padding: 0 16px;
        border: 1px solid var(--line);
        border-radius: 12px;
        background-color: var(--bg);
        color: var(--text);
        font-size: 13px;
        line-height: 44px;
        transition: border-color .15s, box-shadow .15s;
    }

    .store-catalog .store-catalog-filters label select {
        appearance: none;
        -webkit-appearance: none;
        padding-right: 38px;
        background-image: linear-gradient(45deg, transparent 50%, var(--muted) 50%), linear-gradient(135deg, var(--muted) 50%, transparent 50%);
        background-position: calc(100% - 20px) 20px, calc(100% - 15px) 20px;
        background-size: 5px 5px, 5px 5px;
        background-repeat: no-repeat;
        cursor: pointer;
    }

    .store-catalog .store-catalog-filters label input:hover,
    .store-catalog .store-catalog-filters label select:hover {
        border-color: #3a4436;
    }

    .store-catalog .store-catalog-filters label input:focus,
    .store-catalog .store-catalog-filters label select:focus {
        outline: none;
        border-color: var(--lime);
        box-shadow: 0 0 0 3px rgba(163, 230, 53, .22);
    }

    .store-catalog .store-search-box {
        position: relative;
        display: block;
        min-width: 0;
    }

    .store-catalog .store-search-box .store-search-icon {
        position: absolute;
        top: 50%;
        left: 16px;
        width: 17px;
        height: 17px;
        transform: translateY(-50%);
        color: var(--muted);
        pointer-events: none;
        transition: color .15s;
    }

    .store-catalog .store-search-box:focus-within .store-search-icon {
        color: var(--lime);
    }

    .store-catalog .store-catalog-filters label .store-search-box input {
        padding: 0 42px 0 44px;
        border-radius: 9999px;
    }

    .store-catalog .store-catalog-filters label .store-search-box input::placeholder {
        color: #6b7467;
    }

    .store-catalog .store-catalog-filters label .store-search-box input::-webkit-search-cancel-button {
        -webkit-appearance: none;
        appearance: none;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background:
            linear-gradient(45deg, transparent 43%, var(--muted) 43% 57%, transparent 57%),
            linear-gradient(-45deg, transparent 43%, var(--muted) 43% 57%, transparent 57%);
        cursor: pointer;
    }

    .store-catalog .store-catalog-filters > a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 46px;
        padding: 0 22px;
        border: 1px solid var(--line);
        border-radius: 9999px;
        background: var(--bg);
        color: var(--muted);
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        transition: border-color .15s, color .15s;
    }

    .store-catalog .store-catalog-filters > a:hover {
        border-color: var(--lime);
        color: var(--lime);
    }

    @media (max-width: 1000px) {
        .store-catalog .store-catalog-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .store-catalog .store-catalog-filters > label:first-child {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 700px) {
        .store-catalog .store-catalog-filters {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="store-catalog" aria-labelledby="store-title">
    <div class="store-catalog-heading">
        <span class="campaign-label">REGIX STORE</span>
        <h1 id="store-title">Browse products</h1>
        <p>Find your next upgrade from the products currently available.</p>
    </div>

    <?php if ($cartNotice !== ''): ?>
        <p class="landing-category-feedback" role="status"><?= e($cartNotice) ?></p>
    <?php endif; ?>

    <form class="store-catalog-filters" action="store.php" method="get" role="search">
        <label>
            <span>Search products</span>
            <div class="store-search-box">
                <svg class="store-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-3.5-3.5"/>
                </svg>
                <input type="search" name="q" id="store-search" value="<?= e($search) ?>" maxlength="100" autocomplete="off" placeholder="Search by name, brand, category or description">
            </div>
        </label>
        <label>
            <span>Category</span>
            <select name="category_id" id="store-category">
                <option value="">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>">
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            <span>Condition</span>
            <select name="condition" id="store-condition">
                <option value="">Any condition</option>
                <option value="new">New</option>
                <option value="used">Used</option>
            </select>
        </label>
        <a href="store.php" id="store-clear" style="display: none;">Clear filters</a>
    </form>

    <div class="store-catalog-results">
        <p id="store-result-count"><?= count($products) ?> <?= count($products) === 1 ? 'product' : 'products' ?> available</p>
    </div>

    <?php if ($products === []): ?>
        <p class="landing-products-empty">No products match your search yet.</p>
    <?php else: ?>
        <p class="landing-products-empty" id="store-no-results" style="display: none;">No products match your search yet.</p>

        <div class="landing-products-grid">
            <?php foreach ($products as $product): ?>
                <?php
                $image = (string) ($product['product_img'] ?? '');
                $imageIsValid = preg_match('#\Aassets/products/[A-Za-z0-9._-]+\z#', $image) === 1;
                ?>
                <article class="landing-product-card"
                    data-search="<?= e(implode(' ', [(string) $product['name'], (string) $product['brand'], (string) $product['description'], (string) $product['category_name']])) ?>"
                    data-category="<?= (int) $product['category_id'] ?>"
                    data-condition="<?= e((string) $product['condition_type']) ?>">
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