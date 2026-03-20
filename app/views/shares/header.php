<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technology Store - Trải nghiệm mua sắm hiện đại</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #0f172a;
            --accent-color: #f97316;
            --accent-soft: #fff0e6;
            --info-color: #06b6d4;
            --success-color: #16a34a;
            --danger-color: #dc2626;
            --warning-color: #f59e0b;
            --bg-light: #f8fafc;
            --surface-color: rgba(255, 255, 255, 0.92);
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: rgba(148, 163, 184, 0.22);
            --shadow-soft: 0 20px 50px rgba(15, 23, 42, 0.08);
            --shadow-card: 0 18px 36px rgba(15, 23, 42, 0.10);
            --shadow-glow: 0 18px 40px rgba(249, 115, 22, 0.18);
            --transition: all 0.28s ease;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            color: var(--text-dark);
            background:
                radial-gradient(circle at top left, rgba(6, 182, 212, 0.10), transparent 28%),
                radial-gradient(circle at top right, rgba(249, 115, 22, 0.12), transparent 26%),
                linear-gradient(180deg, #fff8f2 0%, #f8fafc 22%, #f8fafc 100%);
            min-height: 100vh;
        }

        a { transition: var(--transition); }

        .page-shell {
            position: relative;
            overflow-x: hidden;
        }

        .page-shell::before,
        .page-shell::after {
            content: "";
            position: fixed;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.35;
            z-index: -1;
            pointer-events: none;
        }

        .page-shell::before {
            top: -120px;
            left: -120px;
            background: rgba(6, 182, 212, 0.18);
        }

        .page-shell::after {
            right: -120px;
            top: 120px;
            background: rgba(249, 115, 22, 0.18);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.82) !important;
            backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.55);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            padding: 14px 0;
        }

        .navbar-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            color: var(--primary-color) !important;
            font-size: 1.45rem;
            letter-spacing: -0.04em;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, var(--accent-color), #fb923c);
            box-shadow: var(--shadow-glow);
        }

        .navbar-nav { gap: 6px; }

        .navbar-nav .nav-link {
            color: var(--text-muted) !important;
            font-weight: 600;
            padding: 10px 14px !important;
            border-radius: 12px;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link:focus {
            color: var(--primary-color) !important;
            background: rgba(255, 255, 255, 0.72);
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.18);
        }

        .navbar-toggler {
            border: 0;
            box-shadow: none !important;
        }

        .btn {
            border-radius: 14px;
            font-weight: 700;
            padding: 11px 20px;
            transition: var(--transition);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-color), #fb923c);
            border: none;
            color: #fff;
            box-shadow: var(--shadow-glow);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 22px 40px rgba(249, 115, 22, 0.24);
        }

        .btn-outline-primary {
            border: 1px solid rgba(15, 23, 42, 0.14);
            color: var(--primary-color);
            background: rgba(255, 255, 255, 0.82);
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            color: var(--primary-color);
            background: var(--accent-soft);
            border-color: rgba(249, 115, 22, 0.25);
            transform: translateY(-2px);
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid var(--border-color);
            color: var(--primary-color);
        }

        .btn-light:hover {
            background: #fff;
            transform: translateY(-2px);
        }

        .card,
        .surface-card {
            border: 1px solid rgba(255, 255, 255, 0.68);
            background: var(--surface-color);
            backdrop-filter: blur(18px);
            border-radius: 24px;
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }

        .hover-lift {
            transition: transform 0.28s ease, box-shadow 0.28s ease;
        }

        .hover-lift:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-card);
        }

        .section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--accent-color);
            background: rgba(255, 255, 255, 0.82);
            padding: 8px 12px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            box-shadow: inset 0 0 0 1px rgba(249, 115, 22, 0.12);
        }

        .section-title {
            font-size: clamp(1.9rem, 2.4vw, 2.8rem);
            line-height: 1.08;
            letter-spacing: -0.04em;
            margin: 16px 0 10px;
            font-weight: 800;
        }

        .section-subtitle {
            color: var(--text-muted);
            max-width: 780px;
            font-size: 1rem;
            line-height: 1.75;
        }

        .form-control,
        .custom-select,
        select.form-control,
        .input-group-text,
        textarea.form-control {
            border-radius: 14px !important;
            border: 1px solid rgba(148, 163, 184, 0.22) !important;
            background: rgba(255, 255, 255, 0.84) !important;
            min-height: 48px;
            box-shadow: none !important;
        }

        .form-control:focus,
        .custom-select:focus,
        select.form-control:focus,
        textarea.form-control:focus {
            border-color: rgba(249, 115, 22, 0.38) !important;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12) !important;
            background: #fff !important;
        }

        textarea.form-control { min-height: 120px; }

        .table thead th {
            border-top: 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            background: rgba(248, 250, 252, 0.88);
        }

        .table td { border-top: 1px solid rgba(148, 163, 184, 0.12); }

        .badge {
            padding: 8px 12px;
            border-radius: 999px;
            font-weight: 700;
        }

        .badge-primary { background: var(--accent-soft); color: var(--accent-color); }
        .badge-success { background: rgba(22, 163, 74, 0.12); color: var(--success-color); }
        .badge-warning { background: rgba(245, 158, 11, 0.16); color: #b45309; }
        .badge-danger { background: rgba(220, 38, 38, 0.12); color: var(--danger-color); }
        .badge-info { background: rgba(6, 182, 212, 0.14); color: #0f766e; }
        .badge-secondary { background: rgba(100, 116, 139, 0.12); color: #475569; }

        .cart-wrapper {
            position: relative;
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }

        .cart-wrapper:hover { transform: translateY(-2px); background: #fff; }

        .cart-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ef4444, #f97316);
            color: #fff;
            font-size: 0.72rem;
            font-weight: 800;
            border: 2px solid #fff;
            animation: badgePulse 2.4s infinite;
        }

        .page-container {
            max-width: 1240px;
            margin-top: 40px;
            margin-bottom: 60px;
        }

        .hero-banner {
            position: relative;
            padding: 32px;
            border-radius: 30px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.92));
            color: #fff;
            box-shadow: var(--shadow-soft);
        }

        .soft-panel {
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: var(--shadow-soft);
            border-radius: 24px;
        }

        .metric-card {
            padding: 18px 20px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(148, 163, 184, 0.14);
            box-shadow: var(--shadow-soft);
        }

        .metric-label {
            color: var(--text-muted);
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .metric-value {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--primary-color);
            letter-spacing: -0.04em;
        }

        .reveal-up { animation: revealUp 0.7s ease both; }
        .stagger-1 { animation-delay: 0.08s; }
        .stagger-2 { animation-delay: 0.16s; }
        .stagger-3 { animation-delay: 0.24s; }
        .stagger-4 { animation-delay: 0.32s; }
        .stagger-5 { animation-delay: 0.40s; }
        .stagger-6 { animation-delay: 0.48s; }
        .floating-accent { animation: floatSlow 5.4s ease-in-out infinite; }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .status-pill.success {
            background: rgba(22, 163, 74, 0.12);
            color: var(--success-color);
        }

        .empty-state {
            padding: 44px 24px;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 2.6rem;
            color: rgba(249, 115, 22, 0.55);
            margin-bottom: 14px;
        }

        .dropdown-menu {
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: var(--shadow-soft);
            padding: 10px;
        }

        .dropdown-item {
            border-radius: 12px;
            padding: 10px 12px;
            font-weight: 500;
        }

        .dropdown-item:hover { background: rgba(249, 115, 22, 0.08); }

        @keyframes revealUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes floatSlow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        @keyframes badgePulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.28); }
            50% { box-shadow: 0 0 0 10px rgba(249, 115, 22, 0); }
        }

        @media (max-width: 991px) {
            .navbar-collapse {
                margin-top: 16px;
                padding: 16px;
                border-radius: 22px;
                background: rgba(255, 255, 255, 0.86);
                box-shadow: var(--shadow-soft);
            }
        }

        @media (max-width: 767px) {
            .page-container {
                margin-top: 24px;
                padding-left: 8px;
                padding-right: 8px;
            }

            .hero-banner {
                padding: 24px;
                border-radius: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="page-shell">
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/webbanhang/ProductController">
                <span class="brand-mark floating-accent"><i class="fa-solid fa-bolt"></i></span>
                <span>TECH STORE</span>
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"><i class="fa-solid fa-bars"></i></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="/webbanhang/ProductController">Sản phẩm</a>
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

                    <li class="nav-item mr-lg-3">
                        <a class="nav-link p-0" href="/webbanhang/ProductController/cart">
                            <div class="cart-wrapper">
                                <i class="fa-solid fa-cart-shopping"></i>
                                <span id="cart-count" class="cart-badge"><?php echo getCartItemCount(); ?></span>
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
    <div class="container page-container">
    <script>
        function confirmDelete(url, message = 'Bạn có chắc chắn muốn xóa?') {
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy',
                borderRadius: '20px'
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
                showConfirmButton: false,
                background: '#ffffff',
                borderRadius: '20px'
            });
        </script>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
