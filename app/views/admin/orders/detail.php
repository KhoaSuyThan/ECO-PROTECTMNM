<?php include 'app/shares/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success"><i class="fas fa-file-invoice me-2"></i>Chi tiết Đơn hàng #<?php echo $orderInfo->id; ?></h3>
        <a href="/AdminOrder/index" class="btn btn-outline-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>
    </div>

    <div class="row">
        <!-- Thông tin khách hàng -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-success text-white rounded-top-4 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user-circle me-2"></i>Thông tin Khách hàng</h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-2"><span class="text-muted"><i class="fas fa-user me-2"></i>Họ tên:</span> <strong class="ms-1"><?php echo htmlspecialchars($orderInfo->name); ?></strong></p>
                    <p class="mb-2"><span class="text-muted"><i class="fas fa-phone-alt me-2"></i>SĐT:</span> <strong class="ms-1"><?php echo htmlspecialchars($orderInfo->phone); ?></strong></p>
                    <p class="mb-2"><span class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ:</span> <strong class="ms-1"><?php echo htmlspecialchars($orderInfo->address); ?></strong></p>
                    <p class="mb-0 mt-3 pt-3 border-top"><span class="text-muted"><i class="far fa-clock me-2"></i>Ngày đặt:</span> <span class="ms-1 fw-bold"><?php echo date('d/m/Y H:i', strtotime($orderInfo->created_at)); ?></span></p>
                </div>
            </div>
        </div>

        <!-- Chi tiết sản phẩm -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-box-open me-2 text-success"></i>Sản phẩm đã mua</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Sản phẩm</th>
                                    <th class="py-3 text-center">Đơn giá</th>
                                    <th class="py-3 text-center">Số lượng</th>
                                    <th class="py-3 text-end px-4">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalAmount = 0;
                                foreach ($orderDetails as $detail): 
                                    $subtotal = $detail->price * $detail->quantity;
                                    $totalAmount += $subtotal;
                                ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <?php if($detail->product_image): ?>
                                                <img src="/<?php echo htmlspecialchars($detail->product_image); ?>" class="rounded-3 me-3 border" style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded-3 me-3 d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                                                    <i class="fas fa-image fs-4"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($detail->product_name); ?></h6>
                                                <small class="text-muted">Mã SP: #<?php echo $detail->product_id; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><?php echo number_format($detail->price, 0, ',', '.'); ?> đ</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1 fs-6"><?php echo $detail->quantity; ?></span>
                                    </td>
                                    <td class="text-end px-4 fw-bold text-danger">
                                        <?php echo number_format($subtotal, 0, ',', '.'); ?> đ
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end py-3 fw-bold fs-5">Tổng cộng:</td>
                                    <td class="text-end px-4 py-3 fw-bold text-success fs-5">
                                        <?php echo number_format($totalAmount, 0, ',', '.'); ?> đ
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/shares/footer.php'; ?>
