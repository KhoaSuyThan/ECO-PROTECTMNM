<?php include 'app/shares/header.php'; ?>

<style>
    body { background-color: #f4f7f5; }
    .history-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .history-header {
        background: linear-gradient(135deg, #2d6a4f, #1b4332);
        color: white;
        padding: 20px;
        font-weight: bold;
    }
    .table-hover tbody tr:hover {
        background-color: #f1f8f5;
        transition: 0.3s;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card history-card">
                <div class="history-header d-flex align-items-center">
                    <i class="fas fa-history fa-2x me-3"></i>
                    <h3 class="mb-0">Lịch sử mua hàng</h3>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted opacity-50 mb-3"></i>
                            <h5 class="text-secondary">Bạn chưa có đơn hàng nào.</h5>
                            <a href="/Product/list" class="btn btn-success mt-3 rounded-pill px-4">Mua sắm ngay</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã ĐH</th>
                                        <th>Ngày đặt</th>
                                        <th>Người nhận</th>
                                        <th>Số điện thoại</th>
                                        <th>Trạng thái</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><strong>#<?php echo $order->id; ?></strong></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></td>
                                        <td><?php echo htmlspecialchars($order->name); ?></td>
                                        <td><?php echo htmlspecialchars($order->phone); ?></td>
                                        <td><span class="badge bg-success">Thành công</span></td>
                                        <td class="text-center">
                                            <a href="/Order/details/<?php echo $order->id; ?>" class="btn btn-outline-success btn-sm rounded-pill">
                                                <i class="fas fa-eye me-1"></i> Xem chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/shares/footer.php'; ?>
