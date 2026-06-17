import { api } from '../api/api.js';
import { store } from '../state/store.js';
import { formatPrice, formatDate, escapeHTML } from '../utils/helpers.js';
import { showAlert } from '../components/Alert.js';

export async function renderAdminDashboardPage() {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;

    if (!store.state.currentUser || store.state.currentUser.role !== 'admin') {
        showAlert('Bạn không có quyền truy cập trang quản trị!', 'danger');
        window.location.hash = '#home';
        return;
    }

    mainContent.innerHTML = `
        <h1 style="margin-bottom: 2rem; font-size: 2.2rem; color: var(--primary);">Hệ Thống Quản Trị ECO-PROTECT</h1>
        
        <div class="admin-layout">
            <!-- Sidebar -->
            <div class="admin-sidebar">
                <div class="admin-menu-item active" data-tab="stats">Thống kê chung</div>
                <div class="admin-menu-item" data-tab="products">Quản lý sản phẩm</div>
                <div class="admin-menu-item" data-tab="categories">Quản lý danh mục</div>
                <div class="admin-menu-item" data-tab="orders">Quản lý đơn hàng</div>
                <div class="admin-menu-item" data-tab="users">Quản lý người dùng</div>
            </div>

            <!-- Main Panel -->
            <div class="admin-main" id="admin-main-panel">
                <div class="spinner-container">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Product Modal Overlay -->
        <div id="product-modal-overlay" class="modal-overlay">
            <div class="modal-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">
                    <h3 id="modal-title-text" style="color: var(--primary);">Thêm sản phẩm mới</h3>
                    <button id="close-modal-btn" style="background: transparent; font-size: 1.5rem; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
                </div>
                
                <form id="product-modal-form" enctype="multipart/form-data">
                    <input type="hidden" id="modal-product-id">
                    
                    <div class="form-group">
                        <label for="modal-prod-name">Tên sản phẩm <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="modal-prod-name" class="form-control" required placeholder="Nhập tên sản phẩm...">
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-prod-category">Danh mục <span style="color: var(--danger);">*</span></label>
                        <select id="modal-prod-category" class="form-control" required>
                            <option value="">-- Chọn danh mục --</option>
                            ${store.state.categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal-prod-price">Giá bán (VND) <span style="color: var(--danger);">*</span></label>
                        <input type="number" id="modal-prod-price" class="form-control" required placeholder="Ví dụ: 150000">
                    </div>

                    <div class="form-group">
                        <label for="modal-prod-desc">Mô tả sản phẩm <span style="color: var(--danger);">*</span></label>
                        <textarea id="modal-prod-desc" class="form-control" rows="3" required placeholder="Nhập mô tả sản phẩm..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="modal-prod-image">Hình ảnh sản phẩm</label>
                        <input type="file" id="modal-prod-image" class="form-control" accept="image/*">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;" id="modal-submit-btn">
                        Xác nhận lưu
                    </button>
                </form>
            </div>
        </div>
    `;

    // Quản lý các tab
    let currentTab = 'stats';
    const mainPanel = document.getElementById('admin-main-panel');
    const menuItems = document.querySelectorAll('.admin-menu-item');

    // Mở và đóng modal sản phẩm
    const modal = document.getElementById('product-modal-overlay');
    const closeModalBtn = document.getElementById('close-modal-btn');
    closeModalBtn.addEventListener('click', () => modal.classList.remove('active'));

    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            menuItems.forEach(m => m.classList.remove('active'));
            item.classList.add('active');
            currentTab = item.dataset.tab;
            loadTabContent();
        });
    });

    async function loadTabContent() {
        mainPanel.innerHTML = `<div class="spinner-container"><div class="spinner"></div></div>`;
        try {
            if (currentTab === 'stats') {
                await renderStats();
            } else if (currentTab === 'products') {
                await renderProducts();
            } else if (currentTab === 'categories') {
                await renderCategories();
            } else if (currentTab === 'orders') {
                await renderOrders();
            } else if (currentTab === 'users') {
                await renderUsers();
            }
        } catch (err) {
            mainPanel.innerHTML = `<p style="color: var(--danger); text-align: center;">Lỗi tải dữ liệu quản trị: ${err.message}</p>`;
        }
    }

    // 1. Tab Thống kê
    async function renderStats() {
        // Tải toàn bộ đơn và sản phẩm để đếm
        const productsData = await api.products.getProducts({ limit: 1000 });
        const allOrders = await api.orders.getOrders();
        
        const totalProducts = productsData.total || 0;
        const totalOrders = allOrders.length;
        const completedOrders = allOrders.filter(o => o.status === 'completed');
        const revenue = completedOrders.reduce((sum, o) => sum + parseFloat(o.total_amount || 0), 0);

        mainPanel.innerHTML = `
            <h2 style="margin-bottom: 1.5rem; color: var(--gray-800);">Thống kê tổng quan</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-num">${totalProducts}</div>
                    <div class="stat-label">Sản phẩm đang bán</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num">${totalOrders}</div>
                    <div class="stat-label">Tổng số đơn hàng</div>
                </div>
                <div class="stat-card" style="border-left-color: var(--success);">
                    <div class="stat-num" style="color: var(--success);">${formatPrice(revenue)}</div>
                    <div class="stat-label">Doanh thu hoàn thành</div>
                </div>
            </div>
            
            <h3 style="margin-bottom: 1rem; color: var(--primary);">Giới thiệu hệ thống quản lý</h3>
            <p>Trang quản trị cho phép bạn nhanh chóng cập nhật kho sản phẩm bảo vệ môi trường, kiểm soát các đơn đặt hàng mới từ khách hàng, thay đổi trạng thái vận chuyển và theo dõi dòng tiền doanh thu.</p>
        `;
    }

    // 2. Tab Quản lý sản phẩm
    async function renderProducts() {
        const data = await api.products.getProducts({ limit: 1000 });
        
        mainPanel.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="color: var(--gray-800);">Danh sách sản phẩm</h2>
                <button id="add-prod-btn" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Thêm sản phẩm
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Đơn giá</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="admin-prod-tbody">
                        ${data.products.map(p => `
                            <tr data-id="${p.id}">
                                <td>
                                    <img src="${p.image ? '../' + p.image : 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=50&q=80'}" style="width: 40px; height: 40px; object-fit: cover; border-radius: var(--radius-sm);" onError="this.src='https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=50&q=80'">
                                </td>
                                <td style="font-weight: 600;">${escapeHTML(p.name)}</td>
                                <td>${escapeHTML(p.category_name || 'Sản phẩm')}</td>
                                <td style="font-weight: 700; color: var(--primary);">${formatPrice(p.price)}</td>
                                <td>
                                    <button class="btn btn-secondary btn-sm edit-prod-btn" style="padding: 0.4rem 0.8rem; margin-right: 0.25rem;"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-danger btn-sm delete-prod-btn" style="padding: 0.4rem 0.8rem;"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        // Hành động Thêm sản phẩm mới (Mở Modal)
        document.getElementById('add-prod-btn').addEventListener('click', () => {
            document.getElementById('modal-title-text').textContent = 'Thêm sản phẩm mới';
            document.getElementById('modal-product-id').value = '';
            document.getElementById('product-modal-form').reset();
            modal.classList.add('active');
        });

        // Đăng ký sự kiện edit / delete cho bảng
        const tbody = document.getElementById('admin-prod-tbody');
        tbody.addEventListener('click', async (e) => {
            const row = e.target.closest('tr');
            if (!row) return;
            const prodId = row.dataset.id;
            const product = data.products.find(p => p.id == prodId);

            // Sửa
            if (e.target.closest('.edit-prod-btn')) {
                document.getElementById('modal-title-text').textContent = 'Chỉnh sửa sản phẩm';
                document.getElementById('modal-product-id').value = product.id;
                document.getElementById('modal-prod-name').value = product.name;
                document.getElementById('modal-prod-category').value = product.category_id;
                document.getElementById('modal-prod-price').value = product.price;
                document.getElementById('modal-prod-desc').value = product.description;
                modal.classList.add('active');
            }

            // Xóa
            if (e.target.closest('.delete-prod-btn')) {
                if (confirm(`Bạn chắc chắn muốn xóa sản phẩm "${product.name}"?`)) {
                    try {
                        await api.products.deleteProduct(prodId);
                        showAlert('Đã xóa sản phẩm thành công!', 'success');
                        loadTabContent();
                    } catch (err) {
                        showAlert(err.message, 'danger');
                    }
                }
            }
        });
    }

    // Xử lý nộp form Modal Sản Phẩm (Thêm / Sửa)
    const productForm = document.getElementById('product-modal-form');
    productForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const prodId = document.getElementById('modal-product-id').value;
        const name = document.getElementById('modal-prod-name').value.trim();
        const categoryId = document.getElementById('modal-prod-category').value;
        const price = document.getElementById('modal-prod-price').value;
        const description = document.getElementById('modal-prod-desc').value.trim();
        const imageFile = document.getElementById('modal-prod-image').files[0];

        const formData = new FormData();
        formData.append('name', name);
        formData.append('category_id', categoryId);
        formData.append('price', price);
        formData.append('description', description);
        if (imageFile) {
            formData.append('image', imageFile);
        }

        const modalSubmitBtn = document.getElementById('modal-submit-btn');
        modalSubmitBtn.disabled = true;
        modalSubmitBtn.textContent = 'Đang lưu...';

        try {
            if (prodId) {
                // Đang sửa
                await api.products.updateProduct(prodId, formData);
                showAlert('Cập nhật thông tin sản phẩm thành công!', 'success');
            } else {
                // Đang thêm mới
                await api.products.addProduct(formData);
                showAlert('Đã thêm sản phẩm mới thành công!', 'success');
            }
            modal.classList.remove('active');
            loadTabContent();
        } catch (err) {
            showAlert(err.message || 'Lỗi khi lưu sản phẩm', 'danger');
        } finally {
            modalSubmitBtn.disabled = false;
            modalSubmitBtn.textContent = 'Xác nhận lưu';
        }
    });

    // 3. Tab Quản lý đơn hàng
    async function renderOrders() {
        const orders = await api.orders.getOrders();
        
        const statusMap = {
            'pending': 'Chờ xử lý',
            'confirmed': 'Đã xác nhận',
            'shipping': 'Đang giao hàng',
            'completed': 'Đã hoàn thành',
            'cancelled': 'Đã hủy đơn'
        };

        mainPanel.innerHTML = `
            <h2 style="margin-bottom: 1.5rem; color: var(--gray-800);">Quản lý đơn hàng từ Khách hàng</h2>

            <div style="overflow-x: auto;">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Thông tin nhận</th>
                            <th>Tổng đơn</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="admin-orders-tbody">
                        ${orders.map(o => `
                            <tr data-order-id="${o.id}">
                                <td style="font-weight: 700;">#${o.id}</td>
                                <td style="font-size: 0.85rem;">${formatDate(o.created_at)}</td>
                                <td>
                                    <strong>${escapeHTML(o.name)}</strong> (${escapeHTML(o.phone)})
                                    <div style="font-size: 0.85rem; color: var(--gray-600); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${escapeHTML(o.address)}</div>
                                </td>
                                <td style="font-weight: 700; color: var(--primary);">${formatPrice(o.total_amount)}</td>
                                <td>
                                    <select class="form-control status-select" style="padding: 0.4rem; font-size: 0.9rem;" data-order-id="${o.id}">
                                        ${Object.entries(statusMap).map(([val, label]) => `
                                            <option value="${val}" ${o.status === val ? 'selected' : ''}>${label}</option>
                                        `).join('')}
                                    </select>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm update-status-btn" style="padding: 0.4rem 0.8rem;" data-order-id="${o.id}">
                                        Cập nhật
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        // Đăng ký sự kiện cập nhật trạng thái đơn hàng
        const tbody = document.getElementById('admin-orders-tbody');
        tbody.addEventListener('click', async (e) => {
            const btn = e.target.closest('.update-status-btn');
            if (!btn) return;

            const orderId = btn.dataset.orderId;
            const select = tbody.querySelector(`select[data-order-id="${orderId}"]`);
            const selectedStatus = select.value;

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            try {
                await api.orders.updateOrderStatus(orderId, selectedStatus);
                showAlert(`Đã cập nhật trạng thái đơn hàng #${orderId} thành công!`, 'success');
            } catch (err) {
                showAlert(err.message || 'Lỗi cập nhật trạng thái', 'danger');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Cập nhật';
            }
        });
    }

    // 4. Tab Quản lý danh mục
    async function renderCategories() {
        const categories = await api.categories.getCategories();
        store.setCategories(categories);

        mainPanel.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="color: var(--gray-800);">Quản lý danh mục sản phẩm</h2>
                <button id="add-cat-btn" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Thêm danh mục
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Mã danh mục</th>
                            <th>Tên danh mục</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="admin-cat-tbody">
                        ${categories.map(c => `
                            <tr data-id="${c.id}">
                                <td style="font-weight: 700;">#${c.id}</td>
                                <td style="font-weight: 600;">${escapeHTML(c.name)}</td>
                                <td>
                                    <button class="btn btn-secondary btn-sm edit-cat-btn" style="padding: 0.4rem 0.8rem; margin-right: 0.25rem;"><i class="fa-solid fa-pen"></i> Sửa</button>
                                    <button class="btn btn-danger btn-sm delete-cat-btn" style="padding: 0.4rem 0.8rem;"><i class="fa-solid fa-trash"></i> Xóa</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        // Thêm danh mục mới
        document.getElementById('add-cat-btn').addEventListener('click', async () => {
            const name = prompt('Nhập tên danh mục mới:');
            if (name && name.trim()) {
                try {
                    await api.categories.addCategory(name.trim());
                    showAlert('Đã thêm danh mục mới thành công!', 'success');
                    loadTabContent();
                } catch (err) {
                    showAlert(err.message || 'Lỗi thêm danh mục', 'danger');
                }
            }
        });

        // Sửa / Xóa danh mục
        const tbody = document.getElementById('admin-cat-tbody');
        tbody.addEventListener('click', async (e) => {
            const row = e.target.closest('tr');
            if (!row) return;
            const catId = row.dataset.id;
            const category = categories.find(c => c.id == catId);

            // Sửa danh mục
            if (e.target.closest('.edit-cat-btn')) {
                const newName = prompt('Nhập tên mới cho danh mục:', category.name);
                if (newName && newName.trim() && newName.trim() !== category.name) {
                    try {
                        await api.categories.updateCategory(catId, newName.trim());
                        showAlert('Đã cập nhật tên danh mục thành công!', 'success');
                        loadTabContent();
                    } catch (err) {
                        showAlert(err.message || 'Lỗi sửa danh mục', 'danger');
                    }
                }
            }

            // Xóa danh mục
            if (e.target.closest('.delete-cat-btn')) {
                if (confirm(`Bạn có chắc muốn xóa danh mục "${category.name}"? (Tất cả sản phẩm thuộc danh mục sẽ bị ảnh hưởng)`)) {
                    try {
                        await api.categories.deleteCategory(catId);
                        showAlert('Đã xóa danh mục thành công!', 'success');
                        loadTabContent();
                    } catch (err) {
                        showAlert(err.message || 'Lỗi xóa danh mục', 'danger');
                    }
                }
            }
        });
    }

    // 5. Tab Quản lý người dùng
    async function renderUsers() {
        const users = await api.auth.listUsers();

        const roleLabels = {
            'admin': 'Quản trị viên',
            'user': 'Khách hàng'
        };

        mainPanel.innerHTML = `
            <h2 style="margin-bottom: 1.5rem; color: var(--gray-800);">Quản lý người dùng trong hệ thống</h2>

            <div style="overflow-x: auto;">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Mã KH</th>
                            <th>Username</th>
                            <th>Họ tên</th>
                            <th>Email / SĐT</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="admin-users-tbody">
                        ${users.map(u => {
                            const isLocked = u.status === 'locked';
                            return `
                                <tr data-id="${u.id}">
                                    <td style="font-weight: 700;">#${u.id}</td>
                                    <td><strong>${escapeHTML(u.username)}</strong></td>
                                    <td>${escapeHTML(u.fullname || '-')}</td>
                                    <td>
                                        <div>${escapeHTML(u.email)}</div>
                                        <div style="font-size: 0.85rem; color: var(--gray-600);">${escapeHTML(u.phone || '-')}</div>
                                    </td>
                                    <td>
                                        <span class="badge ${u.role === 'admin' ? 'badge-primary' : 'badge-success'}">${roleLabels[u.role] || u.role}</span>
                                    </td>
                                    <td>
                                        <span class="badge ${isLocked ? 'badge-danger' : 'badge-success'}">${isLocked ? 'Bị khóa' : 'Hoạt động'}</span>
                                    </td>
                                    <td>
                                        ${u.role === 'admin' ? `
                                            <span style="font-size: 0.85rem; color: var(--gray-600);">Không khóa được Admin</span>
                                        ` : `
                                            <button class="btn ${isLocked ? 'btn-primary' : 'btn-danger'} btn-sm toggle-status-btn" style="padding: 0.4rem 0.8rem;" data-id="${u.id}">
                                                ${isLocked ? '<i class="fa-solid fa-lock-open"></i> Mở khóa' : '<i class="fa-solid fa-lock"></i> Khóa'}
                                            </button>
                                        `}
                                    </td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        `;

        // Khóa / Mở khóa người dùng
        const tbody = document.getElementById('admin-users-tbody');
        tbody.addEventListener('click', async (e) => {
            const btn = e.target.closest('.toggle-status-btn');
            if (!btn) return;

            const userId = btn.dataset.id;
            const user = users.find(u => u.id == userId);
            const isLocked = user.status === 'locked';
            const actionText = isLocked ? 'mở khóa' : 'khóa';

            if (confirm(`Bạn chắc chắn muốn ${actionText} tài khoản của "${user.username}"?`)) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

                try {
                    await api.auth.toggleUserStatus(userId);
                    showAlert(`Đã ${actionText} tài khoản "${user.username}" thành công!`, 'success');
                    loadTabContent();
                } catch (err) {
                    showAlert(err.message || 'Lỗi cập nhật trạng thái', 'danger');
                    loadTabContent();
                }
            }
        });
    }

    // Tải tab mặc định lần đầu
    await loadTabContent();
}
