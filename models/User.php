<?php
require_once ROOT_PATH . "/core/Model.php";
class User extends Model {
    protected $table = 'users';

    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function register($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['role'] = $data['role'] ?? 'member';
        $data['status'] = 'active';
        $data['created_at'] = date('Y-m-d H:i:s');

        return $this->create($data);
    }

    public function updateProfile($id ,$data) {
        $allowedFields = ['first_name', 'last_name', 'phone', 'date_of_birth', 'gender', 'avatar'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        $updateData['updated_at'] = date('Y-m-d H:i:s');

        return $this->update($id, $updateData);
    }

    public function changePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->update($id, ['password' => $hashedPassword]);
    }

    public function banUser($id) {
        return $this->update($id, ['status' => 'banned']);
    }

    public function activateUser($id) {
        return $this->update($id, ['status' => 'active']);
    }

    public function search($keyword, $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE email LIKE :keyword 
                OR first_name LIKE :keyword 
                OR last_name LIKE :keyword 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%$keyword%";
        $stmt->bindParam(':keyword', $searchTerm);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>