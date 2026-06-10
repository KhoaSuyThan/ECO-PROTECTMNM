<?php
require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/utils/JWTMiddleware.php';

class ProductApiController
{
    private $productModel;
    private $categoryModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->categoryModel = new CategoryModel($this->db);
    }

    // Lấy danh sách sản phẩm (có tìm kiếm, lọc, sắp xếp, khoảng giá, phân trang)
    public function index()
    {
        // Cho phép công khai không cần token (ai cũng xem được danh sách)
        $params = [];
        
        // 1. Tìm kiếm theo tên
        if (!empty($_GET['name'])) {
            $params['name'] = trim($_GET['name']);
        }
        
        // 2. Lọc theo danh mục
        if (!empty($_GET['category_id'])) {
            $params['category_id'] = (int)$_GET['category_id'];
        }

        // 3. Lọc theo khoảng giá
        if (isset($_GET['min_price']) && $_GET['min_price'] !== '') {
            $params['min_price'] = (float)$_GET['min_price'];
        }
        if (isset($_GET['max_price']) && $_GET['max_price'] !== '') {
            $params['max_price'] = (float)$_GET['max_price'];
        }

        // 4. Sắp xếp theo giá
        if (!empty($_GET['sort'])) {
            $params['sort'] = $_GET['sort']; // price_asc, price_desc
        }

        // 5. Phân trang
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8; // Mặc định 8 sản phẩm/trang
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 8;
        
        $params['limit'] = $limit;
        $params['offset'] = ($page - 1) * $limit;

        $products = $this->productModel->getProductsAdvanced($params);
        $total = $this->productModel->countProductsAdvanced($params);
        $totalPages = ceil($total / $limit);

        echo json_encode([
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $totalPages
        ]);
    }

    // Lấy chi tiết một sản phẩm
    public function show($id)
    {
        $product = $this->productModel->getProductById($id);
        if ($product) {
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Không tìm thấy sản phẩm này']);
        }
    }

    // Thêm sản phẩm mới (chỉ dành cho Admin, hỗ trợ upload ảnh qua API)
    public function store()
    {
        // Xác thực quyền Admin
        JWTMiddleware::requireRole('admin');

        // Lấy dữ liệu từ $_POST (vì khi upload file, client gửi dạng multipart/form-data)
        $data = $_POST;
        if (empty($data)) {
            $data = json_decode(file_get_contents("php://input"), true) ?? [];
        }

        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? '';
        $category_id = $data['category_id'] ?? null;

        // 1. Kiểm tra tên không được rỗng
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['message' => 'Tên sản phẩm không được rỗng']);
            return;
        }

        // 2. Kiểm tra giá phải là số và lớn hơn 0
        if (!is_numeric($price) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Giá sản phẩm phải là số và lớn hơn 0']);
            return;
        }

        // 3. Kiểm tra danh mục hợp lệ
        if (empty($category_id) || !$this->categoryModel->getCategoryById($category_id)) {
            http_response_code(400);
            echo json_encode(['message' => 'Danh mục sản phẩm không hợp lệ']);
            return;
        }

        // 4. Xử lý upload hình ảnh
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $newFileName = time() . '_' . $filename;
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFileName)) {
                    $image = $uploadDir . $newFileName;
                }
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Định dạng hình ảnh không hợp lệ (chấp nhận jpg, jpeg, png, gif, webp)']);
                return;
            }
        }

        $result = $this->productModel->addProduct($name, $description, $price, $category_id, $image);
        if (is_array($result)) {
            http_response_code(400);
            echo json_encode(['errors' => $result]);
        } else if ($result === true) {
            http_response_code(201);
            echo json_encode(['message' => 'Thêm sản phẩm thành công!']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi thêm sản phẩm']);
        }
    }

    // Cập nhật thông tin sản phẩm (chỉ dành cho Admin, hỗ trợ upload ảnh mới qua API)
    public function update($id)
    {
        // Xác thực quyền Admin
        JWTMiddleware::requireRole('admin');

        $product = $this->productModel->getProductById($id);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['message' => 'Không tìm thấy sản phẩm cần cập nhật']);
            return;
        }

        // Để PUT hỗ trợ multipart/form-data trong PHP dễ dàng, 
        // ở phía giao diện JS chúng ta có thể POST kèm thêm trường giả lập phương thức hoặc gửi POST lên API chuyên biệt.
        // Tuy nhiên, đối với cập nhật API, ta parse dữ liệu từ $_POST/form-data hoặc JSON.
        // Trường hợp PUT request với multipart/form-data, PHP mặc định không parse tự động vào $_POST và $_FILES.
        // Để giải quyết đơn giản, chúng ta cho phép gửi dữ liệu dạng POST có đính kèm file hoặc JSON.
        // Chúng ta sẽ lấy dữ liệu từ POST/JSON:
        $data = $_POST;
        if (empty($data)) {
            $data = json_decode(file_get_contents("php://input"), true) ?? [];
        }

        $name = $data['name'] ?? $product->name;
        $description = $data['description'] ?? $product->description;
        $price = $data['price'] ?? $product->price;
        $category_id = $data['category_id'] ?? $product->category_id;

        // 1. Kiểm tra tên không được rỗng
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['message' => 'Tên sản phẩm không được rỗng']);
            return;
        }

        // 2. Kiểm tra giá phải là số và lớn hơn 0
        if (!is_numeric($price) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Giá sản phẩm phải là số và lớn hơn 0']);
            return;
        }

        // 3. Kiểm tra danh mục hợp lệ
        if (empty($category_id) || !$this->categoryModel->getCategoryById($category_id)) {
            http_response_code(400);
            echo json_encode(['message' => 'Danh mục sản phẩm không hợp lệ']);
            return;
        }

        // 4. Xử lý upload hình ảnh mới nếu có
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $newFileName = time() . '_' . $filename;
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFileName)) {
                    $image = $uploadDir . $newFileName;
                    // Xóa file ảnh cũ nếu tồn tại
                    if (!empty($product->image) && file_exists($product->image)) {
                        unlink($product->image);
                    }
                }
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Định dạng hình ảnh không hợp lệ']);
                return;
            }
        }

        if ($this->productModel->updateProduct($id, $name, $description, $price, $category_id, $image)) {
            echo json_encode(['message' => 'Cập nhật sản phẩm thành công!']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi cập nhật sản phẩm']);
        }
    }

    // Xóa sản phẩm theo ID (chỉ dành cho Admin)
    public function destroy($id)
    {
        // Xác thực quyền Admin
        JWTMiddleware::requireRole('admin');

        $product = $this->productModel->getProductById($id);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['message' => 'Không tìm thấy sản phẩm cần xóa']);
            return;
        }

        if ($this->productModel->deleteProduct($id)) {
            // Xóa file ảnh vật lý nếu có
            if (!empty($product->image) && file_exists($product->image)) {
                unlink($product->image);
            }
            echo json_encode(['message' => 'Xóa sản phẩm thành công!']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi xóa sản phẩm']);
        }
    }
}
?>
