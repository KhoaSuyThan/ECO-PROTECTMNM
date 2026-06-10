<?php include 'app/shares/header.php'; ?>

<style>
    body { background-color: #f4f7f5; }
    .details-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .details-header {
        background: linear-gradient(135deg, #2d6a4f, #1b4332);
        color: white;
        padding: 20px;
    }
    .product-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
    }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success"><i class="fas fa-file-invoice me-2"></i>Quản lý Đơn hàng #<?php echo (int)$orderInfo->id; ?></h3>
        <a href="/AdminOrder/index" class="btn btn-outline-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>
    </div>

    <div class="row" id="admin-order-detail-container">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Đang tải chi tiết đơn hàng...</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('admin-order-detail-container');
    const token = localStorage.getItem('jwtToken');
    const orderId = <?php echo (int)$orderInfo->id; ?>;

    if (!token) {
        alert('Vui lòng đăng nhập với tài khoản Admin.');
        window.location.href = '/User/login';
        return;
    }

    function fetchOrderDetails() {
        fetch('/api/order/' + orderId, {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Lỗi tải thông tin đơn hàng hoặc bạn không phải Admin');
            }
            return response.json();
        })
        .then(data => {
            renderDetails(data);
        })
        .catch(err => {
            console.error("Lỗi:", err);
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-secondary">${err.message || 'Không thể tải đơn hàng.'}</h5>
                </div>
            `;
        });
    }

    function renderDetails(data) {
        const info = data.info;
        const details = data.details || [];
        const date = new Date(info.created_at).toLocaleString('vi-VN');

        let statusText = '';
        let badgeClass = '';
        switch(info.status) {
            case 'pending':
                statusText = 'Chờ duyệt';
                badgeClass = 'bg-warning text-dark';
                break;
            case 'confirmed':
                statusText = 'Đã duyệt';
                badgeClass = 'bg-primary';
                break;
            case 'shipping':
                statusText = 'Đang giao';
                badgeClass = 'bg-info text-dark';
                break;
            case 'completed':
                statusText = 'Đã giao';
                badgeClass = 'bg-success';
                break;
            case 'cancelled':
                statusText = 'Đã hủy';
                badgeClass = 'bg-danger';
                break;
        }

        let paymentBadge = info.payment_status === 'paid' 
            ? '<span class="badge bg-success">Đã thanh toán</span>' 
            : '<span class="badge bg-secondary">Chưa thanh toán</span>';

        let tableRows = '';
        let total = 0;

        details.forEach(item => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            const formattedPrice = Number(item.price).toLocaleString('vi-VN');
            const formattedSubtotal = Number(subtotal).toLocaleString('vi-VN');
            const imgHtml = item.product_image 
                ? `<img src="/${item.product_image}" class="product-img me-3" alt="Image">`
                : `<div class="bg-light me-3 d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px; border-radius: 10px;">
                       <i class="fas fa-image fs-4"></i>
                   </div>`;

            tableRows += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            ${imgHtml}
                            <div>
                                <h6 class="mb-0 fw-bold">${item.product_name}</h6>
                                <small class="text-muted">Mã SP: #${item.product_id}</small>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">${formattedPrice} đ</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border px-2 py-1 fs-6">${item.quantity}</span>
                    </td>
                    <td class="text-end px-4 fw-bold text-danger">${formattedSubtotal} đ</td>
                </tr>
            `;
        });

        const formattedTotal = Number(total).toLocaleString('vi-VN');

        container.innerHTML = `
            <!-- Khách hàng -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-success text-white rounded-top-4 py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-user-circle me-2"></i>Khách hàng</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="mb-2"><span class="text-muted">Họ tên:</span> <strong>${escapeHtml(info.name)}</strong></p>
                        <p class="mb-2"><span class="text-muted">SĐT:</span> <strong>${escapeHtml(info.phone)}</strong></p>
                        <p class="mb-2"><span class="text-muted">Địa chỉ:</span> <strong>${escapeHtml(info.address)}</strong></p>
                        <p class="mb-0 mt-3 pt-3 border-top"><span class="text-muted">Ngày đặt:</span> <strong>${date}</strong></p>
                    </div>
                </div>

                <!-- Cập nhật trạng thái -->
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-success text-white rounded-top-4 py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-tasks me-2"></i>Trạng thái đơn hàng</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <span class="text-muted">Trạng thái hiện tại:</span>
                            <div class="mt-1"><span class="badge ${badgeClass} fs-6 px-3 py-2">${statusText}</span> ${paymentBadge}</div>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted">Phương thức thanh toán:</span>
                            <div class="fw-bold mt-1 text-success">${escapeHtml(info.payment_method)}</div>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <label for="statusSelect" class="form-label text-muted fw-bold">Cập nhật trạng thái mới:</label>
                            <select id="statusSelect" class="form-select mb-3">
                                <option value="pending" ${info.status === 'pending' ? 'selected' : ''}>Chờ duyệt (Pending)</option>
                                <option value="confirmed" ${info.status === 'confirmed' ? 'selected' : ''}>Đã duyệt (Confirmed)</option>
                                <option value="shipping" ${info.status === 'shipping' ? 'selected' : ''}>Đang giao (Shipping)</option>
                                <option value="completed" ${info.status === 'completed' ? 'selected' : ''}>Đã giao (Completed)</option>
                                <option value="cancelled" ${info.status === 'cancelled' ? 'selected' : ''}>Đã hủy (Cancelled)</option>
                            </select>
                            <button onclick="updateStatusAPI()" class="btn btn-success w-100 rounded-pill fw-bold">
                                <i class="fas fa-sync-alt me-1"></i> CẬP NHẬT TRẠNG THÁI
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sản phẩm -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-box-open me-2 text-success"></i>Sản phẩm đã mua</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-3">Sản phẩm</th>
                                        <th class="py-3 text-center">Đơn giá</th>
                                        <th class="py-3 text-center">Số lượng</th>
                                        <th class="py-3 text-end px-4">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${tableRows}
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end py-3 fw-bold fs-5">Tổng cộng:</td>
                                        <td class="text-end px-4 py-3 fw-bold text-success fs-5">${formattedTotal} đ</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    window.updateStatusAPI = function() {
        const select = document.getElementById('statusSelect');
        const newStatus = select.value;

        fetch(`/api/order/${orderId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Lỗi cập nhật trạng thái'); });
            }
            return response.json();
        })
        .then(data => {
            alert(data.message || 'Đã cập nhật trạng thái đơn hàng thành công!');
            fetchOrderDetails();
        })
        .catch(error => {
            alert(error.message);
            console.error('Lỗi Admin status API:', error);
        });
    };

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    fetchOrderDetails();
});
</script>

<?php include 'app/shares/footer.php'; ?>
