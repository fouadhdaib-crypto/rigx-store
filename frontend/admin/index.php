<?php
require_once __DIR__ . '/includes/layout.php';
$period = filter_input(INPUT_GET, 'period', FILTER_VALIDATE_INT);
if (!in_array($period, [7, 30, 90], true)) $period = 30;
$summary = [7 => [11982.40, 318, 37.68, 25.1], 30 => [48294.50, 1284, 37.61, 24.8], 90 => [139842.75, 3672, 38.08, 25.4]][$period];
$categories = [['PC Components', 18544.5, 'sage'], ['Peripherals', 12682, 'blue'], ['Storage', 8876, 'purple'], ['Accessories', 8192, 'amber']];
$products = [['GeForce RTX 4070 SUPER', 'Graphics card · 12GB', 8640, 18, 94], ['Corsair Vengeance DDR5', 'Memory · 32GB', 5760, 32, 79], ['WD Black SN850X', 'SSD · 2TB NVMe', 4680, 26, 68], ['AMD Ryzen 5 7600', 'Processor · 6-Core', 4320, 18, 62], ['Corsair K70 RGB Pro', 'Mechanical keyboard', 2940, 14, 48]];
page_start('Overview', 'index.php');
heading('STORE PERFORMANCE', 'Welcome to your command center', 'A beautifully clear view of your commerce performance.'); ?>
<div class="toolbar">
  <span class="demo-label">Illustrative demo data · not connected to a database</span>
  <div class="period-form">
    <span class="period-label" id="period-label">Reporting period</span>
    <details class="period-picker">
      <summary aria-labelledby="period-label period-current" aria-haspopup="true"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="5" width="18" height="16" rx="2" />
          <path d="M16 3v4M8 3v4M3 10h18" />
        </svg><span id="period-current">Last <?= $period ?> days</span><svg class="period-chevron" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m6 9 6 6 6-6" />
        </svg></summary>
      <nav class="period-options" aria-label="Reporting period options">
        <?php foreach ([7, 30, 90] as $days): ?>
          <a href="?period=<?= $days ?>" <?= $period === $days ? 'aria-current="true"' : '' ?>><span>Last <?= $days ?> days</span><span class="period-check" aria-hidden="true">✓</span></a>
        <?php endforeach; ?>
      </nav>
    </details>
  </div>
</div>
<section class="kpi-grid" aria-label="Key performance indicators">
  <?php foreach ([['Net sales', 'JD ' . number_format($summary[0], 2), '↗', 'green'], ['Total orders', number_format($summary[1]), '◈', 'purple'], ['Average order value', 'JD ' . number_format($summary[2], 2), '◎', 'blue'], ['Gross margin', number_format($summary[3], 1) . '%', '◉', 'amber']] as [$label, $value, $icon, $tone]): ?><article class="kpi-card">
      <div class="kpi-top"><span class="kpi-label"><?= e($label) ?></span><span class="kpi-icon <?= e($tone) ?>"><?= e($icon) ?></span></div>
      <div class="kpi-value"><?= e($value) ?></div>
      <div class="kpi-bottom">Illustrative · last <?= $period ?> days</div>
    </article><?php endforeach; ?></section>
<div class="primary-grid">
  <section class="panel sales-panel">
    <div class="panel-heading">
      <div>
        <h2>Sales performance</h2>
        <p>Visual overview of example revenue distribution</p>
      </div><span class="live-tag">DEMO</span>
    </div>
    <div class="chart-summary"><strong>JD <?= number_format($summary[0], 2) ?></strong><span>in selected period</span></div>
    <div class="bar-chart" aria-label="Illustrative sales bar chart"><?php foreach ([40, 57, 49, 72, 61, 85, 76, 68, 92, 79, 100, 88] as $i => $height): ?><div class="bar-column">
          <div class="bar" style="--bar-height:<?= $height ?>%" title="Relative sales bar <?= $i + 1 ?>"></div><small><?= $i + 1 ?></small>
        </div><?php endforeach; ?></div>
    <div class="chart-footnote">Illustrative trend only; connect actual order records for real analytics.</div>
  </section>
  <section class="panel categories-panel">
    <div class="panel-heading">
      <div>
        <h2>Revenue by category</h2>
        <p>Example product category mix</p>
      </div><a class="subtle-link" href="categories.php">View categories ↗</a>
    </div>
    <div class="donut-row">
      <div class="donut">
        <div class="donut-hole"><small>REVENUE</small><strong>100%</strong></div>
      </div>
    </div>
    <div class="category-list"><?php foreach ($categories as [$name, $amount, $tone]): ?><div class="category-item"><span class="legend-dot <?= e($tone) ?>"></span><span><?= e($name) ?></span><strong>JD <?= number_format($amount, 0) ?></strong></div><?php endforeach; ?></div>
  </section>
</div>
<div class="secondary-grid">
  <section class="panel table-panel">
    <div class="panel-heading">
      <div>
        <h2>Top performing products</h2>
        <p>Sample best sellers</p>
      </div><a class="subtle-link" href="products.php">All products ↗</a>
    </div>
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>PRODUCT</th>
            <th>REVENUE</th>
            <th>UNITS</th>
            <th>PERFORMANCE</th>
          </tr>
        </thead>
        <tbody><?php foreach ($products as [$name, $detail, $revenue, $units, $score]): ?><tr>
              <td><span class="product-cell"><span class="product-thumb">▣</span><span><strong><?= e($name) ?></strong><small><?= e($detail) ?></small></span></span></td>
              <td>JD <?= number_format($revenue, 0) ?></td>
              <td><?= $units ?></td>
              <td>
                <div class="performance-track"><span style="width:<?= $score ?>%"></span></div><small><?= $score ?>%</small>
              </td>
            </tr><?php endforeach; ?></tbody>
      </table>
    </div>
  </section>
  <section class="panel inventory-panel">
    <div class="panel-heading">
      <div>
        <h2>Inventory alerts</h2>
        <p>Illustrative low-stock products</p>
      </div><span class="alert-count">4 alerts</span>
    </div><?php foreach ([['ASUS Dual RTX 4060', 'GPU-4060-ASU', 2], ['Samsung 990 PRO 2TB', 'SSD-990P-2T', 3], ['Corsair RM850x PSU', 'PSU-RM850X', 4], ['Kingston Fury DDR5', 'RAM-KF32-DDR5', 1]] as [$name, $sku, $stock]): ?><div class="inventory-item"><span class="stock-symbol">!</span><span><strong><?= e($name) ?></strong><small><?= e($sku) ?></small></span><span class="stock-level"><?= $stock ?> left</span></div><?php endforeach; ?>
  </section>
</div>
<?php page_end();
