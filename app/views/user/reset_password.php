<?php include 'app/shares/header.php'; ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4 text-success fw-bold">Đặt Lại Mật Khẩu</h3>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger rounded-3"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
                        <div class="text-center mt-3">
                            <a href="/User/forgotPassword" class="btn btn-outline-success rounded-pill">Thử lại</a>
                        </div>
                    <?php else: ?>
                        <form action="/User/processResetPassword" method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">Mật khẩu mới</label>
                                <input type="password" class="form-control form-control-lg rounded-3" id="password" name="password" required>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-bold">Xác nhận mật khẩu</label>
                                <input type="password" class="form-control form-control-lg rounded-3" id="confirm_password" name="confirm_password" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg rounded-3 fw-bold">Lưu Mật Khẩu Mới</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/shares/footer.php'; ?>
