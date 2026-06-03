<?php include 'app/shares/header.php'; ?>

<style>
    body { background-color: #f4f7f5; }
    .details-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .details-header {
        background: linear-gradient(135deg, #2d6a4f, #1b4332);
        color: white;
        padding: 20px;
    }
    .product-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card details-card">
                <div class="details-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1"><i class="fas fa-file-invoice me-2"></i>Chi tiết đơn hàng #<?php echo $orderInfo->id; ?></h4>
                        <small class="opacity-75">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($orderInfo->created_at)); ?></small>
                    </div>
                    <a href="/Order/history" class="btn btn-light btn-sm rounded-pill text-success fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted fw-bold mb-3">Thông tin người nhận</h6>
                            <p class="mb-1"><strong>Họ tên:</strong> <?php echo htmlspecialchars($orderInfo->name); ?></p>
                            <p class="mb-1"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($orderInfo->phone); ?></p>
                            <p class="mb-0"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($orderInfo->address); ?></p>
                        </div>
                    </div>

                    <h6 class="text-muted fw-bold mb-3">Sản phẩm đã mua</h6>
                    <div class="table-responsive">
                        <table class="table align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                foreach ($orderDetails as $item): 
                                    $subtotal = $item->price * $item->quantity;
                                    $total += $subtotal;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($item->product_image)): ?>
                                                <img src="/<?php echo $item->product_image; ?>" class="product-img me-3" alt="Image">
                                            <?php else: ?>
                                                <div class="bg-light me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 10px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($item->product_name ?? 'Sản phẩm không xác định'); ?></strong>
                                        </div>
                                    </td>
                                    <td class="text-center"><?php echo number_format($item->price, 0, ',', '.'); ?> ₫</td>
                                    <td class="text-center"><?php echo $item->quantity; ?></td>
                                    <td class="text-end fw-bold text-success"><?php echo number_format($subtotal, 0, ',', '.'); ?> ₫</td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-light">
                                    <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                                    <td class="text-end fw-bold text-danger fs-5"><?php echo number_format($total, 0, ',', '.'); ?> ₫</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/shares/footer.php'; ?>
