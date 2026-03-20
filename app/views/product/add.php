<?php include 'app/views/shares/header.php'; ?>

<h1>Thêm sản phẩm mới</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="/webbanhang/ProductController/save" enctype="multipart/form-data">
    <div class="row">

        <!-- FORM -->
        <div class="col-md-6">

            <form method="POST" action="/webbanhang/ProductController/save" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="name">Tên sản phẩm:</label>
                    <input type="text" id="name" name="name" class="form-control" required minlength="10">
                </div>

                <div class="form-group">
                    <label for="description">Mô tả:</label>
                    <textarea id="description" name="description" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Giá:</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" required min="1000">
                </div>

                <div class="form-group">
                    <label for="category_id">Danh mục:</label>
                    <div class="input-group">
                        <select id="category_id" name="category_id" class="form-control" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category->id; ?>">
                                    <?php echo htmlspecialchars($category->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="input-group-append">
                            <a href="/webbanhang/CategoryController/add" class="btn btn-outline-success">
                                <i class="fa fa-plus"></i> Tạo danh mục
                            </a>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Hình ảnh</label>
                    <input type="file" name="image" class="form-control" onchange="previewImage(event)">
                </div>

                <button type="submit" class="btn btn-primary">
                    Thêm sản phẩm
                </button>

                <a href="/webbanhang/ProductController" class="btn btn-secondary mt-2">
                    Quay lại danh sách sản phẩm
                </a>

            </form>

        </div>
        <div class="col-md-6 text-center">

            <h4 class="mb-3">Xem trước hình ảnh</h4>

            <div id="preview-container">
                <img id="preview" src="https://via.placeholder.com/600x400?text=Preview">
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