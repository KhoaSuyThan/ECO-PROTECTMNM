<?php
require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/utils/JWTMiddleware.php';

class PaymentApiController
{
    private $orderModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);
    }

    // Tạo thanh toán cho đơn hàng
    public function pay()
    {
        $decoded = JWTMiddleware::authenticate();
        $user_id = $decoded['id'];

        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $order_id = $data['order_id'] ?? null;
        $payment_method = $data['payment_method'] ?? 'COD'; // COD, BANK_TRANSFER, MOMO...

        if (empty($order_id)) {
            http_response_code(400);
            echo json_encode(['message' => 'Thiếu order_id']);
            return;
        }

        // 1. Kiểm tra đơn hàng có tồn tại và thuộc về user này không
        $order = $this->orderModel->getOrderById($order_id);
        if (!$order) {
            http_response_code(404);
            echo json_encode(['message' => 'Đơn hàng không tồn tại']);
            return;
        }

        if ($order->user_id != $user_id && $decoded['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['message' => 'Bạn không có quyền thanh toán cho đơn hàng này']);
            return;
        }

        // 2. Không cho thanh toán lại đơn hàng đã thanh toán
        if ($order->payment_status === 'paid') {
            http_response_code(400);
            echo json_encode(['message' => 'Đơn hàng này đã được thanh toán trước đó, không thể thanh toán lại']);
            return;
        }

        // 3. Cập nhật trạng thái thanh toán và phương thức thanh toán
        // Giả lập xử lý thanh toán ví điện tử hoặc chuyển khoản thành công
        $payment_status = 'paid'; 
        
        // Nếu chọn COD thì có thể đánh dấu là chưa thanh toán (chờ thu hộ) hoặc đã thanh toán tùy nghiệp vụ. 
        // Tuy nhiên theo đề bài, ta hỗ trợ thanh toán khi nhận hàng (COD) và mô phỏng chuyển khoản/ví điện tử.
        if ($payment_method === 'COD') {
            $payment_status = 'unpaid'; // COD thường thanh toán khi nhận hàng
        }

        if ($this->orderModel->updatePaymentStatus($order_id, $payment_status, $payment_method)) {
            // Tự động chuyển đơn hàng sang confirmed khi đã thanh toán (chuyển khoản/ví điện tử)
            if ($payment_status === 'paid') {
                $this->orderModel->updateOrderStatus($order_id, 'confirmed');
            }
            
            echo json_encode([
                'message' => 'Xử lý thanh toán đơn hàng thành công!',
                'order_id' => $order_id,
                'payment_method' => $payment_method,
                'payment_status' => $payment_status
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi hệ thống khi cập nhật thanh toán']);
        }
    }
}
?>
