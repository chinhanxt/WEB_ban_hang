# Specification: Authentication & Authorization System (Auth-Spec)

## 1. Tổng quan hệ thống
Hệ thống quản lý người dùng, đăng nhập, đăng ký và phân quyền (RBAC) cho dự án WebBanHang. Hệ thống chia làm 3 đối tượng chính: **Admin**, **User (Thành viên)**, và **Guest (Khách)**.

## 2. Phân quyền & Vai trò (Roles)

### 2.1. Admin (Quản trị viên)
- **Quyền hạn:** Toàn quyền (Full Access).
- **Giao diện:** Thấy tất cả các nút Thêm, Sửa, Xóa, Quản lý đơn hàng.
- **Backend:** Được phép truy cập tất cả các API/Controller quản trị.

### 2.2. User (Người dùng đã đăng nhập)
- **Quyền hạn:** Xem sản phẩm, Quản lý giỏ hàng, Đặt hàng, Xem lịch sử đơn hàng cá nhân.
- **Hạn chế giao diện:**
    - **Trang chủ/Danh sách sản phẩm:** Ẩn hoàn toàn các nút "Thêm", "Sửa", "Xóa".
    - **Trang danh sách đơn hàng:** Ẩn cột "Hành động" (Action), chỉ được xem trạng thái.
- **Hạn chế Backend:** Bị chặn truy cập vào các hàm `add`, `edit`, `delete`, `update` trong tất cả các Controller.

### 2.3. Guest (Khách chưa đăng nhập)
- **Quyền hạn:** Chỉ được **Xem** (Read-only).
- **Hành vi nghiệp vụ:** 
    - Khi bấm "Thêm vào giỏ hàng" hoặc "Mua ngay": Hệ thống hiển thị Popup thông báo yêu cầu đăng nhập và tự động chuyển hướng sang trang Login/Register.
    - Không thể truy cập trang Thanh toán (Checkout) hoặc Lịch sử đơn hàng.

## 3. Quy tắc Xác thực & Kiểm tra dữ liệu (Validation)

### 3.1. Đăng ký (Register)
- **Username:** Bắt buộc > 10 ký tự.
- **Email:** Phải đúng định dạng (Sử dụng Regex: `/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/`).
- **Password (3 Quy tắc chuẩn):**
    1. Độ dài tối thiểu 8 ký tự.
    2. Có ít nhất 1 chữ hoa và 1 chữ thường.
    3. Có ít nhất 1 chữ số hoặc ký tự đặc biệt.
- **Database:** Password phải được mã hóa bằng `password_hash()` trước khi lưu.

### 3.2. Đăng nhập (Login)
- Sử dụng **Email** và **Password**.
- Kiểm tra bằng `password_verify()`.

## 4. Trải nghiệm người dùng (UX/UI)
- **Thông báo:** Sử dụng **SweetAlert2** cho tất cả các thông báo thành công/thất bại.
- **Trạng thái Loading:** 
    - Khi submit form: Disable nút Submit để tránh spam.
    - Hiển thị hiệu ứng loading (spinner hoặc text "Đang xử lý...").
- **Error Handling:** Thông báo lỗi chi tiết cho từng trường hợp (Ví dụ: "Mật khẩu chưa đủ mạnh", "Email đã tồn tại", "Tài khoản không chính xác").

## 5. Kiến trúc Bảo mật (Security & Middleware)

### 5.1. Guard/Middleware (Backend)
- Xây dựng cơ chế kiểm tra Session và Role ở mức Controller.
- **AuthGuard:** Chặn các trang yêu cầu đăng nhập.
- **AdminGuard:** Chặn các hành động quản trị (Thêm/Sửa/Xóa).
- *Nguyên tắc:* Nếu vi phạm, redirect về trang chủ kèm thông báo "Permission Denied".

### 5.2. Database Schema (Bảng `account`)
- Thêm cột `Email` (UNIQUE).
- Cập nhật độ dài `Password` lên 255 ký tự.
- Cột `Role` mặc định là `user`.

## 6. Luồng nghiệp vụ đặc biệt
- **Guest-to-Login:** Khi khách thực hiện hành động mua hàng, hệ thống phải giữ được ngữ cảnh (context) hoặc thông báo rõ ràng lý do yêu cầu đăng nhập trước khi chuyển hướng.
