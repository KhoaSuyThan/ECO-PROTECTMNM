import { api } from '../api/api.js';
import { store } from '../state/store.js';
import { formatPrice, escapeHTML } from '../utils/helpers.js';
import { showAlert } from '../components/Alert.js';

export async function renderCartPage() {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;

    if (!store.state.currentUser) {
        mainContent.innerHTML = `
            <div style="text-align: center; padding: 4rem 2rem;">
                <i class="fa-solid fa-lock" style="font-size: 3rem; color: var(--primary-light); margin-bottom: 1rem;"></i>
                <h2>Vui lòng đăng nhập</h2>
                <p style="color: var(--gray-600); margin-bottom: 2rem;">Bạn cần đăng nhập để xem và quản lý giỏ hàng của mình.</p>
                <a href="#login" class="btn btn-primary">Đăng nhập ngay</a>
            </div>
        `;
        return;
    }

    mainContent.innerHTML = `
        <div class="spinner-container">
            <div class="spinner"></div>
        </div>
    `;

    try {
        const cartData = await api.cart.getCart();
        store.setCart(cartData.cart, cartData.total_price);
        
        renderCartUI();
    } catch (error) {
        mainContent.innerHTML = `<p style="text-align: center; color: var(--danger); padding: 2rem;">Lỗi tải giỏ hàng: ${error.message}</p>`;
    }

    function renderCartUI() {
        const { cartItems, cartTotalPrice } = store.state;

        if (cartItems.length === 0) {
            mainContent.innerHTML = `
                <div style="text-align: center; padding: 4rem 2rem;">
                    <i class="fa-solid fa-cart-flatbed-suitcases" style="font-size: 4rem; color: var(--primary-light); margin-bottom: 1.5rem;"></i>
                    <h2>Giỏ hàng của bạn đang trống</h2>
                    <p style="color: var(--gray-600); margin-bottom: 2rem;">Hãy lấp đầy giỏ hàng bằng những sản phẩm xanh bảo vệ môi trường nhé.</p>
                    <a href="#home" class="btn btn-primary">Khám phá sản phẩm</a>
                </div>
            `;
            return;
        }

        mainContent.innerHTML = `
            <h1 style="margin-bottom: 2rem; font-size: 2.2rem; color: var(--primary);">Giỏ Hàng Của Bạn</h1>
            
            <div class="cart-layout">
                <!-- Items Panel -->
                <div class="cart-items-panel">
                    <div style="display: flex; justify-content: space-between; border-bottom: 2px solid var(--gray-200); padding-bottom: 1rem; margin-bottom: 1rem;">
                        <span style="font-weight: 700; color: var(--gray-800);">Sản phẩm trong giỏ</span>
                        <button id="clear-cart-btn" class="btn btn-secondary btn-sm" style="color: var(--danger); background: transparent; box-shadow: none; padding: 0;">
                            <i class="fa-solid fa-trash-can"></i> Xóa sạch giỏ hàng
                        </button>
                    </div>

                    <div id="cart-rows-container">
                        ${cartItems.map(item => {
                            const imgUrl = item.image ? `../${item.image}` : 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=100&q=80';
                            return `
                                <div class="cart-item-row" data-product-id="${item.product_id}">
                                    <img class="cart-item-img" src="${imgUrl}" alt="${escapeHTML(item.name)}" onError="this.src='https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=100&q=80'">
                                    
                                    <div>
                                        <h3 class="cart-item-name"><a href="#product-detail?id=${item.product_id}">${escapeHTML(item.name)}</a></h3>
                                        <span class="cart-item-price">${formatPrice(item.price)}</span>
                                    </div>
                                    
                                    <!-- Quantity controls -->
                                    <div class="quantity-control" style="margin-bottom: 0;">
                                        <button class="qty-btn dec-qty-btn" style="width: 32px; height: 32px;"><i class="fa-solid fa-minus" style="font-size: 0.8rem;"></i></button>
                                        <input type="number" class="qty-input item-qty-val" value="${item.quantity}" style="width: 40px; height: 32px;" readonly>
                                        <button class="qty-btn inc-qty-btn" style="width: 32px; height: 32px;"><i class="fa-solid fa-plus" style="font-size: 0.8rem;"></i></button>
                                    </div>
                                    
                                    <div style="font-weight: 700; font-family: var(--font-heading); color: var(--primary); text-align: right;">
                                        ${formatPrice(item.price * item.quantity)}
                                    </div>
                                    
                                    <button class="btn btn-secondary btn-sm remove-item-btn" style="background: transparent; box-shadow: none; color: var(--danger); padding: 0.5rem;">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>

                <!-- Summary Panel -->
                <div class="cart-summary-panel">
                    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--gray-200); padding-bottom: 0.5rem;">Tóm tắt đơn hàng</h2>
                    
                    <div class="summary-row">
                        <span>Tạm tính</span>
                        <span style="font-weight: 600;">${formatPrice(cartTotalPrice)}</span>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển</span>
                        <span style="color: var(--success); font-weight: 600;">Miễn phí</span>
                    </div>
                    
                    <div class="summary-row summary-total">
                        <span>Tổng thanh toán</span>
                        <span>${formatPrice(cartTotalPrice)}</span>
                    </div>
                    
                    <a href="#checkout" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem; padding: 1rem;">
                        <i class="fa-solid fa-credit-card"></i> Tiến hành thanh toán
                    </a>
                </div>
            </div>
        `;

        // Đăng ký sự kiện
        const rowsContainer = document.getElementById('cart-rows-container');

        rowsContainer.addEventListener('click', async (e) => {
            const row = e.target.closest('.cart-item-row');
            if (!row) return;
            const productId = row.dataset.productId;
            const item = cartItems.find(i => i.product_id == productId);
            if (!item) return;

            // Xử lý giảm số lượng
            if (e.target.closest('.dec-qty-btn')) {
                const newQty = parseInt(item.quantity) - 1;
                if (newQty <= 0) {
                    await handleRemove(productId, item.name);
                } else {
                    await handleUpdate(productId, newQty);
                }
            }

            // Xử lý tăng số lượng
            if (e.target.closest('.inc-qty-btn')) {
                const newQty = parseInt(item.quantity) + 1;
                await handleUpdate(productId, newQty);
            }

            // Xử lý xóa sản phẩm
            if (e.target.closest('.remove-item-btn')) {
                await handleRemove(productId, item.name);
            }
        });

        // Xóa sạch giỏ hàng
        const clearBtn = document.getElementById('clear-cart-btn');
        clearBtn.addEventListener('click', async () => {
            if (confirm('Bạn có chắc chắn muốn xóa toàn bộ sản phẩm trong giỏ hàng?')) {
                try {
                    await api.cart.clearCart();
                    store.setCart([], 0);
                    showAlert('Đã xóa sạch giỏ hàng!', 'info');
                    renderCartUI();
                } catch (err) {
                    showAlert(err.message, 'danger');
                }
            }
        });
    }

    async function handleUpdate(productId, newQty) {
        try {
            await api.cart.updateQuantity(productId, newQty);
            const cartData = await api.cart.getCart();
            store.setCart(cartData.cart, cartData.total_price);
            renderCartUI();
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    }

    async function handleRemove(productId, productName) {
        if (confirm(`Bạn muốn xóa "${productName}" khỏi giỏ hàng?`)) {
            try {
                await api.cart.removeFromCart(productId);
                const cartData = await api.cart.getCart();
                store.setCart(cartData.cart, cartData.total_price);
                showAlert(`Đã xóa "${productName}" khỏi giỏ hàng.`, 'info');
                renderCartUI();
            } catch (err) {
                showAlert(err.message, 'danger');
            }
        }
    }
}
