<?php

declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';

// PHP 8.0 alternative to an enum.
final class OrderSaveMode
{
    public const ADD_NEW = 'add_new';
    public const UPDATE = 'update';
}

class Order
{
    public function __construct(
        private PDO $db,
        private string $mode = OrderSaveMode::ADD_NEW,
        public ?int $id = null,
        public ?int $userId = null,
        public ?int $cartId = null,
        public string $status = 'pending',
        public string $totalJod = '0.00',
        public string $shippingName = '',
        public string $shippingPhone = '',
        public string $shippingAddressLine1 = '',
        public ?string $shippingAddressLine2 = null,
        public ?string $notes = null
    ) {
        if (!in_array($mode, [OrderSaveMode::ADD_NEW, OrderSaveMode::UPDATE], true)) {
            throw new InvalidArgumentException('Invalid order save mode.');
        }
    }

    public function save(?string $orderSaveMode = null): bool
    {
        $mode = $orderSaveMode ?? $this->mode;

        if ($mode === OrderSaveMode::ADD_NEW) {
            return $this->create();
        }

        if ($mode === OrderSaveMode::UPDATE) {
            return $this->update();
        }

        throw new InvalidArgumentException('Invalid order save mode.');
    }

    public function create(): bool
    {
        $this->validate();

        $sql = 'INSERT INTO orders (
                    user_id, cart_id, status, total_jod,
                    shipping_name, shipping_phone, shipping_address_line1,
                    shipping_address_line2, notes
                ) VALUES (
                    :user_id, :cart_id, :status, :total_jod,
                    :shipping_name, :shipping_phone, :shipping_address_line1,
                    :shipping_address_line2, :notes
                )';

        $statement = $this->db->prepare($sql);
        $saved = $statement->execute($this->values());

        if ($saved) {
            $this->id = (int) $this->db->lastInsertId();
            $this->mode = OrderSaveMode::UPDATE;
        }

        return $saved;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM orders WHERE id = :id AND deleted_at IS NULL'
        );
        $statement->execute(['id' => $id]);
        $order = $statement->fetch(PDO::FETCH_ASSOC);

        return $order === false ? null : $order;
    }

    public function findAll(): array
    {
        $statement = $this->db->query(
            'SELECT * FROM orders WHERE deleted_at IS NULL ORDER BY id DESC'
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByUserId(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM orders
             WHERE user_id = :user_id AND deleted_at IS NULL
             ORDER BY id DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(): bool
    {
        if ($this->id === null || $this->findById($this->id) === null) {
            return false;
        }

        $this->validate();

        $sql = 'UPDATE orders SET
                    user_id = :user_id,
                    cart_id = :cart_id,
                    status = :status,
                    total_jod = :total_jod,
                    shipping_name = :shipping_name,
                    shipping_phone = :shipping_phone,
                    shipping_address_line1 = :shipping_address_line1,
                    shipping_address_line2 = :shipping_address_line2,
                    notes = :notes
                WHERE id = :id AND deleted_at IS NULL';

        $values = $this->values();
        $values['id'] = $this->id;
        $statement = $this->db->prepare($sql);

        return $statement->execute($values);
    }

    public function delete(int $id): bool
    {
        // Soft delete: keep the order history in the database.
        $statement = $this->db->prepare(
            'UPDATE orders SET deleted_at = CURRENT_TIMESTAMP
             WHERE id = :id AND deleted_at IS NULL'
        );
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    private function values(): array
    {
        return [
            'user_id' => $this->userId,
            'cart_id' => $this->cartId,
            'status' => $this->status,
            'total_jod' => $this->totalJod,
            'shipping_name' => $this->shippingName,
            'shipping_phone' => $this->shippingPhone,
            'shipping_address_line1' => $this->shippingAddressLine1,
            'shipping_address_line2' => $this->shippingAddressLine2,
            'notes' => $this->notes,
        ];
    }

    private function validate(): void
    {
        if ($this->userId === null || $this->userId < 1) {
            throw new InvalidArgumentException('A valid customer ID is required.');
        }

        $statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($this->status, $statuses, true)) {
            throw new InvalidArgumentException('Invalid order status.');
        }

        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $this->totalJod)) {
            throw new InvalidArgumentException('Total must be a valid non-negative amount with up to 2 decimal places.');
        }

        if (trim($this->shippingName) === '' || trim($this->shippingPhone) === '' || trim($this->shippingAddressLine1) === '') {
            throw new InvalidArgumentException('Shipping name, phone, and address are required.');
        }
    }
}
