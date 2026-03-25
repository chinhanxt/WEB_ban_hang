# Order Management System (OMS) Specification

## 1. Overview
Hệ thống quản lý đơn hàng chuyên sâu (Order Management System) dành cho dự án webbanhang, đảm bảo tính toàn vẹn dữ liệu, chống gian lận và hỗ trợ mở rộng kiến trúc Event-Driven.

## 2. Main Features To Be Implemented

### 2.1. OMS Core (State Machine)
- Xây dựng lớp logic trung tâm để kiểm soát mọi chuyển trạng thái đơn hàng.
- Chặn mọi hành vi chuyển trạng thái sai quy tắc hoặc bỏ qua bước nghiệp vụ bắt buộc.
- Mọi cập nhật trạng thái phải đi qua Order Service để đảm bảo tính nhất quán.

### 2.2. Order Snapshot System
- Khi phát sinh sự kiện `ORDER_CREATED`, hệ thống sao chép dữ liệu sản phẩm vào `order_items`.
- Snapshot bao gồm tối thiểu: `product_id`, `product_name`, `product_image`, `original_price`, `sale_price`, `tax_amount`, `quantity`, `subtotal`.
- Sau khi đơn được tạo, thay đổi ở bảng `product` không được làm sai lệch dữ liệu lịch sử của đơn hàng.

### 2.3. Payment Gateway Integration
- Quản lý trạng thái thanh toán độc lập nhưng song song với trạng thái đơn hàng.
- Hỗ trợ các trạng thái thanh toán: `UNPAID`, `PENDING`, `PAID`, `FAILED`, `REFUND_PENDING`, `REFUNDED`.
- Tích hợp logic tự động kích hoạt quy trình hoàn tiền khi đơn đã thanh toán bị hủy hoặc đủ điều kiện refund.

### 2.4. Shipping Module
- Bổ sung các trường vận chuyển: `tracking_code`, `carrier`, `estimated_delivery`.
- Hệ thống cần có endpoint webhook để nhận cập nhật trạng thái giao hàng từ đơn vị vận chuyển.
- Dữ liệu webhook phải được xác thực trước khi cập nhật đơn hàng.

### 2.5. Return & Refund Workflow
- Quy trình hoàn trả/hoàn tiền gồm 4 bước: yêu cầu -> duyệt -> nhận hàng trả -> hoàn tiền.
- Người dùng phải cung cấp bằng chứng ảnh/video khi tạo yêu cầu trả hàng.
- Hệ thống phải lưu lý do trả hàng, bằng chứng đính kèm, người duyệt và thời điểm xử lý từng bước.

### 2.6. Audit Log & History
- Tự động ghi nhật ký mỗi khi đơn hàng thay đổi trạng thái hoặc dữ liệu quan trọng.
- Nhật ký phải lưu được `order_id`, hành động, `from_state`, `to_state`, `action_by`, lý do, timestamp.
- Lịch sử là dữ liệu bất biến, không cho phép admin/shop xóa hoặc sửa tay.

### 2.7. Review System
- Chỉ mở khóa tính năng đánh giá sản phẩm khi đơn hàng đạt trạng thái đủ điều kiện thực hiện review.
- Mỗi sản phẩm trong đơn chỉ được đánh giá bởi đúng người mua hợp lệ.
- Review phải liên kết ngược được với `order_id` và `order_item_id` để chống spam/fake review.

## 3. Order States & Transitions (State Machine)

### 3.1. States List
- `PENDING`: Chờ xử lý (Mặc định khi tạo đơn).
- `CONFIRMED`: Đã xác nhận (Shop đã kiểm kho).
- `PROCESSING`: Đang chuẩn bị hàng.
- `SHIPPING`: Đang giao hàng.
- `DELIVERED`: Giao hàng thành công.
- `COMPLETED`: Hoàn tất đơn hàng (User xác nhận hoặc tự động hoàn tất).
- `CANCELLED`: Đã hủy.
- `RETURN_REQUESTED`: Yêu cầu trả hàng/hoàn tiền.
- `RETURNED`: Đã nhận lại hàng trả.
- `REFUNDED`: Đã hoàn tiền.

### 3.2. Valid Transitions
- `PENDING` -> `CONFIRMED` | `CANCELLED`
- `CONFIRMED` -> `PROCESSING` | `CANCELLED`
- `PROCESSING` -> `SHIPPING`
- `SHIPPING` -> `DELIVERED` | `CANCELLED` (Trường hợp hoàn hàng do không liên lạc được)
- `DELIVERED` -> `COMPLETED` | `RETURN_REQUESTED`
- `RETURN_REQUESTED` -> `RETURNED` | `DELIVERED` (Từ chối trả hàng)
- `RETURNED` -> `REFUNDED`

## 4. Roles & Permissions

### 4.1. User (Customer)
- **Allowed:** 
    - Tạo đơn hàng.
    - Hủy đơn khi trạng thái là `PENDING` hoặc `CONFIRMED`.
    - Xác nhận `COMPLETED` khi nhận được hàng.
    - Yêu cầu `RETURN_REQUESTED` (trong vòng 7 ngày kể từ khi `DELIVERED`).
    - Đánh giá sản phẩm sau khi đơn đạt `COMPLETED`.
- **Forbidden:**
    - Hủy đơn khi đang `SHIPPING` hoặc muộn hơn.
    - Sửa thông tin đơn sau khi `CONFIRMED`.
    - Tự ý thay đổi trạng thái thanh toán sang `PAID`.

### 4.2. Admin / Shop
- **Allowed:**
    - Xác nhận đơn, cập nhật trạng thái `PROCESSING`, `SHIPPING`, `DELIVERED`.
    - Nhập và cập nhật thông tin vận chuyển (`tracking_code`, `carrier`, `estimated_delivery`).
    - Phê duyệt/Từ chối yêu cầu trả hàng.
    - Thực hiện lệnh hoàn tiền.
- **Forbidden:**
    - Chỉnh sửa nội dung đơn hàng (sản phẩm/giá) sau khi đã `CONFIRMED`.
    - Rollback trạng thái `COMPLETED`.
    - Xóa lịch sử (Audit Log).

## 5. Technical Requirements

### 5.1. Data Integrity (Immutability)
- **Order Snapshot:** Lưu snapshot sản phẩm tại thời điểm đặt hàng vào bảng `order_items`, không phụ thuộc dữ liệu động ở bảng `product`.
- **Audit Log:** Bảng `order_logs` ghi lại: `order_id`, `action`, `from_state`, `to_state`, `action_by`, `reason`, `metadata`, `timestamp`.

### 5.2. Payment & Shipping
- **Payment Status:** `UNPAID`, `PENDING`, `PAID`, `FAILED`, `REFUND_PENDING`, `REFUNDED`.
- **Parallel State Tracking:** Trạng thái thanh toán phải được quản lý độc lập với vòng đời đơn hàng.
- **Auto-Refund:** Nếu đơn hàng đã `PAID` mà bị `CANCELLED` hoặc được duyệt hoàn tiền -> trigger quy trình refund.
- **Shipping Integration:** Hỗ trợ lưu `carrier`, `tracking_code`, `estimated_delivery` và endpoint webhook cập nhật từ bên thứ 3.

### 5.3. Return, Review & Optimization
- **Return Workflow:** Lưu quy trình 4 bước gồm yêu cầu, duyệt, nhận hàng trả và hoàn tiền; bắt buộc có bằng chứng ảnh/video.
- **Review Unlock:** Chỉ cho phép review khi đơn ở trạng thái `COMPLETED` hoặc trạng thái nghiệp vụ được định nghĩa là hoàn tất.
- **Fraud Check:** Giới hạn 3 đơn hủy liên tiếp/ngày cho mỗi User. Cảnh báo địa chỉ rác hoặc spam COD.
- **Event-Driven:** Phát ra các event `OrderCreated`, `OrderStatusUpdated` để xử lý async (Gửi mail, thông báo, trừ kho).

## 6. Constraints & Business Rules
- Trạng thái `COMPLETED` là trạng thái cuối, không thể thay đổi.
- Mọi thay đổi dữ liệu đơn hàng phải thông qua Order Service để đảm bảo đi qua State Machine.
- Yêu cầu trả hàng phải đính kèm minh chứng (ảnh/video).
- Hệ thống không cho phép sửa snapshot sản phẩm trong `order_items` sau khi đơn đã tạo.
- Review chỉ hợp lệ nếu gắn với đơn hàng đã mua thật và đúng sản phẩm thuộc đơn đó.
