<?php
require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';

class OrderController {
    private $orderModel;
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);
    }

    private function requireLogin() {
        if (!isset($_SESSION['user'])) {
            // Lưu lại trang muốn truy cập để chuyển hướng sau khi đăng nhập (tùy chọn)
            header('Location: /User/login');
            exit();
        }
    }

    public function history() {
        $this->requireLogin();
        
        $user_id = $_SESSION['user']['id'];
        $orders = $this->orderModel->getOrdersByUserId($user_id);
        
        include 'app/views/order/history.php';
    }

    public function details($id = null) {
        $this->requireLogin();

        if (!$id) {
            header('Location: /Order/history');
            exit();
        }

        $user_id = $_SESSION['user']['id'];
        $orderData = $this->orderModel->getOrderDetails($id, $user_id);

        if ($orderData) {
            $orderInfo = $orderData['info'];
            $orderDetails = $orderData['details'];
            include 'app/views/order/details.php';
        } else {
            die('Không tìm thấy đơn hàng hoặc bạn không có quyền xem.');
        }
    }
}
?>
