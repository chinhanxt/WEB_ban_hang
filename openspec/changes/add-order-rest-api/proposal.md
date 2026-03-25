## Why

Dự án hiện đã có nghiệp vụ quản lý đơn hàng khá đầy đủ nhưng vẫn chủ yếu phục vụ giao diện PHP render trực tiếp. Bài 5 yêu cầu triển khai RESTful API với CRUD, router theo HTTP method và khả năng kiểm thử bằng Postman, nên cần bổ sung một lớp API riêng cho tài nguyên đơn hàng thay vì tiếp tục gắn chặt dữ liệu với view.

## What Changes

- Bổ sung RESTful API cho tài nguyên `order` với các thao tác `GET`, `POST`, `PUT`, `DELETE`.
- Thêm controller API trả JSON, tái sử dụng nghiệp vụ sẵn có trong `OrderModel`.
- Mở rộng router hiện tại để nhận nhánh `/api/order` và ánh xạ request theo `REQUEST_METHOD`.
- Cho phép màn hình đơn hàng sử dụng API để lấy dữ liệu thay vì chỉ phụ thuộc vào render server-side.
- Chuẩn hóa mã phản hồi và thông báo lỗi JSON để thuận tiện kiểm thử bằng Postman.

## Capabilities

### New Capabilities
- `order-rest-api`: Cung cấp RESTful API cho đơn hàng, bao gồm danh sách, chi tiết, tạo mới, cập nhật và xóa đơn hàng.

### Modified Capabilities
- `order-management-spec`: Mở rộng quản lý đơn hàng để hỗ trợ truy cập dữ liệu qua API JSON bên cạnh giao diện HTML hiện có.

## Impact

- Affected code: `index.php`, `app/controllers/OrderController.php`, `app/models/OrderModel.php`, view đơn hàng và controller API mới.
- APIs: thêm các endpoint `/api/order` và `/api/order/{id}`.
- Testing: cần kiểm thử bằng Postman cho đủ các phương thức CRUD và trường hợp lỗi.
