<?php include 'app/views/shares/header.php'; ?>

<div class="row justify-content-center mt-5 mb-5">
    <div class="col-md-5">
        <div class="card shadow-lg border-0 rounded-lg">
            <div class="card-header bg-white text-center py-4">
                <h3 class="font-weight-bold text-primary mb-0">ĐĂNG NHẬP</h3>
                <p class="text-muted small mt-2">Chào mừng bạn quay trở lại với Tech Store</p>
            </div>
            <div class="card-body p-5">
                <form id="loginForm" action="/webbanhang/AuthController/handleLogin" method="POST">
                    <div class="form-group mb-4">
                        <label for="email" class="font-weight-bold small text-uppercase">Email</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-envelope text-primary"></i></span>
                            </div>
                            <input type="text" name="email" id="email" class="form-control bg-light border-0" placeholder="example@gmail.com" value="<?php echo htmlspecialchars($_SESSION['old_input']['email'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="password" class="font-weight-bold small text-uppercase">Mật khẩu</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-primary"></i></span>
                            </div>
                            <input type="password" name="password" id="password" class="form-control bg-light border-0" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="rememberMe">
                            <label class="custom-control-label small text-muted" for="rememberMe">Ghi nhớ đăng nhập</label>
                        </div>
                        <a href="#" class="small text-primary font-weight-bold">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" id="btnSubmit" class="btn btn-primary btn-block py-3 font-weight-bold shadow-sm">
                        ĐĂNG NHẬP NGAY
                    </button>
                </form>
            </div>
            <div class="card-footer bg-light text-center py-3 border-0">
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

<?php include 'app/views/shares/footer.php'; ?>
