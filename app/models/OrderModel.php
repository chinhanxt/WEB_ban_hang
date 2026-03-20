<?php

class OrderModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy tất cả đơn hàng
    public function getAllOrders()
    {
        $query = "SELECT * FROM orders ORDER BY Created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy đơn hàng theo số điện thoại
    public function getOrdersByPhone($phone)
    {
        $query = "SELECT * FROM orders WHERE Phone = :phone ORDER BY Created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy đơn hàng theo email
    public function getOrdersByEmail($email)
    {
        $query = "SELECT * FROM orders WHERE Email = :email ORDER BY Created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy thông tin chi tiết một đơn hàng
    public function getOrderById($id)
    {
        $query = "SELECT * FROM orders WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy các sản phẩm trong một đơn hàng
    public function getOrderDetails($order_id)
    {
        $query = "SELECT od.*, p.Name as product_name, p.Image 
                  FROM order_details od 
                  JOIN product p ON od.Product_Id = p.Id 
                  WHERE od.Order_Id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createOrder($total, $name, $address, $phone, $email, $payment_method, $notes)
    {
        // Try with 'total' if previous ones failed
        $query = "INSERT INTO orders (total, Name, Address, Phone, Email, Payment_Method, Notes, status, Created_at) 
                  VALUES (:total, :name, :address, :phone, :email, :payment_method, :notes, 'Đang chờ xử lý', NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':notes', $notes);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function addOrderDetail($order_id, $product_id, $quantity, $price)
    {
        $query = "INSERT INTO order_details (Order_Id, Product_Id, Quantity, Price) 
                  VALUES (:order_id, :product_id, :quantity, :price)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);
        $stmt->execute();
    }

    // Cập nhật trạng thái đơn hàng
    public function updateOrderStatus($id, $status)
    {
        $query = "UPDATE orders SET status = :status WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Xóa đơn hàng
    public function deleteOrder($id)
    {
        // Xóa chi tiết đơn hàng trước (ràng buộc dữ liệu)
        $queryDetails = "DELETE FROM order_details WHERE Order_Id = :id";
        $stmtDetails = $this->conn->prepare($queryDetails);
        $stmtDetails->bindParam(':id', $id);
        $stmtDetails->execute();

        // Xóa đơn hàng chính
        $queryOrder = "DELETE FROM orders WHERE Id = :id";
        $stmtOrder = $this->conn->prepare($queryOrder);
        $stmtOrder->bindParam(':id', $id);
        return $stmtOrder->execute();
    }
}