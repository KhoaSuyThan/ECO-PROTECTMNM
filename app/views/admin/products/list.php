<?php include 'app/shares/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-success mb-0"><i class="fas fa-boxes me-2"></i>Quản Lý Sản Phẩm</h2>
        <a href="/Product/add" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">
            <i class="fas fa-plus-circle me-1"></i> Thêm Sản Phẩm Mới
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" width="80">Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá bán</th>
                            <th>Ngày đăng</th>
                            <th class="text-center" width="150">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Đang tải sản phẩm...</span>
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
        <ul class="pagination justify-content-center" id="pagination-container"></ul>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('product-table-body');
    const paginationContainer = document.getElementById('pagination-container');
    const token = localStorage.getItem('jwtToken');

    if (!token) {
        alert('Vui lòng đăng nhập với tài khoản Admin.');
        window.location.href = '/User/login';
        return;
    }

    let categoryMap = {};
    let state = {
        page: 1,
        limit: 6
    };

    // Load categories first
    fetch('/api/category')
    .then(res => res.json())
    .then(categories => {
        categories.forEach(c => {
            categoryMap[c.id] = c.name;
        });
        fetchProducts();
    })
    .catch(err => {
        console.error("Lỗi tải danh mục:", err);
        fetchProducts();
    });

    function fetchProducts() {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </td>
            </tr>
        `;

        fetch(`/api/product?page=${state.page}&limit=${state.limit}`)
        .then(res => res.json())
        .then(data => {
            renderProducts(data.products || []);
            renderPagination(data.total_pages || 1);
        })
        .catch(err => {
            console.error("Lỗi fetch sản phẩm:", err);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5 text-danger">Không thể tải dữ liệu sản phẩm từ API.</td>
                </tr>
            `;
        });
    }

    function renderProducts(products) {
        if (products.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                        <h5>Chưa có sản phẩm nào.</h5>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        products.forEach(p => {
            const imgHtml = p.image 
                ? `<img src="/${p.image}" alt="${escapeHtml(p.name)}" class="rounded-3 shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">`
                : `<div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px;">
                       <i class="fas fa-image"></i>
                   </div>`;

            const catName = categoryMap[p.category_id] || p.category_name || 'Không xác định';
            const priceFormatted = Number(p.price).toLocaleString('vi-VN') + ' đ';
            const dateStr = p.created_at ? formatDate(p.created_at) : 'N/A';

            html += `
                <tr>
                    <td class="ps-4 py-3">${imgHtml}</td>
                    <td class="fw-bold text-dark">${escapeHtml(p.name)}</td>
                    <td>
                        <span class="badge bg-light text-success border border-success">${escapeHtml(catName)}</span>
                    </td>
                    <td class="text-danger fw-bold">${priceFormatted}</td>
                    <td class="text-muted small">${dateStr}</td>
                    <td class="text-center">
                        <a href="/Product/edit/${p.id}" class="btn btn-sm btn-outline-primary rounded-circle shadow-sm mx-1" title="Sửa">
                            <i class="fas fa-pen"></i>
                        </a>
                        <button onclick="deleteProduct(${p.id})" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm mx-1" title="Xóa">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tableBody.innerHTML = html;
    }

    function renderPagination(totalPages) {
        paginationContainer.innerHTML = '';
        if (totalPages <= 1) return;

        let html = '';
        // Previous page button
        html += `<li class="page-item ${state.page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(event, ${state.page - 1})">Trước</a>
        </li>`;

        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${state.page === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(event, ${i})">${i}</a>
            </li>`;
        }

        // Next page button
        html += `<li class="page-item ${state.page === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(event, ${state.page + 1})">Sau</a>
        </li>`;

        paginationContainer.innerHTML = html;
    }

    window.changePage = function(event, pageNum) {
        event.preventDefault();
        if (pageNum < 1) return;
        state.page = pageNum;
        fetchProducts();
    };

    window.deleteProduct = function(productId) {
        if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')) return;

        fetch(`/api/product/${productId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Lỗi khi xóa sản phẩm'); });
            }
            return response.json();
        })
        .then(data => {
            alert(data.message || 'Xóa sản phẩm thành công!');
            fetchProducts();
        })
        .catch(error => {
            alert(error.message);
            console.error('Lỗi xóa sản phẩm:', error);
        });
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
});
</script>

<?php include 'app/shares/footer.php'; ?>
