<?php include 'app/views/shares/header.php'; ?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end mb-4 reveal-up">
    <div>
        <div class="section-kicker"><i class="fas fa-file-invoice"></i>OMS</div>
        <h1 class="section-title mb-2"><?php echo isAdmin() ? 'Quản lý đơn hàng' : 'Đơn hàng của tôi'; ?></h1>
        <p class="section-subtitle mb-0">Lọc đơn theo trạng thái bằng tabs để theo dõi nhanh hơn, đồng thời hỗ trợ lọc theo khách hàng ở màn admin.</p>
    </div>
    <div class="d-flex gap-2 mt-3 mt-lg-0">
        <span class="badge badge-primary px-3 py-2"><?php echo count($orders); ?> đơn</span>
    </div>
</div>

<div class="surface-card p-4 mb-4 reveal-up stagger-1">
    <div class="d-flex flex-wrap align-items-center">
        <?php foreach ($tabs as $tab): ?>
            <?php
            $query = ['status' => $tab['label']];
            if (isAdmin() && !empty($selectedCustomerId)) {
                $query['customer_id'] = $selectedCustomerId;
            }
            $isActive = ($selectedStatus === $tab['label']) || ($selectedStatus === '' && $tab['label'] === 'Tất cả');
            ?>
            <a href="/webbanhang/OrderController?<?php echo http_build_query($query); ?>"
               class="mr-2 mb-2 px-3 py-2 rounded-pill font-weight-bold small <?php echo $isActive ? 'text-white' : 'text-dark'; ?>"
               style="<?php echo $isActive ? 'background: linear-gradient(135deg, var(--accent-color), #fb923c); box-shadow: var(--shadow-glow);' : 'background: rgba(255,255,255,0.88); border: 1px solid var(--border-color);'; ?>">
                <?php echo htmlspecialchars($tab['label']); ?>
                <span class="ml-2 badge <?php echo $isActive ? 'badge-light text-dark' : 'badge-primary'; ?>"><?php echo (int)$tab['count']; ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (isAdmin()): ?>
    <div class="surface-card p-3 mb-4 reveal-up stagger-2">
        <form method="GET" action="/webbanhang/OrderController">
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($selectedStatus ?: 'Tất cả'); ?>">
            <div class="row align-items-end">
                <div class="col-lg-3">
                    <label class="small font-weight-bold text-muted mb-1">Khách hàng</label>
                    <input type="text" id="customer-search" class="form-control mb-2" placeholder="Tìm theo tên, email hoặc username...">
                    <select name="customer_id" id="customer-select" class="form-control">
                        <option value="0">Tất cả khách hàng</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo (int)$customer->id; ?>" <?php echo $selectedCustomerId === (int)$customer->id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($customer->fullname . ' | ' . ($customer->email ?? 'Chưa có email') . ' | ' . $customer->username); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-1">
                    <button class="btn btn-primary btn-block">Lọc</button>
                </div>
                <div class="col-lg-1">
                    <a href="/webbanhang/OrderController?status=<?php echo urlencode($selectedStatus ?: 'Tất cả'); ?>" class="btn btn-light btn-block">Xóa</a>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>

<div class="surface-card reveal-up stagger-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="px-4 py-3">Mã đơn</th>
                        <th class="py-3">Khách hàng</th>
                        <th class="py-3">Tổng tiền</th>
                        <th class="py-3">Trạng thái</th>
                        <th class="py-3">Thanh toán</th>
                        <th class="py-3">Vận chuyển</th>
                        <th class="py-3">Ngày tạo</th>
                        <th class="px-4 py-3 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="py-5 text-center text-muted">Không có đơn hàng nào trong bộ lọc hiện tại.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="px-4 py-3 font-weight-bold text-primary">#<?php echo $order['Id']; ?></td>
                            <td class="py-3">
                                <div class="font-weight-bold"><?php echo htmlspecialchars($order['Name']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($order['Phone']); ?></div>
                            </td>
                            <td class="py-3 font-weight-bold"><?php echo number_format($order['total']); ?> VND</td>
                            <td class="py-3">
                                <span class="badge <?php echo $order['display_status_badge_class']; ?>"><?php echo htmlspecialchars($order['display_status']); ?></span>
                            </td>
                            <td class="py-3">
                                <span class="badge <?php echo $order['payment_badge_class']; ?>"><?php echo htmlspecialchars($order['payment_label']); ?></span>
                            </td>
                            <td class="py-3">
                                <?php if (!empty($order['tracking_code'])): ?>
                                    <div class="small font-weight-bold"><?php echo htmlspecialchars($order['carrier'] ?: 'Đang chờ đơn vị vận chuyển'); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($order['tracking_code']); ?></div>
                                <?php else: ?>
                                    <span class="text-muted small">Chưa có mã vận đơn</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-muted small"><?php echo date('d/m/Y H:i', strtotime($order['Created_at'])); ?></td>
                            <td class="px-4 py-3 text-right">
                                <a href="/webbanhang/OrderController/viewDetails/<?php echo $order['Id']; ?>" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                                <?php if (isAdmin() && empty($order['return_request_status']) && !empty($order['available_transitions'])): ?>
                                    <form action="/webbanhang/OrderController/updateStatus" method="POST" class="d-inline-block ml-2">
                                        <input type="hidden" name="id" value="<?php echo $order['Id']; ?>">
                                        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="">Chuyển trạng thái</option>
                                            <?php foreach ($order['available_transitions'] as $transition): ?>
                                                <option value="<?php echo $transition; ?>"><?php echo OrderStateMachine::getStatusLabel($transition); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (isAdmin()): ?>
    <script>
        (function () {
            const searchInput = document.getElementById('customer-search');
            const select = document.getElementById('customer-select');
            if (!searchInput || !select) return;

            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                Array.from(select.options).forEach((option, index) => {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }
                    option.hidden = keyword !== '' && !option.text.toLowerCase().includes(keyword);
                });
            });
        })();
    </script>
<?php endif; ?>

<?php include 'app/views/shares/footer.php'; ?>
