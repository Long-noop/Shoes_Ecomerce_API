<?php
require_once ROOT_PATH . '/core/Model.php';

class Comment extends Model {
    protected $table = 'comments';

    public function findByResource($resourceType, $resourceId, $limit = 20, $offset = 0) {
        $sql = "SELECT c.*, u.first_name, u.last_name
                FROM {$this->table} c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.resource_type = :resource_type 
                AND c.resource_id = :resource_id
                AND c.status = 'approved'
                ORDER BY c.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':resource_type', $resourceType);
        $stmt->bindParam(':resource_id', $resourceId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function countByResource($resourceType, $resourceId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE resource_type = :resource_type 
                AND resource_id = :resource_id 
                AND status = 'approved'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':resource_type', $resourceType);
        $stmt->bindParam(':resource_id', $resourceId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function getAverageRating($resourceType, $resourceId) {
        $sql = "SELECT AVG(rating) as average FROM {$this->table}
                WHERE resource_type = :resource_type
                AND resource_id = :resource_id
                AND status = 'approved'
                AND rating IS NOT NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':resource_type', $resourceType);
        $stmt->bindParam(':resource_id', $resourceId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return round($result['average'] ?? 0, 1);
    }

    public function getPendingComments($limit = 20, $offset = 0) {
        $sql = "SELECT c.*, u.first_name, u.last_name
                FROM {$this->table} c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.status = 'pending'
                ORDER BY c.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>