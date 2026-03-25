<?php include 'app/views/shares/header.php'; ?>

<div class="row justify-content-center mt-4 mb-5">
    <div class="col-lg-6 col-xl-5">
        <div class="surface-card reveal-up">
            <div class="hero-banner text-center" style="border-radius: 0;">
                <div class="section-kicker mx-auto"><i class="fa-solid fa-right-to-bracket"></i>Đăng nhập</div>
                <h1 class="h3 font-weight-bold mt-3 mb-2 text-white">Quay lại tài khoản của bạn</h1>
                <p class="text-white-50 mb-0">Bố cục được tối giản để bạn nhập thông tin nhanh và dễ đọc hơn.</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <form id="loginForm" action="/webbanhang/AuthController/handleLogin" method="POST">
                    <div class="form-group mb-4">
                        <label for="email" class="font-weight-bold small text-uppercase">Email</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-envelope text-primary"></i></span>
                            </div>
                            <input type="text" name="email" id="email" class="form-control" placeholder="example@gmail.com" value="<?php echo htmlspecialchars($_SESSION['old_input']['email'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="password" class="font-weight-bold small text-uppercase">Mật khẩu</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-primary"></i></span>
                            </div>
                            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="rememberMe">
                            <label class="custom-control-label small text-muted" for="rememberMe">Ghi nhớ đăng nhập</label>
                        </div>
                        <a href="#" class="small text-primary font-weight-bold">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" id="btnSubmit" class="btn btn-primary btn-block py-3 font-weight-bold">
                        ĐĂNG NHẬP NGAY
                    </button>
                </form>

                <?php if (isGoogleAuthEnabled()): ?>
                    <div class="auth-divider my-4">
                        <span>hoặc</span>
                    </div>
                    <a href="/webbanhang/AuthController/googleRedirect" class="btn btn-google btn-block py-3 font-weight-bold d-flex align-items-center justify-content-center">
                        <span class="google-mark mr-2">G</span>
                        Đăng nhập bằng Google
                    </a>
                <?php endif; ?>
            </div>
            <div class="text-center py-4" style="background: rgba(248, 250, 252, 0.82);">
                <p class="mb-0 small text-muted">Chưa có tài khoản? <a href="/webbanhang/AuthController/register" class="text-primary font-weight-bold">Đăng ký tại đây</a></p>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Đang xử lý...';
    });
</script>

<style>
    .auth-divider {
        position: relative;
        text-align: center;
    }

    .auth-divider::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: rgba(148, 163, 184, 0.24);
    }

    .auth-divider span {
        position: relative;
        padding: 0 12px;
        background: rgba(255,255,255,0.92);
        color: var(--text-muted);
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
    }

    .btn-google {
        background: #ffffff;
        color: #111827;
        border: 1px solid rgba(148, 163, 184, 0.22);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
    }

    .btn-google:hover,
    .btn-google:focus {
        color: #111827;
        background: #f8fafc;
        transform: translateY(-2px);
    }

    .google-mark {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #4285f4, #34a853);
        color: #fff;
        font-weight: 800;
        box-shadow: 0 8px 18px rgba(66, 133, 244, 0.22);
    }
</style>

<?php include 'app/views/shares/footer.php'; ?>
