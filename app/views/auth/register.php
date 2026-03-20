<?php include 'app/views/shares/header.php'; ?>

<div class="row justify-content-center mt-4 mb-5">
    <div class="col-lg-7 col-xl-6">
        <div class="surface-card reveal-up">
            <div class="hero-banner text-center" style="border-radius: 0;">
                <div class="section-kicker mx-auto"><i class="fa-solid fa-user-plus"></i>Tạo tài khoản</div>
                <h1 class="h3 font-weight-bold mt-3 mb-2 text-white">Tham gia Tech Store</h1>
                <p class="text-white-50 mb-0">Mọi trường thông tin được nhóm lại rõ ràng để giảm nhầm lẫn khi đăng ký.</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <form id="registerForm" action="/webbanhang/AuthController/handleRegister" method="POST" autocomplete="off">
                    
                    <div class="form-group mb-4">
                        <label for="fullname" class="font-weight-bold small text-uppercase">Họ và tên</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-user text-primary"></i></span>
                            </div>
                            <input type="text" name="fullname" id="fullname" class="form-control" placeholder="Nguyễn Văn A" value="<?php echo htmlspecialchars($_SESSION['old_input']['fullname'] ?? ''); ?>" required autocomplete="new-password">
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="username" class="font-weight-bold small text-uppercase">Tên đăng nhập (Username)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-at text-primary"></i></span>
                            </div>
                            <input type="text" name="username" id="username" class="form-control" placeholder="username12345" value="<?php echo htmlspecialchars($_SESSION['old_input']['username'] ?? ''); ?>" required autocomplete="new-password">
                        </div>
                        <small class="text-muted">Tối thiểu 10 ký tự.</small>
                    </div>

                    <div class="form-group mb-4">
                        <label for="email" class="font-weight-bold small text-uppercase">Email</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-envelope text-primary"></i></span>
                            </div>
                            <input type="text" name="email" id="email" class="form-control" placeholder="vd: tung123 (tự thêm @gmail.com)" value="<?php echo htmlspecialchars($_SESSION['old_input']['email'] ?? ''); ?>" required autocomplete="new-password">
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="password" class="font-weight-bold small text-uppercase">Mật khẩu</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-primary"></i></span>
                            </div>
                            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required autocomplete="new-password">
                        </div>
                        <div class="mt-2" id="passwordRules">
                            <small class="d-block text-muted rule" id="rule-len"><i class="far fa-circle mr-1"></i>Ít nhất 8 ký tự</small>
                            <small class="d-block text-muted rule" id="rule-case"><i class="far fa-circle mr-1"></i>Có chữ hoa và chữ thường</small>
                            <small class="d-block text-muted rule" id="rule-spec"><i class="far fa-circle mr-1"></i>Có chữ số hoặc ký tự đặc biệt</small>
                        </div>
                    </div>

                    <button type="submit" id="btnSubmit" class="btn btn-primary btn-block py-3 font-weight-bold">
                        ĐĂNG KÝ NGAY
                    </button>
                </form>
            </div>
            <div class="text-center py-4" style="background: rgba(248, 250, 252, 0.82);">
                <p class="mb-0 small text-muted">Đã có tài khoản? <a href="/webbanhang/AuthController/login" class="text-primary font-weight-bold">Đăng nhập ngay</a></p>
            </div>
        </div>
    </div>
</div>

<script>
    const passwordInput = document.getElementById('password');
    const registerForm = document.getElementById('registerForm');
    const btnSubmit = document.getElementById('btnSubmit');

    passwordInput.addEventListener('input', function() {
        const val = this.value;
        const len = val.length >= 8;
        const upLow = /[A-Z]/.test(val) && /[a-z]/.test(val);
        const specNum = /[0-9]/.test(val) || /[^A-Za-z0-9]/.test(val);

        updateRule('rule-len', len);
        updateRule('rule-case', upLow);
        updateRule('rule-spec', specNum);
    });

    function updateRule(id, valid) {
        const el = document.getElementById(id);
        const icon = el.querySelector('i');
        if (valid) {
            el.classList.remove('text-muted');
            el.classList.add('text-success');
            icon.classList.remove('far', 'fa-circle');
            icon.classList.add('fas', 'fa-check-circle');
        } else {
            el.classList.add('text-muted');
            el.classList.remove('text-success');
            icon.classList.add('far', 'fa-circle');
            icon.classList.remove('fas', 'fa-check-circle');
        }
    }

    registerForm.addEventListener('submit', function(e) {
        const username = document.getElementById('username').value;
        const password = passwordInput.value;

        if (username.length < 10) {
            Swal.fire('Lỗi!', 'Username phải từ 10 ký tự trở lên.', 'error');
            e.preventDefault();
            return;
        }

        const len = password.length >= 8;
        const upLow = /[A-Z]/.test(password) && /[a-z]/.test(password);
        const specNum = /[0-9]/.test(password) || /[^A-Za-z0-9]/.test(password);

        if (!(len && upLow && specNum)) {
            Swal.fire('Lỗi!', 'Mật khẩu không thỏa mãn các quy tắc bảo mật.', 'error');
            e.preventDefault();
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Đang xử lý...';
    });
</script>

<?php include 'app/views/shares/footer.php'; ?>
