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
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card details-card" id="details-container">
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Đang tải chi tiết đơn hàng...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailsContainer = document.getElementById('details-container');
    const token = localStorage.getItem('jwtToken');
    
    // Lấy ID đơn hàng từ URL
    const urlParts = window.location.pathname.split('/');
    const orderId = urlParts[urlParts.length - 1];

    if (!token) {
        alert('Vui lòng đăng nhập.');
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
                throw new Error('Không thể tải thông tin đơn hàng hoặc bạn không có quyền xem');
            }
            return response.json();
        })
        .then(data => {
            renderDetails(data);
        })
        .catch(err => {
            console.error("Lỗi:", err);
            detailsContainer.innerHTML = `
                <div class="details-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Lỗi đơn hàng</h4>
                    <a href="/Order/history" class="btn btn-light btn-sm rounded-pill text-success fw-bold">Quay lại</a>
                </div>
                <div class="card-body p-4 text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-secondary">${err.message || 'Đã xảy ra lỗi khi tải đơn hàng.'}</h5>
                </div>
            `;
        });
    }

    function renderDetails(data) {
        const info = data.info;
        const details = data.details || [];
        const date = new Date(info.created_at).toLocaleString('vi-VN');

        let statusBadge = '';
        switch(info.status) {
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
        }

        let paymentBadge = info.payment_status === 'paid' 
            ? ' <span class="badge bg-success">Đã thanh toán</span>' 
            : ' <span class="badge bg-secondary">Chưa thanh toán</span>';

        // Nút hủy đơn (chỉ cho phép khi đang pending)
        let cancelBtnHtml = '';
        if (info.status === 'pending') {
            cancelBtnHtml = `
                <button onclick="cancelOrder(${info.id})" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fas fa-times-circle me-1"></i> Hủy đơn hàng
                </button>
            `;
        }

        let tableRows = '';
        let total = 0;

        details.forEach(item => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            const formattedPrice = Number(item.price).toLocaleString('vi-VN');
            const formattedSubtotal = Number(subtotal).toLocaleString('vi-VN');
            const imgHtml = item.product_image 
                ? `<img src="/${item.product_image}" class="product-img me-3" alt="Image">`
                : `<div class="bg-light me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 10px;">
                       <i class="fas fa-image text-muted"></i>
                   </div>`;

            tableRows += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            ${imgHtml}
                            <strong>${item.product_name || 'Sản phẩm không xác định'}</strong>
                        </div>
                    </td>
                    <td class="text-center">${formattedPrice} ₫</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-end fw-bold text-success">${formattedSubtotal} ₫</td>
                </tr>
            `;
        });

        const formattedTotal = Number(total).toLocaleString('vi-VN');

        detailsContainer.innerHTML = `
            <div class="details-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fas fa-file-invoice me-2"></i>Chi tiết đơn hàng #${info.id}</h4>
                    <small class="opacity-75">Ngày đặt: ${date}</small>
                </div>
                <div class="d-flex gap-2">
                    ${cancelBtnHtml}
                    <a href="/Order/history" class="btn btn-light btn-sm rounded-pill text-success fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <div class="row mb-4 g-3">
                    <div class="col-md-6">
                        <h6 class="text-muted fw-bold mb-3">Thông tin người nhận</h6>
                        <p class="mb-1"><strong>Họ tên:</strong> ${escapeHtml(info.name)}</p>
                        <p class="mb-1"><strong>Số điện thoại:</strong> ${escapeHtml(info.phone)}</p>
                        <p class="mb-0"><strong>Địa chỉ:</strong> ${escapeHtml(info.address)}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted fw-bold mb-3">Thông tin thanh toán</h6>
                        <p class="mb-1"><strong>Phương thức:</strong> ${escapeHtml(info.payment_method)}</p>
                        <p class="mb-1"><strong>Trạng thái giao hàng:</strong> ${statusBadge}</p>
                        <p class="mb-0"><strong>Trạng thái thanh toán:</strong> ${paymentBadge}</p>
                    </div>
                </div>

                <h6 class="text-muted fw-bold mb-3">Sản phẩm đã mua</h6>
                <div class="table-responsive">
                    <table class="table align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-center">Đơn giá</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows}
                            <tr class="table-light">
                                <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                                <td class="text-end fw-bold text-danger fs-5">${formattedTotal} ₫</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    window.cancelOrder = function(orderId) {
        if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')) return;

        fetch(`/api/order/${orderId}/cancel`, {
            method: 'PUT',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Lỗi hủy đơn hàng'); });
            }
            return response.json();
        })
        .then(data => {
            alert(data.message || 'Đơn hàng đã được hủy thành công!');
            fetchOrderDetails();
        })
        .catch(error => {
            alert(error.message);
            console.error('Lỗi hủy đơn hàng API:', error);
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
