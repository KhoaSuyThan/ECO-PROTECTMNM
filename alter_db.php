<?php
require 'app/config/database.php';
try {
    $db = (new Database())->getConnection();
    
    // Add columns to users table
    $columns = [
        "email VARCHAR(255) NULL",
        "fullname VARCHAR(255) NULL",
        "phone VARCHAR(20) NULL",
        "address TEXT NULL",
        "avatar VARCHAR(255) NULL",
        "remember_token VARCHAR(255) NULL",
        "reset_token VARCHAR(255) NULL",
        "reset_token_expire DATETIME NULL",
        "email_verified_at DATETIME NULL",
        "verification_token VARCHAR(255) NULL",
        "status ENUM('active', 'locked') NOT NULL DEFAULT 'active'"
    ];

    echo "<h1>Cập nhật Database</h1>";
    echo "<ul>";
    foreach ($columns as $col) {
        $colName = explode(" ", $col)[0];
        try {
            $db->exec("ALTER TABLE users ADD COLUMN $col");
            echo "<li>Đã thêm cột: $colName</li>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<li>Cột $colName đã tồn tại.</li>";
            } else {
                echo "<li>Lỗi khi thêm $colName: " . $e->getMessage() . "</li>";
            }
        }
    }
    echo "</ul>";
    echo "<p>Cập nhật thành công!</p>";
    echo '<a href="/">Quay lại trang chủ</a>';
} catch (PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
}
?>
