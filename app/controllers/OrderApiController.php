<?php

require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/ProductModel.php';

class OrderApiController
{
    private $orderModel;
    private $productModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);
        $this->productModel = new ProductModel($this->db);
    }

    private function currentUserId(): ?int
    {
        return isset($_SESSION['user']->id) ? (int)$_SESSION['user']->id : null;
    }

    private function respond($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function requireApiLogin(): ?int
    {
        $accountId = $this->currentUserId();
        if (!$accountId) {
            $this->respond(['message' => 'Vui lòng đăng nhập để sử dụng API đơn hàng.'], 401);
            return null;
        }

        return $accountId;
    }

    private function requireApiAdmin(): ?int
    {
        if (!isAdmin()) {
            $this->respond(['message' => 'Bạn không có quyền sử dụng chức năng API này.'], 403);
            return null;
        }

        return $this->currentUserId();
    }

    private function canAccessOrder(array $order): bool
    {
        if (isAdmin()) {
            return true;
        }

        $accountId = $this->currentUserId();
        return $accountId !== null && $this->orderModel->userOwnsOrder((int)$order['Id'], $accountId);
    }

    private function buildOrderItems(array $items): array
    {
        $orderItems = [];

        foreach ($items as $item) {
            $productId = (int)($item['product_id'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 1);

            if ($productId <= 0 || $quantity <= 0) {
                throw new RuntimeException('Danh sách sản phẩm không hợp lệ.');
            }

            $product = $this->productModel->getProductById($productId);
            if (!$product) {
                throw new RuntimeException('Không tìm thấy sản phẩm #' . $productId . '.');
            }

            $price = (float)$product->Price;
            $orderItems[] = [
                'product_id' => $productId,
                'product_name' => $product->Name,
                'product_image' => $product->Image,
                'original_price' => $price,
                'sale_price' => $price,
                'tax_amount' => 0,
                'quantity' => $quantity,
                'subtotal' => $price * $quantity,
            ];
        }

        return $orderItems;
    }

    public function index(): void
    {
        $accountId = $this->requireApiLogin();
        if ($accountId === null) {
            return;
        }

        $phone = isset($_GET['phone']) ? trim((string)$_GET['phone']) : '';
        if (isAdmin()) {
            $orders = $phone !== ''
                ? $this->orderModel->getOrdersByPhone($phone)
                : $this->orderModel->getAllOrders();
        } else {
            $orders = $this->orderModel->getOrdersByAccountId($accountId);
            if ($phone !== '') {
                $orders = array_values(array_filter($orders, function ($order) use ($phone) {
                    return isset($order['Phone']) && $order['Phone'] === $phone;
                }));
            }
        }

        $this->respond($orders);
    }

    public function show($id): void
    {
        $accountId = $this->requireApiLogin();
        if ($accountId === null) {
            return;
        }

        $order = $this->orderModel->getOrderById((int)$id);
        if (!$order) {
            $this->respond(['message' => 'Không tìm thấy đơn hàng.'], 404);
            return;
        }

        if (!$this->canAccessOrder($order)) {
            $this->respond(['message' => 'Bạn không có quyền xem đơn hàng này.'], 403);
            return;
        }

        $this->respond([
            'order' => $order,
            'items' => $this->orderModel->getOrderDetails((int)$id),
            'logs' => $this->orderModel->getOrderLogs((int)$id),
        ]);
    }

    public function store(): void
    {
        $accountId = $this->requireApiLogin();
        if ($accountId === null) {
            return;
        }

        $data = $this->getJsonInput();
        $name = trim((string)($data['name'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $address = trim((string)($data['address'] ?? ''));
        $paymentMethod = trim((string)($data['payment_method'] ?? 'Tiền mặt'));
        $notes = trim((string)($data['notes'] ?? ''));
        $items = $data['items'] ?? [];

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Tên khách hàng là bắt buộc.';
        }
        if ($phone === '' || !preg_match('/^[0-9]{10,11}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại không hợp lệ.';
        }
        if ($address === '') {
            $errors['address'] = 'Địa chỉ giao hàng là bắt buộc.';
        }
        if (!is_array($items) || count($items) === 0) {
            $errors['items'] = 'Đơn hàng phải có ít nhất một sản phẩm.';
        }

        if (!empty($errors)) {
            $this->respond(['errors' => $errors], 400);
            return;
        }

        try {
            $orderItems = $this->buildOrderItems($items);
            $total = array_sum(array_column($orderItems, 'subtotal'));
            $orderId = $this->orderModel->createOrder([
                'total' => $total,
                'name' => $name,
                'address' => $address,
                'phone' => $phone,
                'email' => $email,
                'payment_method' => $paymentMethod,
                'notes' => $notes,
            ], $orderItems, $accountId);

            $this->respond([
                'message' => 'Tạo đơn hàng thành công.',
                'order_id' => $orderId,
            ], 201);
        } catch (Throwable $e) {
            $this->respond(['message' => $e->getMessage()], 400);
        }
    }

    public function update($id): void
    {
        $accountId = $this->requireApiAdmin();
        if ($accountId === null) {
            return;
        }

        $order = $this->orderModel->getOrderById((int)$id);
        if (!$order) {
            $this->respond(['message' => 'Không tìm thấy đơn hàng.'], 404);
            return;
        }

        $data = $this->getJsonInput();

        try {
            $updatedOrder = $order;

            $basicFields = [
                'name' => $data['name'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];
            $basicFields = array_filter($basicFields, function ($value) {
                return $value !== null;
            });

            if (!empty($basicFields)) {
                $updatedOrder = $this->orderModel->updateOrderBasicInfo((int)$id, $basicFields);
            }

            if (!empty($data['status'])) {
                $updatedOrder = $this->orderModel->transitionOrderStatus((int)$id, (string)$data['status'], $accountId, 'Cập nhật qua REST API');
            }

            if (!empty($data['payment_status'])) {
                $updatedOrder = $this->orderModel->updatePaymentStatus((int)$id, (string)$data['payment_status'], $accountId, 'Cập nhật thanh toán qua REST API');
            }

            if (array_key_exists('carrier', $data) || array_key_exists('tracking_code', $data) || array_key_exists('estimated_delivery', $data)) {
                $updatedOrder = $this->orderModel->updateShippingInfo(
                    (int)$id,
                    isset($data['carrier']) ? (string)$data['carrier'] : ($updatedOrder['carrier'] ?? null),
                    isset($data['tracking_code']) ? (string)$data['tracking_code'] : ($updatedOrder['tracking_code'] ?? null),
                    isset($data['estimated_delivery']) ? (string)$data['estimated_delivery'] : ($updatedOrder['estimated_delivery'] ?? null),
                    $accountId
                );
            }

            $this->respond([
                'message' => 'Cập nhật đơn hàng thành công.',
                'order' => $updatedOrder,
            ]);
        } catch (Throwable $e) {
            $this->respond(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy($id): void
    {
        $accountId = $this->requireApiAdmin();
        if ($accountId === null) {
            return;
        }

        $order = $this->orderModel->getOrderById((int)$id);
        if (!$order) {
            $this->respond(['message' => 'Không tìm thấy đơn hàng.'], 404);
            return;
        }

        $deleted = $this->orderModel->deleteOrder((int)$id);
        if (!$deleted) {
            $this->respond(['message' => 'Xóa đơn hàng thất bại.'], 400);
            return;
        }

        $this->respond(['message' => 'Xóa đơn hàng thành công.']);
    }
}
