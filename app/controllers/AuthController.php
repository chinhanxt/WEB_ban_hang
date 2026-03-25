<?php
require_once 'app/config/database.php';
require_once 'app/models/AccountModel.php';
require_once 'app/config/auth.php';

class AuthController
{
    private $accountModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
    }

    private function redirectToHome()
    {
        header("Location: /webbanhang/ProductController");
        exit;
    }

    private function redirectToLogin()
    {
        header("Location: /webbanhang/AuthController/login");
        exit;
    }

    private function createGoogleState(): string
    {
        $state = bin2hex(random_bytes(24));
        $_SESSION['google_oauth_state'] = $state;
        return $state;
    }

    private function clearGoogleState(): void
    {
        unset($_SESSION['google_oauth_state']);
    }

    private function fetchJson(string $url, array $options = []): array
    {
        $headers = $options['headers'] ?? [];
        $method = strtoupper($options['method'] ?? 'GET');
        $body = $options['body'] ?? null;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                throw new RuntimeException('Không thể kết nối tới Google: ' . $error);
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => $method,
                    'header' => implode("\r\n", $headers),
                    'content' => $body ?? '',
                    'ignore_errors' => true,
                ],
            ]);
            $response = @file_get_contents($url, false, $context);
            $httpCode = 200;
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
                $httpCode = (int)$matches[1];
            }

            if ($response === false) {
                throw new RuntimeException('Không thể kết nối tới Google.');
            }
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Phản hồi từ Google không hợp lệ.');
        }

        if ($httpCode >= 400) {
            $message = $decoded['error_description'] ?? $decoded['error'] ?? 'Google xác thực thất bại.';
            throw new RuntimeException('Google trả về lỗi: ' . $message);
        }

        return $decoded;
    }

    private function buildGoogleAuthUrl(): string
    {
        $config = getAuthConfig();
        $params = [
            'client_id' => $config['google']['client_id'],
            'redirect_uri' => getGoogleRedirectUri(),
            'response_type' => 'code',
            'scope' => $config['google']['scope'],
            'state' => $this->createGoogleState(),
            'access_type' => 'online',
            'include_granted_scopes' => 'true',
            'prompt' => 'select_account',
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    private function exchangeGoogleCodeForToken(string $code): array
    {
        $config = getAuthConfig();
        return $this->fetchJson('https://oauth2.googleapis.com/token', [
            'method' => 'POST',
            'headers' => ['Content-Type: application/x-www-form-urlencoded'],
            'body' => http_build_query([
                'code' => $code,
                'client_id' => $config['google']['client_id'],
                'client_secret' => $config['google']['client_secret'],
                'redirect_uri' => getGoogleRedirectUri(),
                'grant_type' => 'authorization_code',
            ]),
        ]);
    }

    private function fetchGoogleUserProfile(string $accessToken): array
    {
        return $this->fetchJson('https://www.googleapis.com/oauth2/v2/userinfo', [
            'headers' => ['Authorization: Bearer ' . $accessToken],
        ]);
    }

    private function loginUser($user, string $successTitle, string $successText)
    {
        unset($_SESSION['old_input']);
        $_SESSION['user'] = $user;
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'title' => $successTitle,
            'text' => $successText,
        ];
        $this->redirectToHome();
    }

    public function login()
    {
        if (isLoggedIn()) {
            $this->redirectToHome();
        }
        include 'app/views/auth/login.php';
        // Xóa old_input sau khi đã render view
        unset($_SESSION['old_input']);
    }

    public function handleLogin()
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];
        if (empty($email)) $errors[] = "Vui lòng nhập email.";
        if (empty($password)) $errors[] = "Vui lòng nhập mật khẩu.";

        if (empty($errors)) {
            $user = $this->accountModel->getAccountByEmail($email);
            if ($user && ($user->auth_provider ?? 'local') === 'google') {
                $errors[] = "Tài khoản này được tạo bằng Google. Vui lòng đăng nhập bằng Google.";
            } elseif ($user && password_verify($password, $user->password)) {
                if (!$user->is_active) {
                    $errors[] = "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.";
                } else {
                    $this->loginUser($user, 'Chào mừng quay trở lại!', 'Đăng nhập thành công.');
                }
            } else {
                $errors[] = "Email hoặc mật khẩu không chính xác.";
            }
        }

        $_SESSION['old_input'] = $_POST;
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'title' => 'Đăng nhập thất bại',
            'text' => implode('<br>', $errors)
        ];
        header("Location: /webbanhang/AuthController/login");
    }

    public function register()
    {
        if (isLoggedIn()) {
            $this->redirectToHome();
        }
        include 'app/views/auth/register.php';
        // Xóa old_input sau khi đã render view
        unset($_SESSION['old_input']);
    }

    public function handleRegister()
    {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fullname = trim($_POST['fullname'] ?? '');
        $password = $_POST['password'] ?? '';

        // Tự động thêm @gmail.com nếu thiếu
        if (!empty($email) && strpos($email, '@') === false) {
            $email .= '@gmail.com';
        }

        $errors = [];

        // Validation theo spec
        if (strlen($username) < 10) {
            $errors[] = "Tên đăng nhập (Username) phải trên 10 ký tự.";
        }
        
        if (empty($fullname)) {
            $errors[] = "Vui lòng nhập họ tên.";
        }
        
        // Mật khẩu 3 quy tắc: 8 ký tự, 1 hoa, 1 thường, 1 số/đặc biệt
        if (strlen($password) < 8) {
            $errors[] = "Mật khẩu phải ít nhất 8 ký tự.";
        }
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password)) {
            $errors[] = "Mật khẩu phải có cả chữ hoa và chữ thường.";
        }
        if (!preg_match('/[0-9]/', $password) && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Mật khẩu phải có ít nhất 1 chữ số hoặc ký tự đặc biệt.";
        }

        // Kiểm tra trùng lặp
        if (empty($errors)) {
            if ($this->accountModel->getAccountByEmail($email)) {
                $errors[] = "Email đã được sử dụng.";
            }
            if ($this->accountModel->getAccountByUsername($username)) {
                $errors[] = "Tên đăng nhập đã tồn tại.";
            }
        }

        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            if ($this->accountModel->save($username, $email, $fullname, $hashed_password)) {
                unset($_SESSION['old_input']);
                // Tự động đăng nhập sau khi đăng ký thành công
                $user = $this->accountModel->getAccountByEmail($email);
                $this->loginUser($user, 'Chào mừng thành viên mới!', 'Tài khoản của bạn đã được tạo thành công.');
            } else {
                $errors[] = "Có lỗi xảy ra trong quá trình lưu dữ liệu.";
            }
        }

        $_SESSION['old_input'] = $_POST;
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'title' => 'Đăng ký thất bại',
            'text' => implode('<br>', $errors)
        ];
        header("Location: /webbanhang/AuthController/register");
    }

    public function googleRedirect()
    {
        if (isLoggedIn()) {
            $this->redirectToHome();
        }

        if (!isGoogleAuthEnabled()) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Google chưa sẵn sàng',
                'text' => 'Chức năng đăng nhập bằng Google chưa được cấu hình trên hệ thống.'
            ];
            $this->redirectToLogin();
        }

        header('Location: ' . $this->buildGoogleAuthUrl());
        exit;
    }

    public function googleCallback()
    {
        if (!isGoogleAuthEnabled()) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Google chưa sẵn sàng',
                'text' => 'Chức năng đăng nhập bằng Google chưa được cấu hình trên hệ thống.'
            ];
            $this->redirectToLogin();
        }

        $sessionState = $_SESSION['google_oauth_state'] ?? '';
        $requestState = trim($_GET['state'] ?? '');
        $code = trim($_GET['code'] ?? '');
        $error = trim($_GET['error'] ?? '');

        if ($error !== '') {
            $this->clearGoogleState();
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Google từ chối xác thực',
                'text' => 'Google trả về: ' . $error
            ];
            $this->redirectToLogin();
        }

        if ($code === '' || $sessionState === '' || !hash_equals($sessionState, $requestState)) {
            $this->clearGoogleState();
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Phiên xác thực không hợp lệ',
                'text' => 'Không thể xác minh yêu cầu đăng nhập bằng Google.'
            ];
            $this->redirectToLogin();
        }

        $this->clearGoogleState();

        try {
            $tokenData = $this->exchangeGoogleCodeForToken($code);
            $accessToken = $tokenData['access_token'] ?? '';
            if ($accessToken === '') {
                throw new RuntimeException('Không nhận được access token từ Google.');
            }

            $profile = $this->fetchGoogleUserProfile($accessToken);
            $googleId = trim((string)($profile['id'] ?? ''));
            $email = trim((string)($profile['email'] ?? ''));
            $fullname = trim((string)($profile['name'] ?? ''));
            $avatar = trim((string)($profile['picture'] ?? ''));
            $verifiedEmail = !empty($profile['verified_email']);

            if ($googleId === '' || $email === '' || !$verifiedEmail) {
                throw new RuntimeException('Google không cung cấp đủ thông tin tài khoản đã xác minh.');
            }

            $user = $this->accountModel->getAccountByGoogleId($googleId);

            if (!$user) {
                $userByEmail = $this->accountModel->getAccountByEmail($email);
                if ($userByEmail) {
                    $this->accountModel->updateGoogleLink((int)$userByEmail->id, $googleId, $avatar);
                    $user = $this->accountModel->getAccountByEmail($email);
                } else {
                    $username = $this->accountModel->generateUniqueUsername($fullname ?: explode('@', $email)[0]);
                    $saved = $this->accountModel->createGoogleAccount($username, $email, $fullname ?: $username, $googleId, $avatar);
                    if (!$saved) {
                        throw new RuntimeException('Không thể tạo tài khoản từ Google.');
                    }
                    $user = $this->accountModel->getAccountByGoogleId($googleId);
                }
            }

            if (!$user || !$user->is_active) {
                throw new RuntimeException('Tài khoản của bạn đang bị khóa hoặc không khả dụng.');
            }

            $welcomeText = ($user->auth_provider ?? '') === 'google' ? 'Tài khoản Google đã được tạo và đăng nhập thành công.' : 'Đăng nhập bằng Google thành công.';
            $this->loginUser($user, 'Chào mừng bạn đến với Tech Store!', $welcomeText);
        } catch (Throwable $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'title' => 'Không thể đăng nhập bằng Google',
                'text' => $e->getMessage()
            ];
            $this->redirectToLogin();
        }
    }

    public function listAccounts()
    {
        requireAdmin();
        $accounts = $this->accountModel->getAccounts();
        include 'app/views/auth/user_list.php';
    }

    public function toggleStatus($id, $currentStatus)
    {
        requireAdmin();
        if ($this->accountModel->toggleActive($id, $currentStatus)) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Thành công!',
                'text' => 'Trạng thái tài khoản đã được cập nhật.'
            ];
        }
        header("Location: /webbanhang/AuthController/listAccounts");
    }

    public function resetUserPassword($id)
    {
        requireAdmin();
        if ($this->accountModel->resetPassword($id, 'User@123')) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Đã đặt lại!',
                'text' => 'Mật khẩu đã được đặt về mặc định: User@123'
            ];
        }
        header("Location: /webbanhang/AuthController/listAccounts");
    }

    public function logout()
    {
        session_destroy();
        session_start();
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'title' => 'Hẹn gặp lại!',
            'text' => 'Bạn đã đăng xuất thành công.'
        ];
        header("Location: /webbanhang/ProductController");
        exit;
    }
}
