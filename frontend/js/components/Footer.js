/**
 * ECO-PROTECT Footer Component
 */

export function renderFooter() {
    const container = document.getElementById('footer-container');
    if (!container) return;

    container.innerHTML = `
        <footer class="footer">
            <div class="footer-grid">
                <div class="footer-info-col">
                    <div class="footer-logo">
                        <i class="fa-solid fa-leaf"></i> ECO-PROTECT
                    </div>
                    <p style="margin-bottom: 1.5rem;">Cung cấp các giải pháp và sản phẩm thân thiện với môi trường, góp phần bảo vệ hành tinh xanh của chúng ta.</p>
                    <div style="display: flex; gap: 1rem; font-size: 1.25rem;">
                        <a href="#"><i class="fa-brands fa-facebook"></i></a>
                        <a href="#"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>
                <div>
                    <h3 class="footer-title">Khám phá</h3>
                    <ul class="footer-links">
                        <li><a href="#home">Cửa hàng</a></li>
                        <li><a href="#about">Về chúng tôi</a></li>
                        <li><a href="#blog">Blog Xanh</a></li>
                        <li><a href="#contact">Liên hệ</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">Chính sách</h3>
                    <ul class="footer-links">
                        <li><a href="#terms">Điều khoản sử dụng</a></li>
                        <li><a href="#privacy">Chính sách bảo mật</a></li>
                        <li><a href="#shipping">Vận chuyển & Giao hàng</a></li>
                        <li><a href="#refund">Chính sách đổi trả</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">Nhận tin tức</h3>
                    <p style="margin-bottom: 1rem;">Đăng ký nhận thông tin khuyến mãi và bài viết môi trường mới nhất.</p>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="email" placeholder="Email của bạn..." class="form-control" style="background-color: #1a221d; border-color: #2b302d; color: white;">
                        <button class="btn btn-primary" style="padding: 0.75rem;"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; ${new Date().getFullYear()} ECO-PROTECT STORE. Tất cả các quyền được bảo lưu.</p>
            </div>
        </footer>
    `;
}
