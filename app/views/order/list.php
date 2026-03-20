<?php include 'app/views/shares/header.php'; ?>

<h2 class="mb-4">Quản lý Đơn hàng</h2>

<div class="card border-0 shadow-sm" style="border-radius: 20px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="border-0 px-4 py-3">Mã đơn</th>
                        <th class="border-0 py-3">Khách hàng</th>
                        <th class="border-0 py-3">Tổng tiền</th>
                        <th class="border-0 py-3">Trạng thái</th>
                        <th class="border-0 py-3">Ngày tạo</th>
                        <th class="border-0 px-4 py-3">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="px-4 py-4 font-weight-bold">#<?php echo $order['Id']; ?></td>
                            <td class="py-4">
                                <div class="d-flex flex-column">
                                    <span class="font-weight-bold"><?php echo htmlspecialchars($order['Name']); ?></span>
                                    <small class="text-muted"><i class="fa fa-phone mr-1"></i><?php echo htmlspecialchars($order['Phone']); ?></small>
                                </div>
                            </td>
                            <td class="py-4 font-weight-bold text-primary"><?php echo number_format($order['total']); ?> VND</td>
                            <td class="py-4">
                                <?php
                                $statusClass = 'badge-secondary';
                                if ($order['status'] == 'Đang chờ xử lý') $statusClass = 'badge-warning';
                                elseif ($order['status'] == 'Đang giao') $statusClass = 'badge-info';
                                elseif ($order['status'] == 'Hoàn tất') $statusClass = 'badge-success';
                                elseif ($order['status'] == 'Đã hủy') $statusClass = 'badge-danger';
                                ?>
                                <span class="badge <?php echo $statusClass; ?> py-2 px-3">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td class="py-4 text-muted small"><?php echo $order['Created_at']; ?></td>
                            <td class="px-4 py-4">
                                <div class="d-flex align-items-center">
                                    <a href="/webbanhang/OrderController/viewDetails/<?php echo $order['Id']; ?>" class="btn btn-light btn-sm mr-2" title="Xem chi tiết" style="border-radius: 8px;">
                                        <i class="fa fa-eye text-primary"></i>
                                    </a>
                                    <form action="/webbanhang/OrderController/updateStatus" method="POST" class="d-inline-block mr-2">
                                        <input type="hidden" name="id" value="<?php echo $order['Id']; ?>">
                                        <select name="status" class="form-control form-control-sm bg-light border-0 shadow-none" 
                                                style="width: 140px; border-radius: 8px; height: 32px;" 
                                                onchange="this.form.submit()">
                                            <option value="" disabled>-- Cập nhật --</option>
                                            <option value="Đang chờ xử lý" <?php echo ($order['status'] == 'Đang chờ xử lý') ? 'selected' : ''; ?>>Đang chờ xử lý</option>
                                            <option value="Đang giao" <?php echo ($order['status'] == 'Đang giao') ? 'selected' : ''; ?>>Đang giao</option>
                                            <option value="Hoàn tất" <?php echo ($order['status'] == 'Hoàn tất') ? 'selected' : ''; ?>>Hoàn tất</option>
                                            <option value="Đã hủy" <?php echo ($order['status'] == 'Đã hủy') ? 'selected' : ''; ?>>Đã hủy</option>
                                        </select>
                                    </form>
                                    <a href="#" class="btn btn-light btn-sm" style="border-radius: 8px;" 
                                       onclick="return confirmDelete('/webbanhang/OrderController/delete/<?php echo $order['Id']; ?>', 'Đơn hàng này sẽ bị xóa vĩnh viễn!')">
                                        <i class="fa fa-trash text-danger"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>