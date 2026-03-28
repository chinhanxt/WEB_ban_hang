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

function isApiStyleRequest(): bool {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

    if (strpos($requestUri, '/api/') !== false) {
        return true;
    }

    if (stripos($accept, 'application/json') !== false) {
        return true;
    }

    return strtolower($requestedWith) === 'xmlhttprequest';
}

function wantsJsonResponse(): bool {
    return isApiStyleRequest();
}

function respondJson($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function denyAccessResponse(int $statusCode, string $title, string $message, string $redirectPath = ''): void {
    http_response_code($statusCode);

    if (wantsJsonResponse()) {
        respondJson([
            'error' => $title,
            'message' => $message,
        ], $statusCode);
    }

    header('Content-Type: text/html; charset=utf-8');

    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $backLink = $redirectPath !== ''
        ? '<p><a href="' . htmlspecialchars($redirectPath, ENT_QUOTES, 'UTF-8') . '">Quay lại</a></p>'
        : '';

    echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $safeTitle . '</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; padding: 40px 20px; }
        .error-box { max-width: 640px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); }
        h1 { margin-top: 0; font-size: 28px; }
        p { line-height: 1.6; }
        a { color: #ea580c; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>' . $safeTitle . '</h1>
        <p>' . $safeMessage . '</p>
        ' . $backLink . '
    </div>
</body>
</html>';
    exit;
}

function requireLogin() {
    if (!isLoggedIn()) {
        denyAccessResponse(
            401,
            'Yêu cầu đăng nhập',
            'Vui lòng đăng nhập để thực hiện chức năng này.',
            '/webbanhang/AuthController/login'
        );
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        denyAccessResponse(
            403,
            'Truy cập bị từ chối',
            'Bạn không có quyền truy cập vào chức năng này.',
            '/webbanhang/ProductController'
        );
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
