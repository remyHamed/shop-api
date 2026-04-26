<?php
namespace App\Repository\Persistence;

use App\Domain\Entity\Store;

interface StoreRepositoryInterface {
    public function findAll(array $filters, string $sort, string $order): array;
    public function save(Store $store): void;
}