<?php if (!defined('ABSPATH')) exit; ?>
<?php require_once MKV_DIR . 'includes/views/header-kiotviet.php'; ?>
<div class="mkv-page-header">
    <h1 class="mkv-page-title"><i class="hgi-stroke hgi-notification-03"></i> <?php echo esc_html(mkv__('Thông Báo Hệ Thống')); ?></h1>
</div>

    <div class="mkv-grid-2">

        <!-- Đơn hàng hôm nay -->
        <div class="mkv-card">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title">
                    <i class="hgi-stroke hgi-shopping-cart-01"></i>
                    <?php echo esc_html(mkv__('Đơn hàng hôm nay')); ?>
                    <span class="mkv-badge mkv-badge-blue" style="margin-left:8px;"><?php echo count($today_orders); ?></span>
                </h3>
            </div>
            <div class="mkv-card-body" style="padding:0;">
                <?php if (empty($today_orders)): ?>
                    <p style="color:var(--mkv-text-muted); text-align:center; padding:30px;">
                        <i class="hgi-stroke hgi-shopping-cart-01" style="font-size:32px; display:block; margin-bottom:12px; opacity: 0.5;"></i>
                        <?php echo esc_html(mkv__('Chưa có đơn hàng nào trong ngày hôm nay.')); ?>
                    </p>
                <?php else: ?>
                    <ul class="mkv-widget-list">
                        <?php foreach ($today_orders as $o): ?>
                        <li>
                            <div style="flex:1;">
                                <strong style="font-size:14px;"><?php echo esc_html($o->order_code); ?></strong>
                                <div style="color:var(--mkv-text-muted); font-size:12.5px; margin-top:2px;">
                                    <?php echo esc_html(mkv__('Khách: ')); ?><?php echo esc_html($o->customer_name ?: mkv__('Khách lẻ')); ?>
                                </div>
                            </div>
                            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
                                <strong style="color:var(--mkv-primary); font-size:14px;"><?php echo number_format($o->total_amount, 0, ',', '.'); ?> ₫</strong>
                                <span class="mkv-badge <?php
                                    $s = array('completed' => 'mkv-badge-green', 'cancelled' => 'mkv-badge-red', 'pending' => 'mkv-badge-yellow');
                                    $status_map = array(
                                        'draft'     => 'Nháp',
                                        'pending'   => 'Chờ duyệt',
                                        'paid'      => 'Đã thanh toán',
                                        'shipping'  => 'Đang giao',
                                        'completed' => 'Hoàn thành',
                                        'cancelled' => 'Đã hủy',
                                    );
                                    echo $s[$o->status] ?? 'mkv-badge-yellow';
                                ?>"><?php echo esc_html(mkv__($status_map[$o->status] ?? $o->status)); ?></span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hàng sắp hết -->
        <div class="mkv-card" style="border-left:3px solid var(--mkv-yellow);">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title" style="color:#d48806;">
                    <i class="hgi-stroke hgi-alert-02" style="color:#d48806;"></i>
                    <?php echo esc_html(mkv__('Sắp hết hàng')); ?> <span style="font-size:13px; color:var(--mkv-text-muted); font-weight:normal;"><?php echo esc_html(mkv__('(tồn ≤ ')); ?><?php echo get_option('mkv_min_stock_threshold', 5); ?><?php echo esc_html(mkv__(')')); ?></span>
                    <span class="mkv-badge mkv-badge-red" style="margin-left:8px;"><?php echo count($low_stock); ?></span>
                </h3>
            </div>
            <div class="mkv-card-body" style="padding:0;">
                <?php if (empty($low_stock)): ?>
                    <p style="color:var(--mkv-text-muted); text-align:center; padding:30px;">
                        <i class="hgi-stroke hgi-check-mark-circle" style="font-size:32px; color:var(--mkv-green); display:block; margin-bottom:12px;"></i>
                        <?php echo esc_html(mkv__('Tất cả sản phẩm đều còn đủ hàng.')); ?>
                    </p>
                <?php else: ?>
                    <ul class="mkv-widget-list">
                        <?php foreach ($low_stock as $p): ?>
                        <li>
                            <span style="font-size:14px; font-weight:500; color:var(--mkv-text-main);"><?php echo esc_html($p['post_title']); ?></span>
                            <span class="mkv-badge <?php echo $p['stock'] <= 0 ? 'mkv-badge-red' : 'mkv-badge-yellow'; ?>">
                                <?php echo $p['stock'] <= 0 ? esc_html(mkv__('Hết hàng')) : esc_html(mkv__('Còn ')) . $p['stock']; ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Thông báo hệ thống -->
        <div class="mkv-card" style="grid-column: span 2;">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title">
                    <i class="hgi-stroke hgi-notification-03"></i>
                    <?php echo esc_html(mkv__('Cảnh báo hệ thống')); ?>
                    <span class="mkv-badge mkv-badge-blue" style="margin-left:8px;"><?php echo count($alerts); ?></span>
                </h3>
            </div>
            <div class="mkv-card-body" style="padding:0;">
                <?php if (empty($alerts)): ?>
                    <p style="color:var(--mkv-text-muted); text-align:center; padding:30px;">
                        <i class="hgi-stroke hgi-check-mark-circle" style="font-size:32px; color:var(--mkv-green); display:block; margin-bottom:12px;"></i>
                        <?php echo esc_html(mkv__('Không có thông báo mới nào.')); ?>
                    </p>
                <?php else: ?>
                    <ul class="mkv-widget-list">
                        <?php foreach ($alerts as $a): ?>
                        <li style="align-items:flex-start;">
                            <div style="flex:1;">
                                <strong style="font-size:14px; color:var(--mkv-text-main); display:block; margin-bottom:4px;"><?php echo esc_html($a->title); ?></strong>
                                <div style="color:var(--mkv-text-muted); font-size:13px; line-height:1.5;">
                                    <?php echo wp_kses_post($a->message); ?>
                                </div>
                            </div>
                            <span style="font-size:12px; color:#aaa; margin-left:16px; white-space:nowrap;">
                                <?php echo esc_html(date('d/m/Y H:i', strtotime($a->created_at))); ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

<script>document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Thông báo')); ?>');</script>
<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>