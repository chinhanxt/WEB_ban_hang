<?php include 'app/views/shares/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="surface-card reveal-up">
            <div class="hero-banner" style="border-radius: 0;">
                <div class="section-kicker"><i class="fas fa-edit"></i>Chỉnh sửa sản phẩm</div>
                <h1 class="h3 font-weight-bold mt-3 mb-2 text-white">Cập nhật thông tin và xem trước ảnh mới trước khi lưu</h1>
                <p class="text-white-50 mb-0">Ảnh hiện tại và ảnh mới sẽ được hiển thị rõ ràng để tránh cập nhật nhầm.</p>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/webbanhang/ProductController/update" enctype="multipart/form-data" id="product-edit-form">
                    <input type="hidden" name="id" value="<?php echo $product->Id; ?>">
                    <input type="hidden" name="old_image" value="<?php echo $product->Image; ?>">

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Tên sản phẩm</label>
                                <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($product->Name); ?>">
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Mô tả chi tiết</label>
                                <textarea name="description" class="form-control" rows="6" required><?php echo htmlspecialchars($product->Description); ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Giá bán (VND)</label>
                                <input type="number" name="price" class="form-control" required value="<?php echo $product->Price; ?>">
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Danh mục</label>
                                <select name="category_id" class="form-control">
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category->id; ?>" <?php echo $product->Category_Id == $category->id ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Hình ảnh hiện tại</label>
                                <div class="soft-panel mb-3 text-center p-3">
                                    <?php if ($product->Image): ?>
                                        <img id="current-image-preview" src="/webbanhang/<?php echo $product->Image; ?>" style="max-height: 160px; object-fit: contain; border-radius: 16px;" class="img-fluid">
                                    <?php else: ?>
                                        <div id="current-image-placeholder" class="text-muted">
                                            <i class="fa-solid fa-image mb-2" style="font-size: 2rem;"></i>
                                            <p class="mb-0 small">Sản phẩm này chưa có ảnh</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="custom-file mb-3">
                                    <input type="file" name="image" class="custom-file-input" id="customFile" accept="image/*">
                                    <label class="custom-file-label" for="customFile">Thay đổi ảnh...</label>
                                </div>
                                <div id="new-image-preview-wrapper" class="soft-panel p-3 text-center">
                                    <div id="new-image-placeholder" class="text-muted">
                                        <i class="fa-solid fa-wand-magic-sparkles mb-2" style="font-size: 2rem;"></i>
                                        <p class="mb-0 small">Ảnh mới bạn chọn sẽ xem trước ở đây</p>
                                    </div>
                                    <img id="new-image-preview" src="" alt="Xem trước ảnh mới" class="img-fluid d-none" style="max-height: 220px; object-fit: contain; border-radius: 16px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end">
                        <a href="/webbanhang/ProductController" class="btn btn-light px-4 mr-2">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary px-5 font-weight-bold">
                            <i class="fas fa-check-circle mr-2"></i>CẬP NHẬT THÔNG TIN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const editFileInput = document.getElementById('customFile');
    const editFileLabel = document.querySelector('label[for="customFile"]');
    const newImagePreview = document.getElementById('new-image-preview');
    const newImagePlaceholder = document.getElementById('new-image-placeholder');
    const editForm = document.getElementById('product-edit-form');

    function resetEditPreview() {
        editFileLabel.textContent = 'Thay đổi ảnh...';
        newImagePreview.src = '';
        newImagePreview.classList.add('d-none');
        newImagePlaceholder.classList.remove('d-none');
    }

    function showEditImageValidationError() {
        Swal.fire({
            icon: 'error',
            title: 'File không hợp lệ',
            text: 'Vui lòng chọn file ảnh có định dạng hợp lệ.',
            confirmButtonColor: '#f97316',
            borderRadius: '20px'
        });
    }

    editFileInput.addEventListener('change', function (event) {
        const file = event.target.files[0];

        if (!file) {
            resetEditPreview();
            return;
        }

        if (!file.type.startsWith('image/')) {
            editFileInput.value = '';
            resetEditPreview();
            showEditImageValidationError();
            return;
        }

        editFileLabel.textContent = file.name;

        const reader = new FileReader();
        reader.onload = function (e) {
            newImagePreview.src = e.target.result;
            newImagePreview.classList.remove('d-none');
            newImagePlaceholder.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    });

    editForm.addEventListener('submit', function (event) {
        const file = editFileInput.files[0];
        if (file && !file.type.startsWith('image/')) {
            event.preventDefault();
            editFileInput.value = '';
            resetEditPreview();
            showEditImageValidationError();
        }
    });
</script>

<?php include 'app/views/shares/footer.php'; ?>
