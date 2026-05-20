<?php include 'app/shares/header.php'; ?>

<style>
    :root { 
        --eco-green: #2d6a4f; 
        --eco-dark: #1b4332;
        --eco-light: #d8f3dc; 
        --soft-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }

    body { background-color: #fcfdfc; }

    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, var(--eco-green), var(--eco-dark));
        border-radius: 24px;
        padding: 50px 40px;
        color: white;
        margin-bottom: 50px;
        position: relative;
        overflow: hidden;
    }

    /* Product Card */
    .product-card {
        border: none;
        border-radius: 20px;
        background: #fff;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: var(--soft-shadow);
        height: 100%;
    }
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    }

    /* Image Styling */
    .card-img-container {
        position: relative;
        height: 220px;
        border-radius: 20px 20px 0 0;
        overflow: hidden;
    }
    .card-img-top {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .eco-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: rgba(216, 243, 220, 0.9);
        color: var(--eco-green);
        backdrop-filter: blur(8px);
        font-weight: 600;
        font-size: 0.75rem;
        padding: 6px 14px;
        border-radius: 50px;
        z-index: 2;
    }

    /* Content Styling */
    .product-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #2d3436;
        margin-bottom: 15px;
        height: 2.8rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .price-label { font-size: 0.85rem; color: #636e72; margin-bottom: 2px; }
    .price-value { font-size: 1.4rem; color: #1e272e; font-weight: 800; }

    /* Cart Button */
    .btn-cart {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: var(--eco-green);
        color: white;
        border: none;
        transition: 0.3s;
    }
    .btn-cart:hover { background-color: var(--eco-dark); transform: scale(1.1); }

    /* Action Buttons Footer */
    .card-footer-actions {
        background: transparent;
        border-top: 1px solid #f1f2f6;
        padding: 20px;
    }
    .btn-view-detail {
        background-color: var(--eco-green);
        color: white;
        border-radius: 12px;
        font-weight: 600;
        padding: 8px 20px;
        border: none;
        transition: 0.3s;
    }
    .btn-view-detail:hover { background-color: var(--eco-dark); color: white; }

    .btn-edit-outline {
        border: 2px solid var(--eco-light);
        color: var(--eco-green);
        border-radius: 12px;
        font-weight: 600;
        padding: 8px 15px;
        transition: 0.3s;
    }
    .btn-edit-outline:hover { background-color: var(--eco-light); color: var(--eco-green); }

    .delete-link {
        font-size: 0.85rem;
        color: #b2bec3;
        transition: 0.3s;
    }
    .delete-link:hover { color: #d63031; }
</style>

<div class="container mt-4">
    <!-- Hero Section -->
    <div class="hero-section shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="text-center text-md-start mb-4 mb-md-0">
            <h1 class="display-5 fw-bold mb-2">Sản Phẩm Xanh</h1>
            <p class="lead opacity-75 mb-0">Cùng chúng tôi lan tỏa lối sống bền vững.</p>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="row g-4 mb-5">
        <?php if (empty($products)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-seedling fa-4x text-muted opacity-25 mb-3"></i>
                <h3 class="text-secondary">Chưa có sản phẩm nào.</h3>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
            <div class="col-sm-6 col-lg-3">
                <div class="card product-card">
                    <div class="card-img-container">
                        <!-- Category Badge -->
                        <span class="eco-badge">
                            <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($product->category_name ?? 'Eco'); ?>
                        </span>

                        <?php if (!empty($product->image)): ?>
                            <img src="/<?php echo $product->image; ?>" class="card-img-top" alt="Product Image">
                        <?php else: ?>
                            <div class="bg-light h-100 w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-image fa-3x text-muted opacity-20"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body p-4 pb-0">
                        <h5 class="product-title">
                            <?php echo htmlspecialchars($product->name); ?>
                        </h5>
                        
                        <div class="d-flex justify-content-between align-items-end mb-4">
                            <div>
                                <div class="price-label">Giá đóng góp</div>
                                <div class="price-value"><?php echo number_format((float)$product->price, 0, ',', '.'); ?> <small>đ</small></div>
                            </div>
                            <!-- Cart Icon Button -->
                            <a href="/Product/addToCart/<?php echo $product->id; ?>" class="btn-cart shadow-sm d-flex align-items-center justify-content-center text-decoration-none">
                                <i class="fas fa-shopping-basket"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Footer Actions: Edit and View Detail -->
                    <div class="card-footer-actions">
                        <div class="d-flex gap-2 mb-3">
                            <a href="/Product/show/<?php echo $product->id; ?>" class="btn-view-detail flex-grow-1 text-center text-decoration-none shadow-sm">
                                <i class="fas fa-eye me-1"></i> Chi tiết
                            </a>
                            <a href="/Product/edit/<?php echo $product->id; ?>" class="btn-edit-outline text-decoration-none">
                                <i class="fas fa-pen"></i>
                            </a>
                        </div>
                        
                        <!-- Delete Option -->
                        <div class="text-center border-top pt-3">
                            <a href="/Product/delete/<?php echo $product->id; ?>" 
                               class="delete-link text-decoration-none" 
                               onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                <i class="fas fa-trash-alt me-1"></i> Xóa sản phẩm
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'app/shares/footer.php'; ?>