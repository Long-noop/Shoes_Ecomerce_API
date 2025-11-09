<?php
require_once ROOT_PATH . "core/Controller.php";
class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    public function register() {
        $validator = new Validator();
        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'first_name' => 'required',
            'last_name' => 'required'
        ];

        if(!$validator->validate($this->request->all(), $rules)){
            $this->response->error('Validated failed', 422, $validator->getErrors());
        }

        $existingUser = $this->userModel->findByEmail($this->request->get('email'));
        if ($existingUser) {
            $this->response->error('Email already registered', 400);
        }

        $userId = $this->userModel->register($this->request->all());
        if($userId){
            $user = $this->userModel->findById($userId);
            unset($user['password']);

            $token = Auth::login($user['email'], $this->request->get('password'));
            $this->response->success('Registration successful', [
                'user' => $user,
                'token' => $token['token']
            ], 201);
        }
        else{
            $this->response->error('Registration failed', 500);
        }
    }

    public function login() {
        $validator = new Validator();
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        $result = Auth::login(
            $this->request->get('email'),
            $this->request->get('password')
        );

        if ($result) {
            $this->response->success('Login successful', $result);
        } else {
            $this->response->error('Invalid credentials', 401);
        }
    }

    public function me() {
        $user = $this->requireAuth();
        $userData = $this->userModel->findById($user['user_id']);
        unset($userData['password']);
        
        $this->response->success('User data', $userData);
    }

    public function logout() {
        $this->requireAuth();
        // In JWT, logout is handled client-side by removing the token
        $this->response->success('Logout successful', 200);
    }

    public function changePassword() {
        $user = $this->requireAuth();

        $validator = new Validator();
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min:8'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        // Verify current password
        $userData = $this->userModel->findById($user['user_id']);
        if (!password_verify($this->request->get('current_password'), $userData['password'])) {
            $this->response->error('Current password is incorrect', 400);
        }

        // Update password
        $success = $this->userModel->changePassword(
            $user['user_id'],
            $this->request->get('new_password')
        );

        if ($success) {
            $this->response->success('Password changed successfully', 200);
        } else {
            $this->response->error('Failed to change password', 500);
        }
    }
}
?>