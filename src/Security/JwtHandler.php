<?php
namespace App\Security;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHandler {
    private string $secretKey = "votre_cle_secrete_super_secure";
    private string $algorithm = 'HS256';

    public function generateToken(array $userData): string {
        $payload = [
            'iss' => 'store-api',
            'iat' => time(),
            'exp' => time() + (3600 * 24),
            'user' => $userData
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    public function validateToken(string $token): ?array {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded->user;
        } catch (\Exception $e) {
            return null;
        }
    }
}