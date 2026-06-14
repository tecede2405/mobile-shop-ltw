<?php

class AuthController
{
    private $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    public function register()
    {
        try {

            $data = json_decode(
                file_get_contents("php://input"),
                true
            );

            if (
                empty($data['username']) ||
                empty($data['email']) ||
                empty($data['password'])
            ) {
                http_response_code(400);

                echo json_encode([
                    'message' => 'Thiếu thông tin'
                ]);

                return;
            }

            $this->service->register($data);

            http_response_code(201);

            echo json_encode([
                'message' => 'Đăng ký thành công'
            ]);

        } catch (Exception $e) {

            http_response_code(400);

            echo json_encode([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function login()
    {
        try {

            $data = json_decode(
                file_get_contents("php://input"),
                true
            );

            $result = $this->service->login(
                $data['email'],
                $data['password']
            );

            echo json_encode($result);

        } catch (Exception $e) {

            http_response_code(400);

            echo json_encode([
                'message' => $e->getMessage()
            ]);
        }
    }
}