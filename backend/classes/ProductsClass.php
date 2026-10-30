<?php
declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/db.php';

class Product {

    private PDO $pdo;

    public function __construct() {

        $this->pdo = DB_conn::getConnection();
    }

    public function getAllProducts(bool $deleted_at = true) : array|false{

        if ($deleted_at) {
            $sql = "SELECT * FROM products WHERE deleted_at IS NULL";
        } else {
            $sql = "SELECT * FROM products";
        }

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $results = $stmt->fetchAll();

        return empty($results) ? false : $results;
    }

    public function countProducts(
        bool $deleted_at = true,
        string $search = '',
        ?int $categoryId = null
    ) : int {

        [$where, $parameters] = $this->buildProductFilters(
            $deleted_at,
            $search,
            $categoryId
        );
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM products" . $where
        );

        $stmt->execute($parameters);

        return (int) $stmt->fetchColumn();
    }

    public function getProductsPage(
        int $limit,
        int $offset,
        bool $deleted_at = true,
        string $search = '',
        ?int $categoryId = null
    ) : array {

        [$where, $parameters] = $this->buildProductFilters(
            $deleted_at,
            $search,
            $categoryId
        );
        $stmt = $this->pdo->prepare(
            "SELECT * FROM products" . $where .
            " ORDER BY id DESC LIMIT ? OFFSET ?"
        );

        foreach ($parameters as $index => $parameter) {
            $stmt->bindValue(
                $index + 1,
                $parameter,
                is_int($parameter) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }

        $stmt->bindValue(count($parameters) + 1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(count($parameters) + 2, $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function buildProductFilters(
        bool $includeActiveOnly,
        string $search,
        ?int $categoryId
    ) : array {
        $conditions = [];
        $parameters = [];

        if ($includeActiveOnly) {
            $conditions[] = 'deleted_at IS NULL';
        }

        if ($search !== '') {
            $conditions[] = '(name LIKE ? OR brand LIKE ?)';
            $searchTerm = '%' . $search . '%';
            $parameters[] = $searchTerm;
            $parameters[] = $searchTerm;
        }

        if ($categoryId !== null) {
            $conditions[] = 'category_id = ?';
            $parameters[] = $categoryId;
        }

        $where = $conditions
            ? ' WHERE ' . implode(' AND ', $conditions)
            : '';

        return [$where, $parameters];
    }
    
    public function getProduct(int $id, bool $includeDeleted = false) : array|false {

        if ($includeDeleted) {
            $sql = "SELECT * FROM products WHERE id = ?";
        } else {
            $sql = "SELECT * FROM products WHERE deleted_at IS NULL AND id = ?";
        }
        
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([$id]);

        $results = $stmt->fetch();

        return empty($results) ? false : $results;
        
    }

    public function createProduct(array $data) : bool {

        $sql = "INSERT INTO products (
                category_id,
                name,
                brand,
                description,
                price,
                quantity,
                condition_type,
                listing_status,
                product_img,
                product_variant
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['brand'],
            $data['description'],
            $data['price'],
            $data['quantity'],
            $data['condition_type'],
            $data['listing_status'] ?? 'available',
            $data['product_img'] ?? null,
            $data['product_variant'] ?? null
        ]);
    }

    public function updateProduct(int $id, array $data) : bool {
        
        $sql = "UPDATE products
            SET category_id = ?,
                name = ?,
                brand = ?,
                description = ?,
                price = ?,
                quantity = ?,
                condition_type = ?,
                listing_status = ?,
                product_img = ?,
                product_variant = ?
            WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['brand'],
            $data['description'],
            $data['price'],
            $data['quantity'],
            $data['condition_type'],
            $data['listing_status'] ?? 'available',
            $data['product_img'] ?? null,
            $data['product_variant'] ?? null,
            $id
        ]);
    }

    public function deleteProduct(int $id) : bool {

        $sql = "UPDATE products
            SET deleted_at = CURRENT_TIMESTAMP
            WHERE id = ?
              AND deleted_at IS NULL";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$id]) && $stmt->rowCount() > 0;
    }

    public function reactivateProduct(int $id) : bool {
        
        $sql = "UPDATE products
            SET deleted_at = NULL
            WHERE id = ? AND deleted_at IS NOT NULL";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$id]) && $stmt->rowCount() > 0;

    }   
}
?>