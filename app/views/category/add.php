<?php include 'app/views/shares/header.php'; ?>

<h1>Thêm Danh mục mới</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="/webbanhang/CategoryController/save">
    <div class="form-group">
        <label>Tên danh mục:</label>
        <input type="text" name="name" class="form-control" required placeholder="Nhập tên danh mục">
    </div>

    <div class="form-group">
        <label>Mô tả:</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Nhập mô tả danh mục"></textarea>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="fa fa-save"></i> Lưu danh mục
    </button>
    <a href="/webbanhang/ProductController/add" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Quay lại tạo sản phẩm
    </a>
</form>

<?php include 'app/views/shares/footer.php'; ?>
