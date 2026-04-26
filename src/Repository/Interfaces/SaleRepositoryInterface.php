<?php
namespace App\Repository\Interfaces;

use App\Domain\Entity\Sale;

interface SaleRepositoryInterface {
    public function save(Sale $sale): void;
    public function findByStoreId(int $storeId): array;
}