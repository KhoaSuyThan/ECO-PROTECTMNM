<?php include 'app/shares/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold text-success"><i class="fas fa-shopping-cart me-2"></i>Quản lý Đơn hàng</h2>
        </div>
        <div class="col-md-4 text-end">
            <div class="card bg-success text-white shadow-sm rounded-4 border-0">
                <div class="card-body py-2 px-3">
                    <h6 class="mb-1 text-white-50">Tổng doanh thu hệ thống</h6>
                    <h4 class="mb-0 fw-bold" id="total-revenue-badge">0 đ</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">Mã ĐH</th>
                            <th class="py-3">Khách hàng</th>
                            <th class="py-3">Số điện thoại</th>
                            <th class="py-3">Ngày đặt</th>
                            <th class="py-3">Trạng thái</th>
                            <th class="py-3 text-end">Tổng tiền</th>
                            <th class="text-end px-4 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="orders-table-body">
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Đang tải đơn hàng...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Phân trang -->
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center" id="orders-pagination"></ul>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('orders-table-body');
    const paginationContainer = document.getElementById('orders-pagination');
    const revenueBadge = document.getElementById('total-revenue-badge');
    const token = localStorage.getItem('jwtToken');

    if (!token) {
        alert('Vui lòng đăng nhập với tài khoản Admin.');
        window.location.href = '/User/login';
        return;
    }

    let allOrders = [];
    let state = {
        page: 1,
        limit: 10
    };

    function fetchOrders() {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Đang tải đơn hàng...</span>
                    </div>
                </td>
            </tr>
        `;

        fetch('/api/order', {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Không thể tải danh sách đơn hàng hoặc bạn không phải Admin');
            }
            return res.json();
        })
        .then(orders => {
            allOrders = orders || [];
            updateRevenue();
            renderOrders();
        })
        .catch(err => {
            console.error("Lỗi:", err);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-danger">${err.message || 'Không thể kết nối API đơn hàng.'}</td>
                </tr>
            `;
        });
    }

    function updateRevenue() {
        let totalRevenue = 0;
        allOrders.forEach(order => {
            totalRevenue += parseFloat(order.total_price || 0);
        });
        revenueBadge.textContent = Number(totalRevenue).toLocaleString('vi-VN') + ' đ';
    }

    function renderOrders() {
        const totalItems = allOrders.length;
        if (totalItems === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">Chưa có đơn hàng nào.</td></tr>`;
            paginationContainer.innerHTML = '';
            return;
        }

        const totalPages = Math.ceil(totalItems / state.limit);
        if (state.page > totalPages) state.page = totalPages;
        if (state.page < 1) state.page = 1;

        const startIndex = (state.page - 1) * state.limit;
        const endIndex = startIndex + state.limit;
        const pageOrders = allOrders.slice(startIndex, endIndex);

        let html = '';
        pageOrders.forEach(order => {
            const dateStr = order.created_at ? formatDate(order.created_at) : 'N/A';
            const priceFormatted = Number(order.total_price || 0).toLocaleString('vi-VN') + ' đ';
            
            let statusText = '';
            let badgeClass = '';
            switch(order.status) {
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
                default:
                    statusText = order.status || 'Chờ duyệt';
                    badgeClass = 'bg-secondary';
            }

            html += `
                <tr>
                    <td class="px-4 fw-bold text-muted">#${order.id}</td>
                    <td>${escapeHtml(order.name)}</td>
                    <td>${escapeHtml(order.phone)}</td>
                    <td>${dateStr}</td>
                    <td><span class="badge ${badgeClass}">${statusText}</span></td>
                    <td class="text-end fw-bold text-danger">${priceFormatted}</td>
                    <td class="text-end px-4">
                        <a href="/AdminOrder/view/${order.id}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                            <i class="fas fa-eye me-1"></i> Chi tiết
                        </a>
                    </td>
                </tr>
            `;
        });
        tableBody.innerHTML = html;

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        paginationContainer.innerHTML = '';
        if (totalPages <= 1) return;

        let html = '';
        html += `<li class="page-item ${state.page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(event, ${state.page - 1})">Trước</a>
        </li>`;

        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${state.page === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(event, ${i})">${i}</a>
            </li>`;
        }

        html += `<li class="page-item ${state.page === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(event, ${state.page + 1})">Sau</a>
        </li>`;

        paginationContainer.innerHTML = html;
    }

    window.changePage = function(event, pageNum) {
        event.preventDefault();
        if (pageNum < 1) return;
        state.page = pageNum;
        renderOrders();
    };

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }

    fetchOrders();
});
</script>

<?php include 'app/shares/footer.php'; ?>
