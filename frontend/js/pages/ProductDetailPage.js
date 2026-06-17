import { api } from '../api/api.js';
import { store } from '../state/store.js';
import { getQueryParams, formatPrice, escapeHTML } from '../utils/helpers.js';
import { showAlert } from '../components/Alert.js';

export async function renderProductDetailPage() {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;

    // Lấy ID từ URL
    const query = getQueryParams();
    const productId = query.id;

    if (!productId) {
        mainContent.innerHTML = `<p style="text-align: center; color: var(--danger); padding: 2rem;">Thiếu ID sản phẩm!</p>`;
        return;
    }

    mainContent.innerHTML = `
        <div class="spinner-container">
            <div class="spinner"></div>
        </div>
    `;

    try {
        const product = await api.products.getProduct(productId);
        const imgUrl = product.image ? `../${product.image}` : 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=400&q=80';

        mainContent.innerHTML = `
            <a href="#home" class="btn btn-secondary btn-sm" style="margin-bottom: 2rem;">
                <i class="fa-solid fa-arrow-left"></i> Quay lại cửa hàng
            </a>

            <div class="detail-grid">
                <!-- Image Wrapper -->
                <div class="detail-img-container">
                    <img class="detail-img" src="${imgUrl}" alt="${escapeHTML(product.name)}" onError="this.src='https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=400&q=80'">
                </div>

                <!-- Info Column -->
                <div class="detail-info">
                    <span class="detail-category">${escapeHTML(product.category_name || 'Sản phẩm')}</span>
                    <h1 class="detail-title">${escapeHTML(product.name)}</h1>
                    <div class="detail-price">${formatPrice(product.price)}</div>
                    
                    <p class="detail-desc">${escapeHTML(product.description || 'Sản phẩm bảo vệ môi trường chất lượng cao, bền vững và an toàn.')}</p>
                    
                    <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
                        <span style="font-weight: 600;">Số lượng:</span>
                        <div class="quantity-control">
                            <button class="qty-btn" id="qty-minus"><i class="fa-solid fa-minus"></i></button>
                            <input type="number" id="qty-input-val" class="qty-input" value="1" min="1">
                            <button class="qty-btn" id="qty-plus"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="buy-actions" style="margin-top: 1.5rem;">
                        <button class="btn btn-primary" id="add-to-cart-btn" style="flex: 1; padding: 1rem;">
                            <i class="fa-solid fa-cart-arrow-down"></i> Thêm vào giỏ hàng
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Tăng giảm số lượng
        const qtyInput = document.getElementById('qty-input-val');
        const btnMinus = document.getElementById('qty-minus');
        const btnPlus = document.getElementById('qty-plus');

        btnMinus.addEventListener('click', () => {
            let current = parseInt(qtyInput.value) || 1;
            if (current > 1) {
                qtyInput.value = current - 1;
            }
        });

        btnPlus.addEventListener('click', () => {
            let current = parseInt(qtyInput.value) || 1;
            qtyInput.value = current + 1;
        });

        qtyInput.addEventListener('change', () => {
            let current = parseInt(qtyInput.value) || 1;
            if (current < 1) qtyInput.value = 1;
        });

        // Thêm vào giỏ hàng
        const addBtn = document.getElementById('add-to-cart-btn');
        addBtn.addEventListener('click', async () => {
            if (!store.state.currentUser) {
                showAlert('Vui lòng đăng nhập để mua hàng!', 'warning');
                window.location.hash = '#login';
                return;
            }

            const qty = parseInt(qtyInput.value) || 1;
            addBtn.disabled = true;
            addBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang thêm...';

            try {
                await api.cart.addToCart(product.id, qty);
                const cartData = await api.cart.getCart();
                store.setCart(cartData.cart, cartData.total_price);
                
                showAlert(`Đã thêm ${qty} sản phẩm "${product.name}" vào giỏ hàng!`, 'success');
            } catch (error) {
                showAlert(error.message || 'Lỗi khi thêm giỏ hàng', 'danger');
            } finally {
                addBtn.disabled = false;
                addBtn.innerHTML = '<i class="fa-solid fa-cart-arrow-down"></i> Thêm vào giỏ hàng';
            }
        });

    } catch (error) {
        mainContent.innerHTML = `<p style="text-align: center; color: var(--danger); padding: 2rem;">Lỗi khi tải chi tiết sản phẩm: ${error.message}</p>`;
    }
}
