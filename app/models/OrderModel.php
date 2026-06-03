<?php
class OrderModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getOrdersByUserId($user_id) {
        $query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getOrderDetails($order_id, $user_id) {
        // Lấy chi tiết đơn hàng (xác thực order thuộc về user này)
        $queryOrder = "SELECT * FROM orders WHERE id = :order_id AND user_id = :user_id";
        $stmtOrder = $this->conn->prepare($queryOrder);
        $stmtOrder->bindParam(':order_id', $order_id);
        $stmtOrder->bindParam(':user_id', $user_id);
        $stmtOrder->execute();
        
        $orderInfo = $stmtOrder->fetch(PDO::FETCH_OBJ);
        
        if (!$orderInfo) {
            return null; // Không tìm thấy hoặc không có quyền truy cập
        }

        // Lấy chi tiết các sản phẩm trong đơn hàng
        $queryDetails = "SELECT od.*, p.name as product_name, p.image as product_image 
                         FROM order_details od
                         LEFT JOIN product p ON od.product_id = p.id
                         WHERE od.order_id = :order_id";
        $stmtDetails = $this->conn->prepare($queryDetails);
        $stmtDetails->bindParam(':order_id', $order_id);
        $stmtDetails->execute();
        
        $details = $stmtDetails->fetchAll(PDO::FETCH_OBJ);

        return [
            'info' => $orderInfo,
            'details' => $details
        ];
    }
}
?>
