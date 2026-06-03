<?php include 'app/shares/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold text-success"><i class="fas fa-shopping-cart me-2"></i>Quản lý Đơn hàng</h2>
        </div>
        <div class="col-md-4 text-end">
            <div class="card bg-success text-white shadow-sm rounded-4 border-0">
                <div class="card-body py-2 px-3">
                    <h6 class="mb-1 text-white-50">Tổng doanh thu hệ thống</h6>
                    <h4 class="mb-0 fw-bold"><?php echo number_format($totalRevenue, 0, ',', '.'); ?> <small>đ</small></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">Mã ĐH</th>
                            <th class="py-3">Khách hàng</th>
                            <th class="py-3">Số điện thoại</th>
                            <th class="py-3">Ngày đặt</th>
                            <th class="py-3 text-end">Tổng tiền</th>
                            <th class="text-end px-4 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($orders)): ?>
                            <tr><td colspan="6" class="text-center py-4">Chưa có đơn hàng nào.</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-4 fw-bold text-muted">#<?php echo $order->id; ?></td>
                                <td><?php echo htmlspecialchars($order->name); ?></td>
                                <td><?php echo htmlspecialchars($order->phone); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></td>
                                <td class="text-end fw-bold text-danger"><?php echo number_format($order->total_price, 0, ',', '.'); ?> đ</td>
                                <td class="text-end px-4">
                                    <a href="/AdminOrder/view/<?php echo $order->id; ?>" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                        <i class="fas fa-eye me-1"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Phân trang -->
    <?php if (isset($total_pages) && $total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" tabindex="-1">Trước</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $current_page == $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Sau</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include 'app/shares/footer.php'; ?>
