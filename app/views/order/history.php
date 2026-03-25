<?php include 'app/views/shares/header.php'; ?>

<div class="reveal-up mb-4">
    <div class="section-kicker"><i class="fa fa-clock-rotate-left"></i>Lịch sử</div>
    <h1 class="section-title mb-2">Tra cứu lịch sử đơn hàng</h1>
    <p class="section-subtitle mb-0">Theo dõi đơn theo số điện thoại hoặc tài khoản đã đăng nhập.</p>
</div>

<div class="surface-card p-4 mb-4 reveal-up stagger-1">
    <form method="GET" action="/webbanhang/OrderController/history">
        <div class="row align-items-end">
            <div class="col-md-5">
                <label class="small font-weight-bold text-muted">Số điện thoại</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" placeholder="Ví dụ: 0987654321">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-block">Lọc đơn hàng</button>
            </div>
        </div>
    </form>
</div>

<div class="surface-card reveal-up stagger-2">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4 py-3">Mã đơn</th>
                        <th class="py-3">Khách hàng</th>
                        <th class="py-3">Ngày đặt</th>
                        <th class="py-3">Tổng tiền</th>
                        <th class="py-3">Trạng thái</th>
                        <th class="py-3">Thanh toán</th>
                        <th class="px-4 py-3">Chi tiết</th>
                    </tr>
                </thead>
                <tbody id="order-history-body">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Không tìm thấy đơn hàng nào.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="px-4 py-3 font-weight-bold text-primary">#<?php echo $order['Id']; ?></td>
                            <td class="py-3">
                                <div class="font-weight-bold"><?php echo htmlspecialchars($order['Name']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($order['Phone']); ?></div>
                            </td>
                            <td class="py-3"><?php echo date('d/m/Y H:i', strtotime($order['Created_at'])); ?></td>
                            <td class="py-3 font-weight-bold"><?php echo number_format($order['total']); ?> VND</td>
                            <td class="py-3"><span class="badge <?php echo $order['display_status_badge_class']; ?>"><?php echo htmlspecialchars($order['display_status']); ?></span></td>
                            <td class="py-3"><span class="badge <?php echo $order['payment_badge_class']; ?>"><?php echo htmlspecialchars($order['payment_label']); ?></span></td>
                            <td class="px-4 py-3">
                                <a href="/webbanhang/OrderController/viewDetails/<?php echo $order['Id']; ?>" class="btn btn-sm btn-outline-primary">Xem</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tbody = document.getElementById('order-history-body');
        if (!tbody) return;

        const params = new URLSearchParams(window.location.search);
        const query = params.toString();
        const endpoint = '/webbanhang/api/order' + (query ? ('?' + query) : '');

        fetch(endpoint, {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Không thể tải danh sách đơn hàng từ API.');
                }
                return response.json();
            })
            .then(data => {
                if (!Array.isArray(data)) {
                    throw new Error('Dữ liệu API không hợp lệ.');
                }

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">Không tìm thấy đơn hàng nào.</td></tr>';
                    return;
                }

                tbody.innerHTML = data.map(order => {
                    const createdAt = order.Created_at ? new Date(order.Created_at.replace(' ', 'T')) : null;
                    const displayDate = createdAt && !Number.isNaN(createdAt.getTime())
                        ? createdAt.toLocaleString('vi-VN')
                        : '';
                    const total = Number(order.total || 0).toLocaleString('vi-VN');

                    return `
                        <tr>
                            <td class="px-4 py-3 font-weight-bold text-primary">#${order.Id}</td>
                            <td class="py-3">
                                <div class="font-weight-bold">${escapeHtml(order.Name || '')}</div>
                                <div class="small text-muted">${escapeHtml(order.Phone || '')}</div>
                            </td>
                            <td class="py-3">${displayDate}</td>
                            <td class="py-3 font-weight-bold">${total} VND</td>
                            <td class="py-3"><span class="badge ${escapeHtml(order.display_status_badge_class || 'badge-secondary')}">${escapeHtml(order.display_status || '')}</span></td>
                            <td class="py-3"><span class="badge ${escapeHtml(order.payment_badge_class || 'badge-secondary')}">${escapeHtml(order.payment_label || '')}</span></td>
                            <td class="px-4 py-3">
                                <a href="/webbanhang/OrderController/viewDetails/${order.Id}" class="btn btn-sm btn-outline-primary">Xem</a>
                            </td>
                        </tr>
                    `;
                }).join('');
            })
            .catch(error => {
                console.error(error);
            });
    });

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>

<?php include 'app/views/shares/footer.php'; ?>
