<?php
namespace App\Repository\Interfaces;

use App\Domain\Entity\User;

interface UserRepositoryInterface {
    public function findByEmail(string $email): ?User;
    public function save(User $user): void;
    public function findAll(): array;
    public function delete(int $id): void;
}