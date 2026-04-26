<?php

namespace App\Http\Middleware;

use App\Security\JwtHandler;

class AuthMiddleware
{
 
    private const ROLES_HIERARCHY = [
        'ROLE_CLIENT'   => 1,
        'ROLE_EMPLOYEE' => 2,
        'ROLE_ADMIN'    => 3
    ];

    public function __construct(private JwtHandler $jwtHandler) {}

    public function check(string $requiredRole = 'ROLE_CLIENT'): array
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

        if (!$authHeader && function_exists('getallheaders')) {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->abort(401, 'Accès refusé : Token manquant ou format invalide');
        }

        $token = $matches[1];

        $userData = $this->jwtHandler->validateToken($token);

        if (!$userData) {
            $this->abort(401, 'Accès refusé : Token invalide ou expiré');
        }

        $userRole = $userData['role'] ?? 'ROLE_CLIENT';
        
        $userLevel = self::ROLES_HIERARCHY[$userRole] ?? 0;
        $requiredLevel = self::ROLES_HIERARCHY[$requiredRole] ?? 1;

        if ($userLevel < $requiredLevel) {
            $this->abort(403, 'Accès interdit : privilèges insuffisants (Requis: ' . $requiredRole . ')');
        }

        return (array) $userData;
    }

    private function abort(int $code, string $message): void
    {
        header('Content-Type: application/json', true, $code);
        echo json_encode([
            'error' => $message,
            'code' => $code
        ]);
        exit;
    }
}