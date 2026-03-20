<?php include 'app/views/shares/header.php'; ?>

<h2 class="mb-4">Hoàn tất Đặt hàng</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">

    <div class="col-md-7">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
            <h5 class="mb-4 font-weight-bold"><i class="fa-solid fa-user-tag mr-2 text-primary"></i>Thông tin nhận hàng</h5>

            <form method="POST" action="/webbanhang/ProductController/placeOrder">

                <?php if (isset($single_product_id)): ?>
                    <input type="hidden" name="single_product_id" value="<?php echo $single_product_id; ?>">
                <?php endif; ?>

                <?php if (isset($selected_ids)): ?>
                    <?php foreach ($selected_ids as $sid): ?>
                        <input type="hidden" name="selected_ids[]" value="<?php echo $sid; ?>">
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted">Họ và tên</label>
                    <input type="text" name="name" class="form-control border-0 bg-light" style="height: 50px; border-radius: 10px;" required placeholder="Nhập họ và tên người nhận">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control border-0 bg-light" style="height: 50px; border-radius: 10px;" required pattern="[0-9]{10,11}" title="Vui lòng nhập số điện thoại từ 10-11 chữ số" placeholder="Ví dụ: 0987654321">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted">Email (Không bắt buộc)</label>
                            <input type="email" name="email" class="form-control border-0 bg-light" style="height: 50px; border-radius: 10px;" placeholder="example@gmail.com">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted">Địa chỉ giao hàng</label>
                    <textarea name="address" class="form-control border-0 bg-light" style="border-radius: 10px;" required rows="3" placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố"></textarea>
                </div>

                <div class="form-group mb-4">
                    <label class="small font-weight-bold text-muted d-block mb-3">Phương thức thanh toán</label>
                    <div class="row">
                        <div class="col-6">
                            <div class="custom-control custom-radio payment-option p-3 rounded bg-light border">
                                <input type="radio" id="cash" name="payment_method" class="custom-control-input" value="Tiền mặt" checked>
                                <label class="custom-control-label font-weight-bold pointer" for="cash">
                                    <i class="fa-solid fa-money-bill-1-wave mr-2 text-success"></i>Tiền mặt
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="custom-control custom-radio payment-option p-3 rounded bg-light border">
                                <input type="radio" id="transfer" name="payment_method" class="custom-control-input" value="Chuyển khoản">
                                <label class="custom-control-label font-weight-bold pointer" for="transfer">
                                    <i class="fa-solid fa-building-columns mr-2 text-primary"></i>Chuyển khoản
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="small font-weight-bold text-muted">Ghi chú đơn hàng</label>
                    <textarea name="notes" class="form-control border-0 bg-light" style="border-radius: 10px;" rows="2" placeholder="Dặn dò shipper hoặc cửa hàng..."></textarea>
                </div>

                <button class="btn btn-primary btn-lg btn-block shadow py-3" style="border-radius: 15px;">
                    <i class="fa-solid fa-lock mr-2"></i>Xác nhận đặt hàng ngay
                </button>

            </form>
        </div>
    </div>


    <div class="col-md-5">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
            <h5 class="mb-4 font-weight-bold"><i class="fa-solid fa-basket-shopping mr-2 text-primary"></i>Tóm tắt đơn hàng</h5>

            <div class="order-items mb-4">
                <?php
                $total = 0;
                foreach ($products as $p):
                    $sum = $p->Price * $p->qty;
                    $total += $sum;
                    ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <span class="badge badge-primary mr-2"><?php echo $p->qty; ?>x</span>
                            <span class="text-muted small font-weight-bold"><?php echo htmlspecialchars($p->Name); ?></span>
                        </div>
                        <span class="font-weight-bold small"><?php echo number_format($sum); ?> VND</span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Tạm tính:</span>
                <span><?php echo number_format($total); ?> VND</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="text-muted">Phí vận chuyển:</span>
                <span class="text-success font-weight-bold small">Miễn phí</span>
            </div>
            
            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <h5 class="font-weight-bold m-0 text-dark">Tổng cộng:</h5>
                <h4 class="text-primary font-weight-bold m-0"><?php echo number_format($total); ?> VND</h4>
            </div>
        </div>
        
        <div class="mt-4 p-3 bg-white shadow-sm" style="border-radius: 15px; border-left: 5px solid #28a745;">
            <p class="small m-0 text-muted">
                <i class="fa-solid fa-shield-halved mr-2 text-success"></i>Giao dịch của bạn luôn được bảo mật và an toàn.
            </p>
        </div>
    </div>

</div>

<style>
    .payment-option { transition: all 0.2s ease; cursor: pointer; }
    .payment-option:hover { background: #e2e8f0 !important; }
    .pointer { cursor: pointer; }
</style>

<?php include 'app/views/shares/footer.php'; ?>