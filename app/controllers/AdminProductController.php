<?php
require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';

class AdminProductController {
    private $productModel;
    private $categoryModel;

    public function __construct() {
        $this->requireAdmin();
        $db = (new Database())->getConnection();
        $this->productModel = new ProductModel($db);
        $this->categoryModel = new CategoryModel($db);
    }

    private function requireAdmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            die('Bạn không có quyền truy cập trang này. <a href="/Product/list">Quay lại</a>');
        }
    }

    public function index() {
        $allProducts = $this->productModel->getProducts();
        
        // Phân trang
        $items_per_page = 6;
        $total_items = count($allProducts);
        $total_pages = ceil($total_items / $items_per_page);
        
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($current_page < 1) $current_page = 1;
        if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;
        
        $offset = ($current_page - 1) * $items_per_page;
        $products = array_slice($allProducts, $offset, $items_per_page);

        $categories = $this->categoryModel->getCategories();
        
        // Tạo map category_id => name để dễ hiển thị
        $categoryMap = [];
        foreach ($categories as $c) {
            $categoryMap[$c->id] = $c->name;
        }

        include 'app/views/admin/products/list.php';
    }
}
?>
