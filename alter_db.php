<?php
require 'app/config/database.php';
try {
    $db = (new Database())->getConnection();
    echo "<h1>Cập nhật Database</h1>";
    echo "<ul>";

    // 1. Cập nhật bảng users
    $user_cols = [
        "failed_attempts INT NOT NULL DEFAULT 0",
        "lock_until DATETIME NULL",
        "refresh_token VARCHAR(255) NULL"
    ];
    foreach ($user_cols as $col) {
        $colName = explode(" ", $col)[0];
        try {
            $db->exec("ALTER TABLE users ADD COLUMN $col");
            echo "<li>Đã thêm cột users.$colName</li>";
        } catch (PDOException $e) {
            echo "<li>Cột users.$colName: " . $e->getMessage() . "</li>";
        }
    }

    // 2. Cập nhật bảng orders
    $order_cols = [
        "status ENUM('pending','confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending'",
        "payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid'",
        "payment_method VARCHAR(50) NOT NULL DEFAULT 'COD'"
    ];
    foreach ($order_cols as $col) {
        $colName = explode(" ", $col)[0];
        try {
            $db->exec("ALTER TABLE orders ADD COLUMN $col");
            echo "<li>Đã thêm cột orders.$colName</li>";
        } catch (PDOException $e) {
            echo "<li>Cột orders.$colName: " . $e->getMessage() . "</li>";
        }
    }

    // 3. Tạo bảng cart
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS `cart` (
          `id` int NOT NULL AUTO_INCREMENT,
          `user_id` int NOT NULL,
          `product_id` int NOT NULL,
          `quantity` int NOT NULL DEFAULT 1,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`),
          KEY `product_id` (`product_id`),
          CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
          CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;");
        echo "<li>Đã tạo bảng `cart` thành công!</li>";
    } catch (PDOException $e) {
        echo "<li>Lỗi tạo bảng `cart`: " . $e->getMessage() . "</li>";
    }

    echo "</ul>";
    echo "<p>Cập nhật thành công!</p>";
    echo '<a href="/">Quay lại trang chủ</a>';
} catch (PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
}
?>
