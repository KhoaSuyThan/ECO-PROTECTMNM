<?php
require_once 'app/config/database.php';
require_once 'app/models/CartModel.php';
require_once 'app/models/ProductModel.php';
require_once 'app/utils/JWTMiddleware.php';

class CartApiController
{
    private $cartModel;
    private $productModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->cartModel = new CartModel($this->db);
        $this->productModel = new ProductModel($this->db);
    }

    // Xem giỏ hàng & Tính tổng tiền
    public function index()
    {
        $decoded = JWTMiddleware::authenticate();
        $user_id = $decoded['id'];

        $cartItems = $this->cartModel->getCart($user_id);
        $totalPrice = $this->cartModel->getTotalPrice($user_id);

        echo json_encode([
            'cart' => $cartItems,
            'total_price' => $totalPrice
        ]);
    }

    // Thêm sản phẩm vào giỏ hàng (kiểm tra tồn tại, số lượng > 0)
    public function store()
    {
        $decoded = JWTMiddleware::authenticate();
        $user_id = $decoded['id'];

        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $product_id = $data['product_id'] ?? null;
        $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

        if (empty($product_id)) {
            http_response_code(400);
            echo json_encode(['message' => 'Thiếu product_id']);
            return;
        }

        // 1. Kiểm tra sản phẩm có tồn tại trước khi thêm vào giỏ hàng
        $product = $this->productModel->getProductById($product_id);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['message' => 'Sản phẩm không tồn tại']);
            return;
        }

        // 2. Số lượng sản phẩm phải lớn hơn 0
        if ($quantity <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Số lượng sản phẩm phải lớn hơn 0']);
            return;
        }

        if ($this->cartModel->addToCart($user_id, $product_id, $quantity)) {
            echo json_encode(['message' => 'Đã thêm sản phẩm vào giỏ hàng thành công!']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi thêm sản phẩm vào giỏ hàng']);
        }
    }

    // Cập nhật số lượng sản phẩm trong giỏ hàng
    public function update($product_id)
    {
        $decoded = JWTMiddleware::authenticate();
        $user_id = $decoded['id'];

        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

        // 1. Số lượng sản phẩm phải lớn hơn 0
        if ($quantity <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Số lượng sản phẩm phải lớn hơn 0']);
            return;
        }

        if ($this->cartModel->updateQuantity($user_id, $product_id, $quantity)) {
            echo json_encode(['message' => 'Cập nhật số lượng thành công!']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi cập nhật số lượng']);
        }
    }

    // Xóa một sản phẩm khỏi giỏ hàng
    public function destroy($product_id)
    {
        $decoded = JWTMiddleware::authenticate();
        $user_id = $decoded['id'];

        if ($this->cartModel->removeFromCart($user_id, $product_id)) {
            echo json_encode(['message' => 'Đã xóa sản phẩm khỏi giỏ hàng']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi xóa sản phẩm khỏi giỏ hàng']);
        }
    }

    // Xóa toàn bộ giỏ hàng
    public function clear()
    {
        $decoded = JWTMiddleware::authenticate();
        $user_id = $decoded['id'];

        if ($this->cartModel->clearCart($user_id)) {
            echo json_encode(['message' => 'Đã xóa sạch giỏ hàng']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi xóa sạch giỏ hàng']);
        }
    }
}
?>
