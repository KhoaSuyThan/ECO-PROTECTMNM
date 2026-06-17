import { formatPrice, escapeHTML } from '../utils/helpers.js';
import { api } from '../api/api.js';
import { store } from '../state/store.js';
import { showAlert } from './Alert.js';

export function createProductCard(product) {
    const { id, name, price, category_name, image } = product;
    
    // Xử lý đường dẫn ảnh vật lý
    const imgUrl = image ? `../${image}` : 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=400&q=80';

    const card = document.createElement('div');
    card.className = 'product-card';
    card.innerHTML = `
        <a href="#product-detail?id=${id}" class="product-card-img-wrapper">
            <img class="product-card-img" src="${imgUrl}" alt="${escapeHTML(name)}" loading="lazy" onError="this.src='https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=400&q=80'">
        </a>
        <div class="product-card-body">
            <span class="product-card-category">${escapeHTML(category_name || 'Sản phẩm')}</span>
            <a href="#product-detail?id=${id}">
                <h3 class="product-card-title">${escapeHTML(name)}</h3>
            </a>
            <div class="product-card-footer">
                <span class="product-card-price">${formatPrice(price)}</span>
                <button class="add-cart-icon-btn" data-product-id="${id}" title="Thêm nhanh vào giỏ hàng">
                    <i class="fa-solid fa-cart-plus"></i>
                </button>
            </div>
        </div>
    `;

    // Nút mua nhanh
    const addBtn = card.querySelector('.add-cart-icon-btn');
    addBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (!store.state.currentUser) {
            showAlert('Vui lòng đăng nhập để mua hàng!', 'warning');
            window.location.hash = '#login';
            return;
        }

        try {
            // Thêm vào giỏ hàng
            await api.cart.addToCart(id, 1);
            
            // Tải lại giỏ hàng để cập nhật Navbar badge
            const cartData = await api.cart.getCart();
            store.setCart(cartData.cart, cartData.total_price);
            
            showAlert(`Đã thêm "${name}" vào giỏ hàng!`, 'success');
        } catch (error) {
            showAlert(error.message || 'Không thể thêm sản phẩm vào giỏ hàng.', 'danger');
        }
    });

    return card;
}
