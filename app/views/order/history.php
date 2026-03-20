<?php include 'app/views/shares/header.php'; ?>

<h1>Tra cứu lịch sử đơn hàng</h1>

<div class="card p-4 mb-4">
    <form method="GET" action="/webbanhang/OrderController/history">
        <div class="row align-items-end">
            <div class="col-md-4">
                <label>Nhập số điện thoại của bạn:</label>
                <input type="text" name="phone" class="form-control w-100" value="<?php echo htmlspecialchars($phone); ?>" required placeholder="Ví dụ: 0987654321">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-search"></i> Tìm kiếm
                </button>
            </div>
        </div>
    </form>
</div>

<?php if ($phone): ?>
    <h3>Kết quả cho số điện thoại: <?php echo htmlspecialchars($phone); ?></h3>
<?php else: ?>
    <h3>Tất cả đơn hàng của khách hàng</h3>
<?php endif; ?>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">Không tìm thấy đơn hàng nào.</div>
<?php else: ?>
    <table class="table table-hover">
        <thead class="thead-light">
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Chi tiết</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo $order['Id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($order['Name']); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($order['Phone']); ?></small>
                    </td>
                    <td><?php echo $order['Created_at']; ?></td>
                    <td><strong class="text-danger"><?php echo number_format($order['total']); ?> VND</strong></td>
                    <td>
                        <?php
                        $statusClass = 'bg-secondary';
                        if ($order['status'] == 'Đang chờ xử lý') $statusClass = 'bg-warning text-dark';
                        elseif ($order['status'] == 'Đang giao') $statusClass = 'bg-info text-dark';
                        elseif ($order['status'] == 'Hoàn tất') $statusClass = 'bg-success';
                        elseif ($order['status'] == 'Đã hủy') $statusClass = 'bg-danger';
                        ?>
                        <span class="badge <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="/webbanhang/OrderController/viewDetails/<?php echo $order['Id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-eye"></i> Xem sản phẩm
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'app/views/shares/footer.php'; ?>
