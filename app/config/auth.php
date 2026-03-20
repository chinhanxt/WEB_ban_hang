<?php

require_once 'app/config/database.php';
require_once 'app/models/CartModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']->role === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_message'] = [
            'type' => 'warning',
            'title' => 'Yêu cầu đăng nhập',
            'text' => 'Vui lòng đăng nhập để thực hiện chức năng này.'
        ];
        header("Location: /webbanhang/AuthController/login");
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'title' => 'Truy cập bị từ chối',
            'text' => 'Bạn không có quyền truy cập vào chức năng này.'
        ];
        header("Location: /webbanhang/ProductController");
        exit;
    }
}

function getCartItemCount() {
    static $cartCount = null;

    if ($cartCount !== null) {
        return $cartCount;
    }

    $sessionCount = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? count($_SESSION['cart']) : 0;

    if (!isLoggedIn()) {
        $cartCount = $sessionCount;
        return $cartCount;
    }

    try {
        $db = (new Database())->getConnection();
        if (!$db) {
            $cartCount = $sessionCount;
            return $cartCount;
        }

        $cartModel = new CartModel($db);

        if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            $cartModel->syncSessionCart((int)$_SESSION['user']->id, $_SESSION['cart']);
            unset($_SESSION['cart']);
        }

        $cartCount = $cartModel->countItemsByAccountId((int)$_SESSION['user']->id);
        return $cartCount;
    } catch (Throwable $e) {
        $cartCount = $sessionCount;
        return $cartCount;
    }
}
