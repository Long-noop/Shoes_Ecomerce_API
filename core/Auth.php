<?php
require_once ROOT_PATH . "/core/JWT.php";
class Auth {
    public static function login($email, $password){
    require_once ROOT_PATH . '/models/User.php';
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        $token = JWT::encode($payload);

        unset($user['password']);
        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public static function validateToken($token){
        try {
            $payload = JWT::decode($token);
            return $payload;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function hashPassword($password){
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
?>