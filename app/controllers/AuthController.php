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

    public function login()
    {
        if (isLoggedIn()) {
            header("Location: /webbanhang/ProductController");
            exit;
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
            if ($user && password_verify($password, $user->password)) {
                if (!$user->is_active) {
                    $errors[] = "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.";
                } else {
                    unset($_SESSION['old_input']);
                    $_SESSION['user'] = $user;
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'title' => 'Chào mừng quay trở lại!',
                        'text' => 'Đăng nhập thành công.'
                    ];
                    header("Location: /webbanhang/ProductController");
                    exit;
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
            header("Location: /webbanhang/ProductController");
            exit;
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
                $_SESSION['user'] = $user;
                
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'title' => 'Chào mừng thành viên mới!',
                    'text' => 'Tài khoản của bạn đã được tạo thành công.'
                ];
                header("Location: /webbanhang/ProductController");
                exit;
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
