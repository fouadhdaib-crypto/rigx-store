<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../backend/config/stripe.php';
require_once __DIR__ . '/../../backend/classes/StripeClass.php';
require_once __DIR__ . '/../../backend/classes/OrderClass.php';

$items = [
    ['name' => 'RTX 4070', 'price' => 599.99, 'quantity' => 1],
    ['name' => 'Ryzen 7 7800X3D', 'price' => 399.00, 'quantity' => 1],
];

$orderId = 1;
$checkoutError = '';
$cashOnDeliveryConfirmation = $_SESSION['cash_on_delivery_confirmation'] ?? null;
$selectedPaymentMethod = 'stripe';
unset($_SESSION['cash_on_delivery_confirmation']);
$customerName = '';
$customerPhone = '';
$deliveryAddress = '';

if (empty($_SESSION['checkout_csrf'])) {
    $_SESSION['checkout_csrf'] = bin2hex(random_bytes(32));
}

$subtotal = array_reduce(
    $items,
    static fn(float $total, array $item): float =>
        $total + ($item['price'] * $item['quantity']),
    0.0
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedPaymentMethod = is_string($_POST['payment_method'] ?? null)
        ? $_POST['payment_method']
        : '';
    $customerName = is_string($_POST['customer_name'] ?? null)
        ? trim($_POST['customer_name'])
        : '';
    $customerPhone = is_string($_POST['customer_phone'] ?? null)
        ? trim($_POST['customer_phone'])
        : '';
    $deliveryAddress = is_string($_POST['delivery_address'] ?? null)
        ? trim($_POST['delivery_address'])
        : '';
    $submittedToken = is_string($_POST['csrf_token'] ?? null)
        ? $_POST['csrf_token']
        : '';

    if (!hash_equals($_SESSION['checkout_csrf'], $submittedToken)) {
        $checkoutError = 'Your checkout session expired. Please reload and try again.';
    } elseif ($selectedPaymentMethod === 'cash_on_delivery') {
        try {
            $order = (new OrderClass())->createCashOnDeliveryOrder(
                [
                    'name' => $customerName,
                    'phone' => $customerPhone,
                    'address' => $deliveryAddress,
                ],
                $items
            );

            $_SESSION['cash_on_delivery_confirmation'] = $order;
            $_SESSION['checkout_csrf'] = bin2hex(random_bytes(32));
            header('Location: checkout.php');
            exit;
        } catch (InvalidArgumentException $exception) {
            $checkoutError = $exception->getMessage();
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $checkoutError = 'We could not save your order. Please try again.';
        }
    } elseif ($selectedPaymentMethod === 'stripe') {
        try {
            $stripe = new StripeClass();
            $checkoutSession = $stripe->createCheckoutSession($items, $orderId);

            if (!empty($checkoutSession['url'])) {
                header('Location: ' . $checkoutSession['url']);
                exit;
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
        }

        $checkoutError = 'Secure checkout is unavailable right now. Please try again.';
    } else {
        $checkoutError = 'Choose a payment method to continue.';
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#090a0c">
    <title>Checkout | REGIX Store</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/checkout.css">

</head>

<body class="checkout-page">
    <header class="checkout-header">
        <div class="checkout-header-inner">
            <a class="checkout-brand" href="index.php" aria-label="REGIX Store home">
                <img src="../assets/regix-logo.svg" alt="REGIX Store" width="220" height="53">
            </a>
            <a class="back-link" href="user_categories.php"><span aria-hidden="true">←</span> Continue shopping</a>
        </div>
    </header>

    <main class="checkout-main">
        <div class="checkout-heading">
            <div class="checkout-kicker">SECURE CHECKOUT</div>
            <h1>Review your order</h1>
            <p>Check your items, add delivery details, and choose how you would like to pay.</p>
        </div>

        <?php if ($checkoutError !== ''): ?>
            <div class="checkout-error" role="alert"><?= htmlspecialchars($checkoutError, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="checkout-grid">
            <section class="checkout-panel" aria-labelledby="orderItemsTitle">
                <div class="panel-heading">
                    <h2 id="orderItemsTitle">Order items</h2>
                    <span><?= count($items) ?> items</span>
                </div>
                <div class="order-items">
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <div class="item-copy">
                                <strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span>Quantity: <?= (int) $item['quantity'] ?></span>
                            </div>
                            <span class="item-price">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <section class="checkout-policies" aria-labelledby="checkoutPoliciesTitle">
                    <h3 id="checkoutPoliciesTitle">Store policies</h3>
                    <details class="checkout-policy">
                        <summary>Purchase policy</summary>
                        <p>Review the items, quantities, and total before placing your order. Cash on delivery is paid
                            when the order arrives; Stripe payments are completed online.</p>
                    </details>
                    <details class="checkout-policy">
                        <summary>Returns and exchanges</summary>
                        <p>Keep items unused and in their original packaging. Contact the store with your order number
                            to request a return or exchange; each request is reviewed according to the item and its
                            condition.</p>
                    </details>
                    <details class="checkout-policy">
                        <summary>Delivery policy</summary>
                        <p>Enter an accurate phone number and delivery address. Delivery timing and any applicable
                            charges depend on the delivery area and will be confirmed by the store.</p>
                    </details>
                </section>
            </section>

            <aside class="checkout-panel" aria-labelledby="summaryTitle">
                <div class="panel-heading">
                    <h2 id="summaryTitle">Order summary</h2>
                </div>
                <div class="summary-body">
                    <div class="summary-line"><span>Subtotal</span><span>$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="summary-line"><span>Shipping</span><span>Calculated at payment</span></div>
                    <div class="summary-total"><span>Total
                            due</span><strong>$<?= number_format($subtotal, 2) ?></strong></div>
                    <?php if ($cashOnDeliveryConfirmation): ?>
                        <div class="checkout-confirmation" role="status">
                            <strong>Cash on delivery order placed</strong>
                            <span>Order
                                <?= htmlspecialchars($cashOnDeliveryConfirmation['order_number'], ENT_QUOTES, 'UTF-8') ?></span>
                            Total due on delivery: $<?= number_format((float) $cashOnDeliveryConfirmation['subtotal'], 2) ?>
                            <a href="user_categories.php">Continue shopping</a>
                        </div>
                    <?php else: ?>
                        <form method="post" action="checkout.php" id="paymentForm">
                            <input type="hidden" name="csrf_token"
                                value="<?= htmlspecialchars($_SESSION['checkout_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                            <fieldset class="payment-choices">
                                <legend>Payment method</legend>
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="cash_on_delivery"
                                        <?= $selectedPaymentMethod === 'cash_on_delivery' ? 'checked' : '' ?>>
                                    <span>
                                        <strong>Cash on delivery</strong>
                                        <small>Pay when your order arrives</small>
                                    </span>
                                    <span class="payment-option-badge">On delivery</span>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="stripe"
                                        <?= $selectedPaymentMethod === 'stripe' ? 'checked' : '' ?>>
                                    <span>
                                        <strong>Stripe</strong>
                                        <small>Pay securely online</small>
                                    </span>
                                    <span class="payment-option-badge">Secure</span>
                                </label>
                            </fieldset>
                            <div class="delivery-fields" id="deliveryFields" <?= $selectedPaymentMethod === 'cash_on_delivery' ? '' : 'hidden' ?>>
                                <label for="customerName">
                                    Full name
                                    <input id="customerName" name="customer_name" type="text" maxlength="100"
                                        value="<?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?>"
                                        autocomplete="name" <?= $selectedPaymentMethod === 'cash_on_delivery' ? 'required' : '' ?>>
                                </label>
                                <label for="customerPhone">
                                    Phone number
                                    <input id="customerPhone" name="customer_phone" type="tel" maxlength="30"
                                        value="<?= htmlspecialchars($customerPhone, ENT_QUOTES, 'UTF-8') ?>"
                                        autocomplete="tel" <?= $selectedPaymentMethod === 'cash_on_delivery' ? 'required' : '' ?>>
                                </label>
                                <label for="deliveryAddress">
                                    Delivery address
                                    <textarea id="deliveryAddress" name="delivery_address" maxlength="2000"
                                        autocomplete="street-address" <?= $selectedPaymentMethod === 'cash_on_delivery' ? 'required' : '' ?>><?= htmlspecialchars($deliveryAddress, ENT_QUOTES, 'UTF-8') ?></textarea>
                                </label>
                            </div>
                            <p class="secure-note" id="paymentHelp"><span class="secure-mark"
                                    aria-hidden="true">✓</span><span>Payment details are handled securely by Stripe.</span>
                            </p>
                            <button class="pay-button" type="submit" id="paymentSubmit">Continue to secure payment</button>
                        </form>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </main>

    <footer class="checkout-footer"><strong>REGIX<span>.</span></strong> · Secure checkout</footer>
    <script>
        const paymentForm = document.getElementById('paymentForm');

        if (paymentForm) {
            const paymentSubmit = document.getElementById('paymentSubmit');
            const paymentHelp = document.getElementById('paymentHelp');
            const deliveryFields = document.getElementById('deliveryFields');
            const deliveryInputs = deliveryFields.querySelectorAll('input, textarea');

            paymentForm.querySelectorAll('input[name="payment_method"]').forEach(input => {
                input.addEventListener('change', () => {
                    const isStripe = input.value === 'stripe' && input.checked;

                    deliveryFields.hidden = isStripe;
                    deliveryInputs.forEach(field => {
                        field.required = !isStripe;
                    });
                    paymentSubmit.textContent = isStripe
                        ? 'Continue to secure payment'
                        : 'Place cash on delivery order';
                    paymentHelp.lastElementChild.textContent = isStripe
                        ? 'Payment details are handled securely by Stripe.'
                        : 'Pay in cash when your order is delivered.';
                });
            });

            const selectedMethod = paymentForm.querySelector('input[name="payment_method"]:checked');
            if (selectedMethod) {
                selectedMethod.dispatchEvent(new Event('change'));
            }
        }
    </script>
</body>

</html>