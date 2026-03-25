## ADDED Requirements

### Requirement: Order management views can consume REST data
Hệ thống SHALL cho phép màn hình quản lý đơn hàng lấy dữ liệu từ REST API JSON mà không bắt buộc phải render toàn bộ dữ liệu phía server.

#### Scenario: Trang danh sách đơn hàng tải dữ liệu qua API
- **WHEN** người dùng mở trang quản lý đơn hàng đã được cập nhật
- **THEN** trang có thể gọi endpoint `/api/order` để nhận dữ liệu JSON và hiển thị danh sách đơn hàng
