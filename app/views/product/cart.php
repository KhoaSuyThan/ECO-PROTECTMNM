<?php include $_SERVER['DOCUMENT_ROOT'] . '/app/shares/header.php'; ?>

<div class="container mt-5" id="cart-container">
    <h2 class="fw-bold text-success mb-4"><i class="fas fa-shopping-basket me-2"></i>Giỏ Hàng Của Bạn</h2>

    <!-- JS will dynamically populate the contents here -->
    <div class="text-center py-5">
        <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Đang tải giỏ hàng...</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartContainer = document.getElementById('cart-container');
    
    function fetchCart() {
        const token = localStorage.getItem('jwtToken');
        if (!token) {
            cartContainer.innerHTML = `
                <h2 class="fw-bold text-success mb-4"><i class="fas fa-shopping-basket me-2"></i>Giỏ Hàng Của Bạn</h2>
                <div class="card border-0 shadow-sm rounded-4 py-5 text-center bg-white">
                    <div class="mb-3 text-muted opacity-20"><i class="fas fa-user-lock fa-5x"></i></div>
                    <h4 class="text-secondary">Bạn chưa đăng nhập</h4>
                    <p class="text-muted small">Vui lòng đăng nhập tài khoản để xem giỏ hàng.</p>
                    <div class="mt-3">
                        <a href="/User/login" class="btn btn-success rounded-pill px-4 fw-bold">Đăng Nhập Ngay</a>
                    </div>
                </div>
            `;
            return;
        }

        fetch('/api/cart', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 401) {
                    localStorage.removeItem('jwtToken');
                    throw new Error('Unauthorized');
                }
                throw new Error('Lỗi tải giỏ hàng');
            }
            return response.json();
        })
        .then(data => {
            renderCart(data.cart || [], data.total_price || 0);
        })
        .catch(err => {
            console.error("Lỗi:", err);
            cartContainer.innerHTML = `
                <h2 class="fw-bold text-success mb-4"><i class="fas fa-shopping-basket me-2"></i>Giỏ Hàng Của Bạn</h2>
                <div class="card border-0 shadow-sm rounded-4 py-5 text-center bg-white">
                    <div class="mb-3 text-muted opacity-20"><i class="fas fa-exclamation-triangle fa-5x text-danger"></i></div>
                    <h4 class="text-secondary">Phiên đăng nhập hết hạn</h4>
                    <p class="text-muted small">Vui lòng đăng nhập lại để tiếp tục.</p>
                    <div class="mt-3">
                        <a href="/User/login" class="btn btn-success rounded-pill px-4 fw-bold">Đăng Nhập</a>
                    </div>
                </div>
            `;
        });
    }

    function renderCart(cart, total) {
        if (cart.length === 0) {
            cartContainer.innerHTML = `
                <h2 class="fw-bold text-success mb-4"><i class="fas fa-shopping-basket me-2"></i>Giỏ Hàng Của Bạn</h2>
                <div class="card border-0 shadow-sm rounded-4 py-5 text-center bg-white">
                    <div class="mb-3 text-muted opacity-20"><i class="fas fa-shopping-bag fa-5x"></i></div>
                    <h4 class="text-secondary">Giỏ hàng của bạn đang trống rỗng</h4>
                    <p class="text-muted small">Hãy lấp đầy giỏ hàng bằng những sản phẩm xanh bảo vệ môi trường nhé.</p>
                    <div class="mt-3">
                        <a href="/Product/list" class="btn btn-success rounded-pill px-4 fw-bold">Mua Sắm Ngay</a>
                    </div>
                </div>
            `;
            return;
        }

        const formattedTotal = Number(total).toLocaleString('vi-VN');
        let tableRows = '';

        cart.forEach(item => {
            const formattedPrice = Number(item.price).toLocaleString('vi-VN');
            const formattedSubtotal = Number(item.subtotal).toLocaleString('vi-VN');
            const imgHtml = item.image 
                ? `<img src="/${item.image}" width="60" height="60" style="object-fit: cover;" class="rounded-3 shadow-sm">`
                : `<div class="bg-light rounded-3 text-center d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                       <i class="fas fa-box text-muted opacity-50"></i>
                   </div>`;

            tableRows += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            ${imgHtml}
                            <div>
                                <span class="fw-bold d-block text-dark">${item.name}</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="text-muted">${formattedPrice}đ</span></td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <button onclick="updateQty(${item.product_id}, ${item.quantity - 1})" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                <i class="fas fa-minus" style="font-size: 10px;"></i>
                            </button>
                            <span class="fw-bold mx-2">${item.quantity}</span>
                            <button onclick="updateQty(${item.product_id}, ${item.quantity + 1})" class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                <i class="fas fa-plus" style="font-size: 10px;"></i>
                            </button>
                        </div>
                    </td>
                    <td><span class="text-success fw-bold">${formattedSubtotal}đ</span></td>
                    <td class="text-center">
                        <button onclick="deleteItem(${item.product_id})" class="btn btn-sm btn-outline-danger border-0 rounded-circle">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        cartContainer.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-success m-0"><i class="fas fa-shopping-basket me-2"></i>Giỏ Hàng Của Bạn</h2>
                <button onclick="clearCart()" class="btn btn-outline-danger rounded-pill btn-sm px-3 fw-bold">
                    <i class="fas fa-trash-alt me-1"></i> Xóa sạch giỏ hàng
                </button>
            </div>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 rounded-4 p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Giá</th>
                                        <th style="width: 100px;" class="text-center">Số lượng</th>
                                        <th>Tổng</th>
                                        <th class="text-center">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${tableRows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 rounded-4 p-4 bg-white">
                        <h5 class="fw-bold text-dark mb-4">Tóm tắt đơn hàng</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Tạm tính:</span>
                            <span class="fw-bold">${formattedTotal}đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4 border-top pt-3">
                            <span class="fw-bold">Tổng tiền thanh toán:</span>
                            <span class="text-danger fw-extrabold fs-4">${formattedTotal}đ</span>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="/Product/checkout" class="btn btn-success btn-lg rounded-pill fw-bold py-3 shadow-sm">
                                <i class="fas fa-credit-card me-2"></i>TIẾN HÀNH THANH TOÁN
                            </a>
                            <a href="/Product/list" class="btn btn-link text-decoration-none text-muted small mt-2">
                                <i class="fas fa-arrow-left me-1"></i> Tiếp tục mua sắm
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    window.updateQty = function(productId, newQty) {
        const token = localStorage.getItem('jwtToken');
        if (newQty <= 0) {
            deleteItem(productId);
            return;
        }

        fetch('/api/cart/' + productId, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ quantity: newQty })
        })
        .then(response => response.json())
        .then(data => {
            fetchCart();
            updateCartBadgeGlobal();
        })
        .catch(err => console.error("Lỗi cập nhật số lượng:", err));
    };

    window.deleteItem = function(productId) {
        if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) return;
        const token = localStorage.getItem('jwtToken');

        fetch('/api/cart/' + productId, {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => response.json())
        .then(data => {
            fetchCart();
            updateCartBadgeGlobal();
        })
        .catch(err => console.error("Lỗi xóa sản phẩm:", err));
    };

    window.clearCart = function() {
        if (!confirm('Bạn có chắc muốn xóa sạch toàn bộ sản phẩm trong giỏ hàng?')) return;
        const token = localStorage.getItem('jwtToken');

        fetch('/api/cart', {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => response.json())
        .then(data => {
            fetchCart();
            updateCartBadgeGlobal();
        })
        .catch(err => console.error("Lỗi xóa sạch giỏ hàng:", err));
    };

    function updateCartBadgeGlobal() {
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
        .catch(err => console.log(err));
    }

    fetchCart();
});
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/app/shares/footer.php'; ?>