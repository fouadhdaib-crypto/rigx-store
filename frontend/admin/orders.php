<?php

require_once __DIR__ . '/../layout.php';

$orders = [
    [
        'id' => '#RGX-1048',
        'customer' => 'Olivia Rhye',
        'email' => 'olivia.rhye@example.com',
        'date' => 'Oct 24, 2026',
        'items' => '2 items',
        'total' => '$248.00',
        'payment' => 'Paid',
        'status' => 'Processing',
        'method' => 'Visa ···· 4242',
    ],
    [
        'id' => '#RGX-1047',
        'customer' => 'Phoenix Baker',
        'email' => 'phoenix.baker@example.com',
        'date' => 'Oct 24, 2026',
        'items' => '1 item',
        'total' => '$89.00',
        'payment' => 'Paid',
        'status' => 'Shipped',
        'method' => 'PayPal',
    ],
    [
        'id' => '#RGX-1046',
        'customer' => 'Lana Steiner',
        'email' => 'lana.steiner@example.com',
        'date' => 'Oct 23, 2026',
        'items' => '3 items',
        'total' => '$412.50',
        'payment' => 'Paid',
        'status' => 'Delivered',
        'method' => 'Mastercard ···· 8210',
    ],
    [
        'id' => '#RGX-1045',
        'customer' => 'Demi Wilkinson',
        'email' => 'demi.wilkinson@example.com',
        'date' => 'Oct 23, 2026',
        'items' => '1 item',
        'total' => '$129.00',
        'payment' => 'Pending',
        'status' => 'Pending',
        'method' => 'Visa ···· 1098',
    ],
    [
        'id' => '#RGX-1044',
        'customer' => 'Candice Wu',
        'email' => 'candice.wu@example.com',
        'date' => 'Oct 22, 2026',
        'items' => '4 items',
        'total' => '$536.00',
        'payment' => 'Paid',
        'status' => 'Processing',
        'method' => 'Apple Pay',
    ],
    [
        'id' => '#RGX-1043',
        'customer' => 'Natali Craig',
        'email' => 'natali.craig@example.com',
        'date' => 'Oct 21, 2026',
        'items' => '2 items',
        'total' => '$184.00',
        'payment' => 'Refunded',
        'status' => 'Cancelled',
        'method' => 'Visa ···· 5567',
    ],
];

$statusCounts = [
    'all' => count($orders),
    'pending' => 0,
    'processing' => 0,
    'shipped' => 0,
    'delivered' => 0,
    'cancelled' => 0,
];

foreach ($orders as $order) {
    $status = strtolower($order['status']);
    $statusCounts[$status]++;
}

page_start('Orders', 'orders.php');

?>
<div class="orders-page">
<?php

heading(
    'STORE MANAGEMENT',
    'Orders',
    'Track, review and manage your store orders.'
);
?>

<section class="orders-overview" aria-label="Order overview">
    <article class="orders-stat">
        <div class="orders-stat-top">
            <span>Total orders</span>
            <span class="orders-stat-icon orders-icon-green" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M4 17 10 11l4 4 6-8M14 7h6v6"/></svg>
            </span>
        </div>
        <strong>1,284</strong>
        <small><span class="orders-positive">↑ 12.8%</span><span>vs. last month</span></small>
    </article>
    <article class="orders-stat">
        <div class="orders-stat-top">
            <span>Needs attention</span>
            <span class="orders-stat-icon orders-icon-amber" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 8v4m0 4h.01"/><path d="m10.3 3.9-8 14a2 2 0 0 0 1.7 3h16a2 2 0 0 0 1.7-3l-8-14a2 2 0 0 0-3.4 0Z"/></svg>
            </span>
        </div>
        <strong>28</strong>
        <small><span>Orders awaiting fulfillment</span></small>
    </article>
    <article class="orders-stat">
        <div class="orders-stat-top">
            <span>In transit</span>
            <span class="orders-stat-icon orders-icon-blue" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M3 7h11v11H3zM14 11h4l3 3v4h-7z"/><circle cx="7.5" cy="18.5" r="1.5"/><circle cx="17.5" cy="18.5" r="1.5"/></svg>
            </span>
        </div>
        <strong>64</strong>
        <small><span>Orders on their way</span></small>
    </article>
    <article class="orders-stat">
        <div class="orders-stat-top">
            <span>Gross sales</span>
            <span class="orders-stat-icon orders-icon-purple" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 3v18m4-14H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H7"/></svg>
            </span>
        </div>
        <strong>$48,290</strong>
        <small><span class="orders-positive">↑ 8.2%</span><span>vs. last month</span></small>
    </article>
</section>

<section class="panel orders-panel" aria-labelledby="orders-list-title">
    <div class="orders-panel-heading">
        <div>
            <div class="orders-title-line">
                <h2 id="orders-list-title">All orders</h2>
                <span class="orders-sample-tag"><span></span> SAMPLE DATA</span>
            </div>
            <p>Review and track every order in your store.</p>
        </div>
        <button class="orders-export" id="orders-export" type="button">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3" />
            </svg>
            Export CSV
        </button>
    </div>

    <div class="orders-controls">
        <div class="orders-tabs" role="group" aria-label="Filter orders by status">
            <button type="button" class="orders-filter active" data-status-filter="all" aria-pressed="true">All <span><?= $statusCounts['all'] ?></span></button>
            <button type="button" class="orders-filter" data-status-filter="pending" aria-pressed="false">Pending <span><?= $statusCounts['pending'] ?></span></button>
            <button type="button" class="orders-filter" data-status-filter="processing" aria-pressed="false">Processing <span><?= $statusCounts['processing'] ?></span></button>
            <button type="button" class="orders-filter" data-status-filter="shipped" aria-pressed="false">Shipped <span><?= $statusCounts['shipped'] ?></span></button>
            <button type="button" class="orders-filter" data-status-filter="delivered" aria-pressed="false">Delivered <span><?= $statusCounts['delivered'] ?></span></button>
            <button type="button" class="orders-filter" data-status-filter="cancelled" aria-pressed="false">Cancelled <span><?= $statusCounts['cancelled'] ?></span></button>
        </div>
        <label class="orders-search">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="10.8" cy="10.8" r="6.8" />
                <path d="m16 16 4.2 4.2" />
            </svg>
            <span class="visually-hidden">Search orders</span>
            <input id="orders-search" type="search" placeholder="Search orders..." autocomplete="off">
        </label>
    </div>

    <div class="table-scroll orders-table-scroll">
        <table class="orders-table">
            <thead>
                <tr>
                    <th scope="col">ORDER</th>
                    <th scope="col">CUSTOMER</th>
                    <th scope="col">DATE</th>
                    <th scope="col">ITEMS</th>
                    <th scope="col">TOTAL</th>
                    <th scope="col">PAYMENT</th>
                    <th scope="col">STATUS</th>
                </tr>
            </thead>
            <tbody id="orders-table-body">
                <?php foreach ($orders as $index => $order): ?>
                    <tr
                        data-order-row
                        data-status="<?= e(strtolower($order['status'])) ?>"
                    >
                        <td><strong class="orders-id"><?= e($order['id']) ?></strong></td>
                        <td>
                            <div class="orders-customer">
                                <span class="orders-avatar orders-avatar-<?= $index % 4 ?>"><?= e(initials($order['customer'])) ?></span>
                                <span>
                                    <strong><?= e($order['customer']) ?></strong>
                                    <small><?= e($order['email']) ?></small>
                                </span>
                            </div>
                        </td>
                        <td><?= e($order['date']) ?></td>
                        <td><?= e($order['items']) ?></td>
                        <td><strong class="orders-total"><?= e($order['total']) ?></strong></td>
                        <td>
                            <span class="orders-payment orders-payment-<?= e(strtolower($order['payment'])) ?>">
                                <span></span><?= e($order['payment']) ?>
                            </span>
                            <small class="orders-method"><?= e($order['method']) ?></small>
                        </td>
                        <td>
                            <span class="orders-status orders-status-<?= e(strtolower($order['status'])) ?>">
                                <span></span><?= e($order['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="orders-empty" id="orders-empty" hidden>
                    <td colspan="7">No orders match your search.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="orders-table-footer">
        <span id="orders-result-count" aria-live="polite">Showing 6 sample orders</span>
        <span class="orders-demo-note">October 2026</span>
    </div>
</section>

<script src="../js/orders.js" defer></script>
</div>
<?php page_end(); ?>
