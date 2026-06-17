/**
 * ECO-PROTECT Helper Utilities
 */

// Định dạng tiền tệ VND (ví dụ: 150000 -> 150.000 đ)
export function formatPrice(amount) {
    if (isNaN(amount) || amount === null) return '0 đ';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}

// Chống tấn công XSS bằng cách escape ký tự HTML đặc biệt
export function escapeHTML(str) {
    if (typeof str !== 'string') return str;
    return str.replace(/[&<>'"]/g, 
        tag => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[tag] || tag)
    );
}

// Định dạng ngày tháng năm (ví dụ: 2026-06-17 13:00:00 -> 17/06/2026)
export function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Phân tích tham số truy vấn từ URL hash (ví dụ: #product-detail?id=5 -> { id: "5" })
export function getQueryParams() {
    const hash = window.location.hash;
    const qIndex = hash.indexOf('?');
    if (qIndex === -1) return {};
    
    const qString = hash.substring(qIndex + 1);
    const pairs = qString.split('&');
    const params = {};
    
    for (const pair of pairs) {
        const [key, val] = pair.split('=');
        if (key) {
            params[decodeURIComponent(key)] = decodeURIComponent(val || '');
        }
    }
    return params;
}

// Lấy hash chính không kèm tham số query (ví dụ: #product-detail?id=5 -> #product-detail)
export function getBaseHash() {
    const hash = window.location.hash || '#home';
    const qIndex = hash.indexOf('?');
    return qIndex === -1 ? hash : hash.substring(0, qIndex);
}
