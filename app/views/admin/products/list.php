<?php include 'app/shares/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-success mb-0"><i class="fas fa-boxes me-2"></i>Quản Lý Sản Phẩm</h2>
        <a href="/Product/add" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">
            <i class="fas fa-plus-circle me-1"></i> Thêm Sản Phẩm Mới
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" width="80">Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá bán</th>
                            <th>Ngày đăng</th>
                            <th class="text-center" width="150">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                                    <h5>Chưa có sản phẩm nào.</h5>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <?php if (!empty($p->image)): ?>
                                            <img src="/<?php echo htmlspecialchars($p->image); ?>" alt="Product" class="rounded-3 shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        <?php echo htmlspecialchars($p->name); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-success border border-success">
                                            <?php echo htmlspecialchars($categoryMap[$p->category_id] ?? 'Unknown'); ?>
                                        </span>
                                    </td>
                                    <td class="text-danger fw-bold">
                                        <?php echo number_format($p->price, 0, ',', '.'); ?> đ
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo date('d/m/Y H:i', strtotime($p->created_at ?? 'now')); ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="/Product/edit/<?php echo $p->id; ?>" class="btn btn-sm btn-outline-primary rounded-circle shadow-sm mx-1" title="Sửa">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="/Product/delete/<?php echo $p->id; ?>" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm mx-1" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?');">
                                            <i class="fas fa-trash-alt"></i>
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
