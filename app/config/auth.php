<?php

require_once 'app/config/database.php';
require_once 'app/models/CartModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getAuthConfig() {
    $googleClientId = getenv('GOOGLE_CLIENT_ID') ?: ($_ENV['GOOGLE_CLIENT_ID'] ?? $_SERVER['GOOGLE_CLIENT_ID'] ?? '');
    $googleClientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: ($_ENV['GOOGLE_CLIENT_SECRET'] ?? $_SERVER['GOOGLE_CLIENT_SECRET'] ?? '');
    $googleRedirectUri = getenv('GOOGLE_REDIRECT_URI') ?: ($_ENV['GOOGLE_REDIRECT_URI'] ?? $_SERVER['GOOGLE_REDIRECT_URI'] ?? '/webbanhang/AuthController/googleCallback');

    return [
        'google' => [
            'client_id' => $googleClientId,
            'client_secret' => $googleClientSecret,
            'redirect_uri' => $googleRedirectUri,
            'scope' => 'openid email profile',
        ],
    ];
}

function isGoogleAuthEnabled() {
    $config = getAuthConfig();
    return !empty($config['google']['client_id']) && !empty($config['google']['client_secret']);
}

function buildAppUrl(string $path = ''): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $normalizedPath = '/' . ltrim($path, '/');
    return $scheme . '://' . $host . $normalizedPath;
}

function getGoogleRedirectUri(): string {
    $config = getAuthConfig();
    $redirectUri = trim($config['google']['redirect_uri'] ?? '');

    if ($redirectUri === '') {
        return buildAppUrl('/webbanhang/AuthController/googleCallback');
    }

    if (preg_match('/^https?:\/\//i', $redirectUri)) {
        return $redirectUri;
    }

    return buildAppUrl($redirectUri);
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
