<?php
namespace App\Repository\Persistence;

use App\Domain\Entity\Product;
use App\Repository\Interfaces\ProductRepositoryInterface;
use PDO;

class SqlProductRepository implements ProductRepositoryInterface {
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?Product {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new Product(
            $data['id'], $data['store_id'], $data['name'], 
            $data['description'], (float)$data['price'], (int)$data['stock']
        ) : null;
    }

    public function findByStoreId(int $storeId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE store_id = ?");
        $stmt->execute([$storeId]);
        $products = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = new Product(
                $data['id'], $data['store_id'], $data['name'], 
                $data['description'], (float)$data['price'], (int)$data['stock']
            );
        }
        return $products;
    }

    public function save(Product $product): void {
        $stmt = $this->pdo->prepare("INSERT INTO products (store_id, name, description, price, stock, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $product->getStoreId(), 
            $product->getName(), 
            $product->getDescription(), 
            $product->getPrice(), 
            $product->getStock()
        ]);

        $id = (int)$this->pdo->lastInsertId();
            
        $product->setId($id); 
    }

    public function update(Product $product): void {
        $stmt = $this->pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ? WHERE id = ?");
        $stmt->execute([
            $product->getName(), 
            $product->getDescription(), 
            $product->getPrice(), 
            $product->getStock(), 
            $product->getId()
        ]);
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
    }
}