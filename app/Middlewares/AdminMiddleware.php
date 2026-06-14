<?php

class AdminMiddleware
{
    public static function user()
    {
        $user = AuthMiddleware::user();

        if (
            !isset($user['role']) ||
            $user['role'] !== 'admin'
        ) {

            http_response_code(403);

            echo json_encode([
                'success' => false,
                'message' => 'Không có quyền truy cập'
            ]);

            exit;
        }

        return $user;
    }
}