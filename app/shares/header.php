<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ECO-PROTECT STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 800; color: #2d6a4f !important; }
        
        /* Hiệu ứng mượt mà cho các nút trên Header */
        .nav-link {
            font-weight: 600;
            color: #4a4a4a !important;
            transition: color 0.2s ease;
        }
        .nav-link:hover {
            color: #2d6a4f !important;
        }
        /* Style riêng cho nút Thêm sản phẩm nhanh */
        .btn-add-quick {
            background-color: #2d6a4f;
            color: white !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-add-quick:hover {
            background-color: #1b4332;
            box-shadow: 0 4px 12px rgba(45, 106, 79, 0.2);
        }
    </style>
</head>
<body>

<!-- Flash Message Toast -->
<?php if (isset($_SESSION['success'])): ?>
<div class="position-fixed p-3" style="z-index: 9999; top: 90px; right: 20px;">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0 show shadow-lg rounded-4" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-bold px-3 py-2">
                <i class="fas fa-check-circle me-2 fs-5 align-middle"></i>
                <span class="align-middle">
                    <?php 
                        echo htmlspecialchars($_SESSION['success']); 
                        unset($_SESSION['success']); 
                    ?>
                </span>
            </div>
            <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close" onclick="this.closest('.toast').remove()"></button>
        </div>
    </div>
</div>
<script>
    setTimeout(function() {
        var toastEl = document.getElementById('successToast');
        if (toastEl) {
            toastEl.style.transition = 'all 0.5s ease-out';
            toastEl.style.opacity = '0';
            toastEl.style.transform = 'translateY(-10px)';
            setTimeout(() => toastEl.remove(), 500);
        }
    }, 3000);
</script>
<?php endif; ?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 py-3">
    <div class="container">
        <a class="navbar-brand fs-4" href="/Product/list">🌿 ECO-PROTECT</a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavNav" aria-controls="navbarNavNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNavNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2 mt-3 mt-lg-0">
                <li class="nav-item">
                    <a class="nav-link px-3" href="/Product/list">
                        <i class="fas fa-box-open me-2 text-success"></i>Sản phẩm
                    </a>
                </li>
                
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link px-3" href="/Category/list">
                        <i class="fas fa-tags me-2 text-warning"></i>Danh mục
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <?php 
                        // Tính số loại sản phẩm khác nhau đang có trong giỏ hàng session
                        $total_items = 0;
                        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                            $total_items = count($_SESSION['cart']);
                        }
                    ?>
                    <a class="nav-link px-3 position-relative" href="/Product/cart" title="Xem giỏ hàng">
                        <i class="fas fa-shopping-cart me-2 text-info fs-5"></i>Giỏ hàng
                        <?php if ($total_items > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                                <?php echo $total_items; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>

                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-add-quick rounded-pill px-4 shadow-sm" href="/Product/add">
                        <i class="fas fa-plus-circle me-1"></i> Thêm sản phẩm
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item ms-lg-2 border-start ps-lg-3">
                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-light rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle text-success me-1"></i> 
                                <strong><?php echo htmlspecialchars($_SESSION['user']['username']); ?></strong>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2 rounded-3">
                                <li><a class="dropdown-item text-danger" href="/User/logout"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="/User/login" class="btn btn-outline-success rounded-pill px-4">Đăng nhập</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>