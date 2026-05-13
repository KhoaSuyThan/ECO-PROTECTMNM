<?php include 'app/shares/header.php'; ?>

<style>
    :root {
        --eco-green: #2d6a4f;
        --eco-dark: #1b4332;
        --eco-light: #d8f3dc;
        --soft-bg: #f8fbf9;
    }

    body { background-color: var(--soft-bg); }

    /* Breadcrumb tinh tế */
    .breadcrumb-item a { color: var(--eco-green); text-decoration: none; font-size: 0.9rem; }
    
    /* Container Sản phẩm */
    .product-detail-container {
        background: white;
        border-radius: 30px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.04);
        overflow: hidden;
        padding: 40px;
        margin-bottom: 50px;
    }

    /* Hình ảnh sản phẩm */
    .image-wrapper {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        background: #fdfdfd;
        border: 1px solid #f1f1f1;
        transition: 0.3s;
    }
    .image-wrapper:hover { transform: scale(1.02); }
    .product-img { width: 100%; height: auto; object-fit: cover; }

    /* Thông tin sản phẩm */
    .category-label {
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        font-size: 0.8rem;
        color: var(--eco-green);
        margin-bottom: 10px;
        display: block;
    }
    .product-title { font-size: 2.5rem; font-weight: 800; color: #1e272e; margin-bottom: 15px; }
    
    .price-box {
        background: #fff9f9;
        padding: 15px 25px;
        border-radius: 15px;
        display: inline-block;
        margin-bottom: 25px;
    }
    .price-value { font-size: 2rem; color: #d63031; font-weight: 800; margin: 0; }

    .description-text { font-size: 1.05rem; line-height: 1.8; color: #636e72; }

    /* Quantity Selector */
    .qty-input {
        width: 100px;
        border-radius: 10px;
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
        font-weight: bold;
    }

    /* Nút bấm */
    .btn-buy {
        background: var(--eco-green);
        color: white;
        border: none;
        padding: 15px 40px;
        border-radius: 15px;
        font-weight: 700;
        transition: 0.3s;
    }
    .btn-buy:hover { background: var(--eco-dark); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(45, 106, 79, 0.2); color: white;}

    /* Trust Badges */
    .trust-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px;
        border-radius: 12px;
        background: #fcfdfc;
        border: 1px dashed #ced4da;
    }
    .trust-badge i { color: var(--eco-green); font-size: 1.5rem; }
    .trust-badge span { font-size: 0.85rem; font-weight: 600; color: #2d3436; }
</style>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/Product/list">Sản phẩm</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product->name); ?></li>
        </ol>
    </nav>

    <div class="product-detail-container">
        <div class="row g-5">
            <!-- Cột trái: Hình ảnh -->
            <div class="col-lg-6">
                <div class="image-wrapper shadow-sm">
                    <?php if (!empty($product->image)): ?>
                        <img src="/<?php echo $product->image; ?>" class="product-img" alt="Sản phẩm">
                    <?php else: ?>
                        <div class="p-5 text-center bg-light">
                            <i class="fas fa-image fa-10x text-muted opacity-25"></i>
                            <p class="mt-3 text-muted">Chưa có hình ảnh sản phẩm</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cột phải: Thông tin -->
            <div class="col-lg-6">
                <span class="category-label"><?php echo htmlspecialchars($product->category_name ?? 'Eco Store'); ?></span>
                <h1 class="product-title"><?php echo htmlspecialchars($product->name); ?></h1>
                
                <div class="price-box">
                    <p class="price-value"><?php echo number_format($product->price, 0, ',', '.'); ?> <small>VNĐ</small></p>
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-leaf me-2 text-success"></i>Đặc điểm nổi bật:</h5>
                    <p class="description-text">
                        <?php echo nl2br(htmlspecialchars($product->description)); ?>
                    </p>
                </div>

                <hr class="my-4 opacity-50">

                <!-- Số lượng và Nút mua -->
                <div class="d-flex align-items-center gap-3 mb-5">
                    <div>
                        <label class="small fw-bold d-block mb-2">SỐ LƯỢNG</label>
                        <input type="number" value="1" min="1" class="form-control qty-input shadow-sm">
                    </div>
                    <div class="flex-grow-1 pt-4">
                        <button class="btn-buy w-100 shadow-sm">
                            <i class="fas fa-shopping-basket me-2"></i>THÊM VÀO GIỎ HÀNG
                        </button>
                    </div>
                </div>

                <!-- Trust Badges Section -->
                <div class="row g-3">
                    <div class="col-6">
                        <div class="trust-badge">
                            <i class="fas fa-shipping-fast"></i>
                            <span>Giao hàng xanh<br><small class="text-muted">Trong 24h</small></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="trust-badge">
                            <i class="fas fa-check-circle"></i>
                            <span>100% Tự nhiên<br><small class="text-muted">An toàn sức khỏe</small></span>
                        </div>
                    </div>
                </div>

                <!-- Quay lại -->
                <div class="mt-5">
                    <a href="/Product/list" class="text-decoration-none text-muted small fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> KHÁM PHÁ THÊM SẢN PHẨM KHÁC
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/shares/footer.php'; ?>