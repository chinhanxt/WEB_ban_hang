<?php include 'app/views/shares/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
            <div class="card-header bg-white py-3 border-0">
                <h4 class="font-weight-bold mb-0 text-primary"><i class="fas fa-plus-circle mr-2"></i>THÊM SẢN PHẨM MỚI</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/webbanhang/ProductController/save" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Tên sản phẩm</label>
                                <input type="text" name="name" class="form-control form-control-sm border-0 bg-light" style="border-radius: 8px; height: 40px;" required placeholder="vd: iPhone 15 Pro Max">
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Mô tả chi tiết</label>
                                <textarea name="description" class="form-control form-control-sm border-0 bg-light" style="border-radius: 8px;" rows="5" required placeholder="Nhập đặc điểm nổi bật..."></textarea>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Giá bán (VND)</label>
                                <input type="number" name="price" class="form-control form-control-sm border-0 bg-light" style="border-radius: 8px; height: 40px;" required placeholder="0">
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Danh mục</label>
                                <div class="input-group input-group-sm">
                                    <select name="category_id" class="form-control border-0 bg-light" style="border-radius: 8px 0 0 8px; height: 40px;">
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category->id; ?>"><?php echo htmlspecialchars($category->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <a href="/webbanhang/CategoryController/add" class="btn btn-outline-primary border-0 bg-light" title="Thêm danh mục mới" style="border-radius: 0 8px 8px 0; line-height: 28px;">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="small font-weight-bold text-muted text-uppercase">Hình ảnh sản phẩm</label>
                                <div class="custom-file custom-file-sm">
                                    <input type="file" name="image" class="custom-file-input" id="customFile">
                                    <label class="custom-file-label border-0 bg-light" for="customFile" style="border-radius: 8px; height: 40px; line-height: 30px;">Chọn ảnh...</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end">
                        <a href="/webbanhang/ProductController" class="btn btn-light btn-sm px-4 mr-2" style="border-radius: 8px;">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary btn-sm px-5 font-weight-bold" style="border-radius: 8px; height: 40px;">
                            <i class="fas fa-save mr-2"></i>LƯU SẢN PHẨM
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
