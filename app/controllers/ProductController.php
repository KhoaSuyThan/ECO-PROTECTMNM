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

    private function requireAdmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            die('Bạn không có quyền truy cập trang này. <a href="/Product/list">Quay lại</a>');
        }
    }

    // Hàm hỗ trợ lấy tên Session Giỏ hàng theo tài khoản người dùng
    private function getCartKey() {
        return isset($_SESSION['user']) ? 'cart_' . $_SESSION['user']['username'] : 'cart_guest';
    }

    public function index() {
        $this->list();
    }

    public function list() {
        $products = $this->productModel->getProducts();
        $categories = $this->categoryModel->getCategories();
        include 'app/views/product/list.php';
    }

    public function add() {
        $this->requireAdmin();
        $categoryModel = new CategoryModel($this->db);
        $categories = $categoryModel->getCategories();
        include 'app/views/product/add.php';
    }

    public function save() {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $category_id = $_POST['category_id'] ?? null;

            $image = "";
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                
                if (in_array($file_ext, $allowed_exts)) {
                    $target_dir = "uploads/";
                    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                    $image = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
                    move_uploaded_file($_FILES["image"]["tmp_name"], $image);
                } else {
                    $errors = "Định dạng ảnh không hợp lệ (Chỉ nhận .jpg, .png, .jpeg, .webp, .gif)";
                    $categories = (new CategoryModel($this->db))->getCategories();
                    include 'app/views/product/add.php';
                    return;
                }
            }

            $result = $this->productModel->addProduct($name, $description, $price, $category_id, $image);
            
            if ($result === true) {
                header('Location: /Product/list');
                exit();
            } else {
                $errors = $result;
                $categories = (new CategoryModel($this->db))->getCategories();
                include 'app/views/product/add.php';
            }
        }
    }

    public function edit($id = null) {
        $this->requireAdmin();
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
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $category_id = $_POST['category_id'];

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            } else {
                $image = $_POST['existing_image'] ?? "";
            }

            if ($this->productModel->updateProduct($id, $name, $description, $price, $category_id, $image)) {
                header('Location: /Product/list');
                exit();
            }
        }
    }

    public function delete($id) {
        $this->requireAdmin();
        if ($this->productModel->deleteProduct($id)) {
            $_SESSION['success'] = "Đã xóa sản phẩm thành công!";
            header('Location: /Product/list');
            exit();
        }
    }

    private function uploadImage($file) {
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $file_ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_exts)) {
            return "";
        }

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

    public function show($id) {
        $product = $this->productModel->getProductById($id);
        if ($product) {
            include 'app/views/product/show.php';
        } else {
            die('Sản phẩm không tồn tại.');
        }
    }

    // --- CÁC HÀM XỬ LÝ GIỎ HÀNG ĐÃ ĐƯỢC CẬP NHẬT ---
    public function addToCart($id) {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.";
            header('Location: /User/login');
            exit();
        }

        $product = $this->productModel->getProductById($id);
        if (!$product) {
            die("Không tìm thấy sản phẩm.");
        }

        $quantity = 1;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
            $quantity = (int)$_POST['quantity'];
            if ($quantity < 1) $quantity = 1;
        }

        $cartKey = $this->getCartKey(); // Lấy đúng giỏ hàng của user

        if (!isset($_SESSION[$cartKey])) {
            $_SESSION[$cartKey] = [];
        }

        if (isset($_SESSION[$cartKey][$id])) {
            $_SESSION[$cartKey][$id]['quantity'] += $quantity;
        } else {
            $_SESSION[$cartKey][$id] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'image' => $product->image
            ];
        }
        
        $_SESSION['success'] = "Đã thêm " . $product->name . " vào giỏ hàng!";
        
        $referer = $_SERVER['HTTP_REFERER'] ?? '/Product/cart';
        header("Location: $referer");
        exit();
    }

    public function cart() {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Bạn cần đăng nhập để xem giỏ hàng.";
            header('Location: /User/login');
            exit();
        }
        $cartKey = $this->getCartKey();
        $cart = isset($_SESSION[$cartKey]) ? $_SESSION[$cartKey] : [];
        include 'app/views/product/cart.php';
    }

    public function removeFromCart($id) {
        $cartKey = $this->getCartKey();
        if (isset($_SESSION[$cartKey][$id])) {
            unset($_SESSION[$cartKey][$id]);
        }
        header('Location: /Product/cart');
        exit();
    }

    public function updateCart($id, $action) {
        $cartKey = $this->getCartKey();
        if (isset($_SESSION[$cartKey][$id])) {
            if ($action === 'increase') {
                $_SESSION[$cartKey][$id]['quantity']++;
            } elseif ($action === 'decrease') {
                $_SESSION[$cartKey][$id]['quantity']--;
                if ($_SESSION[$cartKey][$id]['quantity'] <= 0) {
                    unset($_SESSION[$cartKey][$id]);
                }
            }
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? '/Product/cart';
        header("Location: $referer");
        exit();
    }

    public function checkout() {
        include 'app/views/product/checkout.php';
    }

    public function processCheckout() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';

            $cartKey = $this->getCartKey();

            if (!isset($_SESSION[$cartKey]) || empty($_SESSION[$cartKey])) {
                die("Giỏ hàng của bạn đang trống.");
            }

            // Lấy user_id nếu đã đăng nhập (Chức năng Lịch sử mua hàng)
            $user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;

            $this->db->beginTransaction();
            try {
                $query = "INSERT INTO orders (user_id, name, phone, address) VALUES (:user_id, :name, :phone, :address)";
                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':name' => htmlspecialchars(strip_tags($name)),
                    ':phone' => htmlspecialchars(strip_tags($phone)),
                    ':address' => htmlspecialchars(strip_tags($address))
                ]);
                $order_id = $this->db->lastInsertId();

                $cart = $_SESSION[$cartKey];
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

                // Xóa giỏ hàng CỦA USER ĐÓ sau khi đặt thành công
                unset($_SESSION[$cartKey]);
                
                $this->db->commit();

                header('Location: /Product/orderConfirmation');
                exit();

            } catch (Exception $e) {
                $this->db->rollBack();
                die("Đã xảy ra lỗi khi xử lý đơn hàng: " . $e->getMessage());
            }
        }
    }

    public function orderConfirmation() {
        include 'app/views/product/orderConfirmation.php';
    }
}