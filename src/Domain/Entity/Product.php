<?php

namespace App\Domain\Entity;

class Product
{
    public function __construct(
        private ?int $id,
        private int $storeId,
        private string $name,
        private string $description,
        private float $price,
        private int $stock,
        private \DateTimeImmutable $createdAt
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getStoreId(): int { return $this->storeId; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getPrice(): float { return $this->price; }
    public function getStock(): int { return $this->stock; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    
    public function setId(int $id): void {$this->id = $id;}

    public function addStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("La quantité à ajouter doit être positive.");
        }
        $this->stock += $quantity;
    }

    public function removeStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("La quantité à retirer doit être positive.");
        }
        if ($quantity > $this->stock) {
            throw new \RuntimeException("Stock insuffisant. Disponible : {$this->stock}, demandé : {$quantity}.");
        }
        $this->stock -= $quantity;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'store_id'    => $this->storeId,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => $this->price,
            'stock'       => $this->stock,
            'created_at'  => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}