<?php

require_once 'app/models/OrderStateMachine.php';

class OrderModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->ensureOmsSchema();
    }

    private function ensureOmsSchema()
    {
        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS order_items (
                Id INT AUTO_INCREMENT PRIMARY KEY,
                Order_Id INT NOT NULL,
                Product_Id INT NULL,
                Product_Name VARCHAR(150) NOT NULL,
                Product_Image VARCHAR(255) DEFAULT NULL,
                Original_Price DECIMAL(15,2) NOT NULL DEFAULT 0,
                Sale_Price DECIMAL(15,2) NOT NULL DEFAULT 0,
                Tax_Amount DECIMAL(15,2) NOT NULL DEFAULT 0,
                Quantity INT NOT NULL DEFAULT 1,
                Subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
                Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (Order_Id) REFERENCES orders(Id) ON DELETE CASCADE,
                FOREIGN KEY (Product_Id) REFERENCES product(Id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS order_logs (
                Id INT AUTO_INCREMENT PRIMARY KEY,
                Order_Id INT NOT NULL,
                Action VARCHAR(100) NOT NULL,
                From_State VARCHAR(50) DEFAULT NULL,
                To_State VARCHAR(50) DEFAULT NULL,
                Action_By INT DEFAULT NULL,
                Reason TEXT DEFAULT NULL,
                Metadata TEXT DEFAULT NULL,
                Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (Order_Id) REFERENCES orders(Id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS return_requests (
                Id INT AUTO_INCREMENT PRIMARY KEY,
                Order_Id INT NOT NULL,
                Requested_By INT DEFAULT NULL,
                Previous_Order_Status VARCHAR(50) DEFAULT NULL,
                Status VARCHAR(50) NOT NULL DEFAULT 'REQUESTED',
                Reason TEXT NOT NULL,
                Evidence_Paths TEXT DEFAULT NULL,
                Admin_Note TEXT DEFAULT NULL,
                Reviewed_By INT DEFAULT NULL,
                Reviewed_At DATETIME DEFAULT NULL,
                Received_At DATETIME DEFAULT NULL,
                Refunded_At DATETIME DEFAULT NULL,
                Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                Updated_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (Order_Id) REFERENCES orders(Id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS reviews (
                Id INT AUTO_INCREMENT PRIMARY KEY,
                Order_Id INT NOT NULL,
                Order_Item_Id INT NOT NULL,
                Account_Id INT NOT NULL,
                Rating INT NOT NULL,
                Content TEXT NOT NULL,
                Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_order_item_review (Order_Item_Id, Account_Id),
                FOREIGN KEY (Order_Id) REFERENCES orders(Id) ON DELETE CASCADE,
                FOREIGN KEY (Order_Item_Id) REFERENCES order_items(Id) ON DELETE CASCADE,
                FOREIGN KEY (Account_Id) REFERENCES account(Id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $this->addColumnIfMissing('orders', 'Account_Id', "ALTER TABLE orders ADD COLUMN Account_Id INT NULL AFTER Id");
        $this->addColumnIfMissing('orders', 'status_code', "ALTER TABLE orders ADD COLUMN status_code VARCHAR(50) DEFAULT 'PENDING' AFTER status");
        $this->addColumnIfMissing('orders', 'payment_status', "ALTER TABLE orders ADD COLUMN payment_status VARCHAR(50) DEFAULT 'UNPAID' AFTER Payment_Method");
        $this->addColumnIfMissing('orders', 'tracking_code', "ALTER TABLE orders ADD COLUMN tracking_code VARCHAR(100) DEFAULT NULL AFTER payment_status");
        $this->addColumnIfMissing('orders', 'carrier', "ALTER TABLE orders ADD COLUMN carrier VARCHAR(100) DEFAULT NULL AFTER tracking_code");
        $this->addColumnIfMissing('orders', 'estimated_delivery', "ALTER TABLE orders ADD COLUMN estimated_delivery DATETIME DEFAULT NULL AFTER carrier");
        $this->addColumnIfMissing('orders', 'delivered_at', "ALTER TABLE orders ADD COLUMN delivered_at DATETIME DEFAULT NULL AFTER estimated_delivery");
        $this->addColumnIfMissing('orders', 'completed_at', "ALTER TABLE orders ADD COLUMN completed_at DATETIME DEFAULT NULL AFTER delivered_at");
        $this->addColumnIfMissing('orders', 'cancelled_at', "ALTER TABLE orders ADD COLUMN cancelled_at DATETIME DEFAULT NULL AFTER completed_at");
        $this->addColumnIfMissing('orders', 'return_requested_at', "ALTER TABLE orders ADD COLUMN return_requested_at DATETIME DEFAULT NULL AFTER cancelled_at");
        $this->addColumnIfMissing('orders', 'refunded_at', "ALTER TABLE orders ADD COLUMN refunded_at DATETIME DEFAULT NULL AFTER return_requested_at");
        $this->addColumnIfMissing('orders', 'updated_at', "ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER Created_at");

        $this->addColumnIfMissing('account', 'Email', "ALTER TABLE account ADD COLUMN Email VARCHAR(100) DEFAULT NULL AFTER Username");
        $this->addColumnIfMissing('account', 'is_active', "ALTER TABLE account ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER Role");
        $this->addColumnIfMissing('return_requests', 'Previous_Order_Status', "ALTER TABLE return_requests ADD COLUMN Previous_Order_Status VARCHAR(50) DEFAULT NULL AFTER Requested_By");

        $this->safeExec("ALTER TABLE orders MODIFY COLUMN total DECIMAL(15,2)");
        $this->safeExec("ALTER TABLE order_items MODIFY COLUMN Original_Price DECIMAL(15,2) NOT NULL DEFAULT 0");
        $this->safeExec("ALTER TABLE order_items MODIFY COLUMN Sale_Price DECIMAL(15,2) NOT NULL DEFAULT 0");
        $this->safeExec("ALTER TABLE order_items MODIFY COLUMN Tax_Amount DECIMAL(15,2) NOT NULL DEFAULT 0");
        $this->safeExec("ALTER TABLE order_items MODIFY COLUMN Subtotal DECIMAL(15,2) NOT NULL DEFAULT 0");

        try {
            $this->conn->exec("ALTER TABLE orders ADD CONSTRAINT fk_orders_account FOREIGN KEY (Account_Id) REFERENCES account(Id) ON DELETE SET NULL");
        } catch (Throwable $e) {
        }

        $this->backfillOrderStatuses();
        $this->migrateLegacyOrderDetails();
    }

    private function addColumnIfMissing(string $table, string $column, string $sql): void
    {
        $query = "SELECT COUNT(*) FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':table' => $table,
            ':column' => $column,
        ]);

        if ((int)$stmt->fetchColumn() === 0) {
            $this->conn->exec($sql);
        }
    }

    private function safeExec(string $sql): void
    {
        try {
            $this->conn->exec($sql);
        } catch (Throwable $e) {
        }
    }

    private function backfillOrderStatuses(): void
    {
        $orders = $this->conn->query("SELECT Id, status, status_code, Payment_Method, payment_status FROM orders")->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->conn->prepare(
            "UPDATE orders
             SET status_code = :status_code,
                 status = :status,
                 payment_status = :payment_status
             WHERE Id = :id"
        );

        foreach ($orders as $order) {
            $statusCode = OrderStateMachine::normalizeStatus($order['status_code'] ?: $order['status']);
            $paymentStatus = OrderStateMachine::normalizePaymentStatus($order['payment_status'] ?? null, $order['Payment_Method'] ?? null);

            $stmt->execute([
                ':status_code' => $statusCode,
                ':status' => OrderStateMachine::getStatusLabel($statusCode),
                ':payment_status' => $paymentStatus,
                ':id' => $order['Id'],
            ]);
        }
    }

    private function migrateLegacyOrderDetails(): void
    {
        $legacyRows = $this->conn->query(
            "SELECT od.Order_Id, od.Product_Id, od.Quantity, od.Price, p.Name, p.Image
             FROM order_details od
             LEFT JOIN product p ON od.Product_Id = p.Id
             WHERE NOT EXISTS (
                SELECT 1 FROM order_items oi
                WHERE oi.Order_Id = od.Order_Id
                AND ((oi.Product_Id IS NOT NULL AND oi.Product_Id = od.Product_Id) OR (oi.Product_Id IS NULL AND od.Product_Id IS NULL))
                AND oi.Quantity = od.Quantity
                AND oi.Sale_Price = od.Price
             )"
        )->fetchAll(PDO::FETCH_ASSOC);

        if (empty($legacyRows)) {
            return;
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO order_items
            (Order_Id, Product_Id, Product_Name, Product_Image, Original_Price, Sale_Price, Tax_Amount, Quantity, Subtotal)
            VALUES
            (:order_id, :product_id, :product_name, :product_image, :original_price, :sale_price, :tax_amount, :quantity, :subtotal)"
        );

        foreach ($legacyRows as $row) {
            $price = (float)$row['Price'];
            $quantity = (int)$row['Quantity'];

            $stmt->execute([
                ':order_id' => $row['Order_Id'],
                ':product_id' => $row['Product_Id'],
                ':product_name' => $row['Name'] ?: ('Sản phẩm #' . $row['Product_Id']),
                ':product_image' => $row['Image'],
                ':original_price' => $price,
                ':sale_price' => $price,
                ':tax_amount' => 0,
                ':quantity' => $quantity,
                ':subtotal' => $price * $quantity,
            ]);
        }
    }

    private function hydrateOrderRow(array $order): array
    {
        $order['status_code'] = OrderStateMachine::normalizeStatus($order['status_code'] ?? $order['status'] ?? null);
        $order['status'] = OrderStateMachine::getStatusLabel($order['status_code']);
        $order['payment_status'] = OrderStateMachine::normalizePaymentStatus($order['payment_status'] ?? null, $order['Payment_Method'] ?? null);
        $order['payment_label'] = OrderStateMachine::getPaymentLabel($order['payment_status'], $order['Payment_Method'] ?? null);
        $order['status_badge_class'] = OrderStateMachine::getBadgeClass($order['status_code']);
        $order['payment_badge_class'] = OrderStateMachine::getPaymentBadgeClass($order['payment_status'], $order['Payment_Method'] ?? null);
        $order['available_transitions'] = OrderStateMachine::getAvailableTransitions($order['status_code']);
        $order['review_enabled'] = OrderStateMachine::canReview($order['status_code']);
        $order['return_request_label'] = !empty($order['return_request_status']) ? OrderStateMachine::getReturnLabel($order['return_request_status']) : null;
        $order['return_request_badge_class'] = !empty($order['return_request_status']) ? OrderStateMachine::getReturnBadgeClass($order['return_request_status']) : null;
        $order['display_status'] = $order['return_request_label'] ?: $order['status'];
        $order['display_status_badge_class'] = $order['return_request_badge_class'] ?: $order['status_badge_class'];

        return $order;
    }

    private function getOrdersByCondition(string $whereClause = '', array $params = []): array
    {
        $query = "SELECT orders.*,
                        (SELECT rr.Status FROM return_requests rr WHERE rr.Order_Id = orders.Id ORDER BY rr.Id DESC LIMIT 1) AS return_request_status
                  FROM orders";
        if ($whereClause) {
            $query .= " WHERE " . $whereClause;
        }
        $query .= " ORDER BY Created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'hydrateOrderRow'], $rows);
    }

    public function getAllOrders(): array
    {
        return $this->getOrdersByCondition();
    }

    public function getOrdersByPhone($phone): array
    {
        return $this->getOrdersByCondition("Phone = :phone", [':phone' => $phone]);
    }

    public function getOrdersByEmail($email): array
    {
        return $this->getOrdersByCondition("Email = :email", [':email' => $email]);
    }

    public function getOrdersByAccountId(int $accountId): array
    {
        return $this->getOrdersByCondition("(Account_Id = :account_id OR (Account_Id IS NULL AND Email = (SELECT Email FROM account WHERE Id = :account_email_id)))", [
            ':account_id' => $accountId,
            ':account_email_id' => $accountId,
        ]);
    }

    public function getOrderById($id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT orders.*,
                    (SELECT rr.Status FROM return_requests rr WHERE rr.Order_Id = orders.Id ORDER BY rr.Id DESC LIMIT 1) AS return_request_status
             FROM orders
             WHERE Id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->hydrateOrderRow($row) : null;
    }

    public function getOrderDetails($orderId): array
    {
        $query = "SELECT oi.*, r.Id AS review_id
                  FROM order_items oi
                  LEFT JOIN reviews r ON r.Order_Item_Id = oi.Id
                  WHERE oi.Order_Id = :order_id
                  ORDER BY oi.Id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderLogs($orderId): array
    {
        $query = "SELECT ol.*, a.Fullname
                  FROM order_logs ol
                  LEFT JOIN account a ON ol.Action_By = a.Id
                  WHERE ol.Order_Id = :order_id
                  ORDER BY ol.Created_At DESC, ol.Id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestReturnRequest($orderId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM return_requests WHERE Order_Id = :order_id ORDER BY Id DESC LIMIT 1");
        $stmt->execute([':order_id' => $orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getReviewsByOrder($orderId): array
    {
        $query = "SELECT r.*, oi.Product_Name, a.Fullname
                  FROM reviews r
                  INNER JOIN order_items oi ON r.Order_Item_Id = oi.Id
                  INNER JOIN account a ON r.Account_Id = a.Id
                  WHERE r.Order_Id = :order_id
                  ORDER BY r.Created_At DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function userOwnsOrder(int $orderId, int $accountId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM orders
             WHERE Id = :order_id
             AND (Account_Id = :account_id OR Email = (SELECT Email FROM account WHERE Id = :account_email_id))"
        );
        $stmt->execute([
            ':order_id' => $orderId,
            ':account_id' => $accountId,
            ':account_email_id' => $accountId,
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function createOrder(array $payload, array $items, ?int $actorId = null): int
    {
        $this->conn->beginTransaction();

        try {
            $paymentStatus = OrderStateMachine::normalizePaymentStatus(null, $payload['payment_method'] ?? 'Tiền mặt');
            $statusCode = OrderStateMachine::PENDING;

            $stmt = $this->conn->prepare(
                "INSERT INTO orders
                (Account_Id, total, Name, Address, Phone, Email, Payment_Method, payment_status, Notes, status, status_code, Created_at)
                VALUES
                (:account_id, :total, :name, :address, :phone, :email, :payment_method, :payment_status, :notes, :status, :status_code, NOW())"
            );

            $stmt->execute([
                ':account_id' => $actorId,
                ':total' => $payload['total'],
                ':name' => $payload['name'],
                ':address' => $payload['address'],
                ':phone' => $payload['phone'],
                ':email' => $payload['email'],
                ':payment_method' => $payload['payment_method'],
                ':payment_status' => $paymentStatus,
                ':notes' => $payload['notes'],
                ':status' => OrderStateMachine::getStatusLabel($statusCode),
                ':status_code' => $statusCode,
            ]);

            $orderId = (int)$this->conn->lastInsertId();

            $itemStmt = $this->conn->prepare(
                "INSERT INTO order_items
                (Order_Id, Product_Id, Product_Name, Product_Image, Original_Price, Sale_Price, Tax_Amount, Quantity, Subtotal)
                VALUES
                (:order_id, :product_id, :product_name, :product_image, :original_price, :sale_price, :tax_amount, :quantity, :subtotal)"
            );

            foreach ($items as $item) {
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':product_name' => $item['product_name'],
                    ':product_image' => $item['product_image'],
                    ':original_price' => $item['original_price'],
                    ':sale_price' => $item['sale_price'],
                    ':tax_amount' => $item['tax_amount'],
                    ':quantity' => $item['quantity'],
                    ':subtotal' => $item['subtotal'],
                ]);
            }

            $this->logOrderAction($orderId, 'ORDER_CREATED', null, $statusCode, $actorId, 'Tạo đơn hàng mới', [
                'payment_status' => $paymentStatus,
                'item_count' => count($items),
            ]);

            $this->conn->commit();
            return $orderId;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    private function updateOrderStatusColumns(int $orderId, string $statusCode): void
    {
        $timestampSql = [];
        $params = [
            ':id' => $orderId,
            ':status_code' => $statusCode,
            ':status' => OrderStateMachine::getStatusLabel($statusCode),
        ];

        if ($statusCode === OrderStateMachine::DELIVERED) {
            $timestampSql[] = "delivered_at = NOW()";
        }
        if ($statusCode === OrderStateMachine::COMPLETED) {
            $timestampSql[] = "completed_at = NOW()";
        }
        if ($statusCode === OrderStateMachine::CANCELLED) {
            $timestampSql[] = "cancelled_at = NOW()";
        }
        if ($statusCode === OrderStateMachine::RETURN_REQUESTED) {
            $timestampSql[] = "return_requested_at = NOW()";
        }
        if ($statusCode === OrderStateMachine::REFUNDED) {
            $timestampSql[] = "refunded_at = NOW()";
        }

        $query = "UPDATE orders SET status_code = :status_code, status = :status";
        if (!empty($timestampSql)) {
            $query .= ", " . implode(', ', $timestampSql);
        }
        $query .= " WHERE Id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
    }

    public function transitionOrderStatus(int $orderId, string $toStatus, ?int $actorId = null, string $reason = '', bool $useTransaction = true): array
    {
        $order = $this->getOrderById($orderId);
        if (!$order) {
            throw new RuntimeException('Đơn hàng không tồn tại.');
        }

        $toStatus = OrderStateMachine::normalizeStatus($toStatus);
        $fromStatus = $order['status_code'];

        if ($fromStatus === $toStatus) {
            return $order;
        }

        if (!OrderStateMachine::canTransition($fromStatus, $toStatus)) {
            throw new RuntimeException('Không thể chuyển trạng thái từ ' . OrderStateMachine::getStatusLabel($fromStatus) . ' sang ' . OrderStateMachine::getStatusLabel($toStatus) . '.');
        }

        if ($useTransaction) {
            $this->conn->beginTransaction();
        }

        try {
            $this->updateOrderStatusColumns($orderId, $toStatus);
            $this->logOrderAction($orderId, 'ORDER_STATUS_UPDATED', $fromStatus, $toStatus, $actorId, $reason);

            $updatedOrder = $this->getOrderById($orderId);

            if ($toStatus === OrderStateMachine::CANCELLED && $updatedOrder['payment_status'] === OrderStateMachine::PAYMENT_PAID) {
                $this->updatePaymentStatus($orderId, OrderStateMachine::PAYMENT_REFUND_PENDING, $actorId, 'Tự động kích hoạt hoàn tiền khi hủy đơn đã thanh toán', false);
                $this->updatePaymentStatus($orderId, OrderStateMachine::PAYMENT_REFUNDED, $actorId, 'Hoàn tiền tự động hoàn tất', false);
            }

            if ($toStatus === OrderStateMachine::REFUNDED && $updatedOrder['payment_status'] !== OrderStateMachine::PAYMENT_REFUNDED) {
                $this->updatePaymentStatus($orderId, OrderStateMachine::PAYMENT_REFUNDED, $actorId, 'Đồng bộ trạng thái hoàn tiền', false);
            }

            if ($useTransaction) {
                $this->conn->commit();
            }
            return $this->getOrderById($orderId);
        } catch (Throwable $e) {
            if ($useTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    public function updatePaymentStatus(int $orderId, string $paymentStatus, ?int $actorId = null, string $reason = '', bool $useTransaction = true): array
    {
        $order = $this->getOrderById($orderId);
        if (!$order) {
            throw new RuntimeException('Đơn hàng không tồn tại.');
        }

        $paymentStatus = OrderStateMachine::normalizePaymentStatus($paymentStatus, $order['Payment_Method'] ?? null);

        if ($useTransaction) {
            $this->conn->beginTransaction();
        }

        try {
            $stmt = $this->conn->prepare("UPDATE orders SET payment_status = :payment_status WHERE Id = :id");
            $stmt->execute([
                ':payment_status' => $paymentStatus,
                ':id' => $orderId,
            ]);

            if ($paymentStatus === OrderStateMachine::PAYMENT_REFUNDED) {
                $this->conn->prepare("UPDATE orders SET refunded_at = NOW() WHERE Id = :id")->execute([':id' => $orderId]);
            }

            $this->logOrderAction($orderId, 'PAYMENT_STATUS_UPDATED', $order['payment_status'], $paymentStatus, $actorId, $reason);

            if ($useTransaction) {
                $this->conn->commit();
            }

            return $this->getOrderById($orderId);
        } catch (Throwable $e) {
            if ($useTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    public function updateShippingInfo(int $orderId, ?string $carrier, ?string $trackingCode, ?string $estimatedDelivery, ?int $actorId = null): array
    {
        $order = $this->getOrderById($orderId);
        if (!$order) {
            throw new RuntimeException('Đơn hàng không tồn tại.');
        }

        $stmt = $this->conn->prepare(
            "UPDATE orders
             SET carrier = :carrier,
                 tracking_code = :tracking_code,
                 estimated_delivery = :estimated_delivery
             WHERE Id = :id"
        );
        $stmt->execute([
            ':carrier' => $carrier ?: null,
            ':tracking_code' => $trackingCode ?: null,
            ':estimated_delivery' => $estimatedDelivery ?: null,
            ':id' => $orderId,
        ]);

        $this->logOrderAction($orderId, 'SHIPPING_INFO_UPDATED', null, null, $actorId, 'Cập nhật thông tin vận chuyển', [
            'carrier' => $carrier,
            'tracking_code' => $trackingCode,
            'estimated_delivery' => $estimatedDelivery,
        ]);

        return $this->getOrderById($orderId);
    }

    public function processShippingWebhook(array $payload): array
    {
        $providedSecret = $payload['secret'] ?? '';
        $expectedSecret = getenv('OMS_WEBHOOK_SECRET') ?: 'oms-demo-secret';
        if ($providedSecret !== $expectedSecret) {
            throw new RuntimeException('Webhook secret không hợp lệ.');
        }

        $orderId = isset($payload['order_id']) ? (int)$payload['order_id'] : 0;
        $trackingCode = trim((string)($payload['tracking_code'] ?? ''));
        $shippingStatus = trim((string)($payload['shipping_status'] ?? ''));

        if ($orderId <= 0 && $trackingCode === '') {
            throw new RuntimeException('Webhook cần order_id hoặc tracking_code.');
        }

        if ($orderId > 0) {
            $order = $this->getOrderById($orderId);
        } else {
            $stmt = $this->conn->prepare("SELECT Id FROM orders WHERE tracking_code = :tracking_code LIMIT 1");
            $stmt->execute([':tracking_code' => $trackingCode]);
            $resolvedId = (int)$stmt->fetchColumn();
            $order = $resolvedId > 0 ? $this->getOrderById($resolvedId) : null;
        }

        if (!$order) {
            throw new RuntimeException('Không tìm thấy đơn hàng để xử lý webhook.');
        }

        $this->updateShippingInfo(
            (int)$order['Id'],
            $payload['carrier'] ?? $order['carrier'],
            $trackingCode ?: $order['tracking_code'],
            $payload['estimated_delivery'] ?? $order['estimated_delivery'],
            null
        );

        $mappedStatus = OrderStateMachine::mapWebhookStatusToOrderStatus($shippingStatus);
        if ($mappedStatus && OrderStateMachine::canTransition($order['status_code'], $mappedStatus)) {
            $order = $this->transitionOrderStatus((int)$order['Id'], $mappedStatus, null, 'Cập nhật từ webhook vận chuyển');
        }

        $this->logOrderAction((int)$order['Id'], 'SHIPPING_WEBHOOK_RECEIVED', null, $mappedStatus, null, 'Nhận webhook vận chuyển', $payload);

        return $this->getOrderById((int)$order['Id']);
    }

    public function requestReturn(int $orderId, int $accountId, string $reason, array $evidencePaths): array
    {
        $order = $this->getOrderById($orderId);
        if (!$order) {
            throw new RuntimeException('Đơn hàng không tồn tại.');
        }

        if (!$this->userOwnsOrder($orderId, $accountId)) {
            throw new RuntimeException('Bạn không có quyền yêu cầu trả hàng cho đơn này.');
        }

        if (!OrderStateMachine::canRequestReturn($order['status_code'])) {
            throw new RuntimeException('Đơn hàng chưa ở trạng thái cho phép trả hàng.');
        }

        if (!empty($order['delivered_at'])) {
            $deliveredAt = new DateTime($order['delivered_at']);
            $deadline = (clone $deliveredAt)->modify('+7 days');
            if (new DateTime() > $deadline) {
                throw new RuntimeException('Đã quá thời hạn 7 ngày để yêu cầu trả hàng.');
            }
        }

        $existing = $this->getLatestReturnRequest($orderId);
        if ($existing && $existing['Status'] !== OrderStateMachine::RETURN_REJECTED) {
            throw new RuntimeException('Đơn hàng này đã có yêu cầu trả hàng đang được xử lý.');
        }

        $this->conn->beginTransaction();

        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO return_requests (Order_Id, Requested_By, Previous_Order_Status, Status, Reason, Evidence_Paths)
                 VALUES (:order_id, :requested_by, :previous_order_status, :status, :reason, :evidence_paths)"
            );
            $stmt->execute([
                ':order_id' => $orderId,
                ':requested_by' => $accountId,
                ':previous_order_status' => $order['status_code'],
                ':status' => OrderStateMachine::RETURN_REQUESTED_STEP,
                ':reason' => $reason,
                ':evidence_paths' => json_encode(array_values($evidencePaths), JSON_UNESCAPED_UNICODE),
            ]);

            $this->transitionOrderStatus($orderId, OrderStateMachine::RETURN_REQUESTED, $accountId, 'Khách hàng gửi yêu cầu trả hàng', false);

            $this->conn->commit();
            return $this->getLatestReturnRequest($orderId);
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    public function getReturnRequestById(int $returnRequestId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM return_requests WHERE Id = :id LIMIT 1");
        $stmt->execute([':id' => $returnRequestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function reviewReturnRequest(int $returnRequestId, bool $approved, int $adminId, string $adminNote = ''): array
    {
        $returnRequest = $this->getReturnRequestById($returnRequestId);
        if (!$returnRequest) {
            throw new RuntimeException('Yêu cầu trả hàng không tồn tại.');
        }

        if ($returnRequest['Status'] !== OrderStateMachine::RETURN_REQUESTED_STEP) {
            throw new RuntimeException('Chỉ có thể xử lý yêu cầu đang chờ duyệt.');
        }

        $status = $approved ? OrderStateMachine::RETURN_APPROVED : OrderStateMachine::RETURN_REJECTED;
        $stmt = $this->conn->prepare(
            "UPDATE return_requests
             SET Status = :status,
                 Admin_Note = :admin_note,
                 Reviewed_By = :reviewed_by,
                 Reviewed_At = NOW()
             WHERE Id = :id"
        );
        $stmt->execute([
            ':status' => $status,
            ':admin_note' => $adminNote,
            ':reviewed_by' => $adminId,
            ':id' => $returnRequestId,
        ]);

        $this->logOrderAction((int)$returnRequest['Order_Id'], 'RETURN_REQUEST_REVIEWED', null, $status, $adminId, $adminNote);

        if (!$approved) {
            $restoreStatus = OrderStateMachine::normalizeStatus($returnRequest['Previous_Order_Status'] ?? OrderStateMachine::DELIVERED);
            $order = $this->getOrderById((int)$returnRequest['Order_Id']);
            if ($order && OrderStateMachine::canTransition($order['status_code'], $restoreStatus)) {
                $this->transitionOrderStatus((int)$returnRequest['Order_Id'], $restoreStatus, $adminId, 'Từ chối yêu cầu trả hàng');
            }
        }

        return $this->getReturnRequestById($returnRequestId);
    }

    public function markReturnedReceived(int $returnRequestId, int $adminId): array
    {
        $returnRequest = $this->getReturnRequestById($returnRequestId);
        if (!$returnRequest) {
            throw new RuntimeException('Yêu cầu trả hàng không tồn tại.');
        }

        if ($returnRequest['Status'] !== OrderStateMachine::RETURN_APPROVED) {
            throw new RuntimeException('Chỉ có thể xác nhận nhận hàng trả sau khi yêu cầu đã được duyệt.');
        }

        $stmt = $this->conn->prepare("UPDATE return_requests SET Status = :status, Received_At = NOW() WHERE Id = :id");
        $stmt->execute([
            ':status' => OrderStateMachine::RETURN_ITEMS_RECEIVED,
            ':id' => $returnRequestId,
        ]);

        $this->transitionOrderStatus((int)$returnRequest['Order_Id'], OrderStateMachine::RETURNED, $adminId, 'Đã nhận lại hàng trả');

        return $this->getReturnRequestById($returnRequestId);
    }

    public function refundReturnRequest(int $returnRequestId, int $adminId, string $reason = ''): array
    {
        $returnRequest = $this->getReturnRequestById($returnRequestId);
        if (!$returnRequest) {
            throw new RuntimeException('Yêu cầu trả hàng không tồn tại.');
        }

        if ($returnRequest['Status'] !== OrderStateMachine::RETURN_ITEMS_RECEIVED) {
            throw new RuntimeException('Chỉ có thể hoàn tiền sau khi đã xác nhận nhận hàng trả.');
        }

        $stmt = $this->conn->prepare("UPDATE return_requests SET Status = :status, Refunded_At = NOW() WHERE Id = :id");
        $stmt->execute([
            ':status' => OrderStateMachine::RETURN_REFUNDED,
            ':id' => $returnRequestId,
        ]);

        $this->transitionOrderStatus((int)$returnRequest['Order_Id'], OrderStateMachine::REFUNDED, $adminId, $reason ?: 'Hoàn tiền cho đơn trả hàng');

        return $this->getReturnRequestById($returnRequestId);
    }

    public function canUserCancelToday(int $accountId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM order_logs
             WHERE Action = 'USER_CANCELLED'
             AND Action_By = :account_id
             AND DATE(Created_At) = CURDATE()"
        );
        $stmt->execute([':account_id' => $accountId]);
        return (int)$stmt->fetchColumn() < 3;
    }

    public function cancelOrderByUser(int $orderId, int $accountId, string $reason = ''): array
    {
        if (!$this->canUserCancelToday($accountId)) {
            throw new RuntimeException('Bạn đã đạt giới hạn 3 lần hủy đơn trong ngày.');
        }

        $order = $this->getOrderById($orderId);
        if (!$order || !$this->userOwnsOrder($orderId, $accountId)) {
            throw new RuntimeException('Không tìm thấy đơn hàng hợp lệ để hủy.');
        }

        if (!OrderStateMachine::canUserCancel($order['status_code'])) {
            throw new RuntimeException('Chỉ có thể hủy đơn khi đang chờ xử lý hoặc đã xác nhận.');
        }

        $updated = $this->transitionOrderStatus($orderId, OrderStateMachine::CANCELLED, $accountId, $reason ?: 'Khách hàng hủy đơn');
        $this->logOrderAction($orderId, 'USER_CANCELLED', $order['status_code'], OrderStateMachine::CANCELLED, $accountId, $reason ?: 'Khách hàng hủy đơn');
        return $updated;
    }

    public function confirmCompletedByUser(int $orderId, int $accountId): array
    {
        $order = $this->getOrderById($orderId);
        if (!$order || !$this->userOwnsOrder($orderId, $accountId)) {
            throw new RuntimeException('Không tìm thấy đơn hàng hợp lệ.');
        }

        if (!OrderStateMachine::canConfirmCompleted($order['status_code'])) {
            throw new RuntimeException('Đơn hàng chưa thể xác nhận hoàn tất.');
        }

        return $this->transitionOrderStatus($orderId, OrderStateMachine::COMPLETED, $accountId, 'Khách hàng xác nhận đã nhận hàng');
    }

    public function addReview(int $orderId, int $orderItemId, int $accountId, int $rating, string $content): void
    {
        $order = $this->getOrderById($orderId);
        if (!$order || !$this->userOwnsOrder($orderId, $accountId)) {
            throw new RuntimeException('Bạn không thể đánh giá đơn hàng này.');
        }

        if (!OrderStateMachine::canReview($order['status_code'])) {
            throw new RuntimeException('Đơn hàng chưa mở khóa đánh giá.');
        }

        $itemStmt = $this->conn->prepare("SELECT * FROM order_items WHERE Id = :id AND Order_Id = :order_id LIMIT 1");
        $itemStmt->execute([
            ':id' => $orderItemId,
            ':order_id' => $orderId,
        ]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) {
            throw new RuntimeException('Sản phẩm đánh giá không hợp lệ.');
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO reviews (Order_Id, Order_Item_Id, Account_Id, Rating, Content)
             VALUES (:order_id, :order_item_id, :account_id, :rating, :content)"
        );

        try {
            $stmt->execute([
                ':order_id' => $orderId,
                ':order_item_id' => $orderItemId,
                ':account_id' => $accountId,
                ':rating' => $rating,
                ':content' => $content,
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Sản phẩm này đã được bạn đánh giá trước đó.');
        }

        $this->logOrderAction($orderId, 'REVIEW_CREATED', null, null, $accountId, 'Khách hàng gửi đánh giá', [
            'order_item_id' => $orderItemId,
            'rating' => $rating,
        ]);
    }

    public function deleteOrder($id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM orders WHERE Id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function updateOrderBasicInfo(int $orderId, array $data): array
    {
        $order = $this->getOrderById($orderId);
        if (!$order) {
            throw new RuntimeException('Đơn hàng không tồn tại.');
        }

        $mappedFields = [
            'name' => 'Name',
            'address' => 'Address',
            'phone' => 'Phone',
            'email' => 'Email',
            'payment_method' => 'Payment_Method',
            'notes' => 'Notes',
        ];

        $setParts = [];
        $params = [':id' => $orderId];

        foreach ($mappedFields as $inputKey => $column) {
            if (!array_key_exists($inputKey, $data)) {
                continue;
            }

            $setParts[] = $column . ' = :' . $inputKey;
            $params[':' . $inputKey] = $data[$inputKey];
        }

        if (empty($setParts)) {
            return $order;
        }

        $query = "UPDATE orders SET " . implode(', ', $setParts) . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $this->getOrderById($orderId);
    }

    private function logOrderAction(int $orderId, string $action, ?string $fromState, ?string $toState, ?int $actionBy, string $reason = '', array $metadata = []): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO order_logs (Order_Id, Action, From_State, To_State, Action_By, Reason, Metadata)
             VALUES (:order_id, :action, :from_state, :to_state, :action_by, :reason, :metadata)"
        );

        $stmt->execute([
            ':order_id' => $orderId,
            ':action' => $action,
            ':from_state' => $fromState,
            ':to_state' => $toState,
            ':action_by' => $actionBy,
            ':reason' => $reason ?: null,
            ':metadata' => !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }
}
