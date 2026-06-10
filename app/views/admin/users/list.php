<?php include 'app/shares/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-success"><i class="fas fa-users me-2"></i>Quản lý Người dùng</h2>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="py-3">Tài khoản</th>
                            <th class="py-3">Họ và tên</th>
                            <th class="py-3">Email</th>
                            <th class="py-3">Vai trò</th>
                            <th class="py-3">Trạng thái</th>
                            <th class="text-end px-4 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Đang tải người dùng...</span>
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
        <ul class="pagination justify-content-center" id="users-pagination"></ul>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('users-table-body');
    const paginationContainer = document.getElementById('users-pagination');
    const token = localStorage.getItem('jwtToken');

    if (!token) {
        alert('Vui lòng đăng nhập với tài khoản Admin.');
        window.location.href = '/User/login';
        return;
    }

    let allUsers = [];
    let state = {
        page: 1,
        limit: 10
    };

    function fetchUsers() {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </td>
            </tr>
        `;

        fetch('/api/auth/users', {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Không thể tải danh sách người dùng hoặc bạn không phải Admin');
            }
            return res.json();
        })
        .then(users => {
            allUsers = users || [];
            renderUsers();
        })
        .catch(err => {
            console.error("Lỗi:", err);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-danger">${err.message || 'Không thể kết nối API người dùng.'}</td>
                </tr>
            `;
        });
    }

    function renderUsers() {
        const totalItems = allUsers.length;
        if (totalItems === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">Chưa có người dùng nào.</td></tr>`;
            paginationContainer.innerHTML = '';
            return;
        }

        const totalPages = Math.ceil(totalItems / state.limit);
        if (state.page > totalPages) state.page = totalPages;
        if (state.page < 1) state.page = 1;

        const startIndex = (state.page - 1) * state.limit;
        const endIndex = startIndex + state.limit;
        const pageUsers = allUsers.slice(startIndex, endIndex);

        let html = '';
        pageUsers.forEach(user => {
            const avatarHtml = user.avatar
                ? `<img src="/${escapeHtml(user.avatar)}" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">`
                : `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2 text-success" style="width: 32px; height: 32px;">
                       <i class="fas fa-user"></i>
                   </div>`;

            const emailVerifiedHtml = (user.email && user.email_verified_at)
                ? `<i class="fas fa-check-circle text-success ms-1" title="Đã xác thực"></i>`
                : '';

            const roleBadge = user.role === 'admin'
                ? `<span class="badge bg-danger rounded-pill">Admin</span>`
                : `<span class="badge bg-secondary rounded-pill">User</span>`;

            const statusBadge = user.status === 'active'
                ? `<span class="badge bg-success rounded-pill">Hoạt động</span>`
                : `<span class="badge bg-warning text-dark rounded-pill">Đã khóa</span>`;

            let actionBtn = '';
            if (user.role !== 'admin') {
                if (user.status === 'active') {
                    actionBtn = `
                        <button onclick="toggleUserStatus(${user.id}, 'lock')" class="btn btn-sm btn-outline-warning rounded-pill" title="Khóa tài khoản">
                            <i class="fas fa-lock"></i>
                        </button>
                    `;
                } else {
                    actionBtn = `
                        <button onclick="toggleUserStatus(${user.id}, 'unlock')" class="btn btn-sm btn-outline-success rounded-pill" title="Mở khóa">
                            <i class="fas fa-unlock"></i>
                        </button>
                    `;
                }
            } else {
                actionBtn = `<button class="btn btn-sm btn-light rounded-pill disabled"><i class="fas fa-lock"></i></button>`;
            }

            html += `
                <tr>
                    <td class="px-4 fw-bold text-muted">#${user.id}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            ${avatarHtml}
                            ${escapeHtml(user.username)}
                        </div>
                    </td>
                    <td>${escapeHtml(user.fullname || '-')}</td>
                    <td>
                        ${escapeHtml(user.email || '-')}
                        ${emailVerifiedHtml}
                    </td>
                    <td>${roleBadge}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end px-4">${actionBtn}</td>
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
        renderUsers();
    };

    window.toggleUserStatus = function(userId, action) {
        const confirmMsg = action === 'lock' ? 'Khóa tài khoản này?' : 'Mở khóa tài khoản này?';
        if (!confirm(confirmMsg)) return;

        fetch('/api/auth/users', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ id: userId })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Lỗi cập nhật trạng thái'); });
            }
            return response.json();
        })
        .then(data => {
            alert(data.message || 'Cập nhật trạng thái người dùng thành công!');
            fetchUsers();
        })
        .catch(error => {
            alert(error.message);
            console.error('Lỗi toggle status API:', error);
        });
    };

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    fetchUsers();
});
</script>

<?php include 'app/shares/footer.php'; ?>
