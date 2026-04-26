<?php
namespace App\Repository\Persistence;

use App\Repository\Interfaces\UserRepositoryInterface;
use App\Domain\Entity\User;
use PDO;

class SqlUserRepository implements UserRepositoryInterface {
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new User($data['id'], $data['email'], $data['password'], $data['role']);
    }

    public function save(User $user): void {
        $stmt = $this->pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$user->getEmail(), $user->getPassword(), $user->getRole()]);
    }

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM users");
        return array_map(fn($d) => new User($d['id'], $d['email'], $d['password'], $d['role']), $stmt->fetchAll());
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
}