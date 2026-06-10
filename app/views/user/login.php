<?php include 'app/shares/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4" style="color: #2d6a4f; font-weight: 700;">Đăng nhập</h3>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form id="loginForm" action="/User/processLogin" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold">Tên đăng nhập hoặc Email</label>
                            <input type="text" name="username" class="form-control form-control-lg bg-light border-0" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold">Mật khẩu</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control form-control-lg bg-light border-0" required>
                                <button class="btn btn-light bg-light border-0 text-muted" type="button" id="togglePassword">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label text-muted" for="remember">Ghi nhớ tôi</label>
                            </div>
                            <a href="/User/forgotPassword" style="color: #2d6a4f; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Quên mật khẩu?</a>
                        </div>
                        
                        <button type="submit" class="btn w-100 btn-lg text-white" style="background-color: #2d6a4f; border-radius: 12px; font-weight: 600;">Đăng nhập</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <span class="text-muted">Chưa có tài khoản? </span>
                        <a href="/User/register" style="color: #2d6a4f; font-weight: 600; text-decoration: none;">Đăng ký ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
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

// Xóa JWT token cũ (nếu có) khi người dùng vừa vào trang login (VD: sau khi logout)
localStorage.removeItem('jwtToken');
localStorage.removeItem('refreshToken');
localStorage.removeItem('user');

// Xử lý lưu JWT vào localStorage khi đăng nhập
document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Tạm dừng submit form để gọi API lấy token

    const formData = new FormData(this);
    const jsonData = {};
    formData.forEach((value, key) => {
        jsonData[key] = value;
    });

    fetch('/api/auth/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(jsonData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.message || 'Đăng nhập thất bại'); });
        }
        return response.json();
    })
    .then(data => {
        if (data.access_token) {
            localStorage.setItem('jwtToken', data.access_token);
            localStorage.setItem('refreshToken', data.refresh_token);
            localStorage.setItem('user', JSON.stringify(data.user));
        }
        // Tiếp tục submit form gốc để tạo Session PHP để đồng bộ view MVC thường
        this.submit();
    })
    .catch(error => {
        alert(error.message);
        console.error("Lỗi đăng nhập API:", error);
    });
});
</script>

<?php include 'app/shares/footer.php'; ?>
