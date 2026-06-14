<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper
{
    private static string $secret =
    'my_super_secret_jwt_key_2026_mobile_shop_ltw_123456789';

    public static function generate(array $user): string
    {
        $payload = [
            'id'   => $user['id'],
            'role' => $user['role'],
            'exp'  => time() + 86400
        ];

        return JWT::encode(
            $payload,
            self::$secret,
            'HS256'
        );
    }

    public static function verify(string $token)
    {
        return JWT::decode(
            $token,
            new Key(
                self::$secret,
                'HS256'
            )
        );
    }
}