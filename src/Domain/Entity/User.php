<?php
namespace App\Domain\Entity;

class User {
    public function __construct(
        private ?int $id,
        private string $email,
        private string $password,
        private string $role = 'ROLE_CLIENT'
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getRole(): string { return $this->role; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role
        ];
    }
}