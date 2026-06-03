<?php
require 'app/config/database.php';
try {
    $db = (new Database())->getConnection();
    $db->exec("ALTER TABLE orders ADD COLUMN user_id INT DEFAULT NULL");
    $db->exec("ALTER TABLE orders ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
    echo "<h1>Cập nhật Database thành công!</h1>";
    echo "<p>Cột user_id đã được thêm vào bảng orders.</p>";
    echo '<a href="/">Quay lại trang chủ</a>';
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "<h1>Cột user_id đã tồn tại!</h1>";
        echo '<a href="/">Quay lại trang chủ</a>';
    } else {
        echo "<h1>Lỗi: " . $e->getMessage() . "</h1>";
    }
}
?>
