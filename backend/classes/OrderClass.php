<?php
require_once __DIR__ . '/../config/db.php';

class OrderClass
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB_conn::getConnection();
    }

    public function createCashOnDeliveryOrder(array $customer, array $items): array
    {
        $name = trim($customer['name'] ?? '');
        $phone = trim($customer['phone'] ?? '');
        $address = trim($customer['address'] ?? '');

        if ($name === '' || mb_strlen($name) > 100) {
            throw new InvalidArgumentException('Enter a valid name.');
        }

        if ($phone === '' || mb_strlen($phone) > 30) {
            throw new InvalidArgumentException('Enter a valid phone number.');
        }

        if ($address === '' || mb_strlen($address) > 2000) {
            throw new InvalidArgumentException('Enter a valid delivery address.');
        }

        if ($items === []) {
            throw new InvalidArgumentException('The order has no items.');
        }

        $normalizedItems = [];
        $subtotal = 0.0;

        foreach ($items as $item) {
            $itemName = trim((string) ($item['name'] ?? ''));
            $price = $item['price'] ?? null;
            $quantity = filter_var(
                $item['quantity'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if (
                $itemName === '' ||
                mb_strlen($itemName) > 180 ||
                !is_numeric($price) ||
                !is_finite((float) $price) ||
                (float) $price < 0 ||
                $quantity === false
            ) {
                throw new InvalidArgumentException('The order contains invalid items.');
            }

            $unitPrice = round((float) $price, 2);
            $lineTotal = round($unitPrice * $quantity, 2);
            $subtotal += $lineTotal;

            $normalizedItems[] = [
                'name' => $itemName,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ];
        }

        $subtotal = round($subtotal, 2);
        $orderNumber = 'RGX-' . strtoupper(bin2hex(random_bytes(5)));

        $this->db->beginTransaction();

        try {
            $orderStatement = $this->db->prepare(
                "INSERT INTO orders
                    (order_number, customer_name, customer_phone, delivery_address,
                     payment_method, payment_status, order_status, currency, subtotal)
                 VALUES (?, ?, ?, ?, 'cash_on_delivery', 'pending', 'pending', 'USD', ?)"
            );
            $orderStatement->execute([
                $orderNumber,
                $name,
                $phone,
                $address,
                $subtotal,
            ]);

            $orderId = (int) $this->db->lastInsertId();
            $itemStatement = $this->db->prepare(
                'INSERT INTO order_items
                    (order_id, product_name, unit_price, quantity, line_total)
                 VALUES (?, ?, ?, ?, ?)'
            );

            foreach ($normalizedItems as $item) {
                $itemStatement->execute([
                    $orderId,
                    $item['name'],
                    $item['unit_price'],
                    $item['quantity'],
                    $item['line_total'],
                ]);
            }
            $this->db->commit();

            return [
                'id' => $orderId,
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
            ];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }
}
