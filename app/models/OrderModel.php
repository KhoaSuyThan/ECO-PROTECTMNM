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

    public function getAllOrders() {
        $query = "SELECT o.*, 
                    (SELECT SUM(od.quantity * od.price) FROM order_details od WHERE od.order_id = o.id) as total_price
                  FROM orders o 
                  ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getOrderDetailsForAdmin($order_id) {
        $queryOrder = "SELECT * FROM orders WHERE id = :order_id";
        $stmtOrder = $this->conn->prepare($queryOrder);
        $stmtOrder->bindParam(':order_id', $order_id);
        $stmtOrder->execute();
        
        $orderInfo = $stmtOrder->fetch(PDO::FETCH_OBJ);
        
        if (!$orderInfo) return null;

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

    public function createOrder($user_id, $name, $phone, $address, $cart_items) {
        $this->conn->beginTransaction();
        try {
            $query = "INSERT INTO orders (user_id, name, phone, address, status, payment_status, payment_method) 
                      VALUES (:user_id, :name, :phone, :address, 'pending', 'unpaid', 'COD')";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $user_id,
                ':name' => htmlspecialchars(strip_tags($name)),
                ':phone' => htmlspecialchars(strip_tags($phone)),
                ':address' => htmlspecialchars(strip_tags($address))
            ]);
            $order_id = $this->conn->lastInsertId();

            foreach ($cart_items as $item) {
                $queryDetail = "INSERT INTO order_details (order_id, product_id, quantity, price) 
                                VALUES (:order_id, :product_id, :quantity, :price)";
                $stmtDetail = $this->conn->prepare($queryDetail);
                $stmtDetail->execute([
                    ':order_id' => $order_id,
                    ':product_id' => $item->product_id,
                    ':quantity' => $item->quantity,
                    ':price' => $item->price
                ]);
            }

            // Xóa sạch giỏ hàng của user sau khi đặt hàng thành công
            $queryClearCart = "DELETE FROM cart WHERE user_id = :user_id";
            $stmtClear = $this->conn->prepare($queryClearCart);
            $stmtClear->execute([':user_id' => $user_id]);

            $this->conn->commit();
            return $order_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function cancelOrder($order_id, $user_id) {
        // Kiểm tra xem đơn hàng có thuộc về user này và ở trạng thái pending không
        $queryCheck = "SELECT status FROM orders WHERE id = :order_id AND user_id = :user_id";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->execute([
            ':order_id' => $order_id,
            ':user_id' => $user_id
        ]);
        $order = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$order) return false;
        if ($order['status'] !== 'pending') return false; // Chỉ cho phép hủy khi đang pending

        $queryUpdate = "UPDATE orders SET status = 'cancelled' WHERE id = :order_id";
        $stmtUpdate = $this->conn->prepare($queryUpdate);
        return $stmtUpdate->execute([':order_id' => $order_id]);
    }

    public function updateOrderStatus($order_id, $status) {
        $query = "UPDATE orders SET status = :status WHERE id = :order_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':status' => $status,
            ':order_id' => $order_id
        ]);
    }

    public function updatePaymentStatus($order_id, $payment_status, $payment_method = 'COD') {
        $query = "UPDATE orders SET payment_status = :payment_status, payment_method = :payment_method WHERE id = :order_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':payment_status' => $payment_status,
            ':payment_method' => $payment_method,
            ':order_id' => $order_id
        ]);
    }

    public function getOrderById($order_id) {
        $query = "SELECT * FROM orders WHERE id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}
?>
