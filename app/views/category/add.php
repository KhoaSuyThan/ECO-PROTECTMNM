<?php include 'app/shares/header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0" style="border-radius: 20px;">
                <div class="card-body p-5">
                    <h3 class="fw-bold text-success text-center mb-4"><i class="fas fa-folder-plus me-2"></i>Thêm Danh Mục Xanh</h3>
                    <form method="POST" action="/Category/save">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên danh mục</label>
                            <input type="text" name="name" class="form-control" placeholder="Ví dụ: Đồ gia dụng sinh thái" required style="border-radius: 12px; padding: 12px;">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Mô tả đặc điểm</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Mô tả ngắn gọn về nhóm sản phẩm này..." required style="border-radius: 12px; padding: 12px;"></textarea>
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
<?php include 'app/shares/footer.php'; ?>