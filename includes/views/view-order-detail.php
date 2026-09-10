<?php
/**
 * View: Chi Tiết Đơn Hàng (Order Detail View) - Chuẩn UI/UX KiotViet Pro
 */
if (!defined('ABSPATH')) exit;

$statuses = MKV_Orders::STATUSES;
$current_status = $statuses[$order->status] ?? array('label' => $order->status, 'badge' => 'mkv-badge-gray', 'next' => null);

$store_name    = get_option('mkv_store_name', 'Mini KiotViet');
$store_phone   = get_option('mkv_store_phone', '1900 6522');
$store_address = get_option('mkv_store_address', '');

$channel_labels = array(
    'pos'         => array('label' => mkv__('Tại quầy'),           'class' => 'mkv-badge-green'),
    'online'      => array('label' => mkv__('Online'),             'class' => 'mkv-badge-blue'),
    'social'      => array('label' => mkv__('Facebook / Zalo'),    'class' => 'mkv-badge-yellow'),
    'marketplace' => array('label' => mkv__('Sàn TMĐT'),           'class' => 'mkv-badge-purple'),
);
$channel = $channel_labels[$order->sales_channel ?? 'pos'] ?? $channel_labels['pos'];

$pm_map = array('cash' => mkv__('Tiền mặt'), 'transfer' => mkv__('Chuyển khoản'), 'card' => mkv__('Thẻ'));
$pm_icons = array(
    'cash'     => '<i class="hgi-stroke hgi-money-receive-square" style="color:var(--mkv-green);"></i>',
    'transfer' => '<i class="hgi-stroke hgi-bank" style="color:var(--mkv-primary);"></i>',
    'card'     => '<i class="hgi-stroke hgi-credit-card" style="color:var(--mkv-purple);"></i>',
);

$is_cod_pending = ($order->payment_status ?? '') === 'cod_pending';
$unpaid_balance = max(0.0, (float) $order->total_amount - (float) $order->paid_amount);
if ($is_cod_pending) {
    $payment_due = (float) ($order->cod_amount ?? 0) > 0 ? (float) $order->cod_amount : $unpaid_balance;
} else {
    if ((float) ($order->customer_debt_amount ?? 0) > 0) {
        $payment_due = (float) $order->customer_debt_amount;
    } elseif ((float) ($order->debt_amount ?? 0) > 0) {
        $payment_due = (float) $order->debt_amount;
    } else {
        $payment_due = $unpaid_balance;
    }
}
if ($order->status === 'cancelled') {
    $payment_due = 0.0;
}
?>
<?php require_once MKV_DIR . 'includes/views/header-kiotviet.php'; ?>

<div class="mkv-order-detail-wrap">
    <!-- TOP NAVIGATION & ACTIONS -->
    <div class="mkv-detail-top-nav">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <a href="<?php echo esc_url(admin_url('admin.php?page=mkv-orders')); ?>" class="mkv-back-btn">
                <i class="hgi-stroke hgi-arrow-left-01"></i> <?php echo esc_html(mkv__('Quay lại danh sách đơn hàng')); ?>
            </a>
            <span style="color:var(--mkv-border); font-size:14px;">/</span>
            <span style="font-size:13px; font-weight:600; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Chi tiết đơn')); ?> <?php echo esc_html($order->order_code); ?></span>
        </div>

        <div style="display:flex; align-items:center; gap:8px;">
            <a href="<?php echo esc_url(admin_url('admin.php?page=mkv-pos')); ?>" class="mkv-btn mkv-btn-secondary" style="height:34px; padding:0 12px; font-size:12.5px;">
                <i class="hgi-stroke hgi-add-square"></i> <?php echo esc_html(mkv__('Tạo đơn mới')); ?>
            </a>
        </div>
    </div>

    <!-- ORDER HERO CARD (HEADER + STEPPER UNIFIED) -->
    <div class="mkv-order-hero-card">
        <div class="mkv-hero-main-row">
            <div>
                <div class="mkv-hero-title-group">
                    <h2 class="mkv-order-title">
                        <?php echo esc_html($order->order_code); ?>
                    </h2>
                    <button type="button" class="mkv-copy-btn" onclick="mkvCopyOrderCode('<?php echo esc_js($order->order_code); ?>', this)" title="<?php echo esc_attr(mkv__('Sao chép mã đơn hàng')); ?>">
                        <i class="hgi-stroke hgi-copy-01"></i> <span><?php echo esc_html(mkv__('Copy')); ?></span>
                    </button>
                    <span class="mkv-badge <?php echo esc_attr($current_status['badge']); ?>" style="font-size:12px; padding:4px 10px;">
                        <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:currentColor; margin-right:4px;"></span>
                        <?php echo esc_html(mkv__($current_status['label'])); ?>
                    </span>
                    <span class="mkv-badge <?php echo esc_attr($channel['class']); ?>" style="font-size:12px; padding:4px 10px;">
                        <?php echo esc_html($channel['label']); ?>
                    </span>
                    <?php if ($payment_due <= 0 && $order->status !== 'cancelled'): ?>
                    <span class="mkv-badge mkv-badge-green" style="font-size:12px; padding:4px 10px;">
                        <i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Đã thanh toán đủ')); ?>
                    </span>
                    <?php elseif ($is_cod_pending): ?>
                    <span class="mkv-badge mkv-badge-purple" style="font-size:12px; padding:4px 10px;">
                        <i class="hgi-stroke hgi-truck-delivery"></i> <?php echo esc_html(mkv__('Thu COD: ')) . number_format($payment_due, 0, ',', '.') . ' ₫'; ?>
                    </span>
                    <?php elseif ($order->status !== 'cancelled'): ?>
                    <span class="mkv-badge mkv-badge-red" style="font-size:12px; padding:4px 10px;">
                        <i class="hgi-stroke hgi-alert-circle"></i> <?php echo esc_html(mkv__('Nợ: ')) . number_format($payment_due, 0, ',', '.') . ' ₫'; ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div class="mkv-hero-meta-row">
                    <span><i class="hgi-stroke hgi-calendar-01"></i> <?php echo esc_html(date('d/m/Y - H:i:s', strtotime($order->created_at))); ?></span>
                    <?php if (!empty($order->cashier_name)): ?>
                        <span><i class="hgi-stroke hgi-user"></i> <?php echo esc_html(mkv__('Thu ngân:')); ?> <strong><?php echo esc_html($order->cashier_name); ?></strong></span>
                    <?php endif; ?>
                    <?php if (!empty($order->warehouse_name)): ?>
                        <span><i class="hgi-stroke hgi-store-01"></i> <?php echo esc_html(mkv__('Kho xuất:')); ?> <strong><?php echo esc_html($order->warehouse_name); ?></strong></span>
                    <?php endif; ?>
                    <span><i class="hgi-stroke hgi-package"></i> <strong><?php echo count($items); ?></strong> <?php echo esc_html(mkv__('mặt hàng')); ?></span>
                </div>
            </div>

            <!-- Action Buttons for Order -->
            <div class="mkv-hero-actions">
                <!-- Print Invoice Button -->
                <button type="button" class="mkv-btn mkv-btn-secondary" onclick="mkvOpenPrintModal()" style="height:36px; padding:0 14px; font-weight:600; font-size:12.5px; display:inline-flex; align-items:center; gap:6px;">
                    <i class="hgi-stroke hgi-printer"></i> <?php echo esc_html(mkv__('In hóa đơn')); ?>
                </button>

                <!-- Push Shipping Button -->
                <?php if (!empty($order->shipping_provider) && get_option('mkv_shipping_enable', 0) == 1): ?>
                    <?php if (empty($order->tracking_code)): ?>
                        <?php if (!in_array($order->status, array('completed','cancelled','returned','draft'))): ?>
                        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=mkv_push_shipping&id='.$order->id), 'mkv_shipping_'.$order->id); ?>"
                           class="mkv-btn" style="background:#8b5cf6; color:#fff; border:none; height:36px; padding:0 14px; font-weight:600; font-size:12.5px; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 6px rgba(139,92,246,0.25);">
                            <i class="hgi-stroke hgi-truck-delivery"></i> <?php echo esc_html(mkv__('Tạo vận đơn')); ?>
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Next Status Button -->
                <?php if (!in_array($order->status, array('completed','cancelled','returned')) && isset($current_status['next']) && $current_status['next']): ?>
                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=mkv_update_order_status&id='.$order->id.'&new_status='.$current_status['next']), 'mkv_status_'.$order->id.'_'.$current_status['next']); ?>"
                   class="mkv-btn mkv-btn-primary" style="height:36px; padding:0 16px; font-weight:700; font-size:12.5px; display:inline-flex; align-items:center; gap:6px;">
                    <i class="hgi-stroke hgi-checkmark-circle-02"></i> → <?php echo esc_html(mkv__($statuses[$current_status['next']]['label'])); ?>
                </a>
                <?php endif; ?>

                <!-- Collect Debt / COD Button -->
                <?php if ($payment_due > 0 && !in_array($order->status, array('cancelled','returned'))): ?>
                <button type="button" class="mkv-btn" style="background:#10b981; color:#fff; border:none; height:36px; padding:0 16px; font-weight:700; font-size:12.5px; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 6px rgba(16,185,129,0.25);"
                    onclick='mkvOpenCollectModal(<?php echo htmlspecialchars(json_encode(array(
                        "id" => (int) $order->id,
                        "code" => $order->order_code,
                        "customer" => $order->customer_name ?: mkv__("Khách lẻ"),
                        "due" => (float) $payment_due,
                        "dueFormatted" => number_format($payment_due, 0, ",", "."),
                        "isCod" => $is_cod_pending,
                        "nonce" => wp_create_nonce("mkv_collect_order_debt_" . $order->id)
                    )), ENT_QUOTES, "UTF-8"); ?>)'>
                    <i class="hgi-stroke hgi-money-receive-02"></i> <?php echo esc_html($is_cod_pending ? mkv__('Đối soát COD') : mkv__('Thu tiền nợ')); ?>
                </button>
                <?php endif; ?>

                <!-- Return Order Button -->
                <?php if (in_array($order->status, array('paid','shipping','completed'))): ?>
                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=mkv_return_order&id='.$order->id), 'mkv_return_'.$order->id); ?>"
                   class="mkv-btn mkv-btn-secondary" style="height:36px; padding:0 12px; color:#d97706; font-size:12.5px; display:inline-flex; align-items:center; gap:6px;"
                   onclick="return confirm('<?php echo esc_js(mkv__('Khách muốn trả lại đơn hàng này? Tồn kho sẽ được hoàn lại và tiền sẽ tự động xuất khỏi Sổ Quỹ (Tạo Phiếu Chi).')); ?>');">
                    <i class="hgi-stroke hgi-arrow-reload-horizontal"></i> <?php echo esc_html(mkv__('Trả Hàng')); ?>
                </a>
                <?php endif; ?>

                <!-- Cancel Order Button -->
                <?php if (!in_array($order->status, array('completed','cancelled','returned'))): ?>
                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=mkv_cancel_order&id='.$order->id), 'mkv_cancel_'.$order->id); ?>"
                   class="mkv-btn mkv-btn-secondary" style="height:36px; padding:0 12px; color:#ef4444; font-size:12.5px; display:inline-flex; align-items:center; gap:6px;"
                   onclick="return confirm('<?php echo esc_js(mkv__('Hủy đơn hàng này? Tồn kho sẽ được hoàn trả.')); ?>');">
                    <i class="hgi-stroke hgi-cancel-01"></i> <?php echo esc_html(mkv__('Hủy Đơn')); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- INTEGRATED STEPPER TRACK -->
        <div class="mkv-hero-stepper-divider"></div>

        <?php if ($order->status === 'cancelled'): ?>
            <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:12px 16px; display:flex; align-items:center; gap:12px; color:#991b1b; font-size:13px;">
                <i class="hgi-stroke hgi-cancel-01" style="font-size:22px; flex-shrink:0;"></i>
                <div>
                    <strong><?php echo esc_html(mkv__('Đơn hàng đã bị hủy.')); ?></strong>
                    <span style="margin-left:6px; color:#b91c1c; font-size:12.5px;"><?php echo esc_html(mkv__('Số lượng hàng hóa trong đơn đã được hoàn trả lại tồn kho.')); ?></span>
                </div>
            </div>
        <?php elseif ($order->status === 'returned'): ?>
            <div style="background:#fffbeb; border:1px solid #fef3c7; border-radius:8px; padding:12px 16px; display:flex; align-items:center; gap:12px; color:#92400e; font-size:13px;">
                <i class="hgi-stroke hgi-arrow-reload-horizontal" style="font-size:22px; flex-shrink:0;"></i>
                <div>
                    <strong><?php echo esc_html(mkv__('Đơn hàng đã được trả lại.')); ?></strong>
                    <span style="margin-left:6px; color:#b45309; font-size:12.5px;"><?php echo esc_html(mkv__('Đã hoàn nhập tồn kho và tạo phiếu chi hoàn tiền trong Sổ Quỹ.')); ?></span>
                </div>
            </div>
        <?php else: ?>
            <?php
            $step_order = array('draft' => 1, 'pending' => 1, 'paid' => 2, 'shipping' => 3, 'completed' => 4);
            $current_step = $step_order[$order->status] ?? 1;
            ?>
            <div class="mkv-order-stepper">
                <div class="mkv-step-node <?php echo $current_step >= 1 ? 'completed' : ''; ?>">
                    <div class="mkv-step-dot"><i class="hgi-stroke hgi-invoice-01"></i></div>
                    <div class="mkv-step-label"><?php echo esc_html(mkv__('1. Đặt hàng')); ?></div>
                </div>
                <div class="mkv-step-connector <?php echo $current_step >= 2 ? 'completed' : ''; ?>"></div>
                <div class="mkv-step-node <?php echo $current_step >= 2 ? 'completed' : ($current_step === 1 ? 'active' : ''); ?>">
                    <div class="mkv-step-dot"><i class="hgi-stroke hgi-checkmark-circle-02"></i></div>
                    <div class="mkv-step-label"><?php echo esc_html(mkv__('2. Duyệt & Thanh toán')); ?></div>
                </div>
                <div class="mkv-step-connector <?php echo $current_step >= 3 ? 'completed' : ''; ?>"></div>
                <div class="mkv-step-node <?php echo $current_step >= 3 ? 'completed' : ($current_step === 2 ? 'active' : ''); ?>">
                    <div class="mkv-step-dot"><i class="hgi-stroke hgi-truck-delivery"></i></div>
                    <div class="mkv-step-label"><?php echo esc_html(mkv__('3. Giao hàng / Xuất kho')); ?></div>
                </div>
                <div class="mkv-step-connector <?php echo $current_step >= 4 ? 'completed' : ''; ?>"></div>
                <div class="mkv-step-node <?php echo $current_step >= 4 ? 'completed' : ($current_step === 3 ? 'active' : ''); ?>">
                    <div class="mkv-step-dot"><i class="hgi-stroke hgi-package"></i></div>
                    <div class="mkv-step-label"><?php echo esc_html(mkv__('4. Hoàn thành')); ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- MAIN TWO-COLUMN GRID -->
    <div class="mkv-order-detail-grid">
        <!-- LEFT COLUMN: PRODUCTS & FINANCIAL BREAKDOWN & CASHBOOK -->
        <div class="mkv-order-detail-left">
            <!-- PRODUCTS TABLE CARD -->
            <div class="mkv-card" style="border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                <div style="padding:16px 20px; border-bottom:1px solid var(--mkv-border); display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                    <div style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:15px; color:var(--mkv-text-main);">
                        <i class="hgi-stroke hgi-package" style="color:var(--mkv-primary);"></i>
                        <span><?php echo esc_html(mkv__('Danh sách sản phẩm')); ?> (<?php echo count($items); ?> <?php echo esc_html(mkv__('mặt hàng')); ?>)</span>
                    </div>
                </div>

                <div class="mkv-table-scroll-container">
                    <table class="mkv-table" style="margin:0; width:100%;">
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th style="width:48px; text-align:center; padding-left:12px;"><?php echo esc_html(mkv__('Ảnh')); ?></th>
                                <th><?php echo esc_html(mkv__('Tên sản phẩm / SKU')); ?></th>
                                <th style="width:65px; text-align:center;"><?php echo esc_html(mkv__('ĐVT')); ?></th>
                                <th style="width:105px; text-align:right;"><?php echo esc_html(mkv__('Đơn giá')); ?></th>
                                <th style="width:50px; text-align:center;"><?php echo esc_html(mkv__('SL')); ?></th>
                                <th style="width:115px; text-align:right; padding-right:16px;"><?php echo esc_html(mkv__('Thành tiền')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                            <tr><td colspan="6" style="text-align:center; padding:30px; color:#94a3b8;"><?php echo esc_html(mkv__('Không có dữ liệu mặt hàng.')); ?></td></tr>
                            <?php else: foreach ($items as $it): ?>
                            <tr>
                                <td style="text-align:center; padding-left:12px;">
                                    <?php if (!empty($it->thumb)): ?>
                                        <img src="<?php echo esc_url($it->thumb); ?>" alt="" style="width:36px; height:36px; object-fit:cover; border-radius:6px; border:1px solid #e2e8f0; display:inline-block;">
                                    <?php else: ?>
                                        <div style="width:36px; height:36px; border-radius:6px; background:#f1f5f9; display:inline-flex; align-items:center; justify-content:center; color:#94a3b8; font-size:16px;">
                                            <i class="hgi-stroke hgi-package"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color:var(--mkv-text-main); font-size:13px; display:block; margin-bottom:2px; line-height:1.4;">
                                        <?php echo esc_html($it->product_name ?: ('Sản phẩm #' . $it->product_id)); ?>
                                    </strong>
                                    <div style="font-size:11px; color:#64748b; display:flex; flex-wrap:wrap; gap:8px;">
                                        <span>SKU: <strong><?php echo esc_html($it->sku); ?></strong></span>
                                        <?php if (!empty($it->barcode)): ?>
                                            <span>Barcode: <?php echo esc_html($it->barcode); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="text-align:center; font-size:12.5px; color:#475569;">
                                    <span class="mkv-badge" style="background:#f1f5f9; color:#475569; padding:2px 8px; font-size:11.5px;"><?php echo esc_html($it->unit); ?></span>
                                </td>
                                <td style="text-align:right; font-size:13px; font-weight:500;">
                                    <?php echo number_format($it->price, 0, ',', '.'); ?> ₫
                                </td>
                                <td style="text-align:center; font-size:13.5px; font-weight:700;">
                                    <?php echo (int) $it->qty; ?>
                                </td>
                                <td style="text-align:right; font-size:13.5px; font-weight:700; color:var(--mkv-text-main); padding-right:16px;">
                                    <?php echo number_format($it->subtotal, 0, ',', '.'); ?> ₫
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- FINANCIAL SUMMARY BREAKDOWN -->
                <?php
                $point_value = (float) get_option('mkv_point_value', 1);
                if ($point_value <= 0) $point_value = 1;

                $subtotal     = (float) $order->subtotal;
                $raw_discount = (float) $order->discount;
                $tax          = (float) $order->tax;
                $shipping_fee = (float) $order->shipping_fee;
                $total_amount = (float) $order->total_amount;
                $paid_amount  = (float) $order->paid_amount;
                $points_used  = (int) $order->points_used;

                // Tách biệt chiết khấu khuyến mại và điểm thưởng để không bao giờ bị nhân đôi hoặc sai số
                $points_money = 0.0;
                $general_discount = $raw_discount;
                if ($points_used > 0) {
                    $expected_pts_money = $points_used * $point_value;
                    if ($raw_discount > 0 && abs($raw_discount - ($points_used * 100)) < 1) {
                        // Trường hợp rate 100đ/điểm khớp chính xác với discount trong đơn
                        $points_money = $raw_discount;
                        $general_discount = 0.0;
                    } elseif ($raw_discount >= $expected_pts_money) {
                        $points_money = $expected_pts_money;
                        $general_discount = max(0.0, $raw_discount - $points_money);
                    } else {
                        $points_money = $raw_discount;
                        $general_discount = 0.0;
                    }
                }
                ?>
                <div style="padding: 20px 24px; background:#fafafa; border-top:1px solid var(--mkv-border);">
                    <div style="max-width: 380px; margin-left: auto; display:flex; flex-direction:column; gap:8px;">
                        <div style="display:flex; justify-content:space-between; font-size:13px; color:#475569;">
                            <span><?php echo esc_html(mkv__('Tổng tiền hàng:')); ?></span>
                            <span style="font-weight:600;"><?php echo number_format($subtotal, 0, ',', '.'); ?> ₫</span>
                        </div>

                        <?php if ($general_discount > 0): ?>
                        <div style="display:flex; justify-content:space-between; font-size:13px; color:#ef4444;">
                            <span><?php echo esc_html(mkv__('Chiết khấu / Giảm giá:')); ?></span>
                            <span style="font-weight:600;">-<?php echo number_format($general_discount, 0, ',', '.'); ?> ₫</span>
                        </div>
                        <?php endif; ?>

                        <?php if ($points_money > 0): ?>
                        <div style="display:flex; justify-content:space-between; font-size:13px; color:#2563eb;">
                            <span><?php echo esc_html(mkv__('Trừ điểm thưởng:')); ?> (<?php echo number_format($points_used, 0, ',', '.'); ?> <?php echo esc_html(mkv__('điểm')); ?>)</span>
                            <span style="font-weight:600;">-<?php echo number_format($points_money, 0, ',', '.'); ?> ₫</span>
                        </div>
                        <?php endif; ?>

                        <?php if ($tax > 0): ?>
                        <div style="display:flex; justify-content:space-between; font-size:13px; color:#475569;">
                            <span><?php echo esc_html(mkv__('Thuế VAT:')); ?></span>
                            <span style="font-weight:600;">+<?php echo number_format($tax, 0, ',', '.'); ?> ₫</span>
                        </div>
                        <?php endif; ?>

                        <?php if ($shipping_fee > 0): ?>
                        <div style="display:flex; justify-content:space-between; font-size:13px; color:#475569;">
                            <span><?php echo esc_html(mkv__('Phí giao hàng:')); ?></span>
                            <span style="font-weight:600;">+<?php echo number_format($shipping_fee, 0, ',', '.'); ?> ₫</span>
                        </div>
                        <?php endif; ?>

                        <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:800; color:var(--mkv-primary); padding-top:10px; margin-top:4px; border-top:1px solid #e2e8f0;">
                            <span><?php echo esc_html(mkv__('Tổng thanh toán:')); ?></span>
                            <span><?php echo number_format($total_amount, 0, ',', '.'); ?> ₫</span>
                        </div>

                        <div style="display:flex; justify-content:space-between; font-size:13.5px; color:#10b981; font-weight:600;">
                            <span><?php echo esc_html(mkv__('Khách đã trả:')); ?></span>
                            <span><?php echo number_format($paid_amount, 0, ',', '.'); ?> ₫</span>
                        </div>

                        <?php if ($payment_due > 0 && $order->status !== 'cancelled'): ?>
                        <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:700; color:#ef4444; background:#fef2f2; padding:6px 12px; border-radius:6px; margin-top:4px;">
                            <span><?php echo esc_html($is_cod_pending ? mkv__('Tiền COD chờ thu:') : mkv__('Còn nợ:')); ?></span>
                            <span><?php echo number_format($payment_due, 0, ',', '.'); ?> ₫</span>
                        </div>
                        <?php else: ?>
                        <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:600; color:#10b981; background:#ecfdf5; padding:6px 12px; border-radius:6px; margin-top:4px;">
                            <span><?php echo esc_html(mkv__('Tình trạng:')); ?></span>
                            <span><i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Đã thanh toán đủ')); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- CASHBOOK LOGS CARD -->
            <div class="mkv-card" style="border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                <div style="padding:16px 20px; border-bottom:1px solid var(--mkv-border); display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                    <div style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:15px; color:var(--mkv-text-main);">
                        <i class="hgi-stroke hgi-wallet-02" style="color:var(--mkv-green);"></i>
                        <span><?php echo esc_html(mkv__('Lịch sử giao dịch Sổ Quỹ')); ?></span>
                    </div>
                    <?php if (current_user_can('mkv_manage_cashbook')): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mkv-cashbook')); ?>" style="font-size:12px; color:var(--mkv-primary); text-decoration:none; font-weight:600;">
                        <?php echo esc_html(mkv__('Xem Sổ Quỹ')); ?> →
                    </a>
                    <?php endif; ?>
                </div>

                <div class="mkv-table-scroll-container">
                    <table class="mkv-table" style="margin:0; width:100%;">
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th style="width:75px; padding-left:16px;"><?php echo esc_html(mkv__('Mã phiếu')); ?></th>
                                <th style="width:115px;"><?php echo esc_html(mkv__('Thời gian')); ?></th>
                                <th style="width:90px; text-align:center;"><?php echo esc_html(mkv__('Loại')); ?></th>
                                <th style="width:110px;"><?php echo esc_html(mkv__('Phương thức')); ?></th>
                                <th style="text-align:right; width:110px;"><?php echo esc_html(mkv__('Số tiền')); ?></th>
                                <th style="padding-right:16px;"><?php echo esc_html(mkv__('Người ghi nhận')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cashbook_logs)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding:24px; color:#94a3b8; font-size:13px;">
                                    <i class="hgi-stroke hgi-information-circle"></i> <?php echo esc_html(mkv__('Chưa có phiếu Thu/Chi phát sinh cho đơn hàng này.')); ?>
                                </td>
                            </tr>
                            <?php else: foreach ($cashbook_logs as $cb): ?>
                            <tr>
                                <td style="padding-left:16px;"><strong>#<?php echo $cb->id; ?></strong></td>
                                <td style="font-size:12px; color:#64748b;"><?php echo esc_html(date('d/m/Y H:i', strtotime($cb->created_at))); ?></td>
                                <td style="text-align:center;">
                                    <?php if ($cb->type === 'thu'): ?>
                                        <span class="mkv-badge mkv-badge-green" style="font-size:11.5px; padding:2px 8px;"><i class="hgi-stroke hgi-money-receive-square"></i> <?php echo esc_html(mkv__('Phiếu Thu')); ?></span>
                                    <?php else: ?>
                                        <span class="mkv-badge mkv-badge-red" style="font-size:11.5px; padding:2px 8px;"><i class="hgi-stroke hgi-money-send-square"></i> <?php echo esc_html(mkv__('Phiếu Chi')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-size:12.5px; font-weight:500;">
                                        <?php echo ($pm_icons[$cb->method] ?? '') . ' ' . esc_html($pm_map[$cb->method] ?? $cb->method); ?>
                                    </span>
                                </td>
                                <td style="text-align:right; font-weight:700; font-size:13px; color:<?php echo $cb->type==='thu'?'#10b981':'#ef4444'; ?>;">
                                    <?php echo ($cb->type === 'thu' ? '+' : '-') . number_format($cb->amount, 0, ',', '.'); ?> ₫
                                </td>
                                <td style="font-size:12.5px; color:#475569; padding-right:16px;">
                                    <?php echo esc_html($cb->staff_name ?: ('User #' . $cb->created_by)); ?>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ORDER NOTE CARD -->
            <div class="mkv-card" style="padding:18px 20px; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                <div style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:14px; color:var(--mkv-text-main); margin-bottom:8px;">
                    <i class="hgi-stroke hgi-clipboard" style="color:var(--mkv-primary);"></i>
                    <span><?php echo esc_html(mkv__('Ghi chú đơn hàng')); ?></span>
                </div>
                <div style="font-size:13.5px; color:<?php echo !empty($order->note) ? '#334155' : '#94a3b8'; ?>; background:#f8fafc; padding:12px 16px; border-radius:8px; border:1px solid #e2e8f0; line-height:1.6;">
                    <?php echo !empty($order->note) ? nl2br(esc_html($order->note)) : esc_html(mkv__('Không có ghi chú nào cho đơn hàng này.')); ?>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: CUSTOMER, SHIPPING & METADATA -->
        <div class="mkv-order-detail-right">
            <!-- CUSTOMER CARD -->
            <div class="mkv-card" style="padding:20px; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <div style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:15px; color:var(--mkv-text-main);">
                        <i class="hgi-stroke hgi-user" style="color:var(--mkv-primary);"></i>
                        <span><?php echo esc_html(mkv__('Khách hàng')); ?></span>
                    </div>
                    <?php if (!empty($order->customer_id)): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mkv-customers&id=' . $order->customer_id)); ?>" style="font-size:12px; color:var(--mkv-primary); font-weight:600; text-decoration:none;">
                        <?php echo esc_html(mkv__('Hồ sơ')); ?> →
                    </a>
                    <?php endif; ?>
                </div>

                <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
                    <div style="width:44px; height:44px; border-radius:50%; background:linear-gradient(135deg, #0072bc, #0284c7); color:#ffffff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:16px; box-shadow:0 2px 4px rgba(0,114,188,0.2);">
                        <?php echo esc_html(mb_substr($order->customer_name ?: 'K', 0, 1, 'UTF-8')); ?>
                    </div>
                    <div>
                        <h4 style="margin:0; font-size:15px; font-weight:700; color:var(--mkv-text-main);">
                            <?php echo esc_html($order->customer_name ?: mkv__('Khách lẻ')); ?>
                        </h4>
                        <?php if (!empty($order->customer_phone)): ?>
                        <div style="font-size:13px; color:#64748b; margin-top:2px;">
                            <a href="tel:<?php echo esc_attr($order->customer_phone); ?>" style="color:#64748b; text-decoration:none;">
                                <i class="hgi-stroke hgi-call-02"></i> <?php echo esc_html($order->customer_phone); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($order->customer_full_address) || !empty($order->customer_address)): ?>
                <div style="margin-top:12px; padding-top:12px; border-top:1px dashed #e2e8f0; font-size:12.5px; color:#475569; display:flex; gap:6px; line-height:1.5;">
                    <i class="hgi-stroke hgi-location-01" style="color:#94a3b8; font-size:16px; flex-shrink:0; margin-top:2px;"></i>
                    <span><?php echo esc_html($order->customer_address ?: $order->customer_full_address); ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($order->customer_id)): ?>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:14px; padding-top:14px; border-top:1px solid #f1f5f9;">
                    <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; text-align:center;">
                        <span style="font-size:11.5px; color:#64748b; display:block;"><?php echo esc_html(mkv__('Điểm tích lũy')); ?></span>
                        <strong style="font-size:14px; color:#2563eb;"><?php echo number_format((int) $order->customer_points, 0, ',', '.'); ?></strong>
                    </div>
                    <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; text-align:center;">
                        <span style="font-size:11.5px; color:#64748b; display:block;"><?php echo esc_html(mkv__('Tổng nợ hiện tại')); ?></span>
                        <strong style="font-size:14px; color:<?php echo (float)$order->customer_total_debt > 0 ? '#ef4444' : '#10b981'; ?>;">
                            <?php echo number_format((float)$order->customer_total_debt, 0, ',', '.'); ?> ₫
                        </strong>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- SHIPPING & FULFILLMENT CARD (IF APPLICABLE) -->
            <?php if (!empty($order->shipping_provider) || !empty($order->tracking_code) || (float)$order->shipping_fee > 0 || (float)$order->cod_amount > 0): ?>
            <div class="mkv-card" style="padding:20px; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <div style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:15px; color:var(--mkv-text-main);">
                        <i class="hgi-stroke hgi-truck-delivery" style="color:var(--mkv-purple);"></i>
                        <span><?php echo esc_html(mkv__('Thông tin vận chuyển')); ?></span>
                    </div>
                    <?php if (!empty($order->tracking_code)): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mkv-tracking&code=' . urlencode($order->tracking_code))); ?>" target="_blank" style="font-size:12px; color:var(--mkv-purple); font-weight:600; text-decoration:none;">
                        <?php echo esc_html(mkv__('Hành trình')); ?> →
                    </a>
                    <?php endif; ?>
                </div>

                <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#64748b;"><?php echo esc_html(mkv__('Đơn vị vận chuyển:')); ?></span>
                        <strong style="color:var(--mkv-text-main); text-transform:uppercase;"><?php echo esc_html($order->shipping_provider ?: mkv__('Tự giao hàng')); ?></strong>
                    </div>

                    <?php if (!empty($order->tracking_code)): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:#64748b;"><?php echo esc_html(mkv__('Mã vận đơn:')); ?></span>
                        <span class="mkv-badge mkv-badge-purple" style="font-weight:700;"><?php echo esc_html($order->tracking_code); ?></span>
                    </div>
                    <?php else: ?>
                        <?php if (!in_array($order->status, array('draft','cancelled','returned'))): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="color:#64748b;"><?php echo esc_html(mkv__('Mã vận đơn:')); ?></span>
                            <button type="button" class="mkv-btn mkv-btn-secondary" style="height:24px; padding:0 8px; font-size:11.5px; display:inline-flex; align-items:center; gap:4px;" onclick="mkvOpenTrackingModal()">
                                <i class="hgi-stroke hgi-edit-02"></i> <?php echo esc_html(mkv__('Nhập mã')); ?>
                            </button>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ((float)$order->cod_amount > 0): ?>
                    <div style="display:flex; justify-content:space-between; padding-top:8px; border-top:1px dashed #e2e8f0;">
                        <span style="color:#64748b; font-weight:600;"><?php echo esc_html(mkv__('Thu hộ COD:')); ?></span>
                        <strong style="color:#8b5cf6; font-size:14px;"><?php echo number_format($order->cod_amount, 0, ',', '.'); ?> ₫</strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- PAYMENT & SALES INFO CARD -->
            <div class="mkv-card" style="padding:20px; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                <div style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:15px; color:var(--mkv-text-main); margin-bottom:14px;">
                    <i class="hgi-stroke hgi-information-circle" style="color:var(--mkv-primary);"></i>
                    <span><?php echo esc_html(mkv__('Thông tin giao dịch')); ?></span>
                </div>

                <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#64748b;"><?php echo esc_html(mkv__('Kênh bán hàng:')); ?></span>
                        <span class="mkv-badge <?php echo esc_attr($channel['class']); ?>"><?php echo esc_html($channel['label']); ?></span>
                    </div>

                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#64748b;"><?php echo esc_html(mkv__('Phương thức:')); ?></span>
                        <span style="font-weight:600;"><?php echo ($pm_icons[$order->payment_method] ?? '') . ' ' . esc_html($pm_map[$order->payment_method] ?? $order->payment_method); ?></span>
                    </div>

                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#64748b;"><?php echo esc_html(mkv__('Trạng thái đơn:')); ?></span>
                        <span class="mkv-badge <?php echo esc_attr($current_status['badge']); ?>"><?php echo esc_html(mkv__($current_status['label'])); ?></span>
                    </div>

                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#64748b;"><?php echo esc_html(mkv__('Thanh toán:')); ?></span>
                        <span style="font-weight:700; color:<?php echo $payment_due > 0 ? '#ef4444' : '#10b981'; ?>;">
                            <?php echo $payment_due > 0 ? ($is_cod_pending ? mkv__('Chờ thu COD') : mkv__('Còn nợ tiền')) : mkv__('Đã thanh toán đủ'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     MODAL IN HÓA ĐƠN NHIỆT (PRINTABLE THERMAL RECEIPT MODAL)
     ========================================================================== -->
<div class="mkv-modal-overlay" id="mkv-print-receipt-modal" role="dialog" aria-modal="true" aria-labelledby="mkv-print-receipt-modal-title" style="display:none;" onclick="if(event.target===this) mkvClosePrintModal();">
    <div class="mkv-modal" style="width:420px; max-height:90vh; display:flex; flex-direction:column;">
        <div class="mkv-modal-header" style="padding:14px 20px;">
            <h3 id="mkv-print-receipt-modal-title" class="mkv-modal-title" style="font-size:16px; display:flex; align-items:center; gap:8px;">
                <i class="hgi-stroke hgi-printer" style="color:var(--mkv-primary);"></i>
                <span><?php echo esc_html(mkv__('Xem trước & In hóa đơn')); ?></span>
            </h3>
            <button type="button" class="mkv-modal-close" aria-label="<?php echo esc_attr(mkv__('Đóng')); ?>" onclick="mkvClosePrintModal()">&times;</button>
        </div>

        <div class="mkv-modal-body" style="padding:16px; overflow-y:auto; flex:1; background:#f1f5f9;">
            <!-- THERMAL BILL CONTAINER -->
            <div id="mkv-printable-area" class="mkv-thermal-bill" style="background:#ffffff; padding:24px 20px; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,0.08); font-family:'Inter', -apple-system, sans-serif; font-size:12.5px; color:#000;">
                <!-- Store Header -->
                <div style="text-align:center; margin-bottom:14px; padding-bottom:12px; border-bottom:1px dashed #000;">
                    <h3 style="margin:0 0 4px 0; font-size:18px; font-weight:800; text-transform:uppercase; color:#000;"><?php echo esc_html($store_name); ?></h3>
                    <?php if ($store_address): ?><p style="margin:2px 0; font-size:11px;"><?php echo esc_html($store_address); ?></p><?php endif; ?>
                    <p style="margin:2px 0; font-size:11px;">Hotline: <?php echo esc_html($store_phone); ?></p>
                    <h4 style="margin:12px 0 4px 0; font-size:15px; font-weight:800; text-transform:uppercase;"><?php echo esc_html(mkv__('HÓA ĐƠN BÁN HÀNG')); ?></h4>
                    <p style="margin:0; font-size:11px;"><?php echo esc_html(mkv__('Mã:')); ?> <strong><?php echo esc_html($order->order_code); ?></strong></p>
                    <p style="margin:0; font-size:10.5px; color:#555;"><?php echo esc_html(date('d/m/Y H:i', strtotime($order->created_at))); ?></p>
                </div>

                <!-- Customer Meta -->
                <div style="margin-bottom:12px; font-size:11.5px; line-height:1.5;">
                    <div><?php echo esc_html(mkv__('Khách hàng:')); ?> <strong><?php echo esc_html($order->customer_name ?: mkv__('Khách lẻ')); ?></strong></div>
                    <?php if (!empty($order->customer_phone)): ?><div>SĐT: <?php echo esc_html($order->customer_phone); ?></div><?php endif; ?>
                    <?php if (!empty($order->cashier_name)): ?><div><?php echo esc_html(mkv__('Thu ngân:')); ?> <?php echo esc_html($order->cashier_name); ?></div><?php endif; ?>
                </div>

                <!-- Items Table -->
                <table style="width:100%; border-collapse:collapse; margin-bottom:14px; font-size:11.5px;">
                    <thead>
                        <tr style="border-bottom:1px dashed #000; text-align:left;">
                            <th style="padding:4px 0;"><?php echo esc_html(mkv__('Tên hàng')); ?></th>
                            <th style="padding:4px 0; text-align:center; width:35px;"><?php echo esc_html(mkv__('SL')); ?></th>
                            <th style="padding:4px 0; text-align:right; width:65px;"><?php echo esc_html(mkv__('Đ.Giá')); ?></th>
                            <th style="padding:4px 0; text-align:right; width:75px;"><?php echo esc_html(mkv__('T.Tiền')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                        <tr style="border-bottom:1px dotted #ccc;">
                            <td style="padding:6px 0;"><?php echo esc_html($it->product_name ?: $it->sku); ?></td>
                            <td style="padding:6px 0; text-align:center;"><?php echo (int) $it->qty; ?></td>
                            <td style="padding:6px 0; text-align:right;"><?php echo number_format($it->price, 0, ',', '.'); ?></td>
                            <td style="padding:6px 0; text-align:right; font-weight:700;"><?php echo number_format($it->subtotal, 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Totals -->
                <div style="border-top:1px dashed #000; padding-top:8px; font-size:11.5px; line-height:1.6;">
                    <div style="display:flex; justify-content:space-between;">
                        <span><?php echo esc_html(mkv__('Tổng tiền hàng:')); ?></span>
                        <span><?php echo number_format($order->subtotal, 0, ',', '.'); ?> ₫</span>
                    </div>
                    <?php if ((float)$order->discount > 0): ?>
                    <div style="display:flex; justify-content:space-between;">
                        <span><?php echo esc_html(mkv__('Giảm giá:')); ?></span>
                        <span>-<?php echo number_format($order->discount, 0, ',', '.'); ?> ₫</span>
                    </div>
                    <?php endif; ?>
                    <?php if ((float)$order->shipping_fee > 0): ?>
                    <div style="display:flex; justify-content:space-between;">
                        <span><?php echo esc_html(mkv__('Phí vận chuyển:')); ?></span>
                        <span>+<?php echo number_format($order->shipping_fee, 0, ',', '.'); ?> ₫</span>
                    </div>
                    <?php endif; ?>
                    <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:800; border-top:1px dashed #000; padding-top:4px; margin-top:4px;">
                        <span><?php echo esc_html(mkv__('TỔNG CỘNG:')); ?></span>
                        <span><?php echo number_format($order->total_amount, 0, ',', '.'); ?> ₫</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span><?php echo esc_html(mkv__('Khách đã trả:')); ?> (<?php echo esc_html($pm_map[$order->payment_method] ?? $order->payment_method); ?>)</span>
                        <span><?php echo number_format($order->paid_amount, 0, ',', '.'); ?> ₫</span>
                    </div>
                    <?php if ($payment_due > 0): ?>
                    <div style="display:flex; justify-content:space-between; font-weight:700; color:#ef4444;">
                        <span><?php echo esc_html($is_cod_pending ? mkv__('Thu hộ COD:') : mkv__('Còn nợ:')); ?></span>
                        <span><?php echo number_format($payment_due, 0, ',', '.'); ?> ₫</span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Footer note -->
                <div style="text-align:center; margin-top:16px; padding-top:10px; border-top:1px dashed #000; font-size:11px;">
                    <p style="margin:2px 0; font-weight:600;"><?php echo esc_html(mkv__('Cảm ơn quý khách và hẹn gặp lại!')); ?></p>
                    <p style="margin:2px 0; font-size:10px; color:#777;"><?php echo esc_html(mkv__('Phần mềm quản lý bán hàng Mini KiotViet')); ?></p>
                </div>
            </div>
        </div>

        <div class="mkv-modal-footer" style="padding:12px 20px; display:flex; justify-content:space-between;">
            <button type="button" class="mkv-btn mkv-btn-secondary" onclick="mkvClosePrintModal()"><?php echo esc_html(mkv__('Đóng')); ?></button>
            <button type="button" class="mkv-btn mkv-btn-primary" onclick="window.print()" style="font-weight:700; display:inline-flex; align-items:center; gap:6px;">
                <i class="hgi-stroke hgi-printer"></i> <?php echo esc_html(mkv__('In ngay (Ctrl + P)')); ?>
            </button>
        </div>
    </div>
</div>

<!-- MODAL THU TIỀN / ĐỐI SOÁT COD -->
<div class="mkv-modal-overlay" id="mkv-collect-debt-modal" role="dialog" aria-modal="true" aria-labelledby="mkv-collect-debt-modal-title" style="display:none;">
    <div class="mkv-modal" style="width:460px;">
        <div class="mkv-modal-header">
            <h3 class="mkv-modal-title" id="mkv-collect-debt-modal-title">
                <i class="hgi-stroke hgi-money-receive-square" id="mkv-cdm-icon" style="color:var(--mkv-green);"></i>
                <span id="mkv-cdm-title"><?php echo esc_html(mkv__('Thu Tiền Khách Nợ')); ?></span>
            </h3>
            <button type="button" class="mkv-modal-close" aria-label="<?php echo esc_attr(mkv__('Đóng')); ?>" onclick="mkvCloseCollectModal()">&times;</button>
        </div>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="mkv-collect-debt-form">
            <input type="hidden" name="action" value="mkv_collect_order_debt">
            <input type="hidden" name="order_id" id="mkv-cdm-order-id" value="">
            <input type="hidden" name="_wpnonce" id="mkv-cdm-nonce" value="">
            
            <div class="mkv-modal-body" style="display:flex; flex-direction:column; gap:16px;">
                <!-- Card Thông tin đơn hàng -->
                <div style="background:var(--mkv-border-light); border-radius:10px; padding:14px 16px; border:1px solid var(--mkv-border);">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Mã đơn hàng:')); ?></span>
                        <strong id="mkv-cdm-code" style="font-size:13px; color:var(--mkv-text-main);"></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Khách hàng:')); ?></span>
                        <span id="mkv-cdm-customer" style="font-size:13px; font-weight:600; color:var(--mkv-text-main);"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px; padding-top:8px; border-top:1px dashed var(--mkv-border);">
                        <span id="mkv-cdm-due-label" style="font-size:13px; font-weight:600; color:var(--mkv-red);"><?php echo esc_html(mkv__('Số tiền còn nợ:')); ?></span>
                        <strong id="mkv-cdm-due" style="font-size:16px; color:var(--mkv-red);"></strong>
                    </div>
                </div>

                <!-- Input Số tiền thu -->
                <div class="mkv-form-group" style="margin-bottom:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label class="mkv-label" style="margin-bottom:0; font-weight:600;"><?php echo esc_html(mkv__('Số tiền thu (VNĐ)')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <button type="button" class="mkv-badge mkv-badge-blue" id="mkv-cdm-chip-full" onclick="mkvSetCollectFull()" style="border:none; cursor:pointer; font-size:11.5px; padding:3px 10px;">
                            <i class="hgi-stroke hgi-tick-02"></i> <?php echo esc_html(mkv__('Thu đủ toàn bộ')); ?>
                        </button>
                    </div>
                    <input type="text" name="amount" id="mkv-cdm-amount" required class="mkv-input" style="font-size:16px; font-weight:700; color:var(--mkv-primary); height:42px;" placeholder="0" inputmode="numeric">
                </div>

                <!-- Phương thức thanh toán -->
                <div class="mkv-form-group" style="margin-bottom:0;">
                    <label class="mkv-label" style="font-weight:600; margin-bottom:8px;"><?php echo esc_html(mkv__('Hình thức thanh toán')); ?></label>
                    <div class="mkv-method-grid">
                        <label class="mkv-method-card active">
                            <input type="radio" name="method" value="cash" checked onchange="mkvUpdateMethodCards(this)">
                            <i class="hgi-stroke hgi-money-receive-square" style="font-size:22px; color:var(--mkv-green); display:block; margin-bottom:4px;"></i>
                            <span><?php echo esc_html(mkv__('Tiền mặt')); ?></span>
                        </label>
                        <label class="mkv-method-card">
                            <input type="radio" name="method" value="transfer" onchange="mkvUpdateMethodCards(this)">
                            <i class="hgi-stroke hgi-bank" style="font-size:22px; color:var(--mkv-primary); display:block; margin-bottom:4px;"></i>
                            <span><?php echo esc_html(mkv__('Chuyển khoản')); ?></span>
                        </label>
                        <label class="mkv-method-card">
                            <input type="radio" name="method" value="card" onchange="mkvUpdateMethodCards(this)">
                            <i class="hgi-stroke hgi-credit-card" style="font-size:22px; color:var(--mkv-purple); display:block; margin-bottom:4px;"></i>
                            <span><?php echo esc_html(mkv__('Thẻ ngân hàng')); ?></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mkv-modal-footer">
                <button type="button" class="mkv-btn mkv-btn-secondary" onclick="mkvCloseCollectModal()"><?php echo esc_html(mkv__('Hủy bỏ')); ?></button>
                <button type="submit" class="mkv-btn mkv-btn-primary" id="mkv-cdm-submit-btn" style="background:var(--mkv-green); border-color:var(--mkv-green); font-weight:600;">
                    <i class="hgi-stroke hgi-checkmark-circle-02"></i> <span id="mkv-cdm-submit-text"><?php echo esc_html(mkv__('Xác nhận thu tiền')); ?></span>
                </button>
            </div>
        </form>
    </div>
</div>

    <!-- Modal Nhập Mã Vận Đơn Thủ Công -->
    <div class="mkv-modal-overlay" id="mkv-tracking-modal" role="dialog" aria-modal="true" aria-labelledby="mkv-tracking-modal-title" style="display:none;">
        <div class="mkv-modal" style="width:400px;">
            <div class="mkv-modal-header">
                <h3 class="mkv-modal-title" id="mkv-tracking-modal-title">
                    <i class="hgi-stroke hgi-truck-delivery" style="color:var(--mkv-purple);"></i>
                    <span><?php echo esc_html(mkv__('Nhập mã vận đơn')); ?></span>
                </h3>
                <button type="button" class="mkv-modal-close" aria-label="<?php echo esc_attr(mkv__('Đóng')); ?>" onclick="mkvCloseTrackingModal()">&times;</button>
            </div>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="mkv_update_tracking_code">
                <input type="hidden" name="order_id" value="<?php echo (int) $order->id; ?>">
                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('mkv_tracking_' . $order->id); ?>">
                
                <div class="mkv-modal-body" style="display:flex; flex-direction:column; gap:16px;">
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Mã vận đơn')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <input type="text" name="tracking_code" required class="mkv-input" placeholder="Ví dụ: VT123456789">
                        <div style="font-size:11px; color:var(--mkv-text-muted); margin-top:6px;">
                            <?php echo esc_html(mkv__('Đơn hàng sẽ tự động chuyển sang trạng thái Đang giao (Shipping).')); ?>
                        </div>
                    </div>
                </div>
                <div class="mkv-modal-footer">
                    <button type="button" class="mkv-btn mkv-btn-secondary" onclick="mkvCloseTrackingModal()"><?php echo esc_html(mkv__('Hủy')); ?></button>
                    <button type="submit" class="mkv-btn mkv-btn-primary">
                        <i class="hgi-stroke hgi-check-circle"></i> <?php echo esc_html(mkv__('Lưu mã vận đơn')); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
function mkvCopyOrderCode(code, btn) {
    if (navigator.clipboard && code) {
        navigator.clipboard.writeText(code).then(function() {
            var originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="hgi-stroke hgi-checkmark-circle-02"></i> Đã sao chép!';
            btn.style.color = '#10b981';
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.style.color = '';
            }, 2000);
        });
    }
}

function mkvOpenTrackingModal() {
    document.getElementById('mkv-tracking-modal').style.display = 'flex';
}
function mkvCloseTrackingModal() {
    document.getElementById('mkv-tracking-modal').style.display = 'none';
}

function mkvOpenPrintModal() {
    var modal = document.getElementById('mkv-print-receipt-modal');
    if (!modal) return;

    modal.mkvTrigger = document.activeElement;
    modal.style.display = 'flex';

    var closeButton = modal.querySelector('.mkv-modal-close');
    if (closeButton) closeButton.focus();
}

function mkvClosePrintModal() {
    var modal = document.getElementById('mkv-print-receipt-modal');
    if (!modal) return;

    modal.style.display = 'none';

    var trigger = modal.mkvTrigger;
    modal.mkvTrigger = null;
    if (trigger && document.contains(trigger) && trigger.offsetParent !== null && typeof trigger.focus === 'function') {
        trigger.focus();
    }
}

document.addEventListener('keydown', function(event) {
    var modal = document.getElementById('mkv-print-receipt-modal');
    if (!modal || modal.style.display === 'none' || event.key !== 'Escape') return;

    event.preventDefault();
    event.stopPropagation();
    mkvClosePrintModal();
});

var mkvCurrentCollectDue = 0;
function mkvOpenCollectModal(data) {
    var modal = document.getElementById('mkv-collect-debt-modal');
    if (!modal) return;
    
    mkvCurrentCollectDue = data.due;
    document.getElementById('mkv-cdm-order-id').value = data.id;
    document.getElementById('mkv-cdm-nonce').value = data.nonce;
    document.getElementById('mkv-cdm-code').textContent = data.code;
    document.getElementById('mkv-cdm-customer').textContent = data.customer;
    document.getElementById('mkv-cdm-due').textContent = data.dueFormatted + ' ₫';
    
    var title = document.getElementById('mkv-cdm-title');
    var label = document.getElementById('mkv-cdm-due-label');
    var submitText = document.getElementById('mkv-cdm-submit-text');
    var icon = document.getElementById('mkv-cdm-icon');
    
    if (data.isCod) {
        title.textContent = '<?php echo esc_js(mkv__('Đối Soát Tiền COD')); ?>';
        label.textContent = '<?php echo esc_js(mkv__('Tiền COD chờ thu:')); ?>';
        submitText.textContent = '<?php echo esc_js(mkv__('Xác nhận đối soát')); ?>';
        icon.className = 'hgi-stroke hgi-truck-delivery';
        icon.style.color = 'var(--mkv-purple)';
    } else {
        title.textContent = '<?php echo esc_js(mkv__('Thu Tiền Khách Nợ')); ?>';
        label.textContent = '<?php echo esc_js(mkv__('Số tiền còn nợ:')); ?>';
        submitText.textContent = '<?php echo esc_js(mkv__('Xác nhận thu tiền')); ?>';
        icon.className = 'hgi-stroke hgi-money-receive-square';
        icon.style.color = 'var(--mkv-green)';
    }
    
    document.getElementById('mkv-cdm-amount').value = data.dueFormatted;
    
    modal.style.display = 'flex';
    setTimeout(function() {
        var input = document.getElementById('mkv-cdm-amount');
        if (input) { input.focus(); input.select(); }
    }, 100);
}

function mkvCloseCollectModal() {
    var modal = document.getElementById('mkv-collect-debt-modal');
    if (modal) modal.style.display = 'none';
}

function mkvSetCollectFull() {
    var input = document.getElementById('mkv-cdm-amount');
    if (input && mkvCurrentCollectDue > 0) {
        input.value = Math.round(mkvCurrentCollectDue).toLocaleString('vi-VN');
    }
}

var cdmInput = document.getElementById('mkv-cdm-amount');
if (cdmInput) {
    cdmInput.addEventListener('input', function() {
        var raw = this.value.replace(/[^\d]/g, '');
        if (raw) {
            this.value = parseInt(raw, 10).toLocaleString('vi-VN');
        } else {
            this.value = '';
        }
    });
}

function mkvUpdateMethodCards(radio) {
    document.querySelectorAll('.mkv-method-card').forEach(function(card) {
        var r = card.querySelector('input[type="radio"]');
        if (r && r.checked) {
            card.classList.add('active');
        } else {
            card.classList.remove('active');
        }
    });
}

document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Chi tiết đơn hàng')); ?>');
</script>

<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>
