<?php
namespace App\Http\Controller;

use App\Security\JwtHandler;
use PDO;

class AuthController {
    public function __construct(
        private PDO $pdo, 
        private JwtHandler $jwtHandler
    ) {}

    public function login(): array {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            return ['error' => 'Identifiants invalides'];
        }

        $token = $this->jwtHandler->generateToken([
            'id'    => $user['id'],
            'email' => $user['email'],
            'role'  => $user['role']
        ]);

        return [
            'status' => 'success',
            'token'  => $token
        ];
    }
}