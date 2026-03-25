## 1. OpenSpec And API Contract

- [x] 1.1 Hoàn thiện proposal, design và specs cho change `add-order-rest-api`
- [x] 1.2 Chốt contract endpoint `GET/POST/PUT/DELETE /api/order` và cấu trúc JSON phản hồi

## 2. API Backend Implementation

- [x] 2.1 Tạo `OrderApiController.php` để trả JSON cho danh sách, chi tiết, tạo mới, cập nhật và xóa đơn hàng
- [x] 2.2 Bổ sung hoặc điều chỉnh `OrderModel.php` để hỗ trợ dữ liệu cần cho API mà không lặp logic
- [x] 2.3 Cập nhật `index.php` để định tuyến `/api/order` theo HTTP method và ID

## 3. Client Integration And Verification

- [x] 3.1 Cập nhật tối thiểu giao diện đơn hàng để có thể tiêu thụ dữ liệu từ API
- [x] 3.2 Kiểm tra thủ công các endpoint và xử lý lỗi JSON cơ bản
- [x] 3.3 Chuẩn bị hướng dẫn chạy dự án và test Postman cho bài 5
