<?php
class UserModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($username, $password) {
        // Kiểm tra xem username đã tồn tại chưa
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->rowCount() > 0) {
            return false; // Username đã tồn tại
        }

        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Mặc định role là 'user'
        $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':username' => htmlspecialchars(strip_tags($username)),
            ':password' => $hashed_password
        ]);
    }

    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':username' => $username]);

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                return $row; // Đăng nhập thành công, trả về thông tin user
            }
        }
        return false;
    }
}
