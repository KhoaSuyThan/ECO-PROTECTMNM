<?php include 'app/shares/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow border-0 rounded-4 overflow-hidden bg-white">
                <div style="height: 6px; background: linear-gradient(90deg, #d8f3dc, #2d6a4f);"></div>
                <div class="card-body p-4 p-md-5">
                    <h3 class="fw-bold text-success text-center mb-2"><i class="fas fa-wallet me-2"></i>Thông Tin Đặt Hàng</h3>
                    <p class="text-muted text-center small mb-5">Vui lòng điền thông tin chính xác để Eco-Store giao hàng tận nơi</p>

                    <form method="POST" action="/Product/processCheckout">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark"><i class="fas fa-user me-2 text-success"></i>Họ và tên người nhận</label>
                            <input type="text" name="name" class="form-control rounded-3 py-2" placeholder="Nguyễn Văn A" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark"><i class="fas fa-phone me-2 text-success"></i>Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control rounded-3 py-2" placeholder="0901234567" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark"><i class="fas fa-map-marker-alt me-2 text-success"></i>Địa chỉ nhận hàng</label>
                            <textarea name="address" class="form-control rounded-3" rows="3" placeholder="Số nhà, tên đường, phường/xã, quận/huyện..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100 btn-lg rounded-3 py-3 fw-bold shadow-sm mb-3">
                            <i class="fas fa-check-circle me-2"></i>XÁC NHẬN ĐẶT HÀNG
                        </button>
                    </form>

                    <div class="text-center">
                        <a href="/Product/cart" class="text-decoration-none text-muted small fw-bold">
                            <i class="fas fa-chevron-left me-1"></i> Quay lại sửa giỏ hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/shares/footer.php'; ?>