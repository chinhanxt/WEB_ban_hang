## Context

Ứng dụng hiện dùng router MVC đơn giản trong `index.php`, gọi các controller dạng `ProductController`, `OrderController` và render HTML bằng `include`. Trong khi đó, `OrderModel` đã chứa hầu hết nghiệp vụ đơn hàng như tạo đơn, đọc chi tiết, đổi trạng thái, cập nhật thanh toán, cập nhật vận chuyển và xóa đơn. Thay đổi này cần thêm API theo đúng tinh thần bài 5 nhưng không nên phá vỡ giao diện HTML đang hoạt động.

## Goals / Non-Goals

**Goals:**
- Thêm lớp API riêng cho đơn hàng mà vẫn tái sử dụng `OrderModel`.
- Hỗ trợ đúng các thao tác CRUD cơ bản cho `order` bằng JSON.
- Cập nhật router để nhận `/api/order` và ánh xạ theo `GET`, `POST`, `PUT`, `DELETE`.
- Giữ nguyên các luồng HTML hiện có để tránh hồi quy các chức năng đang dùng.

**Non-Goals:**
- Không chuyển toàn bộ website sang SPA.
- Không thay đổi sâu schema đơn hàng ngoài các phần thực sự cần cho API.
- Không viết lại toàn bộ `OrderController` hiện tại sang API-first.

## Decisions

### 1. Tạo `OrderApiController` riêng thay vì tái sử dụng `OrderController`
- Rationale: `OrderController` hiện phụ thuộc `include`, `header("Location: ...")`, flash session và form POST HTML. API cần `application/json`, `php://input`, HTTP status code và payload JSON rõ ràng.
- Alternatives considered:
  - Gộp thêm nhánh JSON vào `OrderController`: ít file hơn nhưng làm controller trở nên lẫn lộn giữa HTML và API.
  - Viết API hoàn toàn tách rời model hiện có: sạch hơn nhưng lặp logic và tăng rủi ro sai khác nghiệp vụ.

### 2. Ánh xạ CRUD tối thiểu lên nghiệp vụ đơn hàng sẵn có
- `GET /api/order` dùng để lấy danh sách đơn hàng.
- `GET /api/order/{id}` dùng để lấy chi tiết một đơn, gồm thông tin đơn và các dòng hàng.
- `POST /api/order` dùng `createOrder()` để tạo đơn hàng mới.
- `PUT /api/order/{id}` ưu tiên cập nhật trạng thái, thanh toán hoặc vận chuyển tùy trường JSON được gửi lên.
- `DELETE /api/order/{id}` dùng `deleteOrder()`.
- Rationale: cách này vừa đúng bài 5 vừa bám cấu trúc `OrderModel`.
- Alternatives considered:
  - Chia nhỏ thêm `/status`, `/payment`, `/shipping`: hợp nghiệp vụ hơn nhưng lệch khỏi khung CRUD cơ bản của bài thực hành.

### 3. Giữ router hiện tại và thêm nhánh `/api`
- Rationale: ít phá vỡ nhất vì các route HTML cũ vẫn tiếp tục chạy như trước.
- Alternatives considered:
  - Viết lại router hoàn toàn theo front controller mới: sạch hơn nhưng phạm vi thay đổi quá lớn so với mục tiêu bài 5.

### 4. Bổ sung helper trong `OrderModel` khi cần để phục vụ API
- Rationale: API tạo đơn cần một cách chuẩn để chuẩn hóa dữ liệu đầu vào và trả ra cấu trúc nhất quán, nhưng nên tận dụng tối đa model hiện có.
- Alternatives considered:
  - Xử lý tất cả trong controller API: nhanh nhưng khiến controller phình to và khó kiểm thử.

## Risks / Trade-offs

- [PUT cho đơn hàng không tự nhiên như sản phẩm] -> Chỉ hỗ trợ cập nhật các trường được phép và ghi rõ hợp đồng payload trong tài liệu test Postman.
- [Dữ liệu model hiện dùng key kiểu `Id`, `Name`, `Created_at`] -> API cần thống nhất đầu ra để client dễ dùng, hoặc ít nhất phải giữ cấu trúc nhất quán giữa các endpoint.
- [Router hiện tại chưa sanitize riêng cho `/api`] -> Bổ sung nhánh API cẩn thận, tránh làm hỏng route HTML cũ.
- [View hiện có chủ yếu render server-side] -> Chỉ chuyển phần cần thiết sang `fetch()` ở mức tối thiểu để đáp ứng bài mà không tạo hồi quy lớn.
