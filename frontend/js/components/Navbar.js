import { store } from '../state/store.js';
import { api } from '../api/api.js';

export function renderNavbar() {
    const container = document.getElementById('navbar-container');
    if (!container) return;

    const { currentUser, cartItems } = store.state;
    const cartCount = cartItems.reduce((acc, item) => acc + (parseInt(item.quantity) || 0), 0);

    // Kiểm tra quyền Admin
    const isAdmin = currentUser && currentUser.role === 'admin';

    container.innerHTML = `
        <nav class="navbar glass-panel">
            <a href="#home" class="nav-brand">
                <i class="fa-solid fa-leaf"></i> ECO-PROTECT
            </a>

            <div class="nav-menu">
                <a href="#home" class="nav-link ${window.location.hash === '#home' || !window.location.hash ? 'active' : ''}">Cửa hàng</a>
                ${isAdmin ? '<a href="#admin-dashboard" class="nav-link ' + (window.location.hash.startsWith('#admin-dashboard') ? 'active' : '') + '">Trang Quản Trị</a>' : ''}
            </div>

            <div class="nav-actions">
                <!-- Shopping Cart Icon -->
                <a href="#cart" class="nav-cart-btn">
                    <i class="fa-solid fa-bag-shopping"></i>
                    ${cartCount > 0 ? `<span class="cart-count-badge">${cartCount}</span>` : ''}
                </a>

                <!-- User Dropdown Menu -->
                <div class="user-menu-container">
                    ${currentUser ? `
                        <div class="user-menu-trigger">
                            <img class="user-avatar-mini" src="${currentUser.avatar ? '../' + currentUser.avatar : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80'}" alt="Avatar" onError="this.src='https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80'">
                            <span class="user-display-name">${currentUser.fullname || currentUser.username}</span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; opacity: 0.7;"></i>
                        </div>
                        <div class="user-dropdown glass-panel">
                            <a href="#profile" class="dropdown-item">
                                <i class="fa-regular fa-user"></i> Hồ sơ của tôi
                            </a>
                            <a href="#orders" class="dropdown-item">
                                <i class="fa-solid fa-list-check"></i> Đơn hàng của tôi
                            </a>
                            <div class="dropdown-item" id="logout-btn" style="border-top: 1px solid var(--gray-200); color: var(--danger);">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất
                            </div>
                        </div>
                    ` : `
                        <a href="#login" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-user-plus"></i> Đăng nhập
                        </a>
                    `}
                </div>
            </div>
        </nav>
    `;

    // Lắng nghe sự kiện click Đăng xuất
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            await api.auth.logout();
            store.setCurrentUser(null);
            store.setCart([], 0);
            window.location.hash = '#home';
            window.location.reload(); // Reload để làm mới hoàn toàn
        });
    }
}

// Đăng ký tự động lắng nghe sự thay đổi của Store để render lại Navbar
store.subscribe(() => {
    renderNavbar();
});
