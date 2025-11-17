<?php
require_once ROOT_PATH . '/core/Model.php';

class Blog extends Model {
    protected $table = 'blogs';

    public function search($keyword = "", $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (title LIKE :keyword1 OR content LIKE :keyword2) 
                AND status = 'publish'
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%$keyword%";
        $stmt->bindParam(':keyword1', $searchTerm);
        $stmt->bindParam(':keyword2', $searchTerm);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function findBySlug($slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug AND status = 'publish' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getPublished($limit = 20, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'publish' 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function incrementViews($id) {
        $sql = "UPDATE {$this->table} SET views = views + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>