<?php
if (isset($_GET['update_db_now']) || strpos($_SERVER['REQUEST_URI'], 'alter_db') !== false || strpos($_SERVER['REQUEST_URI'], 'update_db') !== false) {
    require_once 'app/config/database.php';
    try {
        $db = (new Database())->getConnection();
        
        // 1. Columns for users
        $user_cols = [
            "email VARCHAR(255) NULL", "fullname VARCHAR(255) NULL", "phone VARCHAR(20) NULL", 
            "address TEXT NULL", "avatar VARCHAR(255) NULL", "remember_token VARCHAR(255) NULL", 
            "reset_token VARCHAR(255) NULL", "reset_token_expire DATETIME NULL", 
            "email_verified_at DATETIME NULL", "verification_token VARCHAR(255) NULL", 
            "status ENUM('active', 'locked') NOT NULL DEFAULT 'active'",
            "failed_attempts INT NOT NULL DEFAULT 0",
            "lock_until DATETIME NULL",
            "refresh_token VARCHAR(255) NULL"
        ];
        foreach ($user_cols as $c) {
            try { $db->exec("ALTER TABLE users ADD COLUMN $c"); } catch (Exception $e) {}
        }
        
        // 2. Columns for orders
        $order_cols = [
            "status ENUM('pending','confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending'",
            "payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid'",
            "payment_method VARCHAR(50) NOT NULL DEFAULT 'COD'"
        ];
        foreach ($order_cols as $c) {
            try { $db->exec("ALTER TABLE orders ADD COLUMN $c"); } catch (Exception $e) {}
        }
        
        // 3. Create cart table
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } catch (Exception $e) {}

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
    header('Content-Type: application/json');
    $resource = strtolower($url[1]);
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Auth Routes
    if ($resource === 'auth') {
        require_once 'app/controllers/AuthApiController.php';
        $controller = new AuthApiController();
        $action = $url[2] ?? '';
        
        if ($method === 'POST' && $action === 'register') {
            $controller->register();
        } else if ($method === 'POST' && $action === 'login') {
            $controller->login();
        } else if ($method === 'POST' && $action === 'refresh') {
            $controller->refresh();
        } else if ($method === 'GET' && $action === 'me') {
            $controller->me();
        } else if (($method === 'PUT' || $method === 'POST') && $action === 'profile') {
            $controller->profile();
        } else if ($method === 'PUT' && $action === 'change-password') {
            $controller->changePassword();
        } else if ($method === 'POST' && $action === 'forgot-password') {
            $controller->forgotPassword();
        } else if ($method === 'GET' && $action === 'users') {
            $controller->usersList();
        } else if ($method === 'PUT' && $action === 'users') {
            $controller->toggleUserStatus();
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Auth endpoint not found']);
        }
        exit;
    }
    
    // Product Routes
    if ($resource === 'product') {
        require_once 'app/controllers/ProductApiController.php';
        $controller = new ProductApiController();
        $id = $url[2] ?? null;
        
        if ($method === 'GET') {
            if ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
        } else if ($method === 'POST' && !$id) {
            $controller->store();
        } else if (($method === 'PUT' || $method === 'POST') && $id) {
            $controller->update($id);
        } else if ($method === 'DELETE' && $id) {
            $controller->destroy($id);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Product endpoint not found']);
        }
        exit;
    }
    
    // Category Routes
    if ($resource === 'category') {
        require_once 'app/controllers/CategoryApiController.php';
        $controller = new CategoryApiController();
        $id = $url[2] ?? null;
        
        if ($method === 'GET') {
            if ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
        } else if ($method === 'POST' && !$id) {
            $controller->store();
        } else if ($method === 'PUT' && $id) {
            $controller->update($id);
        } else if ($method === 'DELETE' && $id) {
            $controller->destroy($id);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Category endpoint not found']);
        }
        exit;
    }
    
    // Cart Routes
    if ($resource === 'cart') {
        require_once 'app/controllers/CartApiController.php';
        $controller = new CartApiController();
        $id = $url[2] ?? null;
        
        if ($method === 'GET') {
            $controller->index();
        } else if ($method === 'POST') {
            $controller->store();
        } else if ($method === 'PUT' && $id) {
            $controller->update($id);
        } else if ($method === 'DELETE') {
            if ($id) {
                $controller->destroy($id);
            } else {
                $controller->clear();
            }
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Cart endpoint not found']);
        }
        exit;
    }
    
    // Order Routes
    if ($resource === 'order') {
        require_once 'app/controllers/OrderApiController.php';
        $controller = new OrderApiController();
        $id = $url[2] ?? null;
        $subAction = $url[3] ?? null;
        
        if ($method === 'GET') {
            if ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
        } else if ($method === 'POST' && !$id) {
            $controller->store();
        } else if ($method === 'PUT' && $id) {
            if ($subAction === 'cancel') {
                $controller->cancel($id);
            } else if ($subAction === 'status') {
                $controller->updateStatus($id);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Order action not found']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Order endpoint not found']);
        }
        exit;
    }
    
    // Payment Routes
    if ($resource === 'payment') {
        require_once 'app/controllers/PaymentApiController.php';
        $controller = new PaymentApiController();
        
        if ($method === 'POST') {
            $controller->pay();
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Payment endpoint not found']);
        }
        exit;
    }
    
    http_response_code(404);
    echo json_encode(['message' => 'API Endpoint not found']);
    exit;
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