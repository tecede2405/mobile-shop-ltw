<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public static function user()
    {
        $headers = getallheaders();

        if (
            !isset($headers['Authorization'])
        ) {
            throw new Exception(
                "Không có token"
            );
        }

        $token = str_replace(
            'Bearer ',
            '',
            $headers['Authorization']
        );

        $decoded = JWT::decode(
            $token,
            new Key(
                'my_super_secret_jwt_key_2026_mobile_shop_ltw_123456789',
                'HS256'
            )
        );

        return (array)$decoded;
    }
}