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

                    <form method="POST" action="/Product/save" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Tên sản phẩm -->
                            <div class="col-12 mb-4">
                                <label class="form-label"><i class="fas fa-tag"></i>Tên sản phẩm</label>
                                <input type="text" name="name" class="form-control" placeholder="Ví dụ: Bình nước bã mía" required>
                            </div>

                            <!-- Danh mục -->
                            <div class="col-md-6 mb-4">
                                <label class="form-label"><i class="fas fa-layer-group"></i>Danh mục</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="" selected disabled>Chọn nhóm sản phẩm...</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category->id; ?>">
                                            <?php echo htmlspecialchars($category->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Giá bán -->
                            <div class="col-md-6 mb-4">
                                <label class="form-label"><i class="fas fa-coins"></i>Giá bán lẻ</label>
                                <div class="input-group">
                                    <span class="input-group-text">₫</span>
                                    <input type="number" name="price" class="form-control price-input" placeholder="0" required>
                                </div>
                            </div>

                            <!-- Mô tả -->
                            <div class="col-12 mb-4">
                                <label class="form-label"><i class="fas fa-align-left"></i>Mô tả & Thành phần</label>
                                <textarea name="description" class="form-control" rows="4" 
                                          placeholder="Chia sẻ về nguồn gốc, chất liệu sinh học và ưu điểm của sản phẩm..." required></textarea>
                            </div>

                            <!-- Upload Ảnh -->
                            <div class="col-12 mb-5">
                                <label class="form-label"><i class="fas fa-camera"></i>Hình ảnh sản phẩm</label>
                                <div class="image-upload-wrapper" onclick="document.getElementById('fileInput').click();">
                                    <div id="uploadPlaceholder">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                        <p class="mb-0 text-muted small">Nhấn để chọn ảnh hoặc kéo thả file vào đây</p>
                                    </div>
                                    <img id="imagePreview" src="" alt="Preview">
                                    <input type="file" id="fileInput" name="image" class="d-none" accept="image/*" onchange="previewImage(event)">
                                </div>
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
    function previewImage(event) {
        const reader = new FileReader();
        const fileInput = event.target;
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('uploadPlaceholder');

        reader.onload = function() {
            if (reader.readyState === 2) {
                preview.src = reader.result;
                preview.style.display = 'inline-block';
                placeholder.style.display = 'none';
            }
        }
        
        if (fileInput.files[0]) {
            reader.readAsDataURL(fileInput.files[0]);
        }
    }
</script>

<?php include 'app/shares/footer.php'; ?>