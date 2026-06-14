<?php

class AuthService
{
    private $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    public function register($data)
    {
        $exist = $this->userRepo
            ->findByEmail($data['email']);

        if ($exist) {
            throw new Exception(
                "Email đã tồn tại"
            );
        }

        $data['password'] = password_hash(
            $data['password'],
            PASSWORD_BCRYPT
        );

        $data['role'] = 'user';

        $this->userRepo->create($data);

        return true;
    }

    public function login($email, $password)
    {
        $user = $this->userRepo
            ->findByEmail($email);

        if (!$user) {
            throw new Exception(
                "Người dùng không tồn tại"
            );
        }

        if (
            !password_verify(
                $password,
                $user['password']
            )
        ) {
            throw new Exception(
                "Sai mật khẩu"
            );
        }

        $token = JwtHelper::generate($user);

        unset($user['password']);

        return [
            'token' => $token,
            'user' => $user
        ];
    }
}