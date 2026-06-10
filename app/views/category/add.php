<?php include 'app/shares/header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0" style="border-radius: 20px;">
                <div class="card-body p-5">
                    <h3 class="fw-bold text-success text-center mb-4"><i class="fas fa-folder-plus me-2"></i>Thêm Danh Mục Xanh</h3>
                    <form id="addCategoryForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên danh mục</label>
                            <input type="text" id="catName" name="name" class="form-control" placeholder="Ví dụ: Đồ gia dụng sinh thái" required style="border-radius: 12px; padding: 12px;">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Mô tả đặc điểm</label>
                            <textarea id="catDesc" name="description" class="form-control" rows="3" placeholder="Mô tả ngắn gọn về nhóm sản phẩm này..." required style="border-radius: 12px; padding: 12px;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100 btn-lg shadow-sm" style="border-radius: 12px;">
                            <i class="fas fa-check-circle me-2"></i>Lưu danh mục
                        </button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="/category/list" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left"></i> Trở lại</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addCategoryForm');
    const token = localStorage.getItem('jwtToken');

    if (!token) {
        alert('Vui lòng đăng nhập bằng tài khoản Admin.');
        window.location.href = '/User/login';
        return;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('catName').value.trim();
        const description = document.getElementById('catDesc').value.trim();

        fetch('/api/category', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                name: name,
                description: description
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Lỗi thêm danh mục'); });
            }
            return response.json();
        })
        .then(data => {
            alert(data.message || 'Thêm danh mục thành công!');
            window.location.href = '/category/list';
        })
        .catch(error => {
            alert(error.message);
            console.error('Lỗi API danh mục:', error);
        });
    });
});
</script>

<?php include 'app/shares/footer.php'; ?>