<?php
require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';

class AdminOrderController {
    private $orderModel;

    public function __construct() {
        $this->requireAdmin();
        $db = (new Database())->getConnection();
        $this->orderModel = new OrderModel($db);
    }

    private function requireAdmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            die('Bạn không có quyền truy cập trang này. <a href="/Product/list">Quay lại</a>');
        }
    }

    public function index() {
        $allOrders = $this->orderModel->getAllOrders();
        
        // Tính tổng doanh thu
        $totalRevenue = 0;
        foreach ($allOrders as $order) {
            $totalRevenue += $order->total_price;
        }

        // Phân trang
        $items_per_page = 10;
        $total_items = count($allOrders);
        $total_pages = ceil($total_items / $items_per_page);
        
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($current_page < 1) $current_page = 1;
        if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;
        
        $offset = ($current_page - 1) * $items_per_page;
        $orders = array_slice($allOrders, $offset, $items_per_page);

        include 'app/views/admin/orders/list.php';
    }

    public function view($id) {
        $data = $this->orderModel->getOrderDetailsForAdmin($id);
        if ($data) {
            $orderInfo = $data['info'];
            $orderDetails = $data['details'];
            include 'app/views/admin/orders/detail.php';
        } else {
            die('Không tìm thấy đơn hàng.');
        }
    }
}
?>
