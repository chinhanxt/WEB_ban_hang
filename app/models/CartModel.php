<?php

class CartModel
{
    private $conn;
    private $table_name = "cart_items";

    public function __construct($db)
    {
        $this->conn = $db;
        $this->ensureTableExists();
    }

    public function getCartProductsByAccountId($accountId)
    {
        $this->removeUnavailableProducts($accountId);

        $query = "SELECT p.*, ci.Quantity AS qty
                  FROM " . $this->table_name . " ci
                  INNER JOIN product p ON p.Id = ci.Product_Id
                  WHERE ci.Account_Id = :account_id
                  ORDER BY ci.Updated_at DESC, ci.Id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':account_id', $accountId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getCartQuantitiesByAccountId($accountId)
    {
        $this->removeUnavailableProducts($accountId);

        $query = "SELECT Product_Id, Quantity
                  FROM " . $this->table_name . "
                  WHERE Account_Id = :account_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':account_id', $accountId, PDO::PARAM_INT);
        $stmt->execute();

        $cart = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cart[(int)$row['Product_Id']] = (int)$row['Quantity'];
        }

        return $cart;
    }

    public function countItemsByAccountId($accountId)
    {
        $this->removeUnavailableProducts($accountId);

        $query = "SELECT COUNT(*) 
                  FROM " . $this->table_name . "
                  WHERE Account_Id = :account_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':account_id', $accountId, PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function addItem($accountId, $productId, $quantity = 1)
    {
        $quantity = max(1, (int)$quantity);

        $query = "INSERT INTO " . $this->table_name . " (Account_Id, Product_Id, Quantity)
                  VALUES (:account_id, :product_id, :quantity)
                  ON DUPLICATE KEY UPDATE
                      Quantity = Quantity + VALUES(Quantity),
                      Updated_at = CURRENT_TIMESTAMP";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':account_id', $accountId, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function setItemQuantity($accountId, $productId, $quantity)
    {
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            return $this->removeItem($accountId, $productId);
        }

        $query = "INSERT INTO " . $this->table_name . " (Account_Id, Product_Id, Quantity)
                  VALUES (:account_id, :product_id, :quantity)
                  ON DUPLICATE KEY UPDATE
                      Quantity = VALUES(Quantity),
                      Updated_at = CURRENT_TIMESTAMP";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':account_id', $accountId, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function removeItem($accountId, $productId)
    {
        $query = "DELETE FROM " . $this->table_name . "
                  WHERE Account_Id = :account_id AND Product_Id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':account_id', $accountId, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function clearCart($accountId)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE Account_Id = :account_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':account_id', $accountId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function clearSelectedItems($accountId, array $productIds)
    {
        $productIds = array_values(array_filter(array_map('intval', $productIds)));
        if (empty($productIds)) {
            return true;
        }

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $query = "DELETE FROM " . $this->table_name . "
                  WHERE Account_Id = ? AND Product_Id IN ($placeholders)";

        $stmt = $this->conn->prepare($query);
        $params = array_merge([(int)$accountId], $productIds);

        return $stmt->execute($params);
    }

    public function syncSessionCart($accountId, array $sessionCart)
    {
        if (empty($sessionCart)) {
            return true;
        }

        $this->conn->beginTransaction();

        try {
            foreach ($sessionCart as $productId => $quantity) {
                $productId = (int)$productId;
                $quantity = (int)$quantity;

                if ($productId > 0 && $quantity > 0) {
                    $this->addItem($accountId, $productId, $quantity);
                }
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            return false;
        }
    }

    private function removeUnavailableProducts($accountId)
    {
        $query = "DELETE ci
                  FROM " . $this->table_name . " ci
                  LEFT JOIN product p ON p.Id = ci.Product_Id
                  WHERE ci.Account_Id = :account_id AND p.Id IS NULL";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':account_id', $accountId, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function ensureTableExists()
    {
        try {
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                    Id INT AUTO_INCREMENT PRIMARY KEY,
                    Account_Id INT NOT NULL,
                    Product_Id INT NOT NULL,
                    Quantity INT NOT NULL DEFAULT 1,
                    Created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    Updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_account_product (Account_Id, Product_Id),
                    CONSTRAINT fk_cart_account FOREIGN KEY (Account_Id) REFERENCES account(Id) ON DELETE CASCADE,
                    CONSTRAINT fk_cart_product FOREIGN KEY (Product_Id) REFERENCES product(Id) ON DELETE CASCADE
                )
            ");
        } catch (Throwable $e) {
            // Keep the app usable even if the schema cannot be created automatically.
        }
    }
}
