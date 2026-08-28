<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class JwtService
{
    private string $secret;
    private int $expiry;

    public function __construct()
    {
        $this->secret = (string) getenv('JWT_SECRET');
        $this->expiry = (int) (getenv('JWT_EXPIRY') ?: 28800);

        if ($this->secret === '') {
            throw new \RuntimeException('JWT_SECRET is not configured.');
        }
    }

    public function issueToken(array $user): string
    {
        $now = time();

        $payload = [
            'iss' => 'civentral-superadmin',
            'sub' => (string) $user['user_id'],
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->expiry,
            'jti' => bin2hex(random_bytes(16)),

            'user_id' => (int) $user['user_id'],
            'employee_id' => $user['employee_id'] ?? null,
            'role_id' => isset($user['role_id']) ? (int) $user['role_id'] : null,
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function verifyToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key($this->secret, 'HS256')
            );

            return json_decode(json_encode($decoded), true);
        } catch (Throwable $e) {
            return null;
        }
    }
}