<?php
require_once ROOT_PATH . '/core/Model.php';

class Cart extends Model {
    protected $table = 'cart';

    public function findByUserId($userId) {
        $sql = "SELECT c.*, p.name, p.price, p.image, p.stock 
                FROM {$this->table} c 
                LEFT JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function addItem($userId, $productId, $quantity) {
        // Check if item already exists
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        $existing = $stmt->fetch();

        if ($existing) {
            // Update quantity
            $newQuantity = $existing['quantity'] + $quantity;
            return $this->update($existing['id'], ['quantity' => $newQuantity]);
        } else {
            // Create new item
            return $this->create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function updateQuantity($cartId, $quantity) {
        return $this->update($cartId, ['quantity' => $quantity]);
    }

    public function removeItem($cartId, $userId) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $cartId);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }

    public function clearCart($userId) {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }

    public function getTotal($userId) {
        $sql = "SELECT SUM(c.quantity * p.price) as total 
                FROM {$this->table} c 
                LEFT JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
?>