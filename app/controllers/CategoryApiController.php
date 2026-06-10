<?php
require_once 'app/config/database.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/utils/JWTMiddleware.php';

class CategoryApiController
{
    private $categoryModel;
    private $db;
    
    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    // Lấy danh sách danh mục (công khai)
    public function index()
    {
        header('Content-Type: application/json');
        $categories = $this->categoryModel->getCategories();
        echo json_encode($categories);
    }

    // Lấy chi tiết danh mục (công khai)
    public function show($id)
    {
        header('Content-Type: application/json');
        $category = $this->categoryModel->getCategoryById($id);
        if ($category) {
            echo json_encode($category);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Không tìm thấy danh mục này']);
        }
    }

    // Thêm danh mục mới (chỉ Admin)
    public function store()
    {
        JWTMiddleware::requireRole('admin');
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['message' => 'Tên danh mục không được để trống']);
            return;
        }

        if ($this->categoryModel->addCategory($name, $description)) {
            http_response_code(201);
            echo json_encode(['message' => 'Thêm danh mục thành công!']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi thêm danh mục']);
        }
    }

    // Sửa danh mục (chỉ Admin)
    public function update($id)
    {
        JWTMiddleware::requireRole('admin');
        header('Content-Type: application/json');

        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            http_response_code(404);
            echo json_encode(['message' => 'Không tìm thấy danh mục này']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $name = $data['name'] ?? $category->name;
        $description = $data['description'] ?? $category->description;

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['message' => 'Tên danh mục không được để trống']);
            return;
        }

        if ($this->categoryModel->updateCategory($id, $name, $description)) {
            echo json_encode(['message' => 'Cập nhật danh mục thành công!']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi cập nhật danh mục']);
        }
    }

    // Xóa danh mục (chỉ Admin, chặn nếu còn sản phẩm)
    public function destroy($id)
    {
        JWTMiddleware::requireRole('admin');
        header('Content-Type: application/json');

        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            http_response_code(404);
            echo json_encode(['message' => 'Không tìm thấy danh mục này']);
            return;
        }

        // Kiểm tra xem có sản phẩm nào thuộc danh mục này không
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM product WHERE category_id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $productCount = $result['total'] ?? 0;

        if ($productCount > 0) {
            http_response_code(400);
            echo json_encode([
                'message' => 'Không thể xóa danh mục này vì vẫn còn ' . $productCount . ' sản phẩm thuộc danh mục này!'
            ]);
            return;
        }

        if ($this->categoryModel->deleteCategory($id)) {
            echo json_encode(['message' => 'Xóa danh mục thành công!']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi xóa danh mục']);
        }
    }
}
?>
