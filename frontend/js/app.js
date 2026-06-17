import { store } from './state/store.js';
import { api } from './api/api.js';
import { getBaseHash } from './utils/helpers.js';
import { renderNavbar } from './components/Navbar.js';
import { renderFooter } from './components/Footer.js';

// Khai báo import các trang
import { renderHomePage } from './pages/HomePage.js';
import { renderProductDetailPage } from './pages/ProductDetailPage.js';
import { renderCartPage } from './pages/CartPage.js';
import { renderCheckoutPage } from './pages/CheckoutPage.js';
import { renderLoginPage } from './pages/LoginPage.js';
import { renderRegisterPage } from './pages/RegisterPage.js';
import { renderProfilePage } from './pages/ProfilePage.js';
import { renderOrdersPage } from './pages/OrdersPage.js';
import { renderAdminDashboardPage } from './pages/AdminDashboardPage.js';

// Định tuyến URL Hash sang hàm render tương ứng
const routes = {
    '#home': renderHomePage,
    '#product-detail': renderProductDetailPage,
    '#cart': renderCartPage,
    '#checkout': renderCheckoutPage,
    '#login': renderLoginPage,
    '#register': renderRegisterPage,
    '#profile': renderProfilePage,
    '#orders': renderOrdersPage,
    '#admin-dashboard': renderAdminDashboardPage
};

// Hàm điều phối chính (Router)
async function handleRouting() {
    const baseHash = getBaseHash();
    const renderFn = routes[baseHash] || renderHomePage;
    
    // Đánh dấu active link trong navbar
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href === baseHash) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });

    try {
        await renderFn();
    } catch (e) {
        console.error('Lỗi khi điều phối trang:', e);
        document.getElementById('main-content').innerHTML = `
            <div style="text-align:center; padding: 3rem;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:3rem; color:var(--danger); margin-bottom:1rem;"></i>
                <h2>Có lỗi xảy ra khi tải trang</h2>
                <p style="color:var(--gray-600);">${e.message}</p>
                <a href="#home" class="btn btn-primary" style="margin-top:1.5rem;">Về trang chủ</a>
            </div>
        `;
    }
}

// Khởi chạy ứng dụng (App Bootstrapper)
async function initApp() {
    // 1. Render khung giao diện tĩnh Navbar & Footer
    renderNavbar();
    renderFooter();

    // 2. Tự động kiểm tra đăng nhập bằng Token cũ trong cache
    try {
        const user = await api.auth.getMe();
        if (user) {
            store.setCurrentUser(user);
            
            // Tải giỏ hàng từ cơ sở dữ liệu nếu đã đăng nhập
            const cartData = await api.cart.getCart();
            store.setCart(cartData.cart, cartData.total_price);
        }
    } catch (e) {
        console.warn('Tự động đăng nhập thất bại:', e);
    }

    // 3. Đăng ký sự kiện thay đổi hash của trình duyệt để chuyển trang
    window.addEventListener('hashchange', handleRouting);
    
    // 4. Lần đầu nạp trang (hoặc F5) -> Định tuyến trang hiện tại
    await handleRouting();
}

// Chạy khởi tạo khi cây DOM sẵn sàng
document.addEventListener('DOMContentLoaded', initApp);
