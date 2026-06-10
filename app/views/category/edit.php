<?php include 'app/shares/header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0" style="border-radius: 20px;">
                <div class="card-body p-5">
                    <h3 class="fw-bold text-warning text-center mb-4"><i class="fas fa-edit me-2"></i>Cập Nhật Danh Mục</h3>
                    <form id="editCategoryForm">
                        <input type="hidden" id="catId" value="<?php echo $category->id; ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên danh mục</label>
                            <input type="text" id="catName" name="name" class="form-control" required style="border-radius: 12px; padding: 12px;">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Mô tả đặc điểm</label>
                            <textarea id="catDesc" name="description" class="form-control" rows="3" required style="border-radius: 12px; padding: 12px;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 btn-lg text-white shadow-sm" style="border-radius: 12px;">
                            <i class="fas fa-save me-2"></i>Lưu thay đổi
                        </button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="/category/list" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left"></i> Hủy bỏ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editCategoryForm');
    const catId = document.getElementById('catId').value;
    const token = localStorage.getItem('jwtToken');

    if (!token) {
        alert('Vui lòng đăng nhập bằng tài khoản Admin.');
        window.location.href = '/User/login';
        return;
    }

    // Load category details
    fetch('/api/category/' + catId, {
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.message || 'Lỗi tải thông tin danh mục'); });
        }
        return response.json();
    })
    .then(category => {
        document.getElementById('catName').value = category.name || category.Name || '';
        document.getElementById('catDesc').value = category.description || category.Description || '';
    })
    .catch(error => {
        alert(error.message);
        console.error('Lỗi tải danh mục:', error);
    });

    // Handle submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('catName').value.trim();
        const description = document.getElementById('catDesc').value.trim();

        fetch('/api/category/' + catId, {
            method: 'PUT',
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
                return response.json().then(err => { throw new Error(err.message || 'Lỗi cập nhật danh mục'); });
            }
            return response.json();
        })
        .then(data => {
            alert(data.message || 'Cập nhật danh mục thành công!');
            window.location.href = '/category/list';
        })
        .catch(error => {
            alert(error.message);
            console.error('Lỗi cập nhật danh mục:', error);
        });
    });
});
</script>

<?php include 'app/shares/footer.php'; ?>