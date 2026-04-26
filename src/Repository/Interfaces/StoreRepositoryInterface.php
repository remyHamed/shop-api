<?php
namespace App\Repository\Interfaces;

interface StoreRepositoryInterface 
{
    public function findAll(array $criteria, ?string $orderBy = null, ?string $order = 'ASC'): array;
    public function save(\App\Domain\Entity\Store $store): void;
}