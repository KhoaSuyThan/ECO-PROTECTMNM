<?php
require_once('app/config/database.php');
require_once('app/models/CategoryModel.php');

class CategoryController {
    private $categoryModel;
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function list() {
        $categories = $this->categoryModel->getCategories();
        // Bạn có thể tạo thêm view cho category nếu cần
        echo "Tính năng quản lý danh mục đang cập nhật.";
    }
}