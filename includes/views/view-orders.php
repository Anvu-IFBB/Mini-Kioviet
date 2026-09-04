<?php if (!defined('ABSPATH')) exit;
$statuses = MKV_Orders::STATUSES;
?>
<?php require_once MKV_DIR . 'includes/views/header-kiotviet.php'; ?>

    <div class="mkv-page-header" style="margin-bottom: 24px;">
        <h1 class="mkv-page-title" style="display:flex; align-items:center; gap:16px;">
            <a href="javascript:history.back()" class="mkv-orders-back-btn">
                <i class="hgi-stroke hgi-arrow-left-01"></i> <?php echo esc_html(mkv__('Trở về')); ?>
            </a>
            <span><i class="hgi-stroke hgi-invoice-01"></i> <?php echo esc_html(mkv__('Danh Sách Đơn Hàng')); ?></span>
        </h1>
        <div class="mkv-page-actions">
            <a href="?page=mkv-pos" class="mkv-orders-create-btn"><i class="hgi-stroke hgi-add-square"></i> <?php echo esc_html(mkv__('Tạo đơn mới')); ?></a>
        </div>
    </div>

    <?php if (isset($_GET['created'])): ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html(mkv__('Đơn hàng mới đã được tạo thành công!')); ?></p></div>
    <?php endif; ?>
    <?php if (isset($_GET['status_updated'])): ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html(mkv__('Đã cập nhật trạng thái đơn hàng!')); ?></p></div>
    <?php endif; ?>
    <?php if (isset($_GET['cancelled'])): ?>
        <div class="notice notice-warning is-dismissible"><p><?php echo esc_html(mkv__('Đã hủy đơn hàng và hoàn trả tồn kho.')); ?></p></div>
    <?php endif; ?>
    <?php if (isset($_GET['returned'])): ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html(mkv__('Trả hàng thành công! Đã hoàn tồn kho và tạo Phiếu Chi hoàn tiền trong Sổ quỹ.')); ?></p></div>
    <?php endif; ?>

    <!-- Status filter tabs -->
    <div class="mkv-filter-pills">
        <a href="?page=mkv-orders" class="mkv-filter-pill <?php echo empty($status_filter)?'active':''; ?>"><?php echo esc_html(mkv__('Tất cả')); ?></a>
        <?php foreach ($statuses as $key => $s): ?>
        <a href="?page=mkv-orders&status=<?php echo $key; ?>" class="mkv-filter-pill <?php echo $status_filter===$key?'active':''; ?>">
            <?php echo esc_html(mkv__($s['label'])); ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="mkv-modern-table">
        <table>
            <thead>
                <tr>
                    <th style="width:140px;"><?php echo esc_html(mkv__('Mã đơn hàng')); ?></th>
                    <th><?php echo esc_html(mkv__('Khách hàng')); ?></th>
                    <th style="width:120px;"><?php echo esc_html(mkv__('Trạng thái')); ?></th>
                    <th style="width:130px;"><?php echo esc_html(mkv__('Tổng tiền')); ?></th>
                    <th style="width:100px;"><?php echo esc_html(mkv__('Thanh toán')); ?></th>
                    <th style="width:140px;"><?php echo esc_html(mkv__('Ngày tạo')); ?></th>
                    <th style="width:340px; text-align:right; padding-right:24px;"><?php echo esc_html(mkv__('Hành động')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mkv-text-muted);">
                        <i class="hgi-stroke hgi-invoice-01" style="font-size:40px;display:block;margin-bottom:10px;"></i>
                        <?php echo $status_filter ? esc_html(mkv__('Không có đơn hàng nào với trạng thái này.')) : esc_html(mkv__('Chưa có đơn hàng nào.')) . ' <a href="?page=mkv-pos">' . esc_html(mkv__('Tạo đơn hàng mới')) . '</a>'; ?>
                    </td></tr>
                <?php else: foreach ($orders as $o):
                    $s = $statuses[$o->status] ?? array('label' => $o->status, 'badge' => '');
                ?>
                <tr>
                    <td><strong><a href="?page=mkv-orders&id=<?php echo $o->id; ?>"><?php echo esc_html($o->order_code); ?></a></strong></td>
                    <td>
                        <?php echo esc_html($o->customer_name ?: mkv__('Khách lẻ')); ?>
                        <?php if (!empty($o->shipping_provider) && !empty($o->customer_address)): ?>
                            <div style="font-size:11px; color:#64748b; margin-top:4px;">
                                <i class="hgi-stroke hgi-location-01"></i> <?php echo esc_html($o->customer_address); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><span class="mkv-badge <?php echo $s['badge']; ?>"><?php echo esc_html(mkv__($s['label'])); ?></span></td>
                    <td>
                        <strong style="color:var(--mkv-primary);"><?php echo number_format($o->total_amount, 0, ',', '.'); ?> ₫</strong>
                        <?php if (isset($o->debt_amount) && $o->debt_amount > 0): ?>
                            <div style="font-size:12px; color:var(--mkv-red); margin-top:2px;">
                                <?php echo esc_html(mkv__('Nợ: ')); ?><?php echo number_format($o->debt_amount, 0, ',', '.'); ?> ₫
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="color:#64748b;"><?php
                        $pm_map = array('cash' => mkv__('💵 Tiền mặt'), 'transfer' => mkv__('🏦 Chuyển khoản'), 'card' => mkv__('💳 Thẻ'));
                        echo esc_html($pm_map[$o->payment_method] ?? $o->payment_method);
                    ?></td>
                    <td style="color:#64748b; font-size:13px;"><?php echo esc_html(date('d/m/Y H:i', strtotime($o->created_at))); ?></td>
                    <td style="text-align:right; padding-right:24px;">
                        <div style="display:flex; gap:8px; align-items:center; justify-content:flex-end; flex-wrap:nowrap;">
                            <?php if (!in_array($o->status, array('completed','cancelled')) && isset($s['next']) && $s['next']): ?>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=mkv_update_order_status&id='.$o->id.'&new_status='.$s['next']), 'mkv_status_'.$o->id.'_'.$s['next']); ?>"
                                class="mkv-action-btn mkv-action-primary">
                                → <?php echo esc_html(mkv__($statuses[$s['next']]['label'])); ?>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($o->shipping_provider) && get_option('mkv_shipping_enable', 0) == 1): ?>
                                <?php if (empty($o->tracking_code)): ?>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=mkv_push_shipping&id='.$o->id), 'mkv_shipping_'.$o->id); ?>"
                                        class="mkv-action-btn mkv-action-info">
                                        <i class="hgi-stroke hgi-truck-01"></i> <?php echo esc_html(mkv__('Tạo vận đơn')); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="mkv-badge mkv-badge-purple" style="font-size:11px; height:28px; display:inline-flex; align-items:center; gap:4px;"><i class="hgi-stroke hgi-truck-01"></i> <?php echo esc_html($o->tracking_code); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if (!in_array($o->status, array('completed','cancelled'))): ?>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=mkv_cancel_order&id='.$o->id), 'mkv_cancel_'.$o->id); ?>"
                                class="mkv-action-btn mkv-action-danger"
                                onclick="return confirm('<?php echo esc_js(mkv__('Hủy đơn hàng này? Tồn kho sẽ được hoàn trả.')); ?>');">
                                <i class="hgi-stroke hgi-cancel-01"></i> <?php echo esc_html(mkv__('Hủy')); ?>
                            </a>
                            <?php endif; ?>

                            <?php if (in_array($o->status, array('paid','shipping','completed'))): ?>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=mkv_return_order&id='.$o->id), 'mkv_return_'.$o->id); ?>"
                                class="mkv-action-btn mkv-action-warning"
                                onclick="return confirm('<?php echo esc_js(mkv__('Khách muốn trả lại đơn hàng này? Tồn kho sẽ được cộng lại và tiền sẽ tự động xuất khỏi Sổ Quỹ (Tạo Phiếu Chi).')); ?>');">
                                <i class="hgi-stroke hgi-arrow-reload-02"></i> <?php echo esc_html(mkv__('Trả Hàng')); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

<script>document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Đơn hàng')); ?>');</script>
<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>