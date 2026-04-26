<?php
namespace App\Repository\Interfaces;

use App\Domain\Entity\Product;

interface ProductRepositoryInterface {
    public function findById(int $id): ?Product;
    public function findByStoreId(int $storeId): array;
    public function save(Product $product): void;
    public function update(Product $product): void;
    public function delete(int $id): void;
}