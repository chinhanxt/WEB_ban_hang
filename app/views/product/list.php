<?php include 'app/views/shares/header.php'; ?>

<h1 class="mb-4">Khám phá Sản phẩm</h1>

<!-- Bộ lọc sản phẩm -->
<div class="card mb-4 border-0 shadow-sm" style="background: #fff; border-radius: 15px;">
    <div class="card-body p-3">
        <form method="GET" action="/webbanhang/ProductController" class="row align-items-end gx-2">
            <div class="col-md-3">
                <label class="small font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 0.7rem;">Tìm kiếm</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light border-0" style="border-radius: 8px 0 0 8px;"><i class="fa fa-search text-primary small"></i></span>
                    </div>
                    <input type="text" name="keyword" class="form-control border-0 bg-light" style="border-radius: 0 8px 8px 0; height: 35px; font-size: 0.85rem;" value="<?php echo $_GET['keyword'] ?? ''; ?>" placeholder="Sản phẩm...">
                </div>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 0.7rem;">Danh mục</label>
                <select name="category_id" class="form-control form-control-sm border-0 bg-light" style="border-radius: 8px; height: 35px; font-size: 0.85rem;">
                    <option value="">Tất cả</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat->id; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 0.7rem;">Giá từ</label>
                <input type="number" name="min_price" class="form-control form-control-sm border-0 bg-light" style="border-radius: 8px; height: 35px; font-size: 0.85rem;" value="<?php echo $_GET['min_price'] ?? ''; ?>" placeholder="0">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 0.7rem;">Đến</label>
                <input type="number" name="max_price" class="form-control form-control-sm border-0 bg-light" style="border-radius: 8px; height: 35px; font-size: 0.85rem;" value="<?php echo $_GET['max_price'] ?? ''; ?>" placeholder="Tối đa">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm btn-block shadow-none font-weight-bold" style="height: 35px; border-radius: 8px; font-size: 0.85rem;">
                    <i class="fa fa-filter mr-1"></i>TÌM & LỌC
                </button>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="m-0 font-weight-bold">Danh sách công nghệ</h4>
    <?php if (isAdmin()): ?>
    <a href="/webbanhang/ProductController/add" class="btn btn-success" style="border-radius: 12px;">
        <i class="fa fa-plus-circle mr-2"></i>Thêm sản phẩm
    </a>
    <?php endif; ?>
</div>


<div class="row">

    <?php foreach ($products as $product): ?>

        <div class="col-md-3 mb-4">

            <div class="card h-100 border-0 shadow-sm" style="border-radius: 20px;">

                <?php if ($product->Image): ?>
                    <div style="position: relative;">
                        <img src="/webbanhang/<?php echo $product->Image; ?>" class="card-img-top"
                            style="height:220px; object-fit:cover;">
                        <?php if(isset($product->category_name)): ?>
                            <span class="badge badge-light" style="position: absolute; top: 15px; left: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                <?php echo htmlspecialchars($product->category_name); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="card-body d-flex flex-column p-4">

                    <h6 class="card-title mb-2">
                        <a href="/webbanhang/ProductController/show/<?php echo $product->Id; ?>" 
                           style="color: var(--text-dark); text-decoration: none; font-weight: 700; font-size: 1rem; transition: var(--transition);">
                            <?php echo htmlspecialchars($product->Name); ?>
                        </a>
                    </h6>

                    <p class="small text-muted mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 38px;">
                        <?php echo htmlspecialchars($product->Description); ?>
                    </p>

                    <div class="mt-auto">
                        <p class="h5 mb-3 text-primary font-weight-bold">
                            <?php echo number_format($product->Price); ?> <small>VND</small>
                        </p>

                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <button onclick="addToCart(<?php echo $product->Id; ?>)" class="btn btn-light btn-sm flex-fill mr-1" style="border-radius: 8px;">
                                <i class="fa fa-cart-plus text-success"></i>
                            </button>
                            <a href="/webbanhang/ProductController/buyNow/<?php echo $product->Id; ?>" class="btn btn-primary btn-sm flex-fill" style="border-radius: 8px;">
                                Mua ngay
                            </a>
                        </div>
                        
                        <?php if (isAdmin()): ?>
                        <div class="mt-2 d-flex justify-content-center">
                            <a href="/webbanhang/ProductController/edit/<?php echo $product->Id; ?>" class="text-muted small mr-3" title="Chỉnh sửa">
                                <i class="fa-solid fa-pen-to-square"></i> Sửa
                            </a>
                            <a href="#" class="text-danger small" title="Xoá"
                               onclick="return confirmDelete('/webbanhang/ProductController/delete/<?php echo $product->Id; ?>', 'Sản phẩm này sẽ bị xóa vĩnh viễn khỏi hệ thống!')">
                                <i class="fa-solid fa-trash"></i> Xóa
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
    <div class="text-center py-5">
        <i class="fa-solid fa-box-open fa-3x text-muted mb-3"></i>
        <p class="text-muted">Không tìm thấy sản phẩm nào phù hợp.</p>
    </div>
<?php endif; ?>

<style>
    .card-title a:hover {
        color: var(--primary-color) !important;
    }
</style>

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
