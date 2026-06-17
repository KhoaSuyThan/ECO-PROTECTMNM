<?php

class AuthApiController
{
    private $userModel;
    private $jwtHandler;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->userModel = new UserModel($this->db);
        $this->jwtHandler = new JWTHandler();
    }

    // Đăng ký tài khoản
    public function register()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $confirm_password = $data['confirm_password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['message' => 'Vui lòng nhập đầy đủ thông tin: username, email, password']);
            return;
        }

        if ($password !== $confirm_password) {
            http_response_code(400);
            echo json_encode(['message' => 'Mật khẩu xác nhận không khớp']);
            return;
        }

        $token = bin2hex(random_bytes(32));

        if ($this->userModel->register($username, $password, $email, $token)) {
            // Tự động kích hoạt email luôn để thuận tiện cho việc kiểm thử API
            $this->userModel->verifyEmailToken($token);
            
            http_response_code(201);
            echo json_encode(['message' => 'Đăng ký tài khoản thành công!']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Tên đăng nhập hoặc email đã tồn tại']);
        }
    }

    // Đăng nhập tài khoản (kèm khóa tài khoản và sinh JWT + Refresh Token)
    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['message' => 'Vui lòng nhập username và password']);
            return;
        }

        $user = $this->userModel->getUserByUsernameOrEmail($username);

        if ($user) {
            // Kiểm tra trạng thái khóa tài khoản
            if ($user['status'] === 'locked') {
                if ($user['lock_until'] && strtotime($user['lock_until']) > time()) {
                    $timeLeft = strtotime($user['lock_until']) - time();
                    http_response_code(403);
                    echo json_encode([
                        'message' => "Tài khoản đang bị khóa tạm thời. Thử lại sau $timeLeft giây."
                    ]);
                    return;
                } else {
                    // Đã hết thời gian khóa -> tự động mở khóa
                    $this->userModel->unlockAccount($user['id']);
                    $user['status'] = 'active';
                    $user['failed_attempts'] = 0;
                }
            }

            // Xác thực mật khẩu
            if (password_verify($password, $user['password'])) {
                // Đăng nhập thành công -> Reset failed attempts
                $this->userModel->unlockAccount($user['id']);

                $tokenData = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ];

                $accessToken = $this->jwtHandler->encode($tokenData);
                $refreshToken = $this->jwtHandler->encodeRefresh($tokenData);

                // Lưu Refresh Token vào Database
                $this->userModel->updateRefreshToken($user['id'], $refreshToken);

                echo json_encode([
                    'message' => 'Đăng nhập thành công',
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'role' => $user['role'],
                        'email' => $user['email'],
                        'fullname' => $user['fullname'],
                        'avatar' => $user['avatar']
                    ]
                ]);
            } else {
                // Sai mật khẩu -> Tăng số lần thử sai
                $this->userModel->incrementFailedAttempts($user['id']);
                $attempts = $user['failed_attempts'] + 1;

                if ($attempts >= 5) {
                    $lockUntil = date('Y-m-d H:i:s', time() + 300); // Khóa trong 5 phút
                    $this->userModel->lockAccount($user['id'], $lockUntil);
                    http_response_code(403);
                    echo json_encode(['message' => 'Bạn đã nhập sai 5 lần. Tài khoản bị khóa trong 5 phút.']);
                } else {
                    http_response_code(401);
                    echo json_encode(['message' => "Mật khẩu không đúng. Còn " . (5 - $attempts) . " lần thử."]);
                }
            }
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Tài khoản không tồn tại']);
        }
    }

    // Refresh Token
    public function refresh()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $refreshToken = $data['refresh_token'] ?? '';

        if (empty($refreshToken)) {
            http_response_code(400);
            echo json_encode(['message' => 'Thiếu refresh_token']);
            return;
        }

        $decoded = $this->jwtHandler->decode($refreshToken);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['message' => 'Refresh token không hợp lệ hoặc đã hết hạn']);
            return;
        }

        $userId = $decoded['id'];
        $user = $this->userModel->getUserById($userId);

        if ($user && $user['refresh_token'] === $refreshToken) {
            $tokenData = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            $newAccessToken = $this->jwtHandler->encode($tokenData);
            
            echo json_encode([
                'access_token' => $newAccessToken
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Refresh token không khớp với dữ liệu hệ thống']);
        }
    }

    // Lấy thông tin user hiện tại
    public function me()
    {
        $decoded = JWTMiddleware::authenticate();
        $user = $this->userModel->getUserById($decoded['id']);
        
        if ($user) {
            unset($user['password']);
            unset($user['remember_token']);
            unset($user['reset_token']);
            unset($user['refresh_token']);
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'User không tồn tại']);
        }
    }

    // Cập nhật hồ sơ cá nhân (Hỗ trợ upload avatar)
    public function profile()
    {
        $decoded = JWTMiddleware::authenticate();
        $userId = $decoded['id'];

        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';

        // Nếu là JSON request (không có $_POST) thì parse từ input body
        if (empty($_POST)) {
            $data = json_decode(file_get_contents("php://input"), true) ?? [];
            $fullname = $data['fullname'] ?? '';
            $email = $data['email'] ?? '';
            $phone = $data['phone'] ?? '';
            $address = $data['address'] ?? '';
        }

        $avatarPath = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['avatar']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $newFileName = time() . '_' . $filename;
                $uploadDir = 'uploads/avatars/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $newFileName)) {
                    $avatarPath = $uploadDir . $newFileName;
                }
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Định dạng hình ảnh avatar không hợp lệ']);
                return;
            }
        }

        if ($this->userModel->updateProfile($userId, $fullname, $email, $phone, $address, $avatarPath)) {
            $user = $this->userModel->getUserById($userId);
            unset($user['password']);
            echo json_encode([
                'message' => 'Cập nhật thông tin cá nhân thành công!',
                'user' => $user
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Có lỗi xảy ra khi cập nhật thông tin cá nhân']);
        }
    }

    // Đổi mật khẩu
    public function changePassword()
    {
        $decoded = JWTMiddleware::authenticate();
        $userId = $decoded['id'];

        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $current_password = $data['current_password'] ?? '';
        $new_password = $data['new_password'] ?? '';
        $confirm_password = $data['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            http_response_code(400);
            echo json_encode(['message' => 'Vui lòng điền đầy đủ các thông tin mật khẩu']);
            return;
        }

        if ($new_password !== $confirm_password) {
            http_response_code(400);
            echo json_encode(['message' => 'Mật khẩu mới không khớp']);
            return;
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user || !password_verify($current_password, $user['password'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Mật khẩu hiện tại không khớp']);
            return;
        }

        if ($this->userModel->updatePassword($userId, $new_password)) {
            echo json_encode(['message' => 'Đổi mật khẩu thành công!']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Có lỗi xảy ra khi đổi mật khẩu']);
        }
    }

    // Quên mật khẩu (mô phỏng)
    public function forgotPassword()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $email = $data['email'] ?? '';

        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['message' => 'Vui lòng điền email']);
            return;
        }

        $user = $this->userModel->getUserByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $this->userModel->updateResetToken($email, $token, $expire);

            // Giả lập đường dẫn reset mật khẩu
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/User/resetPassword/" . $token;
            
            echo json_encode([
                'message' => 'Nếu email hợp lệ, một liên kết đặt lại mật khẩu đã được gửi.',
                'simulated_reset_link' => $resetLink
            ]);
        } else {
            echo json_encode(['message' => 'Nếu email hợp lệ, một liên kết đặt lại mật khẩu đã được gửi.']);
        }
    }

    // Lấy danh sách toàn bộ người dùng (Admin)
    public function usersList()
    {
        JWTMiddleware::requireRole('admin');
        header('Content-Type: application/json');

        $users = $this->userModel->getAllUsers();
        // Loại bỏ mật khẩu khỏi payload trả về
        foreach ($users as &$u) {
            unset($u['password']);
            unset($u['remember_token']);
            unset($u['reset_token']);
            unset($u['refresh_token']);
        }
        echo json_encode($users);
    }

    // Khóa / Mở khóa người dùng (Admin)
    public function toggleUserStatus()
    {
        JWTMiddleware::requireRole('admin');
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $id = $data['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['message' => 'Thiếu ID người dùng']);
            return;
        }

        if ($this->userModel->toggleStatus($id)) {
            echo json_encode(['message' => 'Cập nhật trạng thái người dùng thành công!']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Cập nhật trạng thái thất bại (không thể khóa Admin hoặc tài khoản không tồn tại)']);
        }
    }
}
?>
