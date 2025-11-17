<?php
require_once ROOT_PATH . '/core/Model.php';

class Contact extends Model {
    protected $table = 'contacts';

    public function findByStatus($status, $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = :status 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getUnreadCount() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'unread'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }
}
?>