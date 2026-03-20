<?php include 'app/views/shares/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="surface-card reveal-up">
            <div class="hero-banner" style="border-radius: 0;">
                <div class="section-kicker"><i class="fas fa-plus-circle"></i>Thêm sản phẩm</div>
                <h1 class="h3 font-weight-bold mt-3 mb-2 text-white">Tạo sản phẩm mới với thông tin và ảnh xem trước rõ ràng</h1>
                <p class="text-white-50 mb-0">Ảnh tải lên sẽ hiển thị ngay trong form để kiểm tra trước khi lưu.</p>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/webbanhang/ProductController/save" enctype="multipart/form-data" id="product-add-form">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Tên sản phẩm</label>
                                <input type="text" name="name" class="form-control" required placeholder="vd: iPhone 15 Pro Max">
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Mô tả chi tiết</label>
                                <textarea name="description" class="form-control" rows="5" required placeholder="Nhập đặc điểm nổi bật..."></textarea>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Giá bán (VND)</label>
                                <input type="number" name="price" class="form-control" required placeholder="0">
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Danh mục</label>
                                <div class="input-group">
                                    <select name="category_id" class="form-control">
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category->id; ?>"><?php echo htmlspecialchars($category->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <a href="/webbanhang/CategoryController/add" class="btn btn-outline-primary" title="Thêm danh mục mới" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="small font-weight-bold text-muted text-uppercase">Hình ảnh sản phẩm</label>
                                <div class="custom-file mb-3">
                                    <input type="file" name="image" class="custom-file-input" id="customFile" accept="image/*">
                                    <label class="custom-file-label" for="customFile">Chọn ảnh...</label>
                                </div>

                                <div id="image-preview-wrapper" class="soft-panel p-3 text-center">
                                    <div id="image-placeholder" class="text-muted">
                                        <i class="fa-solid fa-image mb-2" style="font-size: 2rem;"></i>
                                        <p class="mb-0 small">Ảnh xem trước sẽ hiển thị tại đây</p>
                                    </div>
                                    <img id="image-preview" src="" alt="Xem trước ảnh sản phẩm" class="img-fluid d-none" style="max-height: 220px; object-fit: contain; border-radius: 16px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end">
                        <a href="/webbanhang/ProductController" class="btn btn-light px-4 mr-2">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary px-5 font-weight-bold">
                            <i class="fas fa-save mr-2"></i>LƯU SẢN PHẨM
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const fileInput = document.getElementById('customFile');
    const fileLabel = document.querySelector('label[for="customFile"]');
    const imagePreview = document.getElementById('image-preview');
    const imagePlaceholder = document.getElementById('image-placeholder');
    const addForm = document.getElementById('product-add-form');

    function resetPreview() {
        fileLabel.textContent = 'Chọn ảnh...';
        imagePreview.src = '';
        imagePreview.classList.add('d-none');
        imagePlaceholder.classList.remove('d-none');
    }

    function showImageValidationError() {
        Swal.fire({
            icon: 'error',
            title: 'File không hợp lệ',
            text: 'Vui lòng chọn file ảnh có định dạng hợp lệ.',
            confirmButtonColor: '#f97316',
            borderRadius: '20px'
        });
    }

    fileInput.addEventListener('change', function (event) {
        const file = event.target.files[0];

        if (!file) {
            resetPreview();
            return;
        }

        if (!file.type.startsWith('image/')) {
            fileInput.value = '';
            resetPreview();
            showImageValidationError();
            return;
        }

        fileLabel.textContent = file.name;

        const reader = new FileReader();
        reader.onload = function (e) {
            imagePreview.src = e.target.result;
            imagePreview.classList.remove('d-none');
            imagePlaceholder.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    });

    addForm.addEventListener('submit', function (event) {
        const file = fileInput.files[0];
        if (file && !file.type.startsWith('image/')) {
            event.preventDefault();
            fileInput.value = '';
            resetPreview();
            showImageValidationError();
        }
    });
</script>

<?php include 'app/views/shares/footer.php'; ?>
