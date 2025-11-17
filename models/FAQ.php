<?php
require_once ROOT_PATH . '/core/Model.php';

class FAQ extends Model {
    protected $table = 'faqs';

    public function findByCategory($category) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE category = :category 
                AND status = 'active'
                ORDER BY sort_order ASC, created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getCategories() {
        $sql = "SELECT DISTINCT category FROM {$this->table} 
                WHERE status = 'active' 
                ORDER BY category";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>