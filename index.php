<?php
if (isset($_GET['update_db_now']) || strpos($_SERVER['REQUEST_URI'], 'alter_db') !== false || strpos($_SERVER['REQUEST_URI'], 'update_db') !== false) {
    require_once 'app/config/database.php';
    try {
        $db = (new Database())->getConnection();
        $cols = [
            "email VARCHAR(255) NULL", "fullname VARCHAR(255) NULL", "phone VARCHAR(20) NULL", 
            "address TEXT NULL", "avatar VARCHAR(255) NULL", "remember_token VARCHAR(255) NULL", 
            "reset_token VARCHAR(255) NULL", "reset_token_expire DATETIME NULL", 
            "email_verified_at DATETIME NULL", "verification_token VARCHAR(255) NULL", 
            "status ENUM('active', 'locked') NOT NULL DEFAULT 'active'"
        ];
        foreach ($cols as $c) {
            try { $db->exec("ALTER TABLE users ADD COLUMN $c"); } catch (Exception $e) {}
        }
        die("<div style='text-align:center; padding: 50px; font-family: sans-serif;'>
                <h1 style='color: green;'>Cập nhật Database thành công!</h1>
                <a href='/' style='padding: 10px 20px; background: green; color: white; text-decoration: none; border-radius: 5px;'>Bấm vào đây để về Trang chủ</a>
             </div>");
    } catch (Exception $e) { die($e->getMessage()); }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/UserModel.php';

// Auto login
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
    $db = (new Database())->getConnection();
    $userModel = new UserModel($db);
    $user = $userModel->getUserByRememberToken($_COOKIE['remember_token']);
    if ($user) {
        if (empty($user['verification_token']) || $user['email_verified_at'] !== null) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'avatar' => $user['avatar'] ?? null
            ];
        }
    }
}

$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Định tuyến các yêu cầu API
if (isset($url[0]) && $url[0] === 'api' && isset($url[1])) {
    $apiControllerName = ucfirst($url[1]) . 'ApiController';
    if (file_exists('app/controllers/' . $apiControllerName . '.php')) {
        require_once 'app/controllers/' . $apiControllerName . '.php';
        $controller = new $apiControllerName();
        $method = $_SERVER['REQUEST_METHOD'];
        $id = $url[2] ?? null;
        $action = '';
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $action = 'show';
                } else {
                    $action = 'index';
                }
                break;
            case 'POST':
                $action = 'store';
                break;
            case 'PUT':
                if ($id) {
                    $action = 'update';
                }
                break;
            case 'DELETE':
                if ($id) {
                    $action = 'destroy';
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['message' => 'Method Not Allowed']);
                exit;
        }
        
        if (method_exists($controller, $action)) {
            if ($id) {
                call_user_func_array([$controller, $action], [$id]);
            } else {
                call_user_func_array([$controller, $action], []);
            }
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Action not found']);
        }
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Controller not found']);
        exit;
    }
}

// Xử lý controller thông thường (không phải API)
$controllerName = isset($url[0]) && $url[0] != '' ? ucfirst($url[0]) . 'Controller' : 'ProductController';
$action = isset($url[1]) && $url[1] != '' ? $url[1] : 'index';

if (!file_exists('app/controllers/' . $controllerName . '.php')) {
    die('Controller not found: ' . $controllerName . ' (URL: ' . htmlspecialchars($_GET['url'] ?? '') . ')');
}

require_once 'app/controllers/' . $controllerName . '.php';
$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    die('Action not found');
}

call_user_func_array([$controller, $action], array_slice($url, 2));
?>