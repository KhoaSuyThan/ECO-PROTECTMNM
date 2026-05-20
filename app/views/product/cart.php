<?php include $_SERVER['DOCUMENT_ROOT'] . '/app/shares/header.php'; ?>

<div class="container mt-5">
    <h2 class="fw-bold text-success mb-4"><i class="fas fa-shopping-basket me-2"></i>Giỏ Hàng Của Bạn</h2>

    <?php if (!empty($cart)): ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4 p-4 bg-white">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Giá</th>
                                    <th style="width: 100px;">Số lượng</th>
                                    <th>Tổng</th>
                                    <th class="text-center">Xóa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_cart = 0;
                                foreach ($cart as $id => $item): 
                                    $subtotal = $item['price'] * $item['quantity'];
                                    $total_cart += $subtotal;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="/<?php echo $item['image']; ?>" width="60" height="60" style="object-fit: cover;" class="rounded-3 shadow-sm">
                                            <?php else: ?>
                                                <div class="bg-light rounded-3 text-center d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                    <i class="fas fa-box text-muted opacity-50"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <span class="fw-bold d-block text-dark"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="text-muted"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</span></td>
                                    <td class="fw-bold text-center"><?php echo $item['quantity']; ?></td>
                                    <td><span class="text-success fw-bold"><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span></td>
                                    <td class="text-center">
                                        <a href="/Product/removeFromCart/<?php echo $id; ?>" class="btn btn-sm btn-outline-danger border-0 rounded-circle">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
                        <span class="fw-bold"><?php echo number_format($total_cart, 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4 border-top pt-3">
                        <span class="fw-bold">Tổng tiền thanh toán:</span>
                        <span class="text-danger fw-extrabold fs-4"><?php echo number_format($total_cart, 0, ',', '.'); ?>đ</span>
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
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 py-5 text-center bg-white">
            <div class="mb-3 text-muted opacity-20"><i class="fas fa-shopping-bag fa-5x"></i></div>
            <h4 class="text-secondary">Giỏ hàng của bạn đang trống rỗng</h4>
            <p class="text-muted small">Hãy lấp đầy giỏ hàng bằng những sản phẩm xanh bảo vệ môi trường nhé.</p>
            <div class="mt-3">
                <a href="/Product/list" class="btn btn-success rounded-pill px-4 fw-bold">Mua Sắm Ngay</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/app/shares/footer.php'; ?>