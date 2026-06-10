<?php 
/** @var stdClass $product */
include 'app/shares/header.php'; 
?>

<style>
    :root {
        --eco-green: #2d6a4f;
        --eco-dark: #1b4332;
        --eco-light: #d8f3dc;
        --soft-bg: #f4f7f5;
    }

    body { background-color: var(--soft-bg); }

    /* Breadcrumb */
    .breadcrumb-item a { color: var(--eco-green); text-decoration: none; font-size: 0.85rem; font-weight: 600; }
    
    /* Main Card Container */
    .product-detail-container {
        background: white;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        overflow: hidden;
        padding: 30px;
        margin-bottom: 40px;
        max-width: 1000px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Product Image Wrapper */
    .image-wrapper {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        background: #fdfdfd;
        border: 1px solid #f1f1f1;
        height: 380px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .product-img { 
        max-width: 100%; 
        max-height: 100%; 
        object-fit: contain; 
        padding: 10px;
    }

    /* Content Styling */
    .category-label {
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        font-size: 0.75rem;
        color: var(--eco-green);
        margin-bottom: 8px;
        display: block;
    }
    
    .product-title { 
        font-size: 1.8rem; 
        font-weight: 800; 
        color: #2d3748; 
        margin-bottom: 12px; 
        line-height: 1.3;
    }
    
    .price-box {
        background: #f7fafc;
        padding: 10px 20px;
        border-radius: 12px;
        display: inline-block;
        margin-bottom: 20px;
        border: 1px solid #edf2f7;
    }
    
    .price-value { 
        font-size: 1.6rem; 
        color: #e53e3e; 
        font-weight: 800; 
        margin: 0; 
    }

    .description-text { 
        font-size: 0.95rem; 
        line-height: 1.7; 
        color: #4a5568; 
    }

    /* Quantity and Form Styles */
    .qty-input-container {
        height: 42px;
        padding: 0 6px;
        background: #f7fafc;
        border-radius: 50px;
        border: 1px solid #edf2f7;
        display: inline-flex;
        align-items: center;
    }

    .qty-btn {
        width: 30px;
        height: 34px;
        border: none;
        background: transparent;
        color: #718096;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    .qty-btn:hover { color: var(--eco-green); }

    .qty-input-field {
        width: 40px;
        height: 34px;
        border: none;
        background: transparent;
        text-align: center;
        font-weight: 700;
        color: #2d3748;
        font-size: 1rem;
    }
    .qty-input-field:focus { outline: none; }
    
    /* Remove Spin Buttons */
    input[type=number].qty-input-field::-webkit-inner-spin-button,
    input[type=number].qty-input-field::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Buy button */
    .btn-buy {
        background: var(--eco-green);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        transition: all 0.3s;
    }
    .btn-buy:hover { 
        background: var(--eco-dark); 
        box-shadow: 0 6px 15px rgba(45, 106, 79, 0.2); 
        color: white;
    }

    /* Trust Badge */
    .trust-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        border-radius: 12px;
        background: #fcfdfc;
        border: 1px dashed #ced4da;
    }
    .trust-badge i { color: var(--eco-green); font-size: 1.2rem; }
    .trust-badge span { font-size: 0.8rem; font-weight: 600; color: #4a5568; }

    /* Admin Action panel */
    .admin-detail-panel {
        background: #fffaf0;
        border: 1px solid #feebc8;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 25px;
    }
</style>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <div class="max-width-container d-flex justify-content-between align-items-center mb-3" style="max-width: 1000px; margin: 0 auto;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/Product/list">Sản phẩm</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product->name); ?></li>
            </ol>
        </nav>
        
        <!-- Back Link -->
        <a href="/Product/list" class="text-decoration-none text-muted small fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <div class="product-detail-container">
        <!-- Admin quick panel if logged in -->
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
            <div class="admin-detail-panel d-flex justify-content-between align-items-center">
                <span class="text-warning-dark fw-bold"><i class="fas fa-user-shield me-2"></i>Chế độ quản trị viên</span>
                <div class="d-flex gap-2">
                    <a href="/Product/edit/<?php echo $product->id; ?>" class="btn btn-sm btn-outline-success rounded-pill px-3">
                        <i class="fas fa-pen me-1"></i> Chỉnh sửa
                    </a>
                    <a href="/Product/delete/<?php echo $product->id; ?>" 
                       class="btn btn-sm btn-outline-danger rounded-pill px-3"
                       onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                        <i class="fas fa-trash-alt me-1"></i> Xóa bỏ
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Cột trái: Hình ảnh giới hạn chiều cao -->
            <div class="col-md-5">
                <div class="image-wrapper shadow-sm">
                    <?php if (!empty($product->image)): ?>
                        <img src="/<?php echo $product->image; ?>" class="product-img" alt="<?php echo htmlspecialchars($product->name); ?>">
                    <?php else: ?>
                        <div class="p-5 text-center bg-light w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-image fa-5x text-muted opacity-25"></i>
                            <p class="mt-2 text-muted small">Chưa có hình ảnh</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cột phải: Thông tin gọn gàng -->
            <div class="col-md-7 d-flex flex-column justify-content-between">
                <div>
                    <span class="category-label"><?php echo htmlspecialchars($product->category_name ?? 'Eco Store'); ?></span>
                    <h1 class="product-title"><?php echo htmlspecialchars($product->name); ?></h1>
                    
                    <div class="price-box">
                        <p class="price-value"><?php echo number_format($product->price, 0, ',', '.'); ?> <small>đ</small></p>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2"><i class="fas fa-leaf me-2 text-success"></i>Đặc điểm nổi bật:</h6>
                        <p class="description-text mb-0">
                            <?php echo nl2br(htmlspecialchars($product->description ?? '')); ?>
                        </p>
                    </div>
                </div>

                <div>
                    <hr class="my-3 opacity-50">

                    <!-- Form Thêm vào giỏ hàng thực tế -->
                    <form id="addToCartForm" class="d-flex align-items-center gap-3 mb-4">
                        <div>
                            <span class="small fw-bold d-block mb-1 text-muted">SỐ LƯỢNG</span>
                            <div class="qty-input-container">
                                <button type="button" class="qty-btn" onclick="let input = this.nextElementSibling; if(input.value > 1) input.value--;"><i class="fas fa-minus" style="font-size: 9px;"></i></button>
                                <input type="number" id="productQuantity" name="quantity" value="1" min="1" class="qty-input-field qty-input">
                                <button type="button" class="qty-btn" onclick="this.previousElementSibling.value++;"><i class="fas fa-plus" style="font-size: 9px;"></i></button>
                            </div>
                        </div>
                        <div class="flex-grow-1 pt-4">
                            <button type="submit" class="btn-buy w-100 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="fas fa-shopping-basket"></i> THÊM VÀO GIỎ HÀNG
                            </button>
                        </div>
                    </form>

                    <!-- Trust Badges Section -->
                    <div class="row g-2">
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
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const product_id = <?php echo (int)$product->id; ?>;
    const addToCartForm = document.getElementById('addToCartForm');
    const deleteBtn = document.querySelector('.btn-outline-danger');

    // Handle add to cart
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const token = localStorage.getItem('jwtToken');
            if (!token) {
                alert('Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.');
                window.location.href = '/User/login';
                return;
            }

            const quantity = parseInt(document.getElementById('productQuantity').value) || 1;

            fetch('/api/cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({
                    product_id: product_id,
                    quantity: quantity
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.message || 'Lỗi thêm vào giỏ hàng'); });
                }
                return response.json();
            })
            .then(data => {
                alert(data.message || 'Đã thêm sản phẩm vào giỏ hàng thành công!');
                // Chuyển hướng hoặc ở lại
                window.location.href = '/Product/cart';
            })
            .catch(error => {
                alert(error.message);
                console.error('Lỗi Cart API:', error);
            });
        });
    }

    // Handle delete via API if Admin
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;

            const token = localStorage.getItem('jwtToken');
            if (!token) {
                alert('Hết hạn phiên đăng nhập. Vui lòng đăng nhập lại.');
                window.location.href = '/User/login';
                return;
            }

            fetch('/api/product/' + product_id, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.message || 'Lỗi xóa sản phẩm'); });
                }
                return response.json();
            })
            .then(data => {
                alert(data.message || 'Xóa sản phẩm thành công!');
                window.location.href = '/Product/list';
            })
            .catch(error => {
                alert(error.message);
                console.error('Lỗi xóa sản phẩm API:', error);
            });
        });
    }
});
</script>

<?php include 'app/shares/footer.php'; ?>