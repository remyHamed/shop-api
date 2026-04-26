<?php
namespace App\Domain\Service;

use App\Repository\Interfaces\UserRepositoryInterface;
use App\Domain\Entity\User;

class UserService {
    public function __construct(private UserRepositoryInterface $repository) {}



    public function createEmployee(array $data, string $requestorRole): User {

        if ($requestorRole !== 'ROLE_ADMIN') {
            throw new Exception("Accès refusé : Seul un administrateur peut créer un employé.");
        }
        
        if (empty($data['email']) || empty($data['password'])) {
            throw new \InvalidArgumentException("Email et mot de passe requis.");
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        $user = new User(
            null,
            $data['email'],
            $hashedPassword,
            'ROLE_EMPLOYEE'
        );

        $this->repository->save($user);
        return $user;
    }

    public function getAllUsers(): array {
        return $this->repository->findAll();
    }

    public function registerClient(array $data): User {
        if (empty($data['email']) || empty($data['password'])) {
            throw new \InvalidArgumentException("Email et mot de passe requis pour le client.");
        }

        if ($this->repository->findByEmail($data['email'])) {
            throw new \Exception("Cet email est déjà utilisé.");
        }

        $user = new User(
            null,
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            'ROLE_CLIENT'
        );

        $this->repository->save($user);
        return $user;
    }

    public function deleteUser(int $id, string $requestorRole): void {
        if ($requestorRole !== 'ROLE_ADMIN') {
            throw new Exception("Accès refusé : Seul un administrateur peut supprimer un utilisateur.");
        }
        $this->repository->delete($id);
    }
}