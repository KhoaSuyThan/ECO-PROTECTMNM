import { api } from '../api/api.js';
import { showAlert } from '../components/Alert.js';

export function renderRegisterPage() {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;

    mainContent.innerHTML = `
        <div class="auth-container">
            <div class="auth-header">
                <h2>Đăng Ký Tài Khoản</h2>
                <p>Cùng chung tay bảo vệ hành tinh xanh</p>
            </div>
            
            <form id="register-form">
                <div class="form-group">
                    <label for="reg-username">Tên đăng nhập <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="reg-username" class="form-control" required placeholder="Nhập tên đăng nhập...">
                </div>
                
                <div class="form-group">
                    <label for="reg-email">Địa chỉ Email <span style="color: var(--danger);">*</span></label>
                    <input type="email" id="reg-email" class="form-control" required placeholder="Nhập địa chỉ email...">
                </div>
                
                <div class="form-group">
                    <label for="reg-password">Mật khẩu <span style="color: var(--danger);">*</span></label>
                    <input type="password" id="reg-password" class="form-control" required placeholder="Mật khẩu tối thiểu 6 ký tự...">
                </div>
                
                <div class="form-group">
                    <label for="reg-confirm-password">Xác nhận mật khẩu <span style="color: var(--danger);">*</span></label>
                    <input type="password" id="reg-confirm-password" class="form-control" required placeholder="Nhập lại mật khẩu...">
                </div>

                <button type="submit" class="btn btn-primary" id="register-submit-btn" style="width: 100%; margin-top: 1rem; padding: 0.85rem;">
                    Đăng Ký
                </button>
            </form>
            
            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem;">
                Bạn đã có tài khoản? <a href="#login" style="color: var(--primary-light); font-weight: 600;">Đăng nhập ngay</a>
            </div>
        </div>
    `;

    const registerForm = document.getElementById('register-form');
    const submitBtn = document.getElementById('register-submit-btn');

    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = document.getElementById('reg-username').value.trim();
        const email = document.getElementById('reg-email').value.trim();
        const password = document.getElementById('reg-password').value;
        const confirmPassword = document.getElementById('reg-confirm-password').value;

        if (!username || !email || !password || !confirmPassword) {
            showAlert('Vui lòng nhập đầy đủ các trường bắt buộc!', 'warning');
            return;
        }

        if (password.length < 6) {
            showAlert('Mật khẩu phải dài tối thiểu 6 ký tự!', 'warning');
            return;
        }

        if (password !== confirmPassword) {
            showAlert('Mật khẩu xác nhận không trùng khớp!', 'warning');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang đăng ký...';

        try {
            await api.auth.register(username, email, password, confirmPassword);
            showAlert('Đăng ký tài khoản thành công! Bạn có thể đăng nhập ngay.', 'success');
            window.location.hash = '#login';
        } catch (error) {
            showAlert(error.message || 'Lỗi đăng ký tài khoản. Tên đăng nhập hoặc email có thể đã được sử dụng.', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Đăng Ký';
        }
    });
}
