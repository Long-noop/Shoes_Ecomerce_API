<?php
require_once ROOT_PATH . '/core/Model.php';

class OrderItem extends Model {
    protected $table = 'order_items';

    public function findByOrderId($orderId) {
        $sql = "SELECT oi.*, p.name as product_name, p.image as product_image
                FROM {$this->table} oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :order_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getBestSellers($limit = 10) {
        $sql = "SELECT p.*, COUNT(oi.id) as sales_count, SUM(oi.quantity) as total_sold
                FROM {$this->table} oi
                JOIN products p ON oi.product_id = p.id
                GROUP BY p.id
                ORDER BY sales_count DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>