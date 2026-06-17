import { api } from '../api/api.js';
import { store } from '../state/store.js';
import { createProductCard } from '../components/ProductCard.js';
import { showAlert } from '../components/Alert.js';

export async function renderHomePage() {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;

    // Hiển thị loading spinner
    mainContent.innerHTML = `
        <div class="spinner-container">
            <div class="spinner"></div>
        </div>
    `;

    try {
        // Tải danh mục nếu chưa có trong Store
        if (store.state.categories.length === 0) {
            const categories = await api.categories.getCategories();
            store.setCategories(categories);
        }

        // Các bộ lọc mặc định lấy từ URL query hoặc store
        let activePage = 1;
        let searchQuery = '';
        let minPrice = '';
        let maxPrice = '';
        let sortOption = '';
        let selectedCategory = store.state.activeCategory;

        // Tải sản phẩm từ API
        async function fetchAndRenderProducts() {
            const productsGrid = document.getElementById('products-list-grid');
            if (!productsGrid) return;
            
            productsGrid.innerHTML = `
                <div class="spinner-container" style="grid-column: 1/-1;">
                    <div class="spinner"></div>
                </div>
            `;

            try {
                const data = await api.products.getProducts({
                    page: activePage,
                    limit: 8,
                    name: searchQuery,
                    category_id: selectedCategory,
                    min_price: minPrice,
                    max_price: maxPrice,
                    sort: sortOption
                });

                productsGrid.innerHTML = '';
                
                if (data.products && data.products.length > 0) {
                    data.products.forEach(product => {
                        const card = createProductCard(product);
                        productsGrid.appendChild(card);
                    });
                } else {
                    productsGrid.innerHTML = `
                        <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--gray-600);">
                            <i class="fa-regular fa-face-frown" style="font-size: 3rem; margin-bottom: 1rem; color: var(--primary-light);"></i>
                            <p>Không tìm thấy sản phẩm nào phù hợp với bộ lọc.</p>
                        </div>
                    `;
                }

                // Render phân trang
                renderPagination(data.page, data.total_pages);
            } catch (error) {
                productsGrid.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: var(--danger);">${error.message}</p>`;
            }
        }

        function renderPagination(currentPage, totalPages) {
            const paginationContainer = document.getElementById('pagination-container');
            if (!paginationContainer) return;
            
            paginationContainer.innerHTML = '';
            if (totalPages <= 1) return;

            // Nút Previous
            const prevBtn = document.createElement('button');
            prevBtn.className = 'page-btn';
            prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
            prevBtn.disabled = currentPage === 1;
            prevBtn.addEventListener('click', () => {
                activePage = currentPage - 1;
                fetchAndRenderProducts();
                window.scrollTo({ top: 400, behavior: 'smooth' });
            });
            paginationContainer.appendChild(prevBtn);

            // Các nút số trang
            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `page-btn ${currentPage === i ? 'active' : ''}`;
                pageBtn.textContent = i;
                pageBtn.addEventListener('click', () => {
                    activePage = i;
                    fetchAndRenderProducts();
                    window.scrollTo({ top: 400, behavior: 'smooth' });
                });
                paginationContainer.appendChild(pageBtn);
            }

            // Nút Next
            const nextBtn = document.createElement('button');
            nextBtn.className = 'page-btn';
            nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.addEventListener('click', () => {
                activePage = currentPage + 1;
                fetchAndRenderProducts();
                window.scrollTo({ top: 400, behavior: 'smooth' });
            });
            paginationContainer.appendChild(nextBtn);
        }

        // Bố cục khung trang chủ
        mainContent.innerHTML = `
            <!-- Hero Banner -->
            <section class="hero-banner">
                <div class="hero-content">
                    <span class="badge badge-primary" style="margin-bottom: 1rem; background: rgba(255,255,255,0.2); color: white;">ECO-PROTECT STORE</span>
                    <h1 class="hero-title">Sản Phẩm Xanh Cho Cuộc Sống Đẹp</h1>
                    <p class="hero-subtitle">Mỗi sản phẩm thân thiện với thiên nhiên là một cam kết bảo vệ tương lai hành tinh của chúng ta.</p>
                    <a href="#about" class="btn btn-secondary">Tìm hiểu thêm</a>
                </div>
            </section>

            <!-- Category Tabs -->
            <div class="category-tabs" id="category-tabs-container">
                <div class="category-tab ${!selectedCategory ? 'active' : ''}" data-category-id="">Tất cả sản phẩm</div>
                ${store.state.categories.map(cat => `
                    <div class="category-tab ${selectedCategory == cat.id ? 'active' : ''}" data-category-id="${cat.id}">
                        ${cat.name}
                    </div>
                `).join('')}
            </div>

            <!-- Filter and Search Bar -->
            <div class="filter-bar">
                <div class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="search-input-field" placeholder="Tìm sản phẩm bảo vệ môi trường..." class="search-input" value="${searchQuery}">
                </div>
                
                <div class="filters-actions">
                    <!-- Price range filter inputs -->
                    <input type="number" id="min-price-filter" placeholder="Giá từ..." class="form-control" style="width: 110px; padding: 0.65rem;" value="${minPrice}">
                    <input type="number" id="max-price-filter" placeholder="đến..." class="form-control" style="width: 110px; padding: 0.65rem;" value="${maxPrice}">
                    
                    <select id="sort-select-filter" class="filter-select">
                        <option value="">Sắp xếp mặc định</option>
                        <option value="price_asc">Giá tăng dần</option>
                        <option value="price_desc">Giá giảm dần</option>
                    </select>

                    <button id="apply-filters-btn" class="btn btn-primary btn-sm" style="padding: 0.75rem 1.25rem;">
                        <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid" id="products-list-grid"></div>

            <!-- Pagination Container -->
            <div id="pagination-container" class="pagination"></div>
        `;

        // Tải sản phẩm lần đầu
        await fetchAndRenderProducts();

        // Đăng ký các sự kiện tương tác
        
        // Tab Danh mục click
        const tabContainer = document.getElementById('category-tabs-container');
        tabContainer.addEventListener('click', (e) => {
            const tab = e.target.closest('.category-tab');
            if (!tab) return;
            
            document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            selectedCategory = tab.dataset.categoryId;
            store.setActiveCategory(selectedCategory);
            activePage = 1;
            fetchAndRenderProducts();
        });

        // Tìm kiếm gõ phím
        const searchInput = document.getElementById('search-input-field');
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value;
            activePage = 1;
            // Debounce đơn giản
            clearTimeout(window.searchDebounce);
            window.searchDebounce = setTimeout(() => {
                fetchAndRenderProducts();
            }, 400);
        });

        // Nút Lọc và Sắp xếp
        const applyBtn = document.getElementById('apply-filters-btn');
        applyBtn.addEventListener('click', () => {
            minPrice = document.getElementById('min-price-filter').value;
            maxPrice = document.getElementById('max-price-filter').value;
            sortOption = document.getElementById('sort-select-filter').value;
            activePage = 1;
            fetchAndRenderProducts();
            showAlert('Đã áp dụng bộ lọc thành công', 'info');
        });

    } catch (error) {
        mainContent.innerHTML = `<p style="color: var(--danger); text-align: center; padding: 2rem;">Lỗi khi tải trang chủ: ${error.message}</p>`;
    }
}
