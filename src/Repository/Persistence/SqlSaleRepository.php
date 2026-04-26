<?php
namespace App\Repository\Persistence;

use App\Domain\Entity\Sale;
use App\Repository\Interfaces\SaleRepositoryInterface;
use PDO;

class SqlSaleRepository implements SaleRepositoryInterface {
    public function __construct(private PDO $pdo) {}

    public function save(Sale $sale): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO sales (product_id, store_id, quantity, unit_price, total_amount, sold_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $sale->getProductId(),
            $sale->getStoreId(),
            $sale->getQuantity(),
            $sale->getUnitPrice(),
            $sale->getTotalAmount()
        ]);
    }

    public function findByStoreId(int $storeId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM sales WHERE store_id = ?");
        $stmt->execute([$storeId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}