<?php include 'app/views/shares/header.php'; ?>

<div class="surface-card p-4 p-lg-5 reveal-up">
    <div class="row align-items-center">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="soft-panel p-4 text-center">
                <?php if ($product->Image): ?>
                    <img src="/webbanhang/<?php echo $product->Image; ?>" class="img-fluid floating-accent" style="max-height: 480px; object-fit: contain; filter: drop-shadow(0 18px 32px rgba(15, 23, 42, 0.12));">
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center" style="height: 420px;">
                        <i class="fa-solid fa-image text-muted" style="font-size: 4rem;"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-6 pl-lg-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-3">
                    <li class="breadcrumb-item"><a href="/webbanhang/ProductController" style="color: var(--accent-color);">Cửa hàng</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Chi tiết sản phẩm</li>
                </ol>
            </nav>

            <div class="section-kicker"><i class="fa-solid fa-box-open"></i>Chi tiết sản phẩm</div>
            <h1 class="section-title"><?php echo htmlspecialchars($product->Name); ?></h1>

            <div class="d-flex flex-wrap align-items-center mb-4">
                <div class="mr-4 mb-2">
                    <span class="small text-muted d-block mb-1">Giá bán hiện tại</span>
                    <h2 class="mb-0 font-weight-bold" style="color: var(--primary-color);"><?php echo number_format($product->Price); ?> VND</h2>
                </div>
                <span class="status-pill success mb-2"><i class="fa fa-check-circle"></i>Còn hàng</span>
            </div>

            <div class="soft-panel p-4 mb-4">
                <h6 class="font-weight-bold text-uppercase small text-muted mb-3">Mô tả sản phẩm</h6>
                <p class="text-muted mb-0" style="line-height: 1.9; font-size: 1rem;">
                    <?php echo nl2br(htmlspecialchars($product->Description)); ?>
                </p>
            </div>

            <div class="row mb-4">
                <div class="col-sm-6 mb-3">
                    <button onclick="addToCart(<?php echo $product->Id; ?>)" class="btn btn-outline-primary btn-lg btn-block py-3">
                        <i class="fa fa-cart-plus mr-2"></i>Thêm vào giỏ
                    </button>
                </div>
                <div class="col-sm-6 mb-3">
                    <a href="/webbanhang/ProductController/buyNow/<?php echo $product->Id; ?>" class="btn btn-primary btn-lg btn-block py-3">
                        <i class="fa-solid fa-bolt mr-2"></i>Mua ngay
                    </a>
                </div>
            </div>

            <?php if (isAdmin()): ?>
            <div class="soft-panel p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <span class="small text-muted font-weight-bold mb-2 mb-md-0">Khu vực quản trị sản phẩm</span>
                <div>
                    <a href="/webbanhang/ProductController/edit/<?php echo $product->Id; ?>" class="btn btn-light btn-sm mr-2">
                        <i class="fa fa-edit mr-1"></i>Sửa
                    </a>
                    <a href="/webbanhang/ProductController" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-list mr-1"></i>Quay lại danh sách
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function addToCart(id) {
        fetch("/webbanhang/ProductController/addToCartAjax/" + id)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Yêu cầu đăng nhập',
                        text: data.message,
                        showCancelButton: true,
                        confirmButtonText: 'Đăng nhập ngay',
                        cancelButtonText: 'Để sau'
                        confirmButtonColor: '#f97316',
                        cancelButtonColor: '#64748b',
                        borderRadius: '20px'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '/webbanhang/AuthController/login';
                        }
                    });
                    return;
                }

                document.getElementById("cart-count").innerText = data.count;
                
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    background: '#ffffff',
                    borderRadius: '18px'
                });

                if (data.status === 'exists') {
                    Toast.fire({
                        icon: 'info',
                        title: 'Sản phẩm đã có trong giỏ hàng!',
                        text: 'Số lượng đã được tăng thêm.'
                    });
                } else {
                    Toast.fire({
                        icon: 'success',
                        title: 'Đã thêm vào giỏ hàng!'
                    });
                }
            });
    }
</script>

<?php include 'app/views/shares/footer.php'; ?>
