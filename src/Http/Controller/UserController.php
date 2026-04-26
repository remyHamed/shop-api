<?php
namespace App\Http\Controller;

use App\Domain\Service\UserService;
use App\Security\JwtHandler;

class UserController {
    public function __construct(
        private UserService $userService,
        private JwtHandler $jwtHandler
    ) {}

    public function createEmployee(): array {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);
        $decoded = $this->jwtHandler->decode($token);
        
        $requestorRole = $decoded->role ?? '';

        try {
            $user = $this->userService->createEmployee($data, $requestorRole);
            http_response_code(201);
            return [
                'message' => 'Employé créé avec succès',
                'user' => $user->toArray()
            ];
        } catch (Exception $e) {
            http_response_code(403);
            return ['error' => $e->getMessage()];
        }
    }

    public function register(): array {
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $user = $this->userService->registerClient($data);
            http_response_code(201);
            return [
                'message' => 'Compte client créé avec succès',
                'user' => $user->toArray()
            ];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }


}