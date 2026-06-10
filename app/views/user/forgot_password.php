<?php include 'app/shares/header.php'; ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4 text-success fw-bold">Quên Mật Khẩu</h3>
                    <p class="text-muted text-center mb-4">Nhập địa chỉ email của bạn và chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu.</p>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger rounded-3"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success rounded-3"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form action="/User/processForgotPassword" method="POST">
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control form-control-lg rounded-3" id="email" name="email" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg rounded-3 fw-bold">Gửi Liên Kết</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="/User/login" class="text-decoration-none text-secondary"><i class="fas fa-arrow-left me-1"></i> Quay lại đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault();
    const email = document.getElementById('email').value;

    fetch('/api/auth/forgot-password', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.message || 'Lỗi gửi yêu cầu'); });
        }
        return response.json();
    })
    .then(data => {
        alert(data.message || 'Đã gửi yêu cầu đặt lại mật khẩu.');
        // Gửi form gốc để đồng bộ hiển thị của view PHP
        this.submit();
    })
    .catch(error => {
        alert(error.message);
        console.error('Lỗi forgot password:', error);
    });
});
</script>

<?php include 'app/shares/footer.php'; ?>
