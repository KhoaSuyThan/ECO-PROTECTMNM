# ECO-PROTECTMNM

Dự án ECO-PROTECTMNM là một hệ thống website thương mại điện tử được xây dựng bằng kiến trúc MVC với PHP thuần và cơ sở dữ liệu MySQL. Hệ thống không chỉ cung cấp giao diện trực quan cho người dùng và quản trị viên mà còn hỗ trợ hệ thống API RESTful có xác thực bảo mật JWT.

## 🚀 Công nghệ sử dụng

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP (Kiến trúc MVC tuỳ chỉnh)
- **Database:** MySQL
- **Authentication:** JWT (JSON Web Tokens) cho API
- **API Testing:** Postman (Có sẵn file collection `EcoProtect.postman_collection.json`)

## 🎯 Tính năng nổi bật

### Dành cho Khách hàng (Client)
- Xem danh sách, tìm kiếm, xem chi tiết sản phẩm.
- Quản lý giỏ hàng (Thêm, sửa, xóa sản phẩm).
- Checkout, thanh toán và theo dõi lịch sử đơn hàng.
- Đăng ký, đăng nhập và quản lý hồ sơ cá nhân.
- Đổi mật khẩu, quên/đặt lại mật khẩu.

### Dành cho Quản trị viên (Admin)
- Quản lý danh mục sản phẩm (Category).
- Quản lý sản phẩm (Product - Thêm, sửa, xóa, quản lý hình ảnh).
- Quản lý tài khoản người dùng (User).
- Quản lý và theo dõi trạng thái đơn hàng (Order).

### API & Bảo mật
- Tích hợp RESTful API đầy đủ cho các chức năng cốt lõi (Auth, Cart, Category, Order, Payment, Product).
- Phân quyền và xác thực API bảo mật thông qua `JWTMiddleware` và `JWTHandler`.

## 📁 Cấu trúc thư mục chính

- **`app/`**: Chứa toàn bộ mã nguồn theo mô hình MVC.
  - **`controllers/`**: Các Controller xử lý logic giao diện web và các API Controller (`AuthApiController`, `CartApiController`,...).
  - **`models/`**: Lớp xử lý dữ liệu (ProductModel, UserModel, OrderModel,...).
  - **`views/`**: Chứa các file giao diện (`admin/`, `user/`, `product/`, `order/`,...).
  - **`utils/`**: Các file hỗ trợ tiện ích như xử lý JWT (`JWTHandler.php`, `JWTMiddleware.php`).
- **`uploads/`**: Nơi lưu trữ tài nguyên hình ảnh được tải lên.
- **`index.php`**: Entry point, đóng vai trò Router điều hướng toàn bộ request của ứng dụng.

