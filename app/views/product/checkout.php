<?php include 'app/shares/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow border-0 rounded-4 overflow-hidden bg-white">
                <div style="height: 6px; background: linear-gradient(90deg, #d8f3dc, #2d6a4f);"></div>
                <div class="card-body p-4 p-md-5">
                    <h3 class="fw-bold text-success text-center mb-2"><i class="fas fa-wallet me-2"></i>Thông Tin Đặt Hàng</h3>
                    <p class="text-muted text-center small mb-5">Vui lòng điền thông tin chính xác để Eco-Store giao hàng tận nơi</p>

                    <form id="checkoutForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark"><i class="fas fa-user me-2 text-success"></i>Họ và tên người nhận</label>
                            <input type="text" id="fullname" name="name" class="form-control rounded-3 py-2" placeholder="Nguyễn Văn A" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark"><i class="fas fa-phone me-2 text-success"></i>Số điện thoại</label>
                            <input type="tel" id="phone" name="phone" class="form-control rounded-3 py-2" placeholder="0901234567" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark"><i class="fas fa-map-marker-alt me-2 text-success"></i>Địa chỉ nhận hàng</label>
                            <textarea id="address" name="address" class="form-control rounded-3" rows="3" placeholder="Số nhà, tên đường, phường/xã, quận/huyện..." required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark"><i class="fas fa-credit-card me-2 text-success"></i>Phương thức thanh toán</label>
                            <select id="payment_method" name="payment_method" class="form-select rounded-3 py-2">
                                <option value="COD">Thanh toán khi nhận hàng (COD)</option>
                                <option value="BANK_TRANSFER">Chuyển khoản ngân hàng (Giả lập)</option>
                                <option value="E_WALLET">Ví điện tử Momo/ZaloPay (Giả lập)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success w-100 btn-lg rounded-3 py-3 fw-bold shadow-sm mb-3">
                            <i class="fas fa-check-circle me-2"></i>XÁC NHẬN ĐẶT HÀNG
                        </button>
                    </form>

                    <div class="text-center">
                        <a href="/Product/cart" class="text-decoration-none text-muted small fw-bold">
                            <i class="fas fa-chevron-left me-1"></i> Quay lại sửa giỏ hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('jwtToken');
    if (!token) {
        alert('Vui lòng đăng nhập để thực hiện thanh toán.');
        window.location.href = '/User/login';
        return;
    }

    // Tự động tải thông tin cá nhân của người dùng để prefill
    fetch('/api/auth/me', {
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(res => res.json())
    .then(user => {
        if (user) {
            if (user.fullname) document.getElementById('fullname').value = user.fullname;
            if (user.phone) document.getElementById('phone').value = user.phone;
            if (user.address) document.getElementById('address').value = user.address;
        }
    })
    .catch(err => console.log('Không thể tải profile tự động:', err));

    // Xử lý tạo đơn hàng và thanh toán
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const name = document.getElementById('fullname').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const address = document.getElementById('address').value.trim();
        const paymentMethod = document.getElementById('payment_method').value;

        // 1. Tạo đơn hàng
        fetch('/api/order', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                name: name,
                phone: phone,
                address: address
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Lỗi tạo đơn hàng'); });
            }
            return response.json();
        })
        .then(orderData => {
            const orderId = orderData.order_id;
            
            // 2. Tạo thanh toán cho đơn hàng
            return fetch('/api/payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({
                    order_id: orderId,
                    payment_method: paymentMethod
                })
            });
        })
        .then(paymentResponse => {
            if (!paymentResponse.ok) {
                return paymentResponse.json().then(err => { throw new Error(err.message || 'Lỗi thanh toán'); });
            }
            return paymentResponse.json();
        })
        .then(paymentData => {
            alert('Đặt hàng & xử lý thanh toán thành công!');
            window.location.href = '/Product/orderConfirmation';
        })
        .catch(error => {
            alert(error.message);
            console.error('Lỗi checkout:', error);
        });
    });
});
</script>

<?php include 'app/shares/footer.php'; ?>