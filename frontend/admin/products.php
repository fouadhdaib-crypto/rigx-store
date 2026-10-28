<?php require_once __DIR__ . '/includes/layout.php';
page_start('Products', 'products.php');
heading('STORE MANAGEMENT', 'Products', 'Manage your product catalog and merchandising.'); ?>
<section class="panel empty-panel"><span class="empty-icon">◇</span>
    <h2><?= e('Products') ?></h2>
    <p>This page is connected to the PHP navigation and ready for your database-driven content. No records have been invented.</p><a class="primary-button" href="index.php">← Back to overview</a>
</section>
<?php page_end();
