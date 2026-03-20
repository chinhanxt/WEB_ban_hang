<?php include 'app/views/shares/header.php'; ?>

<h1>Sửa sản phẩm</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!$product) {
    die("Không tìm thấy sản phẩm");
} ?>

<form method="POST" action="/webbanhang/ProductController/update" enctype="multipart/form-data">
    <div class="row">

        <div class="col-md-6">

            <form method="POST" action="/webbanhang/ProductController/update" enctype="multipart/form-data">

                <input type="hidden" name="id" value="<?php echo $product->Id; ?>">
                <input type="hidden" name="old_image" value="<?php echo $product->Image ?? ''; ?>">

                <div class="form-group">
                    <label>Tên sản phẩm</label>

                    <input type="text" name="name" class="form-control"
                        value="<?php echo htmlspecialchars($product->Name ?? '', ENT_QUOTES, 'UTF-8'); ?>" required minlength="10">

                </div>

                <div class="form-group">

                    <label>Mô tả</label>

                    <textarea name="description" class="form-control"
                        required><?php echo htmlspecialchars($product->Description ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                </div>

                <div class="form-group">

                    <label>Giá</label>

                    <input type="number" name="price" class="form-control" step="0.01"
                        value="<?php echo htmlspecialchars($product->Price ?? '', ENT_QUOTES, 'UTF-8'); ?>" required min="1000">

                </div>

                <div class="form-group">

                    <label>Danh mục</label>

                    <select name="category_id" class="form-control" required>

                        <?php foreach ($categories as $category): ?>

                            <option value="<?php echo $category->id; ?>" <?php echo ($category->id == ($product->Category_Id ?? null)) ? 'selected' : ''; ?>>

                                <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="form-group">

                    <label>Hình ảnh</label>

                    <input type="file" name="image" class="form-control" onchange="previewImage(event)">

                </div>

                <br>

                <button type="submit" class="btn btn-primary">
                    Lưu thay đổi
                </button>

                <a href="/webbanhang/ProductController" class="btn btn-secondary">
                    Quay lại danh sách
                </a>

            </form>

        </div>


        <div class="col-md-6 text-center">

            <h4 class="mb-3">Xem trước hình ảnh</h4>

            <div id="preview-container">

                <img id="preview"
                    src="/webbanhang/<?php echo $product->Image ?? 'https://via.placeholder.com/600x400?text=Preview'; ?>">

            </div>

        </div>

    </div>
</form>

<style>
    #preview-container {
        width: 100%;
        height: 550px;
        /* tăng chiều cao khung */
        border: 2px dashed #0d6efd;
        border-radius: 8px;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #f8f9fa;
    }

    #preview {
        width: 100%;
        height: 100%;
        object-fit: contain;
        /* giữ tỉ lệ ảnh */
    }
</style>
<script>
    function previewImage(event) {
        const reader = new FileReader();

        reader.onload = function () {
            const output = document.getElementById('preview');
            output.src = reader.result;
        }

        reader.readAsDataURL(event.target.files[0]);
    }
</script>
<?php include 'app/views/shares/footer.php'; ?>