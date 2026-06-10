<?php include 'app/shares/header.php'; ?>

<style>
    body { background-color: #f4f7f5; }
    .history-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .history-header {
        background: linear-gradient(135deg, #2d6a4f, #1b4332);
        color: white;
        padding: 20px;
        font-weight: bold;
    }
    .table-hover tbody tr:hover {
        background-color: #f1f8f5;
        transition: 0.3s;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card history-card">
                <div class="history-header d-flex align-items-center">
                    <i class="fas fa-history fa-2x me-3"></i>
                    <h3 class="mb-0">Lịch sử mua hàng</h3>
                </div>
                <div class="card-body p-4" id="history-body">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Đang tải lịch sử đơn hàng...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const historyBody = document.getElementById('history-body');
    const token = localStorage.getItem('jwtToken');

    if (!token) {
        historyBody.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-user-lock fa-3x text-muted mb-3"></i>
                <h5 class="text-secondary">Bạn chưa đăng nhập.</h5>
                <a href="/User/login" class="btn btn-success mt-3 rounded-pill px-4">Đăng nhập ngay</a>
            </div>
        `;
        return;
    }

    fetch('/api/order', {
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Không thể tải lịch sử đơn hàng');
        }
        return response.json();
    })
    .then(orders => {
        renderOrders(orders);
    })
    .catch(err => {
        console.error("Lỗi:", err);
        historyBody.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5 class="text-secondary">Có lỗi xảy ra khi kết nối hệ thống.</h5>
            </div>
        `;
    });

    function renderOrders(orders) {
        if (orders.length === 0) {
            historyBody.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted opacity-50 mb-3"></i>
                    <h5 class="text-secondary">Bạn chưa có đơn hàng nào.</h5>
                    <a href="/Product/list" class="btn btn-success mt-3 rounded-pill px-4">Mua sắm ngay</a>
                </div>
            `;
            return;
        }

        let tableRows = '';
        orders.forEach(order => {
            const date = new Date(order.created_at).toLocaleString('vi-VN');
            
            // Trạng thái đơn hàng
            let statusBadge = '';
            switch(order.status) {
                case 'pending':
                    statusBadge = '<span class="badge bg-warning text-dark">Chờ duyệt</span>';
                    break;
                case 'confirmed':
                    statusBadge = '<span class="badge bg-primary">Đã duyệt</span>';
                    break;
                case 'shipping':
                    statusBadge = '<span class="badge bg-info text-dark">Đang giao</span>';
                    break;
                case 'completed':
                    statusBadge = '<span class="badge bg-success">Đã giao</span>';
                    break;
                case 'cancelled':
                    statusBadge = '<span class="badge bg-danger">Đã hủy</span>';
                    break;
                default:
                    statusBadge = '<span class="badge bg-secondary">Không rõ</span>';
            }

            // Trạng thái thanh toán
            let paymentBadge = order.payment_status === 'paid' 
                ? ' <span class="badge bg-success">Đã thanh toán</span>' 
                : ' <span class="badge bg-secondary">Chưa thanh toán</span>';

            tableRows += `
                <tr>
                    <td><strong>#${order.id}</strong></td>
                    <td>${date}</td>
                    <td>${escapeHtml(order.name)}</td>
                    <td>${escapeHtml(order.phone)}</td>
                    <td>${statusBadge} ${paymentBadge}</td>
                    <td class="text-center">
                        <a href="/Order/details/${order.id}" class="btn btn-outline-success btn-sm rounded-pill">
                            <i class="fas fa-eye me-1"></i> Chi tiết
                        </a>
                    </td>
                </tr>
            `;
        });

        historyBody.innerHTML = `
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Ngày đặt</th>
                            <th>Người nhận</th>
                            <th>Số điện thoại</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableRows}
                    </tbody>
                </table>
            </div>
        `;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
});
</script>

<?php include 'app/shares/footer.php'; ?>
