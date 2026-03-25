<?php include 'app/views/shares/header.php'; ?>

<?php
$canCancel = !isAdmin() && OrderStateMachine::canUserCancel($order['status_code']);
$canConfirmCompleted = !isAdmin() && OrderStateMachine::canConfirmCompleted($order['status_code']);
$canRequestReturn = !isAdmin()
    && $order['status_code'] === OrderStateMachine::DELIVERED
    && (empty($returnRequest) || (($returnRequest['Status'] ?? null) === OrderStateMachine::RETURN_REJECTED));
$canReview = !isAdmin() && OrderStateMachine::canReview($order['status_code']);
$returnEvidence = [];
$returnStatus = $returnRequest['Status'] ?? null;

if (!empty($returnRequest['Evidence_Paths'])) {
    $decoded = json_decode($returnRequest['Evidence_Paths'], true);
    $returnEvidence = is_array($decoded) ? $decoded : [];
}

$formatLogState = function ($state) {
    if (!$state) {
        return 'N/A';
    }

    $returnStates = [
        OrderStateMachine::RETURN_REQUESTED_STEP,
        OrderStateMachine::RETURN_APPROVED,
        OrderStateMachine::RETURN_REJECTED,
        OrderStateMachine::RETURN_ITEMS_RECEIVED,
        OrderStateMachine::RETURN_REFUNDED,
    ];

    if (in_array($state, $returnStates, true)) {
        return OrderStateMachine::getReturnLabel($state);
    }

    $paymentStates = array_keys(OrderStateMachine::getPaymentOptions());
    if (in_array($state, $paymentStates, true)) {
        return OrderStateMachine::getPaymentLabel($state, null);
    }

    return OrderStateMachine::getStatusLabel($state);
};

$formatMetadata = function ($metadata) {
    if (empty($metadata)) {
        return [];
    }

    $decoded = json_decode($metadata, true);
    if (!is_array($decoded)) {
        return [];
    }

    $lines = [];
    foreach ($decoded as $key => $value) {
        if (is_array($value) || is_object($value)) {
            continue;
        }

        $labels = [
            'payment_status' => 'Trạng thái thanh toán',
            'item_count' => 'Số lượng sản phẩm',
            'carrier' => 'Đơn vị vận chuyển',
            'tracking_code' => 'Mã vận đơn',
            'estimated_delivery' => 'Dự kiến giao',
        ];

        $label = $labels[$key] ?? $key;
        $displayValue = $key === 'payment_status' ? OrderStateMachine::getPaymentLabel((string)$value) : (string)$value;
        $lines[] = $label . ': ' . $displayValue;
    }

    return $lines;
};
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start mb-4 reveal-up">
    <div>
        <div class="section-kicker"><i class="fa fa-receipt"></i>Đơn hàng #<?php echo $order['Id']; ?></div>
        <h1 class="section-title mb-2">Chi tiết đơn hàng</h1>
        <p class="section-subtitle mb-0">Theo dõi đầy đủ snapshot sản phẩm, thanh toán, vận chuyển, hoàn hàng và lịch sử thao tác của đơn.</p>
    </div>
    <a href="/webbanhang/OrderController" class="btn btn-outline-primary mt-3 mt-lg-0">Quay lại danh sách</a>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="surface-card p-4 mb-4 reveal-up stagger-1">
            <h5 class="font-weight-bold mb-3">Thông tin khách hàng</h5>
            <p class="mb-2"><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['Name']); ?></p>
            <p class="mb-2"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['Phone']); ?></p>
            <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($order['Email'] ?: 'Chưa cung cấp'); ?></p>
            <p class="mb-2"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['Address']); ?></p>
            <p class="mb-2"><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['Created_at'])); ?></p>
            <p class="mb-0"><strong>Ghi chú:</strong><br><span class="text-muted"><?php echo nl2br(htmlspecialchars($order['Notes'] ?: 'Không có ghi chú')); ?></span></p>
        </div>

        <div class="surface-card p-4 mb-4 reveal-up stagger-2">
            <h5 class="font-weight-bold mb-3">Tình trạng đơn</h5>
            <p class="mb-2"><strong>Trạng thái đơn hàng:</strong> <span class="badge <?php echo $order['status_badge_class']; ?>"><?php echo htmlspecialchars($order['status']); ?></span></p>
            <p class="mb-2"><strong>Thanh toán:</strong> <span class="badge <?php echo $order['payment_badge_class']; ?>"><?php echo htmlspecialchars($order['payment_label']); ?></span></p>
            <p class="mb-2"><strong>Phương thức:</strong> <?php echo htmlspecialchars($order['Payment_Method'] ?: 'Tiền mặt'); ?></p>
            <p class="mb-2"><strong>Đơn vị vận chuyển:</strong> <?php echo htmlspecialchars($order['carrier'] ?: 'Chưa cập nhật'); ?></p>
            <p class="mb-2"><strong>Mã vận đơn:</strong> <?php echo htmlspecialchars($order['tracking_code'] ?: 'Chưa có'); ?></p>
            <p class="mb-2"><strong>Dự kiến giao:</strong> <?php echo !empty($order['estimated_delivery']) ? date('d/m/Y H:i', strtotime($order['estimated_delivery'])) : 'Chưa cập nhật'; ?></p>
            <?php if (!empty($returnRequest)): ?>
                <p class="mb-0"><strong>Trạng thái hoàn hàng:</strong> <span class="badge <?php echo OrderStateMachine::getReturnBadgeClass($returnStatus); ?>"><?php echo htmlspecialchars(OrderStateMachine::getReturnLabel($returnStatus)); ?></span></p>
            <?php endif; ?>
        </div>

        <?php if ($canCancel): ?>
            <div class="surface-card p-4 mb-4 reveal-up stagger-3">
                <h5 class="font-weight-bold mb-3">Hủy đơn</h5>
                <form method="POST" action="/webbanhang/OrderController/cancel">
                    <input type="hidden" name="order_id" value="<?php echo $order['Id']; ?>">
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Lý do hủy</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Ví dụ: đổi địa chỉ, đặt nhầm sản phẩm..."></textarea>
                    </div>
                    <button class="btn btn-danger btn-block">Hủy đơn hàng</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($canConfirmCompleted): ?>
            <div class="surface-card p-4 mb-4 reveal-up stagger-3">
                <h5 class="font-weight-bold mb-3">Xác nhận hoàn tất</h5>
                <form method="POST" action="/webbanhang/OrderController/confirmCompleted">
                    <input type="hidden" name="order_id" value="<?php echo $order['Id']; ?>">
                    <button class="btn btn-primary btn-block">Tôi đã nhận được hàng</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($canRequestReturn): ?>
            <div class="surface-card p-4 mb-4 reveal-up stagger-4">
                <h5 class="font-weight-bold mb-3">Yêu cầu trả hàng / hoàn tiền</h5>
                <form method="POST" action="/webbanhang/OrderController/requestReturn" enctype="multipart/form-data">
                    <input type="hidden" name="order_id" value="<?php echo $order['Id']; ?>">
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Lý do</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Mô tả lỗi hoặc nguyên nhân muốn trả hàng"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Ảnh/Video minh chứng</label>
                        <input type="file" name="evidence[]" class="form-control" multiple required>
                    </div>
                    <button class="btn btn-outline-primary btn-block">Gửi yêu cầu</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="surface-card p-4 mb-4 reveal-up stagger-1">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-weight-bold mb-0">Snapshot sản phẩm trong đơn</h5>
                <span class="badge badge-primary"><?php echo count($details); ?> sản phẩm</span>
            </div>

            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá gốc</th>
                            <th>Giá bán</th>
                            <th>Thuế</th>
                            <th>SL</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($item['Product_Image'])): ?>
                                            <img src="/webbanhang/<?php echo htmlspecialchars($item['Product_Image']); ?>" style="width: 52px; height: 52px; object-fit: cover;" class="rounded border mr-3">
                                        <?php endif; ?>
                                        <div>
                                            <div class="font-weight-bold"><?php echo htmlspecialchars($item['Product_Name']); ?></div>
                                            <div class="small text-muted">Mã SP snapshot: <?php echo $item['Product_Id'] ? (int)$item['Product_Id'] : 'N/A'; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['Original_Price']); ?> VND</td>
                                <td><?php echo number_format($item['Sale_Price']); ?> VND</td>
                                <td><?php echo number_format($item['Tax_Amount']); ?> VND</td>
                                <td><?php echo (int)$item['Quantity']; ?></td>
                                <td class="font-weight-bold"><?php echo number_format($item['Subtotal']); ?> VND</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right font-weight-bold">Tổng cộng</td>
                            <td class="font-weight-bold text-primary"><?php echo number_format($order['total']); ?> VND</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <?php if (isAdmin()): ?>
            <div class="surface-card p-4 mb-4 reveal-up stagger-2">
                <h5 class="font-weight-bold mb-3">Điều phối OMS</h5>
                <div class="row">
                    <div class="col-md-4">
                        <form method="POST" action="/webbanhang/OrderController/updateStatus">
                            <input type="hidden" name="id" value="<?php echo $order['Id']; ?>">
                            <input type="hidden" name="redirect_details" value="1">
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">Chuyển trạng thái đơn</label>
                                <select name="status" class="form-control" required>
                                    <option value="">Chọn trạng thái kế tiếp</option>
                                    <?php foreach ($order['available_transitions'] as $transition): ?>
                                        <option value="<?php echo $transition; ?>"><?php echo OrderStateMachine::getStatusLabel($transition); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">Lý do</label>
                                <input type="text" name="reason" class="form-control" placeholder="Tùy chọn">
                            </div>
                            <button class="btn btn-primary btn-block">Cập nhật trạng thái</button>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form method="POST" action="/webbanhang/OrderController/updatePayment">
                            <input type="hidden" name="order_id" value="<?php echo $order['Id']; ?>">
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">Trạng thái thanh toán</label>
                                <select name="payment_status" class="form-control" required>
                                    <?php foreach (OrderStateMachine::getPaymentOptions() as $code => $label): ?>
                                        <option value="<?php echo $code; ?>" <?php echo $order['payment_status'] === $code ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">Ghi chú</label>
                                <input type="text" name="reason" class="form-control" placeholder="Ví dụ: đã xác minh thanh toán">
                            </div>
                            <button class="btn btn-outline-primary btn-block">Lưu thanh toán</button>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form method="POST" action="/webbanhang/OrderController/updateShipping">
                            <input type="hidden" name="order_id" value="<?php echo $order['Id']; ?>">
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">Đơn vị vận chuyển</label>
                                <input type="text" name="carrier" class="form-control" value="<?php echo htmlspecialchars($order['carrier'] ?: ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">Mã vận đơn</label>
                                <input type="text" name="tracking_code" class="form-control" value="<?php echo htmlspecialchars($order['tracking_code'] ?: ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">Dự kiến giao</label>
                                <input type="datetime-local" name="estimated_delivery" class="form-control" value="<?php echo !empty($order['estimated_delivery']) ? date('Y-m-d\TH:i', strtotime($order['estimated_delivery'])) : ''; ?>">
                            </div>
                            <button class="btn btn-outline-primary btn-block">Lưu vận chuyển</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($returnRequest)): ?>
            <div class="surface-card p-4 mb-4 reveal-up stagger-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold mb-0">Quy trình trả hàng / hoàn tiền</h5>
                    <span class="badge <?php echo OrderStateMachine::getReturnBadgeClass($returnStatus); ?>"><?php echo htmlspecialchars(OrderStateMachine::getReturnLabel($returnStatus)); ?></span>
                </div>
                <p class="mb-2"><strong>Lý do:</strong> <?php echo nl2br(htmlspecialchars($returnRequest['Reason'])); ?></p>
                <?php if (!empty($returnRequest['Admin_Note'])): ?>
                    <p class="mb-2"><strong>Ghi chú admin:</strong> <?php echo nl2br(htmlspecialchars($returnRequest['Admin_Note'])); ?></p>
                <?php endif; ?>

                <?php if (!empty($returnEvidence)): ?>
                    <div class="mb-3">
                        <strong>Minh chứng:</strong>
                        <div class="row mt-2">
                            <?php foreach ($returnEvidence as $path): ?>
                                <div class="col-md-4 mb-3">
                                    <a href="/webbanhang/<?php echo htmlspecialchars($path); ?>" target="_blank" class="d-block">
                                        <img src="/webbanhang/<?php echo htmlspecialchars($path); ?>" class="img-fluid rounded border">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isAdmin()): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <?php if ($returnStatus === OrderStateMachine::RETURN_REQUESTED_STEP): ?>
                                <form method="POST" action="/webbanhang/OrderController/reviewReturn" class="mb-3">
                                    <input type="hidden" name="return_request_id" value="<?php echo $returnRequest['Id']; ?>">
                                    <div class="form-group">
                                        <label class="small font-weight-bold text-muted">Nhận xét xử lý</label>
                                        <textarea name="admin_note" class="form-control" rows="3" placeholder="Ghi chú kiểm tra yêu cầu"></textarea>
                                    </div>
                                    <div class="d-flex">
                                        <button name="decision" value="approve" class="btn btn-primary flex-fill mr-2">Duyệt yêu cầu</button>
                                        <button name="decision" value="reject" class="btn btn-light flex-fill">Từ chối</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="soft-panel p-3 mb-3">
                                    <div class="small text-muted">Yêu cầu này đã được admin xử lý. Trạng thái hiện tại: <strong><?php echo htmlspecialchars(OrderStateMachine::getReturnLabel($returnStatus)); ?></strong>.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if ($returnStatus === OrderStateMachine::RETURN_APPROVED): ?>
                                <form method="POST" action="/webbanhang/OrderController/receiveReturnedItems" class="mb-3">
                                    <input type="hidden" name="return_request_id" value="<?php echo $returnRequest['Id']; ?>">
                                    <button class="btn btn-outline-primary btn-block">Xác nhận đã nhận hàng trả</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($returnStatus === OrderStateMachine::RETURN_ITEMS_RECEIVED): ?>
                                <form method="POST" action="/webbanhang/OrderController/refund">
                                    <input type="hidden" name="return_request_id" value="<?php echo $returnRequest['Id']; ?>">
                                    <div class="form-group">
                                        <label class="small font-weight-bold text-muted">Lý do hoàn tiền</label>
                                        <input type="text" name="reason" class="form-control" placeholder="Ví dụ: xác nhận đủ điều kiện hoàn tiền">
                                    </div>
                                    <button class="btn btn-success btn-block">Thực hiện hoàn tiền</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($canReview || !empty($reviews)): ?>
            <div class="surface-card p-4 mb-4 reveal-up stagger-4">
                <h5 class="font-weight-bold mb-3">Đánh giá sản phẩm</h5>
                <?php if ($canReview): ?>
                    <?php foreach ($details as $item): ?>
                        <?php if (!empty($item['review_id'])) { continue; } ?>
                        <form method="POST" action="/webbanhang/OrderController/submitReview" class="border rounded p-3 mb-3">
                            <input type="hidden" name="order_id" value="<?php echo $order['Id']; ?>">
                            <input type="hidden" name="order_item_id" value="<?php echo $item['Id']; ?>">
                            <div class="font-weight-bold mb-2"><?php echo htmlspecialchars($item['Product_Name']); ?></div>
                            <div class="form-row">
                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted">Số sao</label>
                                    <select name="rating" class="form-control" required>
                                        <option value="">Chọn</option>
                                        <option value="5">5 sao</option>
                                        <option value="4">4 sao</option>
                                        <option value="3">3 sao</option>
                                        <option value="2">2 sao</option>
                                        <option value="1">1 sao</option>
                                    </select>
                                </div>
                                <div class="col-md-9">
                                    <label class="small font-weight-bold text-muted">Nội dung</label>
                                    <textarea name="content" class="form-control" rows="3" required placeholder="Chia sẻ trải nghiệm thực tế của bạn"></textarea>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary mt-3">Gửi đánh giá</button>
                        </form>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="font-weight-bold"><?php echo htmlspecialchars($review['Product_Name']); ?></div>
                                <span class="badge badge-warning"><?php echo (int)$review['Rating']; ?>/5</span>
                            </div>
                            <div class="small text-muted mb-2"><?php echo htmlspecialchars($review['Fullname']); ?> • <?php echo date('d/m/Y H:i', strtotime($review['Created_At'])); ?></div>
                            <div><?php echo nl2br(htmlspecialchars($review['Content'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="surface-card p-4 reveal-up stagger-5">
            <h5 class="font-weight-bold mb-3">Lịch sử thao tác</h5>
            <?php if (empty($logs)): ?>
                <p class="text-muted mb-0">Chưa có lịch sử thao tác.</p>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="font-weight-bold"><?php echo htmlspecialchars(OrderStateMachine::getActionLabel($log['Action'])); ?></div>
                            <div class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($log['Created_At'])); ?></div>
                        </div>
                        <div class="small text-muted mb-2">
                            Người thực hiện:
                            <?php echo htmlspecialchars($log['Fullname'] ?: ($log['Action_By'] ? ('User #' . $log['Action_By']) : 'Hệ thống')); ?>
                        </div>
                        <?php if (!empty($log['From_State']) && !empty($log['To_State'])): ?>
                            <div class="mb-2">
                                <?php echo htmlspecialchars($formatLogState($log['From_State'])); ?> -> <?php echo htmlspecialchars($formatLogState($log['To_State'])); ?>
                            </div>
                        <?php elseif (!empty($log['To_State'])): ?>
                            <div class="mb-2">
                                Trạng thái mới: <?php echo htmlspecialchars($formatLogState($log['To_State'])); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($log['Reason'])): ?>
                            <div class="mb-2"><?php echo nl2br(htmlspecialchars($log['Reason'])); ?></div>
                        <?php endif; ?>
                        <?php $metadataLines = $formatMetadata($log['Metadata']); ?>
                        <?php if (!empty($metadataLines)): ?>
                            <div class="small text-muted">
                                <?php foreach ($metadataLines as $metadataLine): ?>
                                    <div><?php echo htmlspecialchars($metadataLine); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
