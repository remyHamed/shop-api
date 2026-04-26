<?php
namespace App\Domain\Service;

use App\Repository\Interfaces\StoreRepositoryInterface;
use App\Domain\Entity\Store;

class StoreService {
    public function __construct(private StoreRepositoryInterface $repository) {}

    public function listAllStores(): array {
        return $this->repository->findAll([], 'created_at', 'DESC');
    }

    public function createNewStore(array $data): Store {
        if (empty($data['name']) || empty($data['city'])) {
            throw new \InvalidArgumentException("Le nom et la ville sont obligatoires.");
        }

        $store = new Store(
            null,
            $data['name'],
            $data['address'] ?? '',
            $data['city'],
            new \DateTimeImmutable()
        );

        $this->repository->save($store);
        return $store;
    }
}