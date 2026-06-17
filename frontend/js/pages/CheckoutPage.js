import { api } from '../api/api.js';
import { store } from '../state/store.js';
import { formatPrice } from '../utils/helpers.js';
import { showAlert } from '../components/Alert.js';

export async function renderCheckoutPage() {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;

    if (!store.state.currentUser) {
        window.location.hash = '#login';
        return;
    }

    const { cartItems, cartTotalPrice, currentUser } = store.state;

    if (cartItems.length === 0) {
        window.location.hash = '#cart';
        return;
    }

    mainContent.innerHTML = `
        <h1 style="margin-bottom: 2rem; font-size: 2.2rem; color: var(--primary);">Thanh Toán Đơn Hàng</h1>
        
        <div class="cart-layout">
            <!-- Delivery Info Form -->
            <div class="cart-items-panel">
                <h2 style="margin-bottom: 1.5rem; color: var(--gray-800);"><i class="fa-solid fa-truck-ramp-box"></i> Thông tin giao hàng</h2>
                
                <form id="checkout-form">
                    <div class="form-group">
                        <label for="checkout-name">Họ và tên người nhận <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="checkout-name" class="form-control" required placeholder="Nhập đầy đủ họ và tên..." value="${currentUser.fullname || ''}">
                    </div>
                    
                    <div class="form-group">
                        <label for="checkout-phone">Số điện thoại liên hệ <span style="color: var(--danger);">*</span></label>
                        <input type="tel" id="checkout-phone" class="form-control" required placeholder="Nhập số điện thoại giao hàng..." value="${currentUser.phone || ''}">
                    </div>
                    
                    <div class="form-group">
                        <label for="checkout-address">Địa chỉ nhận hàng <span style="color: var(--danger);">*</span></label>
                        <textarea id="checkout-address" class="form-control" rows="3" required placeholder="Ghi rõ số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố...">${currentUser.address || ''}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Phương thức thanh toán</label>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                                <input type="radio" name="payment_method" value="COD" checked style="width: 18px; height: 18px;">
                                <span>Thanh toán khi nhận hàng (COD)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                                <input type="radio" name="payment_method" value="ONLINE" style="width: 18px; height: 18px;">
                                <span>Thanh toán trực tuyến (Simulated Banking)</span>
                            </label>
                        </div>
                    </div>

                    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                        <a href="#cart" class="btn btn-secondary" style="flex: 1;">
                            Quay lại giỏ hàng
                        </a>
                        <button type="submit" class="btn btn-primary" id="place-order-submit" style="flex: 2;">
                            <i class="fa-solid fa-circle-check"></i> Xác nhận đặt hàng
                        </button>
                    </div>
                </form>
            </div>

            <!-- Summary Box -->
            <div class="cart-summary-panel">
                <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--gray-200); padding-bottom: 0.5rem;">Đơn hàng của bạn</h2>
                
                <div style="max-height: 200px; overflow-y: auto; margin-bottom: 1.5rem;">
                    ${cartItems.map(item => `
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; margin-bottom: 0.75rem; border-bottom: 1px solid var(--gray-100); padding-bottom: 0.5rem;">
                            <div style="max-width: 70%;">
                                <span style="font-weight: 600;">${item.name}</span>
                                <div style="color: var(--gray-600);">x${item.quantity}</div>
                            </div>
                            <span style="font-weight: 700; color: var(--primary);">${formatPrice(item.price * item.quantity)}</span>
                        </div>
                    `).join('')}
                </div>

                <div class="summary-row" style="margin-top: 1rem;">
                    <span>Tạm tính</span>
                    <span style="font-weight: 600;">${formatPrice(cartTotalPrice)}</span>
                </div>
                <div class="summary-row">
                    <span>Vận chuyển</span>
                    <span style="color: var(--success); font-weight: 600;">Miễn phí</span>
                </div>
                
                <div class="summary-row summary-total">
                    <span>Tổng thanh toán</span>
                    <span>${formatPrice(cartTotalPrice)}</span>
                </div>
            </div>
        </div>
    `;

    // Đăng ký sự kiện nộp đơn
    const checkoutForm = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('place-order-submit');

    checkoutForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const name = document.getElementById('checkout-name').value.trim();
        const phone = document.getElementById('checkout-phone').value.trim();
        const address = document.getElementById('checkout-address').value.trim();
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

        if (!name || !phone || !address) {
            showAlert('Vui lòng điền đầy đủ các thông tin giao hàng bắt buộc!', 'warning');
            return;
        }

        // Kiểm tra định dạng số điện thoại sơ bộ
        const phoneRegex = /^[0-9]{9,11}$/;
        if (!phoneRegex.test(phone.replace(/[\s.-]/g, ''))) {
            showAlert('Số điện thoại không đúng định dạng!', 'warning');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý đơn hàng...';

        try {
            // 1. Tạo đơn hàng trên backend
            const orderResponse = await api.orders.createOrder(name, phone, address, paymentMethod);
            const orderId = orderResponse.order_id || orderResponse.id;

            // 2. Nếu thanh toán ONLINE -> Gọi API mô phỏng thanh toán
            if (paymentMethod === 'ONLINE') {
                showAlert('Đang chuyển sang cổng thanh toán trực tuyến...', 'info');
                await new Promise(resolve => setTimeout(resolve, 1500)); // Delay mô phỏng chuyển trang
                
                // Gọi API payment
                await api.payment.pay(orderId, cartTotalPrice);
                showAlert('Thanh toán trực tuyến thành công!', 'success');
            }

            // 3. Xóa sạch giỏ hàng trong Store
            store.setCart([], 0);
            
            // Show thành công
            mainContent.innerHTML = `
                <div style="text-align: center; padding: 5rem 2rem; max-width: 600px; margin: 0 auto; background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                    <i class="fa-solid fa-circle-check" style="font-size: 5rem; color: var(--success); margin-bottom: 2rem;"></i>
                    <h1 style="color: var(--primary); margin-bottom: 1rem;">Đặt hàng thành công!</h1>
                    <p style="color: var(--gray-800); font-weight: 500; margin-bottom: 0.5rem;">Cảm ơn bạn đã đồng hành cùng ECO-PROTECT để bảo vệ môi trường.</p>
                    <p style="color: var(--gray-600); margin-bottom: 2.5rem;">Mã đơn hàng của bạn là <strong>#${orderId}</strong>. Bạn có thể theo dõi tiến độ đơn hàng trong lịch sử mua hàng.</p>
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <a href="#home" class="btn btn-primary">Tiếp tục mua sắm</a>
                        <a href="#orders" class="btn btn-secondary">Xem lịch sử đơn hàng</a>
                    </div>
                </div>
            `;
            
            showAlert('Đơn hàng đã được ghi nhận!', 'success');
        } catch (error) {
            showAlert(error.message || 'Lỗi xảy ra khi đặt hàng. Vui lòng thử lại.', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Xác nhận đặt hàng';
        }
    });
}
