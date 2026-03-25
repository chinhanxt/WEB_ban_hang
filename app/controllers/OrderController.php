<?php

require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/AccountModel.php';

class OrderController
{
    private $orderModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);
    }

    private function getCurrentUserId(): ?int
    {
        return isset($_SESSION['user']->id) ? (int)$_SESSION['user']->id : null;
    }

    private function ensureCanAccessOrder(array $order): void
    {
        if (isAdmin()) {
            return;
        }

        $accountId = $this->getCurrentUserId();
        if (!$accountId || !$this->orderModel->userOwnsOrder((int)$order['Id'], $accountId)) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Truy cập bị từ chối',
                'text' => 'Bạn không có quyền xem đơn hàng này.'
            ];
            header('Location: /webbanhang/OrderController');
            exit;
        }
    }

    private function redirectBackToDetails(int $orderId): void
    {
        header('Location: /webbanhang/OrderController/viewDetails/' . $orderId);
        exit;
    }

    private function getStatusTabs(array $orders): array
    {
        $tabOrder = [
            'Tất cả',
            'Chờ xử lý',
            'Đã xác nhận',
            'Đang chuẩn bị hàng',
            'Đang giao hàng',
            'Giao hàng thành công',
            'Hoàn tất',
            'Đã hủy',
            'Đã gửi yêu cầu',
            'Đã duyệt',
            'Từ chối',
            'Đã nhận hàng trả',
            'Đã hoàn tiền',
        ];

        $counts = ['Tất cả' => count($orders)];
        foreach ($orders as $order) {
            $label = $order['display_status'] ?? $order['status'];
            $counts[$label] = ($counts[$label] ?? 0) + 1;
        }

        $tabs = [];
        foreach ($tabOrder as $label) {
            if ($label === 'Tất cả' || isset($counts[$label])) {
                $tabs[] = [
                    'label' => $label,
                    'count' => $counts[$label] ?? 0,
                ];
            }
        }

        foreach ($counts as $label => $count) {
            $exists = false;
            foreach ($tabs as $tab) {
                if ($tab['label'] === $label) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $tabs[] = [
                    'label' => $label,
                    'count' => $count,
                ];
            }
        }

        return $tabs;
    }

    public function index()
    {
        requireLogin();

        if (isAdmin()) {
            $orders = $this->orderModel->getAllOrders();
            $accountModel = new AccountModel($this->db);
            $customers = array_values(array_filter($accountModel->getAccounts(), function ($account) {
                return $account->role === 'user';
            }));
            $selectedCustomerId = (int)($_GET['customer_id'] ?? 0);
            if ($selectedCustomerId > 0) {
                $orders = array_values(array_filter($orders, function ($order) use ($selectedCustomerId) {
                    return (int)($order['Account_Id'] ?? 0) === $selectedCustomerId;
                }));
            }
        } else {
            $orders = $this->orderModel->getOrdersByAccountId($this->getCurrentUserId());
            $customers = [];
            $selectedCustomerId = 0;
        }

        $tabs = $this->getStatusTabs($orders);
        $selectedStatus = trim($_GET['status'] ?? 'Tất cả');
        if ($selectedStatus !== '' && $selectedStatus !== 'Tất cả') {
            $orders = array_values(array_filter($orders, function ($order) use ($selectedStatus) {
                return ($order['display_status'] ?? $order['status']) === $selectedStatus;
            }));
        }

        include 'app/views/order/list.php';
    }

    public function history()
    {
        requireLogin();
        $phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';

        if (isAdmin()) {
            $orders = $phone ? $this->orderModel->getOrdersByPhone($phone) : $this->orderModel->getAllOrders();
        } else {
            $orders = $this->orderModel->getOrdersByAccountId($this->getCurrentUserId());
            if ($phone !== '') {
                $orders = array_values(array_filter($orders, function ($order) use ($phone) {
                    return $order['Phone'] === $phone;
                }));
            }
        }

        include 'app/views/order/history.php';
    }

    public function viewDetails($id)
    {
        requireLogin();

        $order = $this->orderModel->getOrderById((int)$id);
        if (!$order) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Lỗi!',
                'text' => 'Đơn hàng không tồn tại hoặc đã bị xóa.'
            ];
            header('Location: /webbanhang/OrderController');
            return;
        }

        $this->ensureCanAccessOrder($order);

        $details = $this->orderModel->getOrderDetails((int)$id);
        $logs = $this->orderModel->getOrderLogs((int)$id);
        $returnRequest = $this->orderModel->getLatestReturnRequest((int)$id);
        $reviews = $this->orderModel->getReviewsByOrder((int)$id);
        include 'app/views/order/details.php';
    }

    public function updateStatus()
    {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /webbanhang/OrderController');
            return;
        }

        $orderId = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        try {
            $updated = $this->orderModel->transitionOrderStatus($orderId, $status, $this->getCurrentUserId(), $reason);
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Cập nhật!',
                'text' => 'Đơn hàng #' . $orderId . ' đã chuyển sang ' . $updated['status'] . '.'
            ];
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Không thể cập nhật',
                'text' => $e->getMessage()
            ];
        }

        if (!empty($_POST['redirect_details'])) {
            $this->redirectBackToDetails($orderId);
        }

        header('Location: /webbanhang/OrderController');
    }

    public function updatePayment()
    {
        requireAdmin();
        $orderId = (int)($_POST['order_id'] ?? 0);
        $paymentStatus = trim($_POST['payment_status'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        try {
            $this->orderModel->updatePaymentStatus($orderId, $paymentStatus, $this->getCurrentUserId(), $reason);
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Đã cập nhật thanh toán',
                'text' => 'Trạng thái thanh toán đã được lưu.'
            ];
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Cập nhật thất bại',
                'text' => $e->getMessage()
            ];
        }

        $this->redirectBackToDetails($orderId);
    }

    public function updateShipping()
    {
        requireAdmin();
        $orderId = (int)($_POST['order_id'] ?? 0);
        $carrier = trim($_POST['carrier'] ?? '');
        $trackingCode = trim($_POST['tracking_code'] ?? '');
        $estimatedDelivery = trim($_POST['estimated_delivery'] ?? '');

        try {
            $this->orderModel->updateShippingInfo($orderId, $carrier, $trackingCode, $estimatedDelivery, $this->getCurrentUserId());
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Đã cập nhật vận chuyển',
                'text' => 'Thông tin giao hàng đã được lưu.'
            ];
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Không thể lưu vận chuyển',
                'text' => $e->getMessage()
            ];
        }

        $this->redirectBackToDetails($orderId);
    }

    public function cancel()
    {
        requireLogin();
        $orderId = (int)($_POST['order_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        try {
            $this->orderModel->cancelOrderByUser($orderId, $this->getCurrentUserId(), $reason);
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Đã hủy đơn',
                'text' => 'Đơn hàng đã được hủy theo yêu cầu của bạn.'
            ];
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Không thể hủy đơn',
                'text' => $e->getMessage()
            ];
        }

        $this->redirectBackToDetails($orderId);
    }

    public function confirmCompleted()
    {
        requireLogin();
        $orderId = (int)($_POST['order_id'] ?? 0);

        try {
            $this->orderModel->confirmCompletedByUser($orderId, $this->getCurrentUserId());
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Đã xác nhận',
                'text' => 'Cảm ơn bạn đã xác nhận hoàn tất đơn hàng.'
            ];
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Không thể xác nhận',
                'text' => $e->getMessage()
            ];
        }

        $this->redirectBackToDetails($orderId);
    }

    public function requestReturn()
    {
        requireLogin();
        $orderId = (int)($_POST['order_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $evidencePaths = [];

        if (isset($_FILES['evidence']) && is_array($_FILES['evidence']['name'])) {
            $count = count($_FILES['evidence']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['evidence']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $filename = basename($_FILES['evidence']['name'][$i]);
                $target = 'uploads/returns_' . time() . '_' . $i . '_' . $filename;
                if (move_uploaded_file($_FILES['evidence']['tmp_name'][$i], $target)) {
                    $evidencePaths[] = $target;
                }
            }
        }

        try {
            if ($reason === '' || empty($evidencePaths)) {
                throw new RuntimeException('Vui lòng nhập lý do và tải lên ít nhất một ảnh/video minh chứng.');
            }

            $this->orderModel->requestReturn($orderId, $this->getCurrentUserId(), $reason, $evidencePaths);
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Đã gửi yêu cầu',
                'text' => 'Yêu cầu trả hàng/hoàn tiền của bạn đã được ghi nhận.'
            ];
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Không thể gửi yêu cầu',
                'text' => $e->getMessage()
            ];
        }

        $this->redirectBackToDetails($orderId);
    }

    public function reviewReturn()
    {
        requireAdmin();
        $returnRequestId = (int)($_POST['return_request_id'] ?? 0);
        $approved = ($_POST['decision'] ?? '') === 'approve';
        $note = trim($_POST['admin_note'] ?? '');

        try {
            $returnRequest = $this->orderModel->reviewReturnRequest($returnRequestId, $approved, $this->getCurrentUserId(), $note);
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Đã xử lý yêu cầu',
                'text' => 'Yêu cầu trả hàng đã được cập nhật.'
            ];
            $this->redirectBackToDetails((int)$returnRequest['Order_Id']);
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Xử lý thất bại',
                'text' => $e->getMessage()
            ];
        }

        header('Location: /webbanhang/OrderController');
    }

    public function receiveReturnedItems()
    {
        requireAdmin();
        $returnRequestId = (int)($_POST['return_request_id'] ?? 0);

        try {
            $returnRequest = $this->orderModel->markReturnedReceived($returnRequestId, $this->getCurrentUserId());
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Đã nhận hàng trả',
                'text' => 'Đơn hoàn trả đã chuyển sang bước nhận hàng.'
            ];
            $this->redirectBackToDetails((int)$returnRequest['Order_Id']);
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Không thể cập nhật',
                'text' => $e->getMessage()
            ];
        }

        header('Location: /webbanhang/OrderController');
    }

    public function refund()
    {
        requireAdmin();
        $returnRequestId = (int)($_POST['return_request_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        try {
            $returnRequest = $this->orderModel->refundReturnRequest($returnRequestId, $this->getCurrentUserId(), $reason);
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Hoàn tiền thành công',
                'text' => 'Đơn trả hàng đã được hoàn tiền.'
            ];
            $this->redirectBackToDetails((int)$returnRequest['Order_Id']);
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Hoàn tiền thất bại',
                'text' => $e->getMessage()
            ];
        }

        header('Location: /webbanhang/OrderController');
    }

    public function submitReview()
    {
        requireLogin();
        $orderId = (int)($_POST['order_id'] ?? 0);
        $orderItemId = (int)($_POST['order_item_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        try {
            if ($rating < 1 || $rating > 5 || $content === '') {
                throw new RuntimeException('Vui lòng nhập đủ số sao và nội dung đánh giá.');
            }

            $this->orderModel->addReview($orderId, $orderItemId, $this->getCurrentUserId(), $rating, $content);
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Đã gửi đánh giá',
                'text' => 'Cảm ơn bạn đã chia sẻ nhận xét về sản phẩm.'
            ];
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Không thể gửi đánh giá',
                'text' => $e->getMessage()
            ];
        }

        $this->redirectBackToDetails($orderId);
    }

    public function shippingWebhook()
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        header('Content-Type: application/json; charset=utf-8');

        try {
            $order = $this->orderModel->processShippingWebhook($payload);
            echo json_encode([
                'success' => true,
                'order_id' => $order['Id'],
                'status' => $order['status'],
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    public function delete($id)
    {
        requireAdmin();
        $this->orderModel->deleteOrder((int)$id);
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'title' => 'Đã xóa!',
            'text' => 'Đơn hàng đã được loại bỏ khỏi hệ thống.'
        ];
        header('Location: /webbanhang/OrderController');
    }
}
