<?php include 'app/views/shares/header.php'; ?>

<div class="row bg-white p-5 shadow-sm mb-5" style="border-radius: 24px;">

    <div class="col-md-6 text-center">
        <?php if ($product->Image): ?>
            <div class="product-image-container p-3" style="background: #fdfdfd; border-radius: 20px;">
                <img src="/webbanhang/<?php echo $product->Image; ?>" class="img-fluid" style="max-height: 500px; object-fit: contain; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.05));">
            </div>
        <?php else: ?>
            <div class="product-image-container bg-light d-flex align-items-center justify-content-center" style="height: 400px; border-radius: 20px;">
                <i class="fa-solid fa-image fa-4x text-muted opacity-50"></i>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-6 pl-md-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-3">
                <li class="breadcrumb-item"><a href="/webbanhang/ProductController" class="text-primary">Cửa hàng</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
            </ol>
        </nav>

        <h1 class="display-5 mb-2"><?php echo htmlspecialchars($product->Name); ?></h1>

        <div class="d-flex align-items-center mb-4">
            <h2 class="text-primary font-weight-bold m-0"><?php echo number_format($product->Price); ?> VND</h2>
            <span class="badge badge-success ml-3"><i class="fa fa-check-circle mr-1"></i>Còn hàng</span>
        </div>

        <div class="product-description border-top border-bottom py-4 mb-4">
            <h6 class="font-weight-bold text-uppercase small text-muted mb-3">Mô tả sản phẩm</h6>
            <p class="text-muted" style="line-height: 1.8; font-size: 1.05rem;">
                <?php echo nl2br(htmlspecialchars($product->Description)); ?>
            </p>
        </div>

        <div class="action-buttons mb-5">
            <div class="row">
                <div class="col-sm-6 mb-3">
                    <button onclick="addToCart(<?php echo $product->Id; ?>)" class="btn btn-outline-primary btn-lg btn-block shadow-sm py-3" style="border-width: 2px;">
                        <i class="fa fa-cart-plus mr-2"></i>Thêm vào giỏ
                    </button>
                </div>
                <div class="col-sm-6 mb-3">
                    <a href="/webbanhang/ProductController/buyNow/<?php echo $product->Id; ?>" class="btn btn-primary btn-lg btn-block shadow py-3">
                        <i class="fa-solid fa-bolt mr-2"></i>Mua ngay
                    </a>
                </div>
            </div>
        </div>

        <?php if (isAdmin()): ?>
        <div class="admin-controls bg-light p-3 rounded d-flex align-items-center justify-content-between">
            <span class="small text-muted font-weight-bold">Quản trị viên:</span>
            <div>
                <a href="/webbanhang/ProductController/edit/<?php echo $product->Id; ?>" class="btn btn-warning btn-sm mr-2 text-white">
                    <i class="fa fa-edit mr-1"></i>Sửa
                </a>
                <a href="/webbanhang/ProductController" class="btn btn-secondary btn-sm text-white">
                    <i class="fa fa-list mr-1"></i>Danh sách
                </a>
            </div>
        </div>
        <?php endif; ?>

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
