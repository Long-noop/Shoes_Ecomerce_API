<?php
require_once ROOT_PATH . '/core/Model.php';

class Product extends Model {
    protected $table = 'products';

    public function findWithCategory($id) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function search($keyword = '', $categoryId = null, $minPrice = null, $maxPrice = null, $limit = 20, $offset = 0) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($keyword) {
            $sql .= " AND (p.name LIKE :keyword OR p.description LIKE :keyword)";
            $params[':keyword'] = "%$keyword%";
        }
        
        if ($categoryId) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }
        
        if ($minPrice !== null) {
            $sql .= " AND p.price >= :min_price";
            $params[':min_price'] = $minPrice;
        }
        
        if ($maxPrice !== null) {
            $sql .= " AND p.price <= :max_price";
            $params[':max_price'] = $maxPrice;
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function countSearch($keyword = '', $categoryId = null, $minPrice = null, $maxPrice = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($keyword) {
            $sql .= " AND (name LIKE :keyword OR description LIKE :keyword)";
            $params[':keyword'] = "%$keyword%";
        }
        
        if ($categoryId) {
            $sql .= " AND category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }
        
        if ($minPrice !== null) {
            $sql .= " AND price >= :min_price";
            $params[':min_price'] = $minPrice;
        }
        
        if ($maxPrice !== null) {
            $sql .= " AND price <= :max_price";
            $params[':max_price'] = $maxPrice;
        }
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function getFeatured($limit = 10) {
        $sql = "SELECT * FROM {$this->table} WHERE feature = 1 ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStock($id, $quantity) {
        $sql = "UPDATE {$this->table} SET stock = stock - :quantity WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>