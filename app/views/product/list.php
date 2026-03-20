<?php include 'app/views/shares/header.php'; ?>

<div class="hero-banner reveal-up mb-4">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="section-kicker"><i class="fa-solid fa-sparkles"></i>Bộ sưu tập công nghệ</div>
            <h1 class="section-title text-white">Mọi chức năng mua sắm được làm lại để sáng, rõ và dễ thao tác hơn.</h1>
            <p class="section-subtitle text-white-50 mb-0">Lọc nhanh, xem giá rõ ràng, thao tác mua hàng gọn và trực quan trên cả desktop lẫn mobile.</p>
        </div>
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="row">
                <div class="col-6 mb-3">
                    <div class="metric-card reveal-up stagger-1">
                        <span class="metric-label">Sản phẩm hiện có</span>
                        <div class="metric-value"><?php echo count($products); ?></div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="metric-card reveal-up stagger-2">
                        <span class="metric-label">Danh mục</span>
                        <div class="metric-value"><?php echo count($categories); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="soft-panel p-4 mb-4 reveal-up stagger-1">
    <form method="GET" action="/webbanhang/ProductController">
        <div class="row">
            <div class="col-lg-4 mb-3">
                <label class="small font-weight-bold text-muted">Tìm kiếm sản phẩm</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                    </div>
                    <input type="text" name="keyword" class="form-control" value="<?php echo $_GET['keyword'] ?? ''; ?>" placeholder="Nhập tên hoặc từ khóa sản phẩm">
                </div>
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
                <label class="small font-weight-bold text-muted">Danh mục</label>
                <select name="category_id" class="form-control">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat->id; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
                <label class="small font-weight-bold text-muted">Giá từ</label>
                <input type="number" name="min_price" class="form-control" value="<?php echo $_GET['min_price'] ?? ''; ?>" placeholder="0">
            </div>
            <div class="col-md-4 col-lg-2 mb-3">
                <label class="small font-weight-bold text-muted">Đến</label>
                <input type="number" name="max_price" class="form-control" value="<?php echo $_GET['max_price'] ?? ''; ?>" placeholder="Không giới hạn">
            </div>
            <div class="col-md-8 col-lg-2 mb-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-filter mr-2"></i>Lọc ngay
                </button>
            </div>
        </div>
    </form>
</div>

<div class="d-flex justify-content-between align-items-center mb-4 reveal-up stagger-2">
    <div>
        <div class="section-kicker"><i class="fa-solid fa-grid-2"></i>Danh sách hiển thị</div>
        <h2 class="h3 font-weight-bold mt-3 mb-1">Chọn sản phẩm phù hợp với nhu cầu của bạn</h2>
        <p class="text-muted mb-0">Thông tin giá, danh mục và nút thao tác được ưu tiên hiển thị rõ ràng.</p>
    </div>
    <?php if (isAdmin()): ?>
    <a href="/webbanhang/ProductController/add" class="btn btn-primary">
        <i class="fa fa-plus-circle mr-2"></i>Thêm sản phẩm
    </a>
    <?php endif; ?>
</div>

<div class="row">
    <?php foreach ($products as $index => $product): ?>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card hover-lift reveal-up stagger-<?php echo ($index % 6) + 1; ?>">
                <div class="position-relative">
                    <?php if ($product->Image): ?>
                        <img src="/webbanhang/<?php echo $product->Image; ?>" class="card-img-top" style="height: 240px; object-fit: cover;">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center" style="height: 240px; background: linear-gradient(135deg, #f8fafc, #fff7ed);">
                            <i class="fa-solid fa-image text-muted" style="font-size: 2.4rem;"></i>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($product->category_name)): ?>
                        <span class="badge badge-primary position-absolute" style="top: 16px; left: 16px;">
                            <?php echo htmlspecialchars($product->category_name); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <a href="/webbanhang/ProductController/show/<?php echo $product->Id; ?>" class="font-weight-bold mb-2" style="color: var(--text-dark); font-size: 1.05rem; line-height: 1.5; text-decoration: none;">
                        <?php echo htmlspecialchars($product->Name); ?>
                    </a>
                    <p class="text-muted mb-3" style="min-height: 48px; line-height: 1.7;">
                        <?php echo htmlspecialchars($product->Description); ?>
                    </p>
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="small text-muted d-block">Giá bán</span>
                                <strong style="font-size: 1.35rem; color: var(--primary-color);"><?php echo number_format($product->Price); ?> <small>VND</small></strong>
                            </div>
                            <span class="status-pill success"><i class="fa-solid fa-circle-check"></i>Còn hàng</span>
                        </div>
                        <div class="d-flex">
                            <button onclick="addToCart(<?php echo $product->Id; ?>)" class="btn btn-light mr-2" title="Thêm vào giỏ">
                                <i class="fa fa-cart-plus" style="color: var(--success-color);"></i>
                            </button>
                            <a href="/webbanhang/ProductController/buyNow/<?php echo $product->Id; ?>" class="btn btn-primary flex-fill">Mua ngay</a>
                        </div>
                        <?php if (isAdmin()): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid rgba(148, 163, 184, 0.14);">
                            <a href="/webbanhang/ProductController/edit/<?php echo $product->Id; ?>" class="small font-weight-bold" style="color: var(--primary-color); text-decoration: none;">
                                <i class="fa-solid fa-pen-to-square mr-1"></i>Sửa
                            </a>
                            <a href="#" class="small font-weight-bold text-danger" style="text-decoration: none;"
                               onclick="return confirmDelete('/webbanhang/ProductController/delete/<?php echo $product->Id; ?>', 'Sản phẩm này sẽ bị xóa vĩnh viễn khỏi hệ thống!')">
                                <i class="fa-solid fa-trash mr-1"></i>Xóa
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($products)): ?>
    <div class="surface-card empty-state reveal-up">
        <i class="fa-solid fa-box-open"></i>
        <h3 class="h5 font-weight-bold">Không tìm thấy sản phẩm phù hợp</h3>
        <p class="mb-0">Hãy thử đổi từ khóa tìm kiếm hoặc nới khoảng giá để xem thêm sản phẩm.</p>
    </div>
<?php endif; ?>

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
                        cancelButtonText: 'Để sau',
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
                        title: 'Sản phẩm đã có trong giỏ hàng',
                        text: 'Số lượng đã được tăng thêm.'
                    });
                } else {
                    Toast.fire({
                        icon: 'success',
                        title: 'Đã thêm vào giỏ hàng'
                    });
                }
            });
    }
</script>
<?php include 'app/views/shares/footer.php'; ?>
