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
        include 'app/views/user/login.php';
    }

    public function register() {
        include 'app/views/user/register.php';
    }

    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = "Vui lòng nhập đầy đủ thông tin.";
                include 'app/views/user/register.php';
                return;
            }

            if ($password !== $confirm_password) {
                $error = "Mật khẩu xác nhận không khớp.";
                include 'app/views/user/register.php';
                return;
            }

            if ($this->userModel->register($username, $password)) {
                $success = "Đăng ký thành công! Hãy đăng nhập.";
                include 'app/views/user/login.php';
            } else {
                $error = "Tên đăng nhập đã tồn tại.";
                include 'app/views/user/register.php';
            }
        }
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
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ];
                header('Location: /Product/list');
                exit();
            } else {
                $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
                include 'app/views/user/login.php';
            }
        }
    }

    public function logout() {
        unset($_SESSION['user']);
        header('Location: /Product/list');
        exit();
    }
}
