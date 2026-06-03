<?php
require_once 'app/config/database.php';
require_once 'app/models/UserModel.php';

class AdminUserController {
    private $userModel;

    public function __construct() {
        $this->requireAdmin();
        $db = (new Database())->getConnection();
        $this->userModel = new UserModel($db);
    }

    private function requireAdmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            die('Bạn không có quyền truy cập trang này. <a href="/Product/list">Quay lại</a>');
        }
    }

    public function index() {
        $allUsers = $this->userModel->getAllUsers();
        
        $items_per_page = 10;
        $total_items = count($allUsers);
        $total_pages = ceil($total_items / $items_per_page);
        
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($current_page < 1) $current_page = 1;
        if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;
        
        $offset = ($current_page - 1) * $items_per_page;
        $users = array_slice($allUsers, $offset, $items_per_page);

        include 'app/views/admin/users/list.php';
    }

    public function toggleStatus($id) {
        if ($this->userModel->toggleStatus($id)) {
            $_SESSION['success'] = "Cập nhật trạng thái người dùng thành công.";
        } else {
            $_SESSION['error'] = "Không thể cập nhật trạng thái (không thể khóa admin).";
        }
        header('Location: /AdminUser/index');
        exit();
    }
}
?>
