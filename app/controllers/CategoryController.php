<?php
require_once('app/config/database.php');
require_once('app/models/CategoryModel.php');

class CategoryController {
    private $categoryModel;
    private $db;

    public function __construct() {
        $this->requireAdmin();
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    private function requireAdmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            die('Bạn không có quyền truy cập trang này. <a href="/Product/list">Quay lại</a>');
        }
    }

    public function index() { $this->list(); }

    public function list() {
        $allCategories = $this->categoryModel->getCategories();
        
        $items_per_page = 8;
        $total_items = count($allCategories);
        $total_pages = ceil($total_items / $items_per_page);
        
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($current_page < 1) $current_page = 1;
        if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;
        
        $offset = ($current_page - 1) * $items_per_page;
        $categories = array_slice($allCategories, $offset, $items_per_page);
        
        include 'app/views/category/list.php';
    }

    // Giao diện thêm danh mục
    public function add() {
        include 'app/views/category/add.php';
    }

    // Xử lý lưu danh mục mới
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $this->categoryModel->addCategory($name, $description);
            header('Location: /Category/list');
            exit();
        }
    }

    // Giao diện sửa danh mục
    public function edit($id) {
        $category = $this->categoryModel->getCategoryById($id);
        if ($category) {
            include 'app/views/category/edit.php';
        } else {
            die('Không tìm thấy danh mục này.');
        }
    }

    // Xử lý cập nhật danh mục
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $this->categoryModel->updateCategory($id, $name, $description);
            header('Location: /Category/list');
            exit();
        }
    }

    // Xử lý xóa danh mục
    public function delete($id) {
        $this->categoryModel->deleteCategory($id);
        header('Location: /Category/list');
        exit();
    }
}