<?php
namespace App\Repository\Persistence;

use App\Repository\Interfaces\StoreRepositoryInterface;
use App\Domain\Entity\Store;
use PDO;

class SqlStoreRepository implements StoreRepositoryInterface 
{
    public function __construct(private PDO $pdo) {}

    // La signature doit être strictement identique à l'interface
    public function findAll(array $criteria, ?string $orderBy = null, ?string $order = 'ASC'): array 
    {
        $query = "SELECT * FROM stores";
        
        // Gestion très basique du tri (OrderBy)
        if ($orderBy) {
            $query .= " ORDER BY " . $orderBy . " " . $order;
        }

        $stmt = $this->pdo->query($query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($data) {
            return new Store(
                $data['id'],
                $data['name'],
                $data['address'],
                $data['city'],
                new \DateTimeImmutable($data['created_at'])
            );
        }, $rows);
    }

    public function save(Store $store): void 
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO stores (name, address, city, created_at) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $store->getName(),
            $store->getAddress(),
            $store->getCity(),
            $store->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }
}