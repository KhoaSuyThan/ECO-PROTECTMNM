<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật sản phẩm xanh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --eco-green: #2d6a4f; --eco-light: #d8f3dc; --eco-hover: #1b4332; }
        body { background: linear-gradient(135deg, #f1f8f5 0%, #e9f5ee 100%); min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 25px; border: none; overflow: hidden; }
        .card-header-eco { background-color: var(--eco-green); color: white; text-align: center; padding: 2rem 1rem; }
        .form-label { color: var(--eco-green); font-weight: 600; }
        .btn-update { background-color: var(--eco-green); color: white; border-radius: 12px; transition: 0.3s; border: none; padding: 12px; }
        .btn-update:hover { background-color: var(--eco-hover); color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(45, 106, 79, 0.3); }
        .img-preview-container { position: relative; display: inline-block; }
        .img-preview { max-width: 180px; border-radius: 15px; border: 3px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .form-control:focus, .form-select:focus { border-color: var(--eco-green); box-shadow: 0 0 0 0.25rem rgba(45, 106, 79, 0.15); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg">
                <div class="card-header-eco shadow-sm">
                    <i class="fas fa-leaf fa-3x mb-3"></i>
                    <h2 class="fw-bold mb-0">Cập Nhật Eco</h2>
                    <p class="small mb-0 opacity-75">Chỉnh sửa thông tin sản phẩm mã #<?php echo isset($product) ? $product->id : (isset($editId) ? $editId : 0); ?></p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <form id="edit-product-form">
                        <!-- ID sản phẩm -->
                        <input type="hidden" id="id" name="id" value="<?php echo isset($product) ? $product->id : (isset($editId) ? $editId : 0); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-tag me-2"></i>Tên sản phẩm xanh</label>
                            <input type="text" id="name" name="name" class="form-control form-control-lg" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-list me-2"></i>Danh mục sản phẩm</label>
                            <select id="category_id" name="category_id" class="form-select form-select-lg" required>
                                <option value="" selected disabled>Đang tải danh mục...</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-image me-2"></i>Hình ảnh sản phẩm mới</label>
                            <input type="file" id="image" name="image" class="form-control form-control-lg" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-align-left me-2"></i>Mô tả chi tiết</label>
                            <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><i class="fas fa-money-bill-wave me-2"></i>Giá (VNĐ)</label>
                            <input type="number" id="price" name="price" class="form-control" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-update btn-lg shadow-sm fw-bold">
                                <i class="fas fa-check-circle me-2"></i> CẬP NHẬT NGAY
                            </button>
                            <a href="/Product/list" class="btn btn-link text-decoration-none text-muted mt-2">
                                <i class="fas fa-chevron-left me-1"></i> Hủy bỏ và quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    const token = localStorage.getItem('jwtToken');
    if (!token) {
        alert('Vui lòng đăng nhập với tài khoản Admin.');
        window.location.href = '/User/login';
        return;
    }

    const productId = $('#id').val();

    // Load category and product info
    $.ajax({
        url: '/api/category',
        type: 'GET',
        dataType: 'json',
        success: function(categories) {
            const categorySelect = $('#category_id');
            categorySelect.empty();
            categories.forEach(category => {
                categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
            });

            // Sau khi load xong danh mục thì load thông tin sản phẩm
            $.ajax({
                url: `/api/product/${productId}`,
                type: 'GET',
                dataType: 'json',
                success: function(product) {
                    $('#name').val(product.name);
                    $('#description').val(product.description);
                    $('#price').val(product.price);
                    $('#category_id').val(product.category_id);
                }
            });
        }
    });

    // Handle update
    $('#edit-product-form').on('submit', function(e) {
        e.preventDefault();
        
        // Dùng FormData để hỗ trợ upload file trong PHP
        const formData = new FormData(this);

        $.ajax({
            url: `/api/product/${productId}`,
            type: 'POST', // Dùng POST để PHP parse $_FILES dễ dàng
            headers: {
                'Authorization': 'Bearer ' + token
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert(response.message || 'Cập nhật sản phẩm thành công!');
                window.location.href = '/Product/list';
            },
            error: function(xhr) {
                console.error("Error:", xhr.responseText);
                try {
                    const res = JSON.parse(xhr.responseText);
                    alert(res.message || 'Lỗi cập nhật sản phẩm!');
                } catch(e) {
                    alert('Lỗi cập nhật sản phẩm!');
                }
            }
        });
    });
});
</script>

</body>
</html>