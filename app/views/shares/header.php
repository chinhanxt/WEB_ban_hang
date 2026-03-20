<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technology Store - Trải nghiệm mua sắm hiện đại</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #0062ff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --bg-light: #f4f7f6;
            --text-dark: #1a1d23;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
        }

        /* Navbar Modernization */
        .navbar {
            background: #ffffff !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .navbar-nav .nav-link {
            color: #4a5568 !important;
            font-weight: 500;
            padding: 8px 16px !important;
            transition: var(--transition);
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
            background: rgba(0, 98, 255, 0.05);
            border-radius: 8px;
        }

        /* Search Box */
        .search-container {
            position: relative;
            margin: 0 15px;
        }

        .search-container input {
            border-radius: 20px;
            padding-left: 20px;
            border: 1px solid #e2e8f0;
            width: 250px !important;
            transition: var(--transition);
        }

        .search-container input:focus {
            width: 300px !important;
            box-shadow: 0 0 0 3px rgba(0, 98, 255, 0.1);
            border-color: var(--primary-color);
        }

        /* Cart Badge */
        .cart-wrapper {
            position: relative;
            padding: 8px;
            background: var(--bg-light);
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .cart-wrapper:hover {
            background: rgba(0, 98, 255, 0.1);
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4d4d;
            color: white;
            font-size: 10px;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
        }

        /* Generic Styles */
        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 10px 20px;
            transition: var(--transition);
        }

        .btn-primary { background-color: var(--primary-color); border: none; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 98, 255, 0.3); }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        h1, h2, h3 { font-weight: 700; letter-spacing: -0.5px; margin-bottom: 1.5rem; }

        .container { max-width: 1200px; }
        
        /* Badge Status */
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/webbanhang/ProductController">
                <i class="fa-solid fa-bolt mr-2"></i>TECH STORE
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"><i class="fa-solid fa-bars"></i></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="/webbanhang/ProductController">Danh sách</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/webbanhang/OrderController">Đơn hàng</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/webbanhang/OrderController/history">Lịch sử</a>
                    </li>
                    <?php endif; ?>

                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-primary font-weight-bold" href="/webbanhang/AuthController/listAccounts">
                            <i class="fas fa-users-cog mr-1"></i>Thành viên
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-primary font-weight-bold" href="/webbanhang/ProductController/add">
                            <i class="fa-solid fa-plus-circle mr-1"></i>Thêm SP
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item mr-3">
                        <a class="nav-link p-0" href="/webbanhang/ProductController/cart">
                            <div class="cart-wrapper">
                                <i class="fa-solid fa-cart-shopping" style="font-size: 1.2rem;"></i>
                                <span id="cart-count" class="cart-badge">
                                    <?php echo getCartItemCount(); ?>
                                </span>
                            </div>
                        </a>
                    </li>

                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle font-weight-bold" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <i class="fa-solid fa-user-circle mr-1"></i> <?php echo explode(' ', $_SESSION['user']->fullname)[0]; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow border-0" aria-labelledby="userDropdown">
                                <a class="dropdown-item small" href="#"><i class="fas fa-id-card mr-2 text-muted"></i> Hồ sơ</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item small text-danger font-weight-bold" href="/webbanhang/AuthController/logout"><i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-primary btn-sm mr-2" href="/webbanhang/AuthController/login">Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm" href="/webbanhang/AuthController/register">Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
    <script>
        function confirmDelete(url, message = 'Bạn có chắc chắn muốn xóa?') {
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4d4d',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy',
                borderRadius: '16px'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
            return false;
        }
    </script>
    <?php if (isset($_SESSION['flash_message'])): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $_SESSION['flash_message']['type']; ?>',
                title: '<?php echo $_SESSION['flash_message']['title']; ?>',
                text: '<?php echo $_SESSION['flash_message']['text']; ?>',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        </script>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
