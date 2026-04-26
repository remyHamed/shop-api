<?php
namespace App\Http\Controller;

use App\Domain\Service\UserService;
use App\Security\JwtHandler;
use Exception;

class UserController {
    public function __construct(
        private UserService $userService,
        private JwtHandler $jwtHandler
    ) {}

    public function list(): array {
        $users = $this->userService->listAllUsers();
        return array_map(fn($u) => $u->toArray(), $users);
    }

    public function register(): array {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $user = $this->userService->registerClient($data);
            http_response_code(201);
            return ['message' => 'Client créé', 'user' => $user->toArray()];
        } catch (Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }

    public function createEmployee(): array {
        $data = json_decode(file_get_contents('php://input'), true);
        $requestorRole = $this->getRequestorRole();

        try {
            $user = $this->userService->createEmployee($data, $requestorRole);
            http_response_code(201);
            return ['message' => 'Employé créé', 'user' => $user->toArray()];
        } catch (Exception $e) {
            http_response_code(403);
            return ['error' => $e->getMessage()];
        }
    }

    public function update(array $vars): array {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $user = $this->userService->updateUser((int)$vars['id'], $data);
            return ['message' => 'Utilisateur mis à jour', 'user' => $user->toArray()];
        } catch (Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }

    public function delete(array $vars): array {
        try {
            $this->userService->deleteUser((int)$vars['id']);
            http_response_code(204);
            return [];
        } catch (Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }

private function getRequestorRole(): string {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    $userData = $this->jwtHandler->validateToken($token);
    return $userData['role'] ?? '';
}
}