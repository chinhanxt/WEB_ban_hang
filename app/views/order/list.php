<?php include 'app/views/shares/header.php'; ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 reveal-up">
    <div>
        <div class="section-kicker"><i class="fas fa-file-invoice"></i>Đơn hàng</div>
        <h1 class="section-title mb-2"><?php echo isAdmin() ? 'Quản lý đơn hàng' : 'Đơn hàng của tôi'; ?></h1>
        <p class="section-subtitle mb-0">Trạng thái, thời gian tạo và hành động đều được gom rõ ràng để dễ theo dõi hơn.</p>
    </div>
    <span class="badge badge-primary px-3 py-2 mt-3 mt-md-0"><?php echo count($orders); ?> đơn hàng</span>
</div>

<div class="surface-card reveal-up stagger-1">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="border-0 px-4 py-3">Mã đơn</th>
                        <th class="border-0 py-3">Khách hàng</th>
                        <th class="border-0 py-3">Tổng tiền</th>
                        <th class="border-0 py-3">Trạng thái</th>
                        <th class="border-0 py-3">Ngày tạo</th>
                        <?php if (isAdmin()): ?>
                        <th class="border-0 px-4 py-3 text-right">Hành động</th>
                        <?php else: ?>
                        <th class="border-0 px-4 py-3 text-right">Chi tiết</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody style="font-size: 0.9rem;">
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="px-4 py-3 font-weight-bold text-primary">#<?php echo $order['Id']; ?></td>
                            <td class="py-3">
                                <div class="d-flex flex-column">
                                    <span class="font-weight-bold"><?php echo htmlspecialchars($order['Name']); ?></span>
                                    <small class="text-muted" style="font-size: 0.75rem;"><i class="fa fa-phone mr-1"></i><?php echo htmlspecialchars($order['Phone']); ?></small>
                                </div>
                            </td>
                            <td class="py-3 font-weight-bold text-dark"><?php echo number_format($order['total']); ?> <small>VND</small></td>
                            <td class="py-3">
                                <?php
                                $statusClass = 'badge-secondary';
                                if ($order['status'] == 'Đang chờ xử lý') $statusClass = 'badge-warning';
                                elseif ($order['status'] == 'Đang giao') $statusClass = 'badge-info';
                                elseif ($order['status'] == 'Hoàn tất') $statusClass = 'badge-success';
                                elseif ($order['status'] == 'Đã hủy') $statusClass = 'badge-danger';
                                ?>
                                <span class="badge <?php echo $statusClass; ?> py-1 px-2" style="font-size: 0.75rem; border-radius: 6px;">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td class="py-3 text-muted small"><?php echo date('d/m/Y H:i', strtotime($order['Created_at'])); ?></td>
                            <td class="px-4 py-3 text-right">
                                <div class="d-flex align-items-center justify-content-end">
                                    <a href="/webbanhang/OrderController/viewDetails/<?php echo $order['Id']; ?>" class="btn btn-light btn-sm mr-2 rounded-circle" title="Xem chi tiết" style="width: 32px; height: 32px; padding: 0; line-height: 32px;">
                                        <i class="fa fa-eye text-primary small"></i>
                                    </a>
                                    
                                    <?php if (isAdmin()): ?>
                                    <form action="/webbanhang/OrderController/updateStatus" method="POST" class="d-inline-block mr-2">
                                        <input type="hidden" name="id" value="<?php echo $order['Id']; ?>">
                                        <select name="status" class="form-control form-control-sm bg-light border-0 shadow-none" 
                                                style="width: 130px; border-radius: 8px; height: 30px; font-size: 0.8rem;" 
                                                onchange="this.form.submit()">
                                            <option value="" disabled>-- Cập nhật --</option>
                                            <option value="Đang chờ xử lý" <?php echo ($order['status'] == 'Đang chờ xử lý') ? 'selected' : ''; ?>>Đang chờ xử lý</option>
                                            <option value="Đang giao" <?php echo ($order['status'] == 'Đang giao') ? 'selected' : ''; ?>>Đang giao</option>
                                            <option value="Hoàn tất" <?php echo ($order['status'] == 'Hoàn tất') ? 'selected' : ''; ?>>Hoàn tất</option>
                                            <option value="Đã hủy" <?php echo ($order['status'] == 'Đã hủy') ? 'selected' : ''; ?>>Đã hủy</option>
                                        </select>
                                    </form>
                                    <a href="#" class="btn btn-light btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 32px;" 
                                       onclick="return confirmDelete('/webbanhang/OrderController/delete/<?php echo $order['Id']; ?>', 'Đơn hàng này sẽ bị xóa vĩnh viễn!')">
                                        <i class="fa fa-trash text-danger small"></i>
                                    </a>
                                    <?php endif; ?>
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
