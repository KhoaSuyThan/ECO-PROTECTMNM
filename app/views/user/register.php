<?php include 'app/shares/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4" style="color: #2d6a4f; font-weight: 700;">Đăng ký tài khoản</h3>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="/User/processRegister" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold">Tên đăng nhập</label>
                            <input type="text" name="username" class="form-control form-control-lg bg-light border-0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold">Mật khẩu</label>
                            <input type="password" name="password" class="form-control form-control-lg bg-light border-0" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold">Xác nhận mật khẩu</label>
                            <input type="password" name="confirm_password" class="form-control form-control-lg bg-light border-0" required>
                        </div>
                        <button type="submit" class="btn w-100 btn-lg text-white" style="background-color: #2d6a4f; border-radius: 12px; font-weight: 600;">Đăng ký</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <span class="text-muted">Đã có tài khoản? </span>
                        <a href="/User/login" style="color: #2d6a4f; font-weight: 600; text-decoration: none;">Đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/shares/footer.php'; ?>
