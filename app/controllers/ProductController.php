<?php
require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';

class ProductController {
    private $productModel;
    private $categoryModel;
    private $db;

    public function __construct() {
        // Khởi tạo kết nối Database và Model
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function index() {
        $this->list();
    }

    public function list() {
        // Lấy danh sách từ Database thay vì Session
        $products = $this->productModel->getProducts();
        include 'app/views/product/list.php';
    }

    public function add() {
        $categoryModel = new CategoryModel($this->db);
        $categories = $categoryModel->getCategories();
        include 'app/views/product/add.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $category_id = $_POST['category_id'] ?? null;

            // Xử lý ảnh nếu có
            $image = "";
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $target_dir = "uploads/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $image = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
                move_uploaded_file($_FILES["image"]["tmp_name"], $image);
            }

            // Gọi Model để lưu vào Database
            $result = $this->productModel->addProduct($name, $description, $price, $category_id, $image);
            
            if ($result === true) {
                // Lưu thành công, quay về danh sách (chú ý đường dẫn dự án của bạn)
                header('Location: /Product/list');
                exit();
            } else {
                $errors = $result; // Nếu Model trả về mảng lỗi validation
                $categories = (new CategoryModel($this->db))->getCategories();
                include 'app/views/product/add.php';
            }
        }
    }

    public function edit($id = null) {
        // Sửa lỗi Fatal Error bằng cách kiểm tra ID
        if (!$id) {
            header('Location: /Product/list');
            exit();
        }

        $product = $this->productModel->getProductById($id);
        $categories = $this->categoryModel->getCategories();

        if ($product) {
            include 'app/views/product/edit.php';
        } else {
            die('Không tìm thấy sản phẩm này trong hệ thống.');
        }
    }


    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $category_id = $_POST['category_id'];

            // Xử lý ảnh
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            } else {
                // Nếu không chọn ảnh mới, lấy lại đường dẫn ảnh cũ từ input hidden
                $image = $_POST['existing_image'] ?? "";
            }

            if ($this->productModel->updateProduct($id, $name, $description, $price, $category_id, $image)) {
                header('Location: /Product/list');
                exit();
            }
        }
    }

    public function delete($id) {
        if ($this->productModel->deleteProduct($id)) {
            header('Location: /Product/list');
            exit();
        }
    }

    // Hàm hỗ trợ upload hình ảnh
    private function uploadImage($file) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($file["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_file;
        }
        return "";
    }

    // File: app/controllers/ProductController.php

    public function show($id) {
        $product = $this->productModel->getProductById($id);
        if ($product) {
            include 'app/views/product/show.php';
        } else {
            die('Sản phẩm không tồn tại.');
        }
    }

    // Thêm các phương thức này vào bên trong class ProductController trong file app/controllers/ProductController.php

    public function addToCart($id) {
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            die("Không tìm thấy sản phẩm.");
        }

        // Khởi tạo giỏ hàng nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Nếu sản phẩm đã có trong giỏ, tăng số lượng
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            // Nếu chưa có, thêm mới vào giỏ
            $_SESSION['cart'][$id] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image' => $product->image
            ];
        }
        // Quay về trang hiển thị giỏ hàng
        header('Location: /Product/cart');
        exit();
    }

    public function cart() {
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        include 'app/views/product/cart.php';
    }

    // Tính năng bổ sung: Xóa sản phẩm khỏi giỏ hàng
    public function removeFromCart($id) {
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
        header('Location: /Product/cart');
        exit();
    }

    public function checkout() {
        // Nếu giỏ hàng trống thì không cho vào trang thanh toán
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            header('Location: /Product/list');
            exit();
        }
        include 'app/views/product/checkout.php';
    }

    public function processCheckout() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';

            if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                die("Giỏ hàng của bạn đang trống.");
            }

            // Sử dụng Transaction để đảm bảo an toàn dữ liệu
            $this->db->beginTransaction();
            try {
                // 1. Lưu vào bảng orders
                $query = "INSERT INTO orders (name, phone, address) VALUES (:name, :phone, :address)";
                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    ':name' => htmlspecialchars(strip_tags($name)),
                    ':phone' => htmlspecialchars(strip_tags($phone)),
                    ':address' => htmlspecialchars(strip_tags($address))
                ]);
                $order_id = $this->db->lastInsertId();

                // 2. Lưu vào bảng order_details
                $cart = $_SESSION['cart'];
                foreach ($cart as $product_id => $item) {
                    $query = "INSERT INTO order_details (order_id, product_id, quantity, price) 
                            VALUES (:order_id, :product_id, :quantity, :price)";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([
                        ':order_id' => $order_id,
                        ':product_id' => $product_id,
                        ':quantity' => $item['quantity'],
                        ':price' => $item['price']
                    ]);
                }

                // Xóa giỏ hàng sau khi đặt thành công
                unset($_SESSION['cart']);
                
                // Xác nhận hoàn tất transaction
                $this->db->commit();

                // Chuyển hướng sang trang cảm ơn
                header('Location: /Product/orderConfirmation');
                exit();

            } catch (Exception $e) {
                // Có lỗi xảy ra, hoàn tác lại toàn bộ để tránh rác DB
                $this->db->rollBack();
                die("Đã xảy ra lỗi khi xử lý đơn hàng: " . $e->getMessage());
            }
        }
    }

    public function orderConfirmation() {
        include 'app/views/product/orderConfirmation.php';
    }
}