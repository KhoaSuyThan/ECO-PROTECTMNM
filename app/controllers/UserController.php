<?php
require_once 'app/config/database.php';
require_once 'app/models/UserModel.php';

class UserController {
    private $userModel;

    public function __construct() {
        $db = (new Database())->getConnection();
        $this->userModel = new UserModel($db);
    }

    public function login() {
        if (isset($_SESSION['error'])) {
            $error = $_SESSION['error'];
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            $success = $_SESSION['success'];
            unset($_SESSION['success']);
        }
        include 'app/views/user/login.php';
    }

    public function register() {
        include 'app/views/user/register.php';
    }

    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once 'app/config/mail.php';
            
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($username) || empty($password) || empty($email)) {
                $error = "Vui lòng nhập đầy đủ thông tin.";
                include 'app/views/user/register.php';
                return;
            }

            if ($password !== $confirm_password) {
                $error = "Mật khẩu xác nhận không khớp.";
                include 'app/views/user/register.php';
                return;
            }

            $token = bin2hex(random_bytes(32));

            if ($this->userModel->register($username, $password, $email, $token)) {
                $verifyLink = "http://" . $_SERVER['HTTP_HOST'] . "/User/verifyEmail/" . $token;
                $subject = "Xac thuc tai khoan - ECO-PROTECT STORE";
                $body = "<p>Chào $username,</p><p>Cảm ơn bạn đã đăng ký tài khoản. Vui lòng click vào link bên dưới để xác thực email của bạn:</p><p><a href='$verifyLink'>$verifyLink</a></p>";
                
                MailHelper::sendMail($email, $subject, $body);

                $success = "Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.";
                include 'app/views/user/login.php';
            } else {
                $error = "Tên đăng nhập hoặc email đã tồn tại.";
                include 'app/views/user/register.php';
            }
        }
    }

    public function verifyEmail($token = '') {
        if (empty($token)) {
            header('Location: /');
            exit();
        }

        if ($this->userModel->verifyEmailToken($token)) {
            $_SESSION['success'] = "Xác thực email thành công! Bạn có thể đăng nhập ngay.";
        } else {
            $_SESSION['error'] = "Liên kết xác thực không hợp lệ hoặc tài khoản đã được xác thực.";
        }
        
        header('Location: /User/login');
        exit();
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = "Vui lòng nhập đầy đủ thông tin.";
                include 'app/views/user/login.php';
                return;
            }

            $user = $this->userModel->login($username, $password);

            if ($user) {
                if (!empty($user['verification_token']) && $user['email_verified_at'] === null) {
                    $error = "Tài khoản của bạn chưa được xác thực. Vui lòng kiểm tra email để kích hoạt.";
                    include 'app/views/user/login.php';
                    return;
                }

                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'avatar' => $user['avatar'] ?? null
                ];

                if (isset($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    $this->userModel->updateRememberToken($user['id'], $token);
                    setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
                }

                header('Location: /Product/list');
                exit();
            } else {
                $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
                include 'app/views/user/login.php';
            }
        }
    }

    public function logout() {
        if (isset($_SESSION['user']['id'])) {
            $this->userModel->updateRememberToken($_SESSION['user']['id'], null);
        }
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, "/");
        }
        unset($_SESSION['user']);
        header('Location: /Product/list');
        exit();
    }

    public function changePassword() {
        if (!isset($_SESSION['user'])) {
            header('Location: /User/login');
            exit();
        }
        include 'app/views/user/change_password.php';
    }

    public function processChangePassword() {
        if (!isset($_SESSION['user'])) {
            header('Location: /User/login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $error = "Vui lòng nhập đầy đủ thông tin.";
                include 'app/views/user/change_password.php';
                return;
            }

            if ($new_password !== $confirm_password) {
                $error = "Mật khẩu mới không khớp.";
                include 'app/views/user/change_password.php';
                return;
            }

            $user = $this->userModel->getUserById($_SESSION['user']['id']);
            if (!$user || !password_verify($current_password, $user['password'])) {
                $error = "Mật khẩu hiện tại không đúng.";
                include 'app/views/user/change_password.php';
                return;
            }

            if ($this->userModel->updatePassword($_SESSION['user']['id'], $new_password)) {
                $_SESSION['success'] = "Đổi mật khẩu thành công!";
                header('Location: /User/profile');
                exit();
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại sau.";
                include 'app/views/user/change_password.php';
            }
        }
    }

    public function forgotPassword() {
        include 'app/views/user/forgot_password.php';
    }

    public function processForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once 'app/config/mail.php';
            $email = $_POST['email'] ?? '';
            
            if (empty($email)) {
                $error = "Vui lòng nhập email.";
                include 'app/views/user/forgot_password.php';
                return;
            }

            $user = $this->userModel->getUserByEmail($email);
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $this->userModel->updateResetToken($email, $token, $expire);

                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/User/resetPassword/" . $token;
                $subject = "Yeu cau dat lai mat khau - ECO-PROTECT STORE";
                $body = "<p>Chào " . htmlspecialchars($user['username']) . ",</p>
                         <p>Bạn đã yêu cầu đặt lại mật khẩu. Vui lòng click vào link bên dưới để đặt lại mật khẩu (link có hiệu lực trong 1 giờ):</p>
                         <p><a href='$resetLink'>$resetLink</a></p>";
                
                MailHelper::sendMail($email, $subject, $body);
            }
            
            // Luôn hiển thị thông báo thành công dù email có tồn tại hay không để bảo mật
            $success = "Nếu email hợp lệ, một liên kết đặt lại mật khẩu đã được gửi đến bạn.";
            include 'app/views/user/forgot_password.php';
        }
    }

    public function resetPassword($token = '') {
        if (empty($token)) {
            header('Location: /User/login');
            exit();
        }
        $user = $this->userModel->getUserByResetToken($token);
        if (!$user) {
            $error = "Liên kết không hợp lệ hoặc đã hết hạn.";
            // Gọi lại view nhưng báo lỗi
            include 'app/views/user/reset_password.php';
            return;
        }
        include 'app/views/user/reset_password.php';
    }

    public function processResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($token) || empty($password) || empty($confirm_password)) {
                $error = "Vui lòng điền đầy đủ thông tin.";
                include 'app/views/user/reset_password.php';
                return;
            }

            if ($password !== $confirm_password) {
                $error = "Mật khẩu không khớp.";
                include 'app/views/user/reset_password.php';
                return;
            }

            $user = $this->userModel->getUserByResetToken($token);
            if (!$user) {
                $error = "Liên kết không hợp lệ hoặc đã hết hạn.";
                include 'app/views/user/reset_password.php';
                return;
            }

            if ($this->userModel->updatePassword($user['id'], $password)) {
                $this->userModel->clearResetToken($user['id']);
                $_SESSION['success'] = "Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập.";
                header('Location: /User/login');
                exit();
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại sau.";
                include 'app/views/user/reset_password.php';
            }
        }
    }

    public function profile() {
        if (!isset($_SESSION['user'])) {
            header('Location: /User/login');
            exit();
        }
        $user = $this->userModel->getUserById($_SESSION['user']['id']);
        include 'app/views/user/profile.php';
    }

    public function processUpdateProfile() {
        if (!isset($_SESSION['user'])) {
            header('Location: /User/login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $fullname = $_POST['fullname'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            
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
                }
            }

            // Check email if changed (but this app might not have unique email yet, assume ok for now)

            if ($this->userModel->updateProfile($_SESSION['user']['id'], $fullname, $email, $phone, $address, $avatarPath)) {
                if ($avatarPath) {
                    $_SESSION['user']['avatar'] = $avatarPath;
                }
                $_SESSION['success'] = "Cập nhật hồ sơ thành công!";
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi cập nhật hồ sơ.";
            }
            header('Location: /User/profile');
            exit();
        }
    }

    public function deleteAvatar() {
        if (!isset($_SESSION['user'])) {
            header('Location: /User/login');
            exit();
        }

        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->getUserById($userId);
        
        if ($user && !empty($user['avatar'])) {
            $avatarPath = $user['avatar'];
            // Xóa file vật lý nếu tồn tại
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
            
            // Xóa trong Database
            $this->userModel->removeAvatar($userId);
            
            // Xóa trong Session
            $_SESSION['user']['avatar'] = '';
            
            $_SESSION['success'] = "Đã xóa ảnh đại diện thành công.";
        }
        
        header('Location: /User/profile');
        exit();
    }
}
