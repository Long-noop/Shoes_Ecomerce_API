<?php
require_once ROOT_PATH . '/core/Model.php';

class Order extends Model {
    protected $table = 'orders';

    public function createOrder($userId, $orderData, $items) {
        try {
            $this->db->beginTransaction();

            // Create order
            $orderData['user_id'] = $userId;
            $orderData['status'] = 'pending';
            $orderData['created_at'] = date('Y-m-d H:i:s');
            
            $orderId = $this->create($orderData);

            // Create order items
            require_once ROOT_PATH . '/models/OrderItem.php';
            $orderItemModel = new OrderItem();
            
            foreach ($items as $item) {
                $orderItemModel->create([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price']
                ]);

                // Update product stock
                require_once ROOT_PATH . '/models/Product.php';
                $productModel = new Product();
                $productModel->updateStock($item['product_id'], $item['quantity']);
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findByUserId($userId, $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id 
                ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function findWithItems($orderId) {
        $sql = "SELECT o.*, 
                GROUP_CONCAT(
                    JSON_OBJECT(
                        'product_id', oi.product_id,
                        'product_name', p.name,
                        'quantity', oi.quantity,
                        'price', oi.price,
                        'total', oi.total
                    )
                ) as items
                FROM {$this->table} o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE o.id = :order_id
                GROUP BY o.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        if ($result && $result['items']) {
            $result['items'] = json_decode('[' . $result['items'] . ']', true);
        }
        
        return $result;
    }

    public function updateStatus($orderId, $status) {
        return $this->update($orderId, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getStatistics($startDate = null, $endDate = null) {
        $sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_order_value,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
                FROM {$this->table}
                WHERE 1=1";
        
        $params = [];
        
        if ($startDate) {
            $sql .= " AND created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND created_at <= :end_date";
            $params[':end_date'] = $endDate;
        }
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
?>