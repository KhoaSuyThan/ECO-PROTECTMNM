<?php include 'app/shares/header.php'; ?>

<style>
    :root {
        --eco-green: #2d6a4f;
        --eco-light: #d8f3dc;
        --soft-shadow: 0 20px 40px rgba(0,0,0,0.05);
    }

    body { background-color: #f8faf9; }

    .add-product-card {
        border: none;
        border-radius: 30px;
        box-shadow: var(--soft-shadow);
        background: #ffffff;
        overflow: hidden;
    }

    /* Tạo hiệu ứng dải màu trang trí phía trên card */
    .card-decoration {
        height: 8px;
        background: linear-gradient(90deg, var(--eco-light), var(--eco-green));
    }

    .form-label {
        font-weight: 700;
        color: #4a4a4a;
        font-size: 0.9rem;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }

    .form-label i {
        margin-right: 8px;
        color: var(--eco-green);
        width: 20px;
        text-align: center;
    }

    .form-control, .form-select {
        border-radius: 15px;
        padding: 12px 20px;
        border: 1.5px solid #eee;
        transition: all 0.3s ease;
        background-color: #fcfcfc;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--eco-green);
        box-shadow: 0 0 0 4px rgba(45, 106, 79, 0.1);
        background-color: #fff;
    }

    /* Khu vực xem trước ảnh */
    .image-upload-wrapper {
        position: relative;
        border: 2px dashed #ddd;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        background: #fdfdfd;
    }

    .image-upload-wrapper:hover {
        border-color: var(--eco-green);
        background: #f9fffb;
    }

    #imagePreview {
        max-width: 100%;
        max-height: 200px;
        border-radius: 15px;
        display: none;
        margin-top: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .btn-submit {
        background: var(--eco-green);
        color: white;
        border: none;
        border-radius: 15px;
        padding: 15px;
        font-weight: 700;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }

    .btn-submit:hover {
        background: #1b4332;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(45, 106, 79, 0.2);
        color: white;
    }

    .input-group-text {
        border-radius: 15px 0 0 15px;
        background: var(--eco-light);
        border: none;
        font-weight: bold;
        color: var(--eco-green);
    }

    .price-input {
        border-radius: 0 15px 15px 0 !important;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="add-product-card">
                <div class="card-decoration"></div>
                <div class="card-body p-4 p-md-5">
                    
                    <div class="text-center mb-5">
                        <div class="bg-success d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" 
                             style="width: 70px; height: 70px; border-radius: 22px; background: linear-gradient(135deg, #52b788, #2d6a4f) !important;">
                            <i class="fas fa-plus text-white fa-2x"></i>
                        </div>
                        <h2 class="fw-bold text-dark">Thêm Sản Phẩm Mới</h2>
                        <p class="text-muted small">Điền thông tin chi tiết để đưa sản phẩm xanh đến với mọi người</p>
                    </div>

                    <form id="add-product-form">
                        <div class="row">
                            <!-- Tên sản phẩm -->
                            <div class="col-12 mb-4">
                                <label class="form-label"><i class="fas fa-tag"></i>Tên sản phẩm</label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="Ví dụ: Bình nước bã mía" required>
                            </div>

                            <!-- Danh mục -->
                            <div class="col-md-6 mb-4">
                                <label class="form-label"><i class="fas fa-layer-group"></i>Danh mục</label>
                                <select id="category_id" name="category_id" class="form-select" required>
                                    <option value="" selected disabled>Đang tải danh mục...</option>
                                    <!-- Options sẽ được load qua API -->
                                </select>
                            </div>

                            <!-- Giá bán -->
                            <div class="col-md-6 mb-4">
                                <label class="form-label"><i class="fas fa-coins"></i>Giá bán lẻ</label>
                                <div class="input-group">
                                    <span class="input-group-text">₫</span>
                                    <input type="number" id="price" name="price" class="form-control price-input" placeholder="0" required>
                                </div>
                            </div>

                            <!-- Hình ảnh sản phẩm -->
                            <div class="col-12 mb-4">
                                <label class="form-label"><i class="fas fa-image"></i>Hình ảnh sản phẩm</label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                            </div>

                            <!-- Mô tả -->
                            <div class="col-12 mb-4">
                                <label class="form-label"><i class="fas fa-align-left"></i>Mô tả & Thành phần</label>
                                <textarea id="description" name="description" class="form-control" rows="4" 
                                          placeholder="Chia sẻ về nguồn gốc, chất liệu sinh học và ưu điểm của sản phẩm..." required></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-submit w-100 shadow-sm">
                            <i class="fas fa-check-circle me-2"></i>XÁC NHẬN ĐƯA VÀO HỆ THỐNG
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="/Product/list" class="text-decoration-none text-muted small fw-bold">
                            <i class="fas fa-chevron-left me-1"></i> Quay lại danh sách sản phẩm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const token = localStorage.getItem('jwtToken');
    if (!token) {
        alert('Vui lòng đăng nhập bằng tài khoản Admin.');
        window.location.href = '/User/login';
        return;
    }

    // Load categories via API
    $.ajax({
        url: '/api/category',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            const categorySelect = $('#category_id');
            categorySelect.empty();
            categorySelect.append('<option value="" selected disabled>Chọn nhóm sản phẩm...</option>');
            data.forEach(category => {
                categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
            });
        },
        error: function() {
            alert('Lỗi tải danh mục!');
        }
    });

    // Handle form submit
    $('#add-product-form').on('submit', function(e) {
        e.preventDefault();
        
        // Sử dụng FormData để hỗ trợ upload file
        const formData = new FormData(this);

        $.ajax({
            url: '/api/product',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            data: formData,
            processData: false, // không xử lý data
            contentType: false, // bắt buộc khi gửi FormData
            success: function(response) {
                alert(response.message || 'Thêm sản phẩm thành công!');
                window.location.href = '/Product/list';
            },
            error: function(xhr) {
                console.error("Error:", xhr.responseText);
                try {
                    const res = JSON.parse(xhr.responseText);
                    alert(res.message || 'Lỗi thêm sản phẩm!');
                } catch(e) {
                    alert('Lỗi thêm sản phẩm!');
                }
            }
        });
    });
});
</script>

<?php include 'app/shares/footer.php'; ?>