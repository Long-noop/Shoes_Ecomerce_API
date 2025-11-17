<?php
class UserController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    // Admin: Get all users
    public function index() {
        $this->requireAdmin();

        $page = $this->request->get('page', 1);
        $perPage = $this->request->get('per_page', DEFAULT_PAGE_SIZE);
        $offset = ($page - 1) * $perPage;
        $keyword = $this->request->get('keyword', '');

        if ($keyword) {
            $users = $this->userModel->search($keyword, $perPage, $offset);
            $total = $this->userModel->count("email LIKE '%$keyword%' OR first_name LIKE '%$keyword%' OR last_name LIKE '%$keyword%'");
        } else {
            $users = $this->userModel->findAll($perPage, $offset);
            $total = $this->userModel->count();
        }

        // Remove passwords from response
        foreach ($users as &$user) {
            unset($user['password']);
        }

        $this->response->paginate($users, $total, $page, $perPage);
    }

    // Get single user
    public function show($id) {
        $currentUser = $this->requireAuth();

        // Users can only see their own profile, admins can see any
        if ($currentUser['role'] !== 'admin' && $currentUser['user_id'] != $id) {
            $this->response->error('Forbidden', 403);
        }

        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $this->response->error('User not found', 404);
        }

        unset($user['password']);
        $this->response->success('User details', $user);
    }

    // Update user profile
    public function update($id) {
        $currentUser = $this->requireAuth();

        // Users can only update their own profile, admins can update any
        if ($currentUser['role'] !== 'admin' && $currentUser['user_id'] != $id) {
            $this->response->error('Forbidden', 403);
        }

        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->response->error('User not found', 404);
        }

        $validator = new Validator();
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => '',
            'date_of_birth' => '',
            'gender' => 'in:male,female,other'
        ];

        if (!$validator->validate($this->request->all(), $rules)) {
            $this->response->error('Validation failed', 422, $validator->getErrors());
        }

        // Check if email is already taken by another user
        if ($this->request->get('email') !== $user['email']) {
            $existingUser = $this->userModel->findByEmail($this->request->get('email'));
            if ($existingUser) {
                $this->response->error('Email already in use', 400);
            }
        }

        $success = $this->userModel->updateProfile($id, $this->request->all());

        if ($success) {
            $updated = $this->userModel->findById($id);
            unset($updated['password']);
            $this->response->success('Profile updated successfully', $updated);
        } else {
            $this->response->error('Failed to update profile', 500);
        }
    }

    // Admin: Delete user
    public function destroy($id) {
        $this->requireAdmin();

        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->response->error('User not found', 404);
        }

        // Prevent deleting admin accounts
        if ($user['role'] === 'admin') {
            $this->response->error('Cannot delete admin account', 400);
        }

        $success = $this->userModel->delete($id);

        if ($success) {
            $this->response->success('User deleted successfully', null);
        } else {
            $this->response->error('Failed to delete user', 500);
        }
    }

    // Admin: Ban user
    public function ban($id) {
        $this->requireAdmin();

        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->response->error('User not found', 404);
        }

        if ($user['role'] === 'admin') {
            $this->response->error('Cannot ban admin account', 400);
        }

        $success = $this->userModel->banUser($id);

        if ($success) {
            $this->response->success('User banned successfully', null);
        } else {
            $this->response->error('Failed to ban user', 500);
        }
    }

    // Admin: Activate user
    public function activate($id) {
        $this->requireAdmin();

        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->response->error('User not found', 404);
        }

        $success = $this->userModel->activateUser($id);

        if ($success) {
            $this->response->success('User activated successfully', null);
        } else {
            $this->response->error('Failed to activate user', 500);
        }
    }

    // Update avatar
    public function updateAvatar($id) {
        $currentUser = $this->requireAuth();

        if ($currentUser['role'] !== 'admin' && $currentUser['user_id'] != $id) {
            $this->response->error('Forbidden', 403);
        }

        $file = $this->request->file('avatar');
        if (!$file) {
            $this->response->error('No file uploaded', 400);
        }

        $avatarPath = uploadFile($file, 'avatars');
        if (!$avatarPath) {
            $this->response->error('Failed to upload avatar', 500);
        }

        $success = $this->userModel->update($id, ['avatar' => $avatarPath]);

        if ($success) {
            $this->response->success('Avatar updated successfully', ['avatar' => $avatarPath]);
        } else {
            $this->response->error('Failed to update avatar', 500);
        }
    }
}
?>