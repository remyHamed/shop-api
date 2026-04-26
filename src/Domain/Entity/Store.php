<?php
namespace App\Domain\Entity;

class Store {
    public function __construct(
        public ?int $id,
        public string $name,
        public string $address,
        public string $city,
        public \DateTimeImmutable $createdAt
    ) {}

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s')
        ];
    }
}