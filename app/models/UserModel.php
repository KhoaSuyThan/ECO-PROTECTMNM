<?php
class UserModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($username, $password, $email, $token) {
        // Kiểm tra xem username hoặc email đã tồn tại chưa
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);
        if ($stmt->rowCount() > 0) {
            return false; // Username hoặc email đã tồn tại
        }

        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Mặc định role là 'user'
        $query = "INSERT INTO users (username, password, email, role, verification_token) VALUES (:username, :password, :email, 'user', :token)";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':username' => htmlspecialchars(strip_tags($username)),
            ':password' => $hashed_password,
            ':email' => htmlspecialchars(strip_tags($email)),
            ':token' => $token
        ]);
    }

    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE (username = :username OR email = :username) AND status = 'active'";
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

    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $newPassword) {
        $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':password' => $hashed_password,
            ':id' => $id
        ]);
    }

    public function getUserByEmail($email) {
        $query = "SELECT * FROM users WHERE email = :email AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateResetToken($email, $token, $expire) {
        $query = "UPDATE users SET reset_token = :token, reset_token_expire = :expire WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':token' => $token,
            ':expire' => $expire,
            ':email' => $email
        ]);
    }

    public function getUserByResetToken($token) {
        $query = "SELECT * FROM users WHERE reset_token = :token AND reset_token_expire > NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function clearResetToken($id) {
        $query = "UPDATE users SET reset_token = NULL, reset_token_expire = NULL WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function updateRememberToken($id, $token) {
        $query = "UPDATE users SET remember_token = :token WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':token' => $token,
            ':id' => $id
        ]);
    }

    public function getUserByRememberToken($token) {
        $query = "SELECT * FROM users WHERE remember_token = :token AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $fullname, $email, $phone, $address, $avatar) {
        $query = "UPDATE users SET fullname = :fullname, email = :email, phone = :phone, address = :address";
        if ($avatar !== null) {
            $query .= ", avatar = :avatar";
        }
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $params = [
            ':fullname' => $fullname,
            ':email' => $email,
            ':phone' => $phone,
            ':address' => $address,
            ':id' => $id
        ];
        
        if ($avatar !== null) {
            $params[':avatar'] = $avatar;
        }
        
        return $stmt->execute($params);
    }

    public function removeAvatar($id) {
        $query = "UPDATE users SET avatar = '' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function verifyEmailToken($token) {
        $query = "SELECT id FROM users WHERE verification_token = :token AND email_verified_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':token' => $token]);
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $queryUpdate = "UPDATE users SET email_verified_at = NOW(), verification_token = NULL WHERE id = :id";
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            return $stmtUpdate->execute([':id' => $row['id']]);
        }
        return false;
    }

    public function getAllUsers() {
        $query = "SELECT * FROM users ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleStatus($id) {
        $user = $this->getUserById($id);
        if ($user && $user['role'] !== 'admin') { // Không cho phép khóa admin
            $newStatus = ($user['status'] == 'active') ? 'locked' : 'active';
            $query = "UPDATE users SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':status' => $newStatus,
                ':id' => $id
            ]);
        }
        return false;
    }

    public function getUserByUsernameOrEmail($username) {
        $query = "SELECT * FROM users WHERE username = :username OR email = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function incrementFailedAttempts($id) {
        $query = "UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function lockAccount($id, $lockUntil) {
        $query = "UPDATE users SET status = 'locked', lock_until = :lock_until WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':lock_until' => $lockUntil, ':id' => $id]);
    }

    public function unlockAccount($id) {
        $query = "UPDATE users SET status = 'active', failed_attempts = 0, lock_until = NULL WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function updateRefreshToken($id, $token) {
        $query = "UPDATE users SET refresh_token = :token WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':token' => $token, ':id' => $id]);
    }

    public function getUserByRefreshToken($token) {
        $query = "SELECT * FROM users WHERE refresh_token = :token";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
