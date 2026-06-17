<?php

class OrderApiController
{
    private $orderModel;
    private $cartModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);
        $this->cartModel = new CartModel($this->db);
    }

    // Xem danh sách đơn hàng (User xem đơn của mình, Admin xem toàn bộ đơn)
    public function index()
    {
        $decoded = JWTMiddleware::authenticate();
        $user_id = $decoded['id'];
        $role = $decoded['role'];

        if ($role === 'admin') {
            $orders = $this->orderModel->getAllOrders();
        } else {
            $orders = $this->orderModel->getOrdersByUserId($user_id);
        }

        echo json_encode($orders);
    }

    // Xem chi tiết đơn hàng (User chỉ được xem đơn của mình, Admin xem toàn bộ)
    public function show($id)
    {
        $decoded = JWTMiddleware::authenticate();
        $user_id = $decoded['id'];
        $role = $decoded['role'];

        if ($role === 'admin') {
            $data = $this->orderModel->getOrderDetailsForAdmin($id);
        } else {
            $data = $this->orderModel->getOrderDetails($id, $user_id);
        }

        if ($data) {
            echo json_encode($data);
        } else {
            http_response_code(403);
            echo json_encode(['message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền truy cập']);
        }
    }

    // Tạo đơn hàng từ giỏ hàng (kiểm tra giỏ hàng trống, xóa sạch giỏ khi thành công)
    public function store()
    {
        $decoded = JWTMiddleware::authenticate();
        $user_id = $decoded['id'];

        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $name = $data['name'] ?? '';
        $phone = $data['phone'] ?? '';
        $address = $data['address'] ?? '';

        if (empty($name) || empty($phone) || empty($address)) {
            http_response_code(400);
            echo json_encode(['message' => 'Vui lòng cung cấp đầy đủ thông tin giao hàng: tên, điện thoại, địa chỉ']);
            return;
        }

        // 1. Kiểm tra giỏ hàng có rỗng hay không trước khi đặt
        $cart_items = $this->cartModel->getCart($user_id);
        if (empty($cart_items)) {
            http_response_code(400);
            echo json_encode(['message' => 'Không thể đặt hàng vì giỏ hàng đang trống']);
            return;
        }

        $order_id = $this->orderModel->createOrder($user_id, $name, $phone, $address, $cart_items);

        if ($order_id) {
            http_response_code(201);
            echo json_encode([
                'message' => 'Đặt đơn hàng thành công!',
                'order_id' => $order_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi tạo đơn hàng']);
        }
    }

    // Hủy đơn hàng (Chỉ cho phép khi đang ở trạng thái pending)
    public function cancel($id)
    {
        $decoded = JWTMiddleware::authenticate();
        $user_id = $decoded['id'];

        if ($this->orderModel->cancelOrder($id, $user_id)) {
            echo json_encode(['message' => 'Đã hủy đơn hàng thành công!']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Hủy đơn hàng thất bại. Đơn hàng không tồn tại hoặc đã được xử lý.']);
        }
    }

    // Cập nhật trạng thái đơn hàng (chỉ Admin)
    public function updateStatus($id)
    {
        JWTMiddleware::requireRole('admin');

        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $status = $data['status'] ?? '';

        $allowedStatus = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatus)) {
            http_response_code(400);
            echo json_encode(['message' => 'Trạng thái đơn hàng không hợp lệ']);
            return;
        }

        if ($this->orderModel->updateOrderStatus($id, $status)) {
            echo json_encode(['message' => 'Cập nhật trạng thái đơn hàng thành công!']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi cập nhật trạng thái đơn hàng']);
        }
    }
}
?>
