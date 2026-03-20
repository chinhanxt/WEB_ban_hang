<?php include 'app/views/shares/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
            <div class="card-header bg-white py-3 border-0">
                <h4 class="font-weight-bold mb-0 text-primary"><i class="fas fa-edit mr-2"></i>CHỈNH SỬA SẢN PHẨM</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/webbanhang/ProductController/update" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $product->Id; ?>">
                    <input type="hidden" name="old_image" value="<?php echo $product->Image; ?>">

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Tên sản phẩm</label>
                                <input type="text" name="name" class="form-control form-control-sm border-0 bg-light" style="border-radius: 8px; height: 40px;" required value="<?php echo htmlspecialchars($product->Name); ?>">
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Mô tả chi tiết</label>
                                <textarea name="description" class="form-control form-control-sm border-0 bg-light" style="border-radius: 8px;" rows="6" required><?php echo htmlspecialchars($product->Description); ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Giá bán (VND)</label>
                                <input type="number" name="price" class="form-control form-control-sm border-0 bg-light" style="border-radius: 8px; height: 40px;" required value="<?php echo $product->Price; ?>">
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Danh mục</label>
                                <select name="category_id" class="form-control form-control-sm border-0 bg-light" style="border-radius: 8px; height: 40px;">
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category->id; ?>" <?php echo $product->Category_Id == $category->id ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Hình ảnh hiện tại</label>
                                <div class="mb-2 text-center p-2 bg-light rounded" style="border: 1px dashed #ddd;">
                                    <?php if ($product->Image): ?>
                                        <img src="/webbanhang/<?php echo $product->Image; ?>" style="max-height: 100px; object-fit: contain;">
                                    <?php else: ?>
                                        <span class="text-muted small">Chưa có ảnh</span>
                                    <?php endif; ?>
                                </div>
                                <div class="custom-file custom-file-sm">
                                    <input type="file" name="image" class="custom-file-input" id="customFile">
                                    <label class="custom-file-label border-0 bg-light" for="customFile" style="border-radius: 8px; height: 40px; line-height: 30px;">Thay đổi ảnh...</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end">
                        <a href="/webbanhang/ProductController" class="btn btn-light btn-sm px-4 mr-2" style="border-radius: 8px;">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary btn-sm px-5 font-weight-bold" style="border-radius: 8px; height: 40px;">
                            <i class="fas fa-check-circle mr-2"></i>CẬP NHẬT THÔNG TIN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
