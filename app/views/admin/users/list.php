<?php include 'app/shares/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-success"><i class="fas fa-users me-2"></i>Quản lý Người dùng</h2>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="py-3">Tài khoản</th>
                            <th class="py-3">Họ và tên</th>
                            <th class="py-3">Email</th>
                            <th class="py-3">Vai trò</th>
                            <th class="py-3">Trạng thái</th>
                            <th class="text-end px-4 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-4 fw-bold text-muted">#<?php echo $user['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($user['avatar'])): ?>
                                        <img src="/<?php echo htmlspecialchars($user['avatar']); ?>" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2 text-success" style="width: 32px; height: 32px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['fullname'] ?? '-'); ?></td>
                            <td>
                                <?php echo htmlspecialchars($user['email'] ?? '-'); ?>
                                <?php if ($user['email'] && $user['email_verified_at']): ?>
                                    <i class="fas fa-check-circle text-success ms-1" title="Đã xác thực"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['role'] == 'admin'): ?>
                                    <span class="badge bg-danger rounded-pill">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill">User</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['status'] == 'active'): ?>
                                    <span class="badge bg-success rounded-pill">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark rounded-pill">Đã khóa</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end px-4">
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <?php if ($user['status'] == 'active'): ?>
                                        <a href="/AdminUser/toggleStatus/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-warning rounded-pill" onclick="return confirm('Khóa tài khoản này?');" title="Khóa tài khoản">
                                            <i class="fas fa-lock"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="/AdminUser/toggleStatus/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-success rounded-pill" onclick="return confirm('Mở khóa tài khoản này?');" title="Mở khóa">
                                            <i class="fas fa-unlock"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-light rounded-pill disabled"><i class="fas fa-lock"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
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
