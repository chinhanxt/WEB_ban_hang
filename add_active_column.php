<?php
require_once 'app/config/database.php';
$db = (new Database())->getConnection();
try {
    $db->exec("ALTER TABLE account ADD COLUMN is_active TINYINT(1) DEFAULT 1");
    echo "Thêm cột is_active thành công.";
} catch (Exception $e) {
    echo "Lỗi hoặc cột đã tồn tại: " . $e->getMessage();
}
?>
