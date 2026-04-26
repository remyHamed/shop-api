<?php

namespace App\Domain\Entity;

class Sale
{
    public function __construct(
        private ?int $id,
        private int $productId,
        private int $storeId,
        private int $quantity,
        private float $unitPrice,
        private float $totalAmount,
        private \DateTimeImmutable $soldAt
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getProductId(): int { return $this->productId; }
    public function getStoreId(): int { return $this->storeId; }
    public function getQuantity(): int { return $this->quantity; }
    public function getUnitPrice(): float { return $this->unitPrice; }
    public function getTotalAmount(): float { return $this->totalAmount; }
    public function getSoldAt(): \DateTimeImmutable { return $this->soldAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'store_id' => $this->storeId,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'total_amount' => $this->totalAmount,
            'sold_at' => $this->soldAt->format('Y-m-d H:i:s'),
        ];
    }
}