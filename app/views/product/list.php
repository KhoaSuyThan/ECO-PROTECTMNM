<?php include 'app/shares/header.php'; ?>

<style>
    :root { 
        --eco-green: #2d6a4f; 
        --eco-dark: #1b4332;
        --eco-light: #d8f3dc; 
        --eco-accent: #52b788;
        --soft-shadow: 0 12px 30px rgba(0,0,0,0.04);
        --hover-shadow: 0 20px 40px rgba(45, 106, 79, 0.12);
    }

    body { background-color: #f4f7f5; }

    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, var(--eco-green), var(--eco-dark));
        border-radius: 24px;
        padding: 60px 40px;
        color: white;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(27, 67, 50, 0.15);
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        pointer-events: none;
    }

    /* Search & Filter Bar */
    .search-filter-container {
        background: white;
        padding: 20px;
        border-radius: 20px;
        box-shadow: var(--soft-shadow);
        margin-bottom: 40px;
    }

    .search-input-group {
        position: relative;
    }
    
    .search-input-group i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
    }

    .search-control {
        padding-left: 45px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        height: 45px;
        transition: all 0.3s;
    }

    .search-control:focus {
        border-color: var(--eco-green);
        box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.15);
    }

    .filter-btn {
        border-radius: 30px;
        padding: 8px 20px;
        font-weight: 600;
        font-size: 0.9rem;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #4a5568;
        transition: all 0.3s;
        white-space: nowrap;
    }

    .filter-btn:hover {
        border-color: var(--eco-green);
        color: var(--eco-green);
        background: rgba(45, 106, 79, 0.02);
    }

    .filter-btn.active {
        background: var(--eco-green) !important;
        color: white !important;
        border-color: var(--eco-green) !important;
        box-shadow: 0 4px 12px rgba(45, 106, 79, 0.2);
    }

    /* Product Card */
    .product-card {
        border: none;
        border-radius: 20px;
        background: #fff;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: var(--soft-shadow);
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--hover-shadow) !important;
    }

    /* Image Styling with zoom effect */
    .card-img-container {
        position: relative;
        height: 220px;
        overflow: hidden;
        background-color: #f7fafc;
    }

    .card-img-top {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .product-card:hover .card-img-top {
        transform: scale(1.08);
    }
    
    .eco-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: rgba(255, 255, 255, 0.9);
        color: var(--eco-green);
        backdrop-filter: blur(8px);
        font-weight: 700;
        font-size: 0.75rem;
        padding: 6px 14px;
        border-radius: 50px;
        z-index: 2;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    /* Floating Admin Actions */
    .admin-actions-floating {
        position: absolute;
        top: 15px;
        right: 15px;
        display: flex;
        gap: 8px;
        z-index: 3;
        opacity: 1;
        transform: translateY(0);
        transition: all 0.3s ease;
    }

    .btn-admin-floating {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4a5568;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: all 0.2s;
        text-decoration: none;
        font-size: 0.85rem;
        border: none;
    }

    .btn-admin-floating.edit:hover {
        background: var(--eco-green);
        color: white;
    }

    .btn-admin-floating.delete:hover {
        background: #e53e3e;
        color: white;
    }

    /* Content Styling */
    .product-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 8px;
        line-height: 1.4;
        height: 3rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .price-label { font-size: 0.8rem; color: #718096; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px; }
    .price-value { font-size: 1.35rem; color: #1a202c; font-weight: 800; }

    /* Quantity and Cart styling */
    .qty-input-container {
        height: 38px;
        padding: 0 4px;
        background: #f7fafc;
        border-radius: 50px;
        border: 1px solid #edf2f7;
    }

    .qty-btn {
        width: 24px;
        height: 30px;
        border: none;
        background: transparent;
        color: #718096;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    .qty-btn:hover { color: var(--eco-green); }

    .qty-input-field {
        width: 35px;
        height: 30px;
        border: none;
        background: transparent;
        text-align: center;
        font-weight: 700;
        color: #2d3748;
        font-size: 0.95rem;
    }
    .qty-input-field:focus { outline: none; }

    .btn-cart {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background-color: var(--eco-green);
        color: white;
        border: none;
        transition: all 0.3s;
    }
    .btn-cart:hover { 
        background-color: var(--eco-dark); 
        transform: scale(1.05);
        box-shadow: 0 4px 10px rgba(45, 106, 79, 0.25);
    }

    /* Detail Action Button */
    .btn-view-detail-card {
        background-color: #f7fafc;
        color: var(--eco-green);
        border: 1px solid #edf2f7;
        border-radius: 12px;
        font-weight: 700;
        padding: 10px;
        font-size: 0.9rem;
        transition: all 0.3s;
        text-align: center;
        text-decoration: none;
        display: block;
        margin-top: 15px;
    }

    .btn-view-detail-card:hover {
        background-color: var(--eco-green);
        color: white;
        border-color: var(--eco-green);
    }

    /* Custom Number Input webkit hides */
    input[type=number].qty-input-field::-webkit-inner-spin-button,
    input[type=number].qty-input-field::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Smooth Fade In Animation */
    .fade-in {
        animation: fadeIn 0.4s ease forwards;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Pagination Custom Styles */
    .pagination .page-link {
        color: var(--eco-green);
        border-radius: 8px;
        margin: 0 4px;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        transition: all 0.3s;
    }
    .pagination .page-item.active .page-link {
        background-color: var(--eco-green);
        border-color: var(--eco-green);
        color: white;
        box-shadow: 0 4px 10px rgba(45, 106, 79, 0.2);
    }
    .pagination .page-link:hover:not(.active) {
        background-color: var(--eco-light);
        color: var(--eco-dark);
        border-color: var(--eco-accent);
    }
    .pagination .page-item.disabled .page-link {
        color: #a0aec0;
        background-color: #f7fafc;
        border-color: #e2e8f0;
    }
    /* --- Đồ họa trang trí Background 2 bên --- */
    .bg-decor {
        position: fixed;
        z-index: -1; 
        color: var(--eco-dark); /* Đổi sang màu xanh lá đậm nhất */
        opacity: 0.12; /* Tăng độ rõ nét lên (thay vì 0.05 như cũ) */
        pointer-events: none; 
    }
    
    .bg-decor-1 {
        top: 15%;
        left: 2%;
        font-size: 18vw;
        transform: rotate(-25deg);
    }
    
    .bg-decor-2 {
        bottom: 5%;
        left: 5%;
        font-size: 12vw;
        transform: rotate(15deg);
    }
    
    .bg-decor-3 {
        top: 10%;
        right: 1%;
        font-size: 20vw;
        transform: rotate(30deg);
    }
    
    .bg-decor-4 {
        bottom: 10%;
        right: 4%;
        font-size: 14vw;
        transform: rotate(-15deg);
    }

    /* Ẩn họa tiết trên điện thoại/tablet để không làm chật màn hình */
    @media (max-width: 1400px) {
        .bg-decor {
            display: none;
        }
    }
</style>

<div class="bg-decor bg-decor-1"><i class="fas fa-leaf"></i></div>
<div class="bg-decor bg-decor-2"><i class="fab fa-pagelines"></i></div>
<div class="bg-decor bg-decor-3"><i class="fas fa-seedling"></i></div>
<div class="bg-decor bg-decor-4"><i class="fas fa-leaf"></i></div>

<div class="container mt-4">
    <div class="hero-section shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-4 mb-md-0 pe-md-5">
                <h1 class="display-5 fw-bold mb-3">Sản Phẩm Xanh</h1>
                <p class="lead mb-0" style="opacity: 0.85; font-size: 1.15rem; line-height: 1.6;">
                    Cùng chúng tôi bảo vệ môi trường bằng những lựa chọn bền vững.
                </p>
            </div>
            
            <div class="col-md-6 text-center text-md-end">
                <img src="../../banner/image.png" 
                     alt="Eco Banner" 
                     class="img-fluid shadow-lg" 
                     style="border-radius: 20px; max-height: 320px; width: 100%; object-fit: cover; border: 4px solid rgba(255, 255, 255, 0.15);">
            </div>
        </div>
    </div>
    <div class="search-filter-container">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="search-input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-input" class="form-control search-control" placeholder="Tìm kiếm sản phẩm theo tên...">
                </div>
            </div>
            <div class="col-md-5">
                <div class="d-flex gap-2 overflow-x-auto pb-1" style="scrollbar-width: none;">
                    <button class="btn filter-btn active" data-category="all">Tất cả sản phẩm</button>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <button class="btn filter-btn" data-category="<?php echo $cat->id; ?>">
                                <?php echo htmlspecialchars($cat->name); ?>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <select id="sort-select" class="form-select border-0 bg-light py-2 rounded-3 fw-bold text-muted" style="height: 45px;">
                    <option value="">Sắp xếp mặc định</option>
                    <option value="price_asc">Giá tăng dần</option>
                    <option value="price_desc">Giá giảm dần</option>
                </select>
            </div>
        </div>
        <div class="row g-3 align-items-center mt-2">
            <div class="col-md-6 d-flex align-items-center gap-2">
                <span class="fw-bold text-muted text-nowrap">Khoảng giá:</span>
                <input type="number" id="min-price-input" class="form-control form-control-sm rounded-3 border-0 bg-light" placeholder="Từ (đ)" style="height: 38px;">
                <span>-</span>
                <input type="number" id="max-price-input" class="form-control form-control-sm rounded-3 border-0 bg-light" placeholder="Đến (đ)" style="height: 38px;">
                <button id="price-filter-btn" class="btn btn-sm btn-success rounded-pill px-3 py-2 fw-bold">Lọc giá</button>
            </div>
        </div>
    </div>

    <div class="row g-4" id="product-grid">
        <!-- JS will dynamically populate here -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
        </div>
    </div>

    <nav id="pagination-container" class="mt-5 mb-5 d-flex justify-content-center"></nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const sortSelect = document.getElementById('sort-select');
    const minPriceInput = document.getElementById('min-price-input');
    const maxPriceInput = document.getElementById('max-price-input');
    const priceFilterBtn = document.getElementById('price-filter-btn');
    const productGrid = document.getElementById('product-grid');
    
    let state = {
        name: '',
        category_id: 'all',
        min_price: '',
        max_price: '',
        sort: '',
        page: 1,
        limit: 8
    };

    function fetchProducts() {
        productGrid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
            </div>
        `;

        let url = `/api/product?page=${state.page}&limit=${state.limit}`;
        if (state.name) url += `&name=${encodeURIComponent(state.name)}`;
        if (state.category_id !== 'all') url += `&category_id=${state.category_id}`;
        if (state.min_price) url += `&min_price=${state.min_price}`;
        if (state.max_price) url += `&max_price=${state.max_price}`;
        if (state.sort) url += `&sort=${state.sort}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                renderProducts(data.products || []);
                renderPagination(data.total_pages || 1);
            })
            .catch(err => {
                console.error("Lỗi fetch sản phẩm:", err);
                productGrid.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                        <h4 class="text-secondary">Không thể kết nối API sản phẩm.</h4>
                    </div>
                `;
            });
    }

    function renderProducts(products) {
        if (products.length === 0) {
            productGrid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-seedling fa-4x text-muted opacity-25 mb-3"></i>
                    <h3 class="text-secondary">Không tìm thấy sản phẩm nào phù hợp.</h3>
                </div>
            `;
            return;
        }

        let html = '';
        products.forEach(p => {
            const formattedPrice = Number(p.price).toLocaleString('vi-VN');
            const imgHtml = p.image 
                ? `<img src="/${p.image}" class="card-img-top" alt="${p.name}">`
                : `<div class="bg-light h-100 w-100 d-flex align-items-center justify-content-center">
                       <i class="fas fa-image fa-3x text-muted opacity-20"></i>
                   </div>`;

            html += `
            <div class="col-sm-6 col-lg-3 product-item-card fade-in">
                <div class="card product-card">
                    <div class="card-img-container">
                        <span class="eco-badge">
                            <i class="fas fa-tag me-1"></i>${p.category_name || 'Eco'}
                        </span>
                        ${imgHtml}
                    </div>

                    <div class="card-body p-4 d-flex flex-column justify-content-between flex-grow-1">
                        <div>
                            <h5 class="product-title" title="${p.name}">
                                ${p.name}
                            </h5>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-warning" style="font-size: 0.85rem;">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="ms-2" style="font-size: 0.8rem; color: #718096;">
                                    4.7/5 (76 đánh giá)
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div>
                                    <div class="price-label">Giá đóng góp</div>
                                    <div class="price-value text-nowrap">${formattedPrice} <small>đ</small></div>
                                </div>
                                
                                <div class="d-flex align-items-center m-0 gap-1">
                                    <div class="d-flex align-items-center qty-input-container">
                                        <button type="button" class="qty-btn" onclick="let input = this.nextElementSibling; if(input.value > 1) input.value--;"><i class="fas fa-minus" style="font-size: 8px;"></i></button>
                                        <input type="number" value="1" min="1" class="qty-input-field qty-input border-0 text-center p-0" id="qty-${p.id}">
                                        <button type="button" class="qty-btn" onclick="this.previousElementSibling.value++;"><i class="fas fa-plus" style="font-size: 8px;"></i></button>
                                    </div>
                                    <button type="button" onclick="addToCartAPI(${p.id})" class="btn-cart shadow-sm d-flex align-items-center justify-content-center p-0 flex-shrink-0">
                                        <i class="fas fa-shopping-basket" style="font-size: 14px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <a href="/Product/show/${p.id}" class="btn-view-detail-card">
                            <i class="fas fa-eye me-1"></i> Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
            `;
        });
        productGrid.innerHTML = html;
    }

    function renderPagination(totalPages) {
        const paginationContainer = document.getElementById('pagination-container');
        paginationContainer.innerHTML = '';

        if (totalPages <= 1) return;

        const ul = document.createElement('ul');
        ul.className = 'pagination pagination-lg m-0';

        // Nút Previous
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${state.page === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><i class="fas fa-chevron-left" style="font-size: 0.9rem;"></i></a>`;
        prevLi.addEventListener('click', (e) => {
            e.preventDefault();
            if (state.page > 1) {
                state.page--;
                fetchProducts();
                document.getElementById('product-grid').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
        ul.appendChild(prevLi);

        // Các nút số trang
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${state.page === i ? 'active' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = i;
            a.addEventListener('click', (e) => {
                e.preventDefault();
                state.page = i;
                fetchProducts();
                document.getElementById('product-grid').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            li.appendChild(a);
            ul.appendChild(li);
        }

        // Nút Next
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${state.page === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><i class="fas fa-chevron-right" style="font-size: 0.9rem;"></i></a>`;
        nextLi.addEventListener('click', (e) => {
            e.preventDefault();
            if (state.page < totalPages) {
                state.page++;
                fetchProducts();
                document.getElementById('product-grid').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
        ul.appendChild(nextLi);

        paginationContainer.appendChild(ul);
    }

    // Debounce search
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            state.name = searchInput.value.trim();
            state.page = 1;
            fetchProducts();
        }, 300);
    });

    // Category filter
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            state.category_id = this.getAttribute('data-category');
            state.page = 1;
            fetchProducts();
        });
    });

    // Sort selection
    sortSelect.addEventListener('change', () => {
        state.sort = sortSelect.value;
        state.page = 1;
        fetchProducts();
    });

    // Price range filter
    priceFilterBtn.addEventListener('click', () => {
        state.min_price = minPriceInput.value.trim();
        state.max_price = maxPriceInput.value.trim();
        state.page = 1;
        fetchProducts();
    });

    // Hàm global để gọi API thêm vào giỏ hàng
    window.addToCartAPI = function(productId) {
        const token = localStorage.getItem('jwtToken');
        if (!token) {
            alert('Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.');
            window.location.href = '/User/login';
            return;
        }

        const qtyInput = document.getElementById(`qty-${productId}`);
        const quantity = qtyInput ? parseInt(qtyInput.value) : 1;

        fetch('/api/cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Lỗi thêm vào giỏ hàng'); });
            }
            return response.json();
        })
        .then(data => {
            alert(data.message || 'Đã thêm sản phẩm vào giỏ hàng!');
            updateCartBadge();
        })
        .catch(error => {
            alert(error.message);
            console.error('Lỗi Cart API:', error);
        });
    };

    function updateCartBadge() {
        const token = localStorage.getItem('jwtToken');
        if (!token) return;
        fetch('/api/cart', {
            headers: { 'Authorization': 'Bearer ' + token }
        })
        .then(response => response.json())
        .then(data => {
            const count = data.cart ? data.cart.length : 0;
            let badge = document.querySelector('.nav-link[href="/Product/cart"] .badge');
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                    badge.style.fontSize = '0.7rem';
                    document.querySelector('.nav-link[href="/Product/cart"]').appendChild(badge);
                }
                badge.textContent = count;
            } else {
                if (badge) badge.remove();
            }
        })
        .catch(err => console.log('Lỗi cập nhật badge giỏ hàng:', err));
    }

    // Chạy lần đầu
    fetchProducts();
    updateCartBadge();
});
</script>

<?php include 'app/shares/footer.php'; ?>