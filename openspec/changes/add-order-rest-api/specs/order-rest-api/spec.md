## ADDED Requirements

### Requirement: Order list endpoint
Hệ thống SHALL cung cấp endpoint `GET /api/order` để trả về danh sách đơn hàng dưới dạng JSON.

#### Scenario: Lấy danh sách đơn hàng thành công
- **WHEN** client gửi request `GET` tới `/api/order`
- **THEN** hệ thống trả về mã `200` và một mảng JSON chứa các đơn hàng

### Requirement: Order detail endpoint
Hệ thống SHALL cung cấp endpoint `GET /api/order/{id}` để trả về thông tin chi tiết của một đơn hàng dưới dạng JSON.

#### Scenario: Lấy chi tiết đơn hàng hợp lệ
- **WHEN** client gửi request `GET` tới `/api/order/15` với `15` là ID tồn tại
- **THEN** hệ thống trả về mã `200` và JSON chứa thông tin đơn hàng cùng các dòng hàng liên quan

#### Scenario: Không tìm thấy đơn hàng
- **WHEN** client gửi request `GET` tới `/api/order/{id}` với ID không tồn tại
- **THEN** hệ thống trả về mã `404` và JSON thông báo không tìm thấy đơn hàng

### Requirement: Order creation endpoint
Hệ thống SHALL cung cấp endpoint `POST /api/order` để tạo đơn hàng mới từ payload JSON hợp lệ.

#### Scenario: Tạo đơn hàng thành công
- **WHEN** client gửi request `POST` tới `/api/order` với đầy đủ thông tin đơn và danh sách sản phẩm hợp lệ
- **THEN** hệ thống trả về mã `201` và JSON xác nhận tạo đơn thành công kèm ID đơn hàng

#### Scenario: Tạo đơn hàng thất bại do thiếu dữ liệu
- **WHEN** client gửi request `POST` tới `/api/order` với payload thiếu trường bắt buộc
- **THEN** hệ thống trả về mã `400` và JSON mô tả lỗi xác thực

### Requirement: Order update endpoint
Hệ thống SHALL cung cấp endpoint `PUT /api/order/{id}` để cập nhật dữ liệu đơn hàng cho các trường được hỗ trợ.

#### Scenario: Cập nhật trạng thái hoặc thông tin đơn thành công
- **WHEN** client gửi request `PUT` tới `/api/order/{id}` với payload hợp lệ
- **THEN** hệ thống trả về mã `200` và JSON xác nhận cập nhật thành công

#### Scenario: Cập nhật đơn hàng không hợp lệ
- **WHEN** client gửi request `PUT` tới `/api/order/{id}` với payload không hợp lệ hoặc ID không tồn tại
- **THEN** hệ thống trả về mã `400` hoặc `404` cùng JSON mô tả lỗi

### Requirement: Order deletion endpoint
Hệ thống SHALL cung cấp endpoint `DELETE /api/order/{id}` để xóa đơn hàng.

#### Scenario: Xóa đơn hàng thành công
- **WHEN** client gửi request `DELETE` tới `/api/order/{id}` với ID tồn tại
- **THEN** hệ thống trả về mã `200` và JSON xác nhận xóa thành công

#### Scenario: Xóa đơn hàng không tồn tại
- **WHEN** client gửi request `DELETE` tới `/api/order/{id}` với ID không tồn tại
- **THEN** hệ thống trả về mã `404` và JSON thông báo không tìm thấy đơn hàng
