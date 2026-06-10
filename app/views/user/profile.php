<?php include 'app/shares/header.php'; ?>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 rounded-4 text-center">
                <div class="card-body p-4">
                    <?php if (!empty($user['avatar'])): ?>
                        <img id="avatarPreview" src="/<?php echo htmlspecialchars($user['avatar']); ?>" class="rounded-circle mb-3 border border-3 border-success shadow-sm" alt="Avatar" style="width: 150px; height: 150px; object-fit: cover;">
                        <div id="avatarPlaceholder" class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3 border border-3 border-success shadow-sm d-none" style="width: 150px; height: 150px;">
                            <i class="fas fa-user fa-4x text-success opacity-50"></i>
                        </div>
                    <?php else: ?>
                        <img id="avatarPreview" src="#" class="rounded-circle mb-3 border border-3 border-success shadow-sm d-none" alt="Avatar" style="width: 150px; height: 150px; object-fit: cover;">
                        <div id="avatarPlaceholder" class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3 border border-3 border-success shadow-sm" style="width: 150px; height: 150px;">
                            <i class="fas fa-user fa-4x text-success opacity-50"></i>
                        </div>
                    <?php endif; ?>
                    <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user['fullname'] ?? $user['username']); ?></h4>
                    <p class="text-muted mb-3"><i class="fas fa-shield-alt me-1 text-success"></i> <?php echo ucfirst($user['role']); ?></p>
                    
                    <a href="/User/changePassword" class="btn btn-outline-success rounded-pill px-4 btn-sm w-100 mb-2"><i class="fas fa-key me-1"></i> Đổi Mật Khẩu</a>
                    
                    <?php if (!empty($user['avatar'])): ?>
                        <a href="/User/deleteAvatar" class="btn btn-outline-danger rounded-pill px-4 btn-sm w-100" onclick="return confirm('Bạn có chắc chắn muốn xóa ảnh đại diện không?');">
                            <i class="fas fa-trash-alt me-1"></i> Xóa ảnh đại diện
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-5">
                    <h4 class="fw-bold text-success mb-4 border-bottom pb-2">Thông Tin Cập Nhật</h4>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger rounded-3"><i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <form action="/User/processUpdateProfile" method="POST" enctype="multipart/form-data">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tên đăng nhập</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="fullname" class="form-label fw-bold">Họ và tên</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-bold">Số điện thoại</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-bold">Địa chỉ</label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="avatar" class="form-label fw-bold">Thay đổi ảnh đại diện</label>
                            <input class="form-control" type="file" id="avatar" name="avatar" accept="image/*">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">Lưu Thay Đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('avatar').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            const placeholder = document.getElementById('avatarPlaceholder');
            
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            placeholder.classList.add('d-none');
        }
        reader.readAsDataURL(file);
    }
});

document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault();
    const token = localStorage.getItem('jwtToken');
    if (!token) {
        alert('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        window.location.href = '/User/login';
        return;
    }

    const formData = new FormData(this);

    fetch('/api/auth/profile', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401) {
                localStorage.removeItem('jwtToken');
                throw new Error('Unauthorized: Phiên đăng nhập hết hạn');
            }
            return response.json().then(err => { throw new Error(err.message || 'Lỗi cập nhật hồ sơ'); });
        }
        return response.json();
    })
    .then(data => {
        alert(data.message || 'Cập nhật thành công!');
        // Cập nhật lại thông tin user trong localStorage
        if (data.user) {
            localStorage.setItem('user', JSON.stringify(data.user));
        }
        // Gửi form gốc để cập nhật PHP Session đồng bộ
        this.submit();
    })
    .catch(error => {
        alert(error.message);
        if (error.message.includes('Phiên đăng nhập hết hạn')) {
            window.location.href = '/User/login';
        }
    });
});
</script>

<?php include 'app/shares/footer.php'; ?>
