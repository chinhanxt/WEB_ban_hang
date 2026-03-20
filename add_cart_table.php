<?php

require_once 'app/config/database.php';

$db = (new Database())->getConnection();

try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS cart_items (
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

    echo "Tao bang cart_items thanh cong.";
} catch (Exception $e) {
    echo "Loi khi tao bang cart_items: " . $e->getMessage();
}
?>
