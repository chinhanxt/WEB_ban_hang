<?php

require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';

class OrderController
{
    private $orderModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);
    }

    // Hiển thị danh sách đơn hàng
    public function index()
    {
        requireLogin();
        if (isAdmin()) {
            $orders = $this->orderModel->getAllOrders();
        } else {
            $orders = $this->orderModel->getOrdersByEmail($_SESSION['user']->email);
        }
        include 'app/views/order/list.php';
    }

    // Xem lịch sử đơn hàng
    public function history()
    {
        $phone = isset($_GET['phone']) ? $_GET['phone'] : '';
        if ($phone) {
            $orders = $this->orderModel->getOrdersByPhone($phone);
        } else {
            $orders = $this->orderModel->getAllOrders();
        }
        include 'app/views/order/history.php';
    }

    // Xem chi tiết đơn hàng
    public function viewDetails($id)
    {
        $order = $this->orderModel->getOrderById($id);
        if (!$order) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Lỗi!',
                'text' => 'Đơn hàng không tồn tại hoặc đã bị xóa.'
            ];
            header('Location: /webbanhang/OrderController');
            return;
        }
        $details = $this->orderModel->getOrderDetails($id);
        include 'app/views/order/details.php';
    }

    // Cập nhật trạng thái đơn hàng
    public function updateStatus()
    {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $this->orderModel->updateOrderStatus($id, $status);
            
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Cập nhật!',
                'text' => 'Trạng thái đơn hàng #' . $id . ' đã chuyển sang: ' . $status
            ];
        }
        header('Location: /webbanhang/OrderController');
    }

    // Xóa đơn hàng
    public function delete($id)
    {
        requireAdmin();
        $this->orderModel->deleteOrder($id);
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'title' => 'Đã xóa!',
            'text' => 'Đơn hàng #' . $id . ' đã được loại bỏ khỏi hệ thống.'
        ];
        header('Location: /webbanhang/OrderController');
    }
}