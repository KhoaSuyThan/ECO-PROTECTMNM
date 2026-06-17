import { api } from '../api/api.js';
import { store } from '../state/store.js';
import { formatPrice, formatDate, escapeHTML } from '../utils/helpers.js';
import { showAlert } from '../components/Alert.js';

export async function renderOrdersPage() {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;

    if (!store.state.currentUser) {
        window.location.hash = '#login';
        return;
    }

    mainContent.innerHTML = `
        <div class="spinner-container">
            <div class="spinner"></div>
        </div>
    `;

    try {
        const orders = await api.orders.getOrders();
        renderOrdersUI(orders);
    } catch (error) {
        mainContent.innerHTML = `<p style="text-align: center; color: var(--danger); padding: 2rem;">Lỗi tải đơn hàng: ${error.message}</p>`;
    }

    function renderOrdersUI(orders) {
        if (!orders || orders.length === 0) {
            mainContent.innerHTML = `
                <div style="text-align: center; padding: 4rem 2rem;">
                    <i class="fa-solid fa-folder-open" style="font-size: 3rem; color: var(--primary-light); margin-bottom: 1rem;"></i>
                    <h2>Bạn chưa có đơn hàng nào</h2>
                    <p style="color: var(--gray-600); margin-bottom: 2rem;">Các đơn hàng bạn mua sẽ xuất hiện tại đây để bạn tiện theo dõi.</p>
                    <a href="#home" class="btn btn-primary">Mua sắm ngay</a>
                </div>
            `;
            return;
        }

        // Định nghĩa nhãn trạng thái
        const statusLabels = {
            'pending': { text: 'Chờ xử lý', class: 'badge-warning' },
            'confirmed': { text: 'Đã xác nhận', class: 'badge-primary' },
            'shipping': { text: 'Đang giao hàng', class: 'badge-primary' },
            'completed': { text: 'Đã hoàn thành', class: 'badge-success' },
            'cancelled': { text: 'Đã hủy đơn', class: 'badge-danger' }
        };

        const paymentStatusLabels = {
            'unpaid': { text: 'Chưa thanh toán', class: 'badge-danger' },
            'paid': { text: 'Đã thanh toán', class: 'badge-success' }
        };

        mainContent.innerHTML = `
            <h1 style="margin-bottom: 2rem; font-size: 2.2rem; color: var(--primary);">Đơn Hàng Của Bạn</h1>
            
            <div class="cart-items-panel" style="overflow-x: auto;">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Người nhận / SĐT</th>
                            <th>Địa chỉ giao hàng</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái giao</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="orders-rows-body">
                        ${orders.map(order => {
                            const status = statusLabels[order.status] || { text: order.status, class: '' };
                            const payStatus = paymentStatusLabels[order.payment_status] || { text: order.payment_status, class: '' };
                            const canCancel = order.status === 'pending';

                            return `
                                <tr data-order-id="${order.id}">
                                    <td style="font-weight: 700;">#${order.id}</td>
                                    <td style="font-size: 0.9rem;">${formatDate(order.created_at)}</td>
                                    <td>
                                        <div style="font-weight: 600;">${escapeHTML(order.name)}</div>
                                        <div style="font-size: 0.85rem; color: var(--gray-600);">${escapeHTML(order.phone)}</div>
                                    </td>
                                    <td style="font-size: 0.9rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHTML(order.address)}">
                                        ${escapeHTML(order.address)}
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--primary);">${formatPrice(order.total_amount)}</div>
                                        <span class="badge ${payStatus.class}" style="font-size: 0.7rem;">${payStatus.text}</span>
                                    </td>
                                    <td>
                                        <span class="badge ${status.class}">${status.text}</span>
                                    </td>
                                    <td>
                                        ${canCancel ? `
                                            <button class="btn btn-danger btn-sm cancel-order-btn" data-order-id="${order.id}">
                                                Hủy đơn
                                            </button>
                                        ` : `
                                            <span style="font-size: 0.9rem; color: var(--gray-600);">-</span>
                                        `}
                                    </td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        `;

        // Đăng ký sự kiện hủy đơn
        const body = document.getElementById('orders-rows-body');
        body.addEventListener('click', async (e) => {
            const cancelBtn = e.target.closest('.cancel-order-btn');
            if (!cancelBtn) return;

            const orderId = cancelBtn.dataset.orderId;
            if (confirm(`Bạn có chắc chắn muốn hủy đơn hàng #${orderId}?`)) {
                cancelBtn.disabled = true;
                cancelBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

                try {
                    await api.orders.cancelOrder(orderId);
                    showAlert(`Đã hủy đơn hàng #${orderId} thành công!`, 'info');
                    
                    // Reload
                    const updatedOrders = await api.orders.getOrders();
                    renderOrdersUI(updatedOrders);
                } catch (err) {
                    showAlert(err.message || 'Không thể hủy đơn hàng', 'danger');
                    cancelBtn.disabled = false;
                    cancelBtn.innerHTML = 'Hủy đơn';
                }
            }
        });
    }
}
