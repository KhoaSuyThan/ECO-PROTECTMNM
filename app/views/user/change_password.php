<?php include 'app/shares/header.php'; ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4 text-success fw-bold">Đổi Mật Khẩu</h3>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger rounded-3"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="/User/processChangePassword" method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-bold">Mật khẩu hiện tại</label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg rounded-3" id="current_password" name="current_password" required>
                                <button class="btn btn-light border text-muted rounded-end-3" type="button" id="toggleCurrentPassword">
                                    <i class="fas fa-eye" id="toggleCurrentIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-bold">Mật khẩu mới</label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg rounded-3" id="new_password" name="new_password" required>
                                <button class="btn btn-light border text-muted rounded-end-3" type="button" id="toggleNewPassword">
                                    <i class="fas fa-eye" id="toggleNewIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-bold">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control form-control-lg rounded-3" id="confirm_password" name="confirm_password" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg rounded-3 fw-bold">Cập Nhật Mật Khẩu</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="/User/profile" class="text-decoration-none text-secondary"><i class="fas fa-arrow-left me-1"></i> Quay lại hồ sơ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setupToggle(buttonId, inputId, iconId) {
    document.getElementById(buttonId).addEventListener('click', function() {
        const password = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
}

setupToggle('toggleCurrentPassword', 'current_password', 'toggleCurrentIcon');
setupToggle('toggleNewPassword', 'new_password', 'toggleNewIcon');
</script>

<?php include 'app/shares/footer.php'; ?>
