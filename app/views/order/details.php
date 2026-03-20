<?php include 'app/views/shares/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Chi tiết đơn hàng #<?php echo $order['Id']; ?></h1>
    <a href="javascript:history.back()" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['Name']); ?></p>
                <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['Phone']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['Email'] ?: 'Chưa cung cấp'); ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['Address']); ?></p>
                <p><strong>Thanh toán:</strong> <span class="badge bg-light text-primary border border-primary"><?php echo htmlspecialchars($order['Payment_Method'] ?: 'Tiền mặt'); ?></span></p>
                <p><strong>Ghi chú:</strong> <br><small class="text-muted italic"><?php echo nl2br(htmlspecialchars($order['Notes'] ?: 'Không có ghi chú')); ?></small></p>
                <p><strong>Ngày đặt:</strong> <?php echo $order['Created_at']; ?></p>
                <p><strong>Trạng thái:</strong> 
                    <span class="badge bg-info text-dark"><?php echo htmlspecialchars($order['status']); ?></span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Danh sách sản phẩm</h5>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Hình ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['Image']): ?>
                                        <img src="/webbanhang/<?php echo $item['Image']; ?>" style="width: 50px; height: 50px; object-fit: cover;" class="rounded border">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo number_format($item['Price']); ?> VND</td>
                                <td><?php echo $item['Quantity']; ?></td>
                                <td><strong><?php echo number_format($item['Price'] * $item['Quantity']); ?> VND</strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-danger">
                            <td colspan="4" class="text-right"><strong>Tổng cộng:</strong></td>
                            <td><strong><?php echo number_format($order['total']); ?> VND</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
