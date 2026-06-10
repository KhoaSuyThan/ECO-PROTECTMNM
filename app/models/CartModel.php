<?php
class CartModel
{
    private $conn;
    private $table_name = "cart";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Xem giỏ hàng của người dùng
    public function getCart($user_id)
    {
        $query = "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image, (c.quantity * p.price) as subtotal
                  FROM " . $this->table_name . " c
                  JOIN product p ON c.product_id = p.id
                  WHERE c.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Thêm sản phẩm vào giỏ
    public function addToCart($user_id, $product_id, $quantity)
    {
        // Kiểm tra xem sản phẩm đã có trong giỏ chưa
        $query = "SELECT id, quantity FROM " . $this->table_name . " WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Đã có -> cộng dồn số lượng
            $new_quantity = $row['quantity'] + $quantity;
            $update_query = "UPDATE " . $this->table_name . " SET quantity = :quantity WHERE id = :id";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':quantity', $new_quantity);
            $update_stmt->bindParam(':id', $row['id']);
            return $update_stmt->execute();
        } else {
            // Chưa có -> thêm dòng mới
            $insert_query = "INSERT INTO " . $this->table_name . " (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
            $insert_stmt = $this->conn->prepare($insert_query);
            $insert_stmt->bindParam(':user_id', $user_id);
            $insert_stmt->bindParam(':product_id', $product_id);
            $insert_stmt->bindParam(':quantity', $quantity);
            return $insert_stmt->execute();
        }
    }

    // Cập nhật số lượng sản phẩm trong giỏ
    public function updateQuantity($user_id, $product_id, $quantity)
    {
        $query = "UPDATE " . $this->table_name . " SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        return $stmt->execute();
    }

    // Xóa một sản phẩm khỏi giỏ hàng
    public function removeFromCart($user_id, $product_id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        return $stmt->execute();
    }

    // Xóa toàn bộ giỏ hàng
    public function clearCart($user_id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }

    // Tính tổng tiền giỏ hàng
    public function getTotalPrice($user_id)
    {
        $query = "SELECT SUM(c.quantity * p.price) as total_price
                  FROM " . $this->table_name . " c
                  JOIN product p ON c.product_id = p.id
                  WHERE c.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)($row['total_price'] ?? 0) : 0;
    }
}
?>
