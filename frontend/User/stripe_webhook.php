<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/stripe.php';
require_once __DIR__ . '/../../backend/classes/StripeClass.php';
require_once __DIR__ . '/../../backend/classes/OrderClass.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

$payload = file_get_contents('php://input');
$signature = (string) ($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
$secret = defined('STRIPE_WEBHOOK_SECRET') ? STRIPE_WEBHOOK_SECRET : '';

if (!is_string($payload) || !StripeClass::verifyWebhookSignature($payload, $signature, $secret)) {
    http_response_code($secret === '' ? 503 : 400);
    echo json_encode(['error' => $secret === '' ? 'Webhook is not configured.' : 'Invalid signature.']);
    exit;
}

try {
    $event = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($event)) {
        throw new UnexpectedValueException('Invalid Stripe event.');
    }

    $eventType = $event['type'] ?? '';
    $session = $event['data']['object'] ?? null;
    if (!is_array($session)) {
        throw new UnexpectedValueException('Invalid Stripe session event.');
    }

    if (in_array($eventType, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
        $orderId = filter_var(
            $session['metadata']['order_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $referenceId = filter_var(
            $session['client_reference_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($orderId === false || $referenceId !== $orderId) {
            throw new UnexpectedValueException('Stripe order reference is invalid.');
        }

        if (($session['payment_status'] ?? '') === 'paid') {
            $confirmed = (new OrderClass())->confirmStripePayment(
                $orderId,
                (int) ($session['amount_total'] ?? 0),
                (string) ($session['currency'] ?? '')
            );
            if (!$confirmed) {
                throw new RuntimeException('Stripe payment could not be matched to the order.');
            }
        }
    } elseif (in_array($eventType, ['checkout.session.async_payment_failed', 'checkout.session.expired'], true)) {
        $orderId = filter_var(
            $session['metadata']['order_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $referenceId = filter_var(
            $session['client_reference_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($orderId === false || $referenceId !== $orderId) {
            throw new UnexpectedValueException('Stripe order reference is invalid.');
        }

        (new OrderClass())->failPayment($orderId);
    }

    http_response_code(200);
    echo json_encode(['received' => true]);
} catch (JsonException | UnexpectedValueException $error) {
    error_log('Stripe webhook payload: ' . $error->getMessage());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid event payload.']);
} catch (Throwable $error) {
    error_log('Stripe webhook processing: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Event processing failed.']);
}
