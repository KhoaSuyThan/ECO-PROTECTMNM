<?php include 'app/shares/header.php'; ?>
<div class="container mt-4">
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-success m-0"><i class="fas fa-tags me-2"></i>Quản Lý Danh Mục</h3>
                <a href="/category/add" class="btn btn-success rounded-pill px-4">
                    <i class="fas fa-plus me-2"></i>Thêm danh mục mới
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">Mã</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả tóm tắt</th>
                            <th class="text-center" style="width: 150px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">Chưa có danh mục nào.</td></tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="text-muted">#<?php echo $cat->id; ?></td>
                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($cat->name); ?></td>
                                <td class="text-secondary small"><?php echo htmlspecialchars($cat->description); ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="/category/edit/<?php echo $cat->id; ?>" class="btn btn-sm btn-outline-warning border-0"><i class="fas fa-edit"></i></a>
                                        <a href="/category/delete/<?php echo $cat->id; ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Xóa danh mục này sẽ xóa toàn bộ sản phẩm thuộc danh mục. Bạn chắc chắn chứ?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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
    </div>
</div>
<?php include 'app/shares/footer.php'; ?>