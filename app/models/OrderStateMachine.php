<?php

class OrderStateMachine
{
    public const PENDING = 'PENDING';
    public const CONFIRMED = 'CONFIRMED';
    public const PROCESSING = 'PROCESSING';
    public const SHIPPING = 'SHIPPING';
    public const DELIVERED = 'DELIVERED';
    public const COMPLETED = 'COMPLETED';
    public const CANCELLED = 'CANCELLED';
    public const RETURN_REQUESTED = 'RETURN_REQUESTED';
    public const RETURNED = 'RETURNED';
    public const REFUNDED = 'REFUNDED';

    public const PAYMENT_UNPAID = 'UNPAID';
    public const PAYMENT_PENDING = 'PENDING';
    public const PAYMENT_PAID = 'PAID';
    public const PAYMENT_FAILED = 'FAILED';
    public const PAYMENT_REFUND_PENDING = 'REFUND_PENDING';
    public const PAYMENT_REFUNDED = 'REFUNDED';

    public const RETURN_REQUESTED_STEP = 'REQUESTED';
    public const RETURN_APPROVED = 'APPROVED';
    public const RETURN_REJECTED = 'REJECTED';
    public const RETURN_ITEMS_RECEIVED = 'ITEMS_RECEIVED';
    public const RETURN_REFUNDED = 'REFUNDED';

    private static $statusLabels = [
        self::PENDING => 'Chờ xử lý',
        self::CONFIRMED => 'Đã xác nhận',
        self::PROCESSING => 'Đang chuẩn bị hàng',
        self::SHIPPING => 'Đang giao hàng',
        self::DELIVERED => 'Giao hàng thành công',
        self::COMPLETED => 'Hoàn tất',
        self::CANCELLED => 'Đã hủy',
        self::RETURN_REQUESTED => 'Yêu cầu trả hàng/hoàn tiền',
        self::RETURNED => 'Đã nhận hàng trả',
        self::REFUNDED => 'Đã hoàn tiền',
    ];

    private static $paymentLabels = [
        self::PAYMENT_UNPAID => 'Chưa thanh toán',
        self::PAYMENT_PENDING => 'Chờ thanh toán',
        self::PAYMENT_PAID => 'Đã thanh toán',
        self::PAYMENT_FAILED => 'Thanh toán thất bại',
        self::PAYMENT_REFUND_PENDING => 'Đang hoàn tiền',
        self::PAYMENT_REFUNDED => 'Đã hoàn tiền',
    ];

    private static $returnLabels = [
        self::RETURN_REQUESTED_STEP => 'Đã gửi yêu cầu',
        self::RETURN_APPROVED => 'Đã duyệt',
        self::RETURN_REJECTED => 'Từ chối',
        self::RETURN_ITEMS_RECEIVED => 'Đã nhận hàng trả',
        self::RETURN_REFUNDED => 'Đã hoàn tiền',
    ];

    private static $transitions = [
        self::PENDING => [self::CONFIRMED, self::CANCELLED],
        self::CONFIRMED => [self::PROCESSING, self::CANCELLED],
        self::PROCESSING => [self::SHIPPING],
        self::SHIPPING => [self::DELIVERED, self::CANCELLED],
        self::DELIVERED => [self::COMPLETED, self::RETURN_REQUESTED],
        self::RETURN_REQUESTED => [self::RETURNED, self::DELIVERED],
        self::RETURNED => [self::REFUNDED],
        self::COMPLETED => [],
        self::CANCELLED => [],
        self::REFUNDED => [],
    ];

    private static $legacyStatusMap = [
        'Đang chờ xử lý' => self::PENDING,
        'Đang giao' => self::SHIPPING,
        'Hoàn tất' => self::COMPLETED,
        'Đã hủy' => self::CANCELLED,
        'Chờ xử lý' => self::PENDING,
        'Đã xác nhận' => self::CONFIRMED,
        'Đang chuẩn bị hàng' => self::PROCESSING,
        'Đang giao hàng' => self::SHIPPING,
        'Giao hàng thành công' => self::DELIVERED,
        'Yêu cầu trả hàng/hoàn tiền' => self::RETURN_REQUESTED,
        'Đã nhận hàng trả' => self::RETURNED,
        'Đã hoàn tiền' => self::REFUNDED,
    ];

    public static function normalizeStatus(?string $status): string
    {
        if (!$status) {
            return self::PENDING;
        }

        if (isset(self::$statusLabels[$status])) {
            return $status;
        }

        return self::$legacyStatusMap[$status] ?? self::PENDING;
    }

    public static function normalizePaymentStatus(?string $status, ?string $paymentMethod = null): string
    {
        if (isset(self::$paymentLabels[$status])) {
            return $status;
        }

        if ($status === 'Đã thanh toán') {
            return self::PAYMENT_PAID;
        }

        if ($paymentMethod === 'Chuyển khoản') {
            return self::PAYMENT_PENDING;
        }

        return self::PAYMENT_UNPAID;
    }

    public static function getStatusLabel(?string $status): string
    {
        $status = self::normalizeStatus($status);
        return self::$statusLabels[$status] ?? self::$statusLabels[self::PENDING];
    }

    public static function getPaymentLabel(?string $status, ?string $paymentMethod = null): string
    {
        $status = self::normalizePaymentStatus($status, $paymentMethod);
        return self::$paymentLabels[$status] ?? self::$paymentLabels[self::PAYMENT_UNPAID];
    }

    public static function getReturnLabel(?string $status): string
    {
        return self::$returnLabels[$status] ?? self::$returnLabels[self::RETURN_REQUESTED_STEP];
    }

    public static function getReturnBadgeClass(?string $status): string
    {
        switch ($status) {
            case self::RETURN_APPROVED:
            case self::RETURN_ITEMS_RECEIVED:
            case self::RETURN_REFUNDED:
                return 'badge-success';
            case self::RETURN_REJECTED:
                return 'badge-danger';
            case self::RETURN_REQUESTED_STEP:
                return 'badge-warning';
            default:
                return 'badge-secondary';
        }
    }

    public static function getActionLabel(string $action): string
    {
        $labels = [
            'ORDER_CREATED' => 'Tạo đơn hàng',
            'ORDER_STATUS_UPDATED' => 'Cập nhật trạng thái đơn',
            'PAYMENT_STATUS_UPDATED' => 'Cập nhật trạng thái thanh toán',
            'SHIPPING_INFO_UPDATED' => 'Cập nhật vận chuyển',
            'SHIPPING_WEBHOOK_RECEIVED' => 'Nhận webhook vận chuyển',
            'RETURN_REQUEST_REVIEWED' => 'Xử lý yêu cầu trả hàng',
            'USER_CANCELLED' => 'Khách hàng hủy đơn',
            'REVIEW_CREATED' => 'Khách hàng đánh giá',
        ];

        return $labels[$action] ?? $action;
    }

    public static function getStatusOptions(): array
    {
        return self::$statusLabels;
    }

    public static function getPaymentOptions(): array
    {
        return self::$paymentLabels;
    }

    public static function getBadgeClass(?string $status): string
    {
        $status = self::normalizeStatus($status);

        switch ($status) {
            case self::PENDING:
            case self::CONFIRMED:
            case self::PROCESSING:
                return 'badge-warning';
            case self::SHIPPING:
            case self::DELIVERED:
                return 'badge-info';
            case self::COMPLETED:
            case self::REFUNDED:
                return 'badge-success';
            case self::CANCELLED:
                return 'badge-danger';
            case self::RETURN_REQUESTED:
            case self::RETURNED:
                return 'badge-primary';
            default:
                return 'badge-secondary';
        }
    }

    public static function getPaymentBadgeClass(?string $status, ?string $paymentMethod = null): string
    {
        $status = self::normalizePaymentStatus($status, $paymentMethod);

        switch ($status) {
            case self::PAYMENT_PAID:
            case self::PAYMENT_REFUNDED:
                return 'badge-success';
            case self::PAYMENT_PENDING:
            case self::PAYMENT_REFUND_PENDING:
                return 'badge-warning';
            case self::PAYMENT_FAILED:
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }

    public static function canTransition(?string $fromStatus, string $toStatus): bool
    {
        $fromStatus = self::normalizeStatus($fromStatus);
        $toStatus = self::normalizeStatus($toStatus);

        return in_array($toStatus, self::$transitions[$fromStatus] ?? [], true);
    }

    public static function getAvailableTransitions(?string $status): array
    {
        $status = self::normalizeStatus($status);
        return self::$transitions[$status] ?? [];
    }

    public static function canUserCancel(?string $status): bool
    {
        $status = self::normalizeStatus($status);
        return in_array($status, [self::PENDING, self::CONFIRMED], true);
    }

    public static function canConfirmCompleted(?string $status): bool
    {
        return self::normalizeStatus($status) === self::DELIVERED;
    }

    public static function canRequestReturn(?string $status): bool
    {
        $status = self::normalizeStatus($status);
        return in_array($status, [self::DELIVERED, self::COMPLETED], true);
    }

    public static function canReview(?string $status): bool
    {
        return self::normalizeStatus($status) === self::COMPLETED;
    }

    public static function mapWebhookStatusToOrderStatus(string $shippingStatus): ?string
    {
        $shippingStatus = strtoupper(trim($shippingStatus));

        $map = [
            'PICKED_UP' => self::SHIPPING,
            'IN_TRANSIT' => self::SHIPPING,
            'OUT_FOR_DELIVERY' => self::SHIPPING,
            'DELIVERED' => self::DELIVERED,
            'RETURNED' => self::CANCELLED,
            'FAILED' => self::CANCELLED,
        ];

        return $map[$shippingStatus] ?? null;
    }
}
