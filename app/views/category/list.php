<?php include 'app/shares/header.php'; ?>
<div class="container mt-4">
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-success m-0"><i class="fas fa-tags me-2"></i>Quản Lý Danh Mục</h3>
                <a href="/category/add" class="btn btn-success rounded-pill px-4">
                    <i class="fas fa-plus me-2"></i>Thêm danh mục mới
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">Mã</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả tóm tắt</th>
                            <th class="text-center" style="width: 150px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="category-table-body">
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Đang tải danh mục...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('category-table-body');
    const token = localStorage.getItem('jwtToken');

    if (!token) {
        alert('Vui lòng đăng nhập với tài khoản Admin.');
        window.location.href = '/User/login';
        return;
    }

    function fetchCategories() {
        tableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Đang tải danh mục...</span>
                    </div>
                </td>
            </tr>
        `;

        fetch('/api/category')
        .then(res => res.json())
        .then(data => {
            renderCategories(data);
        })
        .catch(err => {
            console.error("Lỗi tải danh mục:", err);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-4 text-danger">Không thể tải dữ liệu từ API danh mục.</td>
                </tr>
            `;
        });
    }

    function renderCategories(categories) {
        if (categories.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted">Chưa có danh mục nào.</td></tr>`;
            return;
        }

        let html = '';
        categories.forEach(cat => {
            html += `
                <tr>
                    <td class="text-muted">#${cat.id}</td>
                    <td class="fw-bold text-dark">${escapeHtml(cat.name)}</td>
                    <td class="text-secondary small">${escapeHtml(cat.description || '')}</td>
                    <td class="text-center">
                        <div class="btn-group">
                            <a href="/category/edit/${cat.id}" class="btn btn-sm btn-outline-warning border-0"><i class="fas fa-edit"></i></a>
                            <button onclick="deleteCategory(${cat.id})" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        });
        tableBody.innerHTML = html;
    }

    window.deleteCategory = function(catId) {
        if (!confirm('Bạn chắc chắn muốn xóa danh mục này?')) return;

        fetch('/api/category/' + catId, {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Lỗi xóa danh mục'); });
            }
            return response.json();
        })
        .then(data => {
            alert(data.message || 'Xóa danh mục thành công!');
            fetchCategories();
        })
        .catch(error => {
            alert(error.message);
            console.error('Lỗi khi xóa danh mục:', error);
        });
    };

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    fetchCategories();
});
</script>
<?php include 'app/shares/footer.php'; ?>