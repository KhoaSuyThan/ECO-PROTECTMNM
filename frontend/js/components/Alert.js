/**
 * ECO-PROTECT Alert Toast Notification Component
 */

export function showAlert(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    // Tạo phần tử thông báo toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} glass-panel`;
    
    // Icon tương ứng
    let iconClass = 'fa-circle-check';
    if (type === 'warning') iconClass = 'fa-circle-exclamation';
    else if (type === 'danger') iconClass = 'fa-circle-xmark';
    else if (type === 'info') iconClass = 'fa-circle-info';

    toast.innerHTML = `
        <i class="fa-solid ${iconClass} toast-icon"></i>
        <span class="toast-message">${message}</span>
    `;

    container.appendChild(toast);

    // Tự động xóa sau 3.5 giây
    setTimeout(() => {
        toast.style.animation = 'fadeIn 0.3s reverse forwards';
        setTimeout(() => {
            if (toast.parentNode) {
                container.removeChild(toast);
            }
        }, 300);
    }, 3500);
}

export default showAlert;
