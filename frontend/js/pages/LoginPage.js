import { api } from '../api/api.js';
import { store } from '../state/store.js';
import { showAlert } from '../components/Alert.js';

export function renderLoginPage() {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;

    if (store.state.currentUser) {
        window.location.hash = '#home';
        return;
    }

    mainContent.innerHTML = `
        <div class="auth-container">
            <div class="auth-header">
                <h2>Đăng Nhập</h2>
                <p>Hành trình sống xanh đang đợi bạn</p>
            </div>
            
            <form id="login-form">
                <div class="form-group">
                    <label for="login-username">Tên đăng nhập hoặc Email</label>
                    <input type="text" id="login-username" class="form-control" required placeholder="Nhập username hoặc email..." autofocus>
                </div>
                
                <div class="form-group" style="position: relative;">
                    <label for="login-password">Mật khẩu</label>
                    <input type="password" id="login-password" class="form-control" required placeholder="Nhập mật khẩu...">
                    <button type="button" id="toggle-password-btn" style="position: absolute; right: 12px; top: 38px; background: transparent; cursor: pointer; color: var(--gray-600);">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>

                <button type="submit" class="btn btn-primary" id="login-submit-btn" style="width: 100%; margin-top: 1rem; padding: 0.85rem;">
                    Đăng Nhập
                </button>
            </form>
            
            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem;">
                Bạn chưa có tài khoản? <a href="#register" style="color: var(--primary-light); font-weight: 600;">Đăng ký ngay</a>
            </div>
        </div>
    `;

    // Toggle ẩn hiện mật khẩu
    const pwdInput = document.getElementById('login-password');
    const toggleBtn = document.getElementById('toggle-password-btn');
    toggleBtn.addEventListener('click', () => {
        const isPwd = pwdInput.type === 'password';
        pwdInput.type = isPwd ? 'text' : 'password';
        toggleBtn.innerHTML = isPwd ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
    });

    // Xử lý nộp form
    const loginForm = document.getElementById('login-form');
    const submitBtn = document.getElementById('login-submit-btn');

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = document.getElementById('login-username').value.trim();
        const password = pwdInput.value;

        if (!username || !password) {
            showAlert('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!', 'warning');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang đăng nhập...';

        try {
            // Gọi API đăng nhập
            const user = await api.auth.login(username, password);
            store.setCurrentUser(user);

            showAlert(`Chào mừng "${user.fullname || user.username}" quay trở lại!`, 'success');

            // Đồng bộ giỏ hàng
            try {
                const cartData = await api.cart.getCart();
                store.setCart(cartData.cart, cartData.total_price);
            } catch (cartErr) {
                console.error('Lỗi sync giỏ hàng khi đăng nhập:', cartErr);
            }

            // Chuyển về trang chủ
            window.location.hash = '#home';
        } catch (error) {
            showAlert(error.message || 'Tên đăng nhập hoặc mật khẩu không chính xác.', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Đăng Nhập';
        }
    });
}
