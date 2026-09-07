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
    <?php if (isset($_GET['payment_collected'])): ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html(mkv__('Đã ghi nhận khoản thu và cập nhật trạng thái thanh toán.')); ?></p></div>
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
    <div class="mkv-filter-bar" style="margin-bottom:18px; padding:10px 16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <label for="mkv-order-channel" style="font-size:13px; font-weight:600; color:var(--mkv-text-muted); display:flex; align-items:center; gap:6px;">
                <i class="hgi-stroke hgi-filter-horizontal"></i> <?php echo esc_html(mkv__('Kênh bán:')); ?>
            </label>
            <select id="mkv-order-channel" class="mkv-select" style="min-width:180px; height:36px; font-size:13px;" onchange="this.value ? (window.location.href='<?php echo esc_js(admin_url('admin.php?page=mkv-orders')); ?>&channel='+encodeURIComponent(this.value)) : (window.location.href='<?php echo esc_js(admin_url('admin.php?page=mkv-orders')); ?>');">
                <option value=""><?php echo esc_html(mkv__('Tất cả kênh')); ?></option>
                <option value="pos" <?php selected($channel_filter, 'pos'); ?>><?php echo esc_html(mkv__('Tại quầy')); ?></option>
                <option value="online" <?php selected($channel_filter, 'online'); ?>><?php echo esc_html(mkv__('Online')); ?></option>
                <option value="social" <?php selected($channel_filter, 'social'); ?>><?php echo esc_html(mkv__('Facebook / Zalo')); ?></option>
                <option value="marketplace" <?php selected($channel_filter, 'marketplace'); ?>><?php echo esc_html(mkv__('Sàn thương mại điện tử')); ?></option>
            </select>
        </div>
        <div style="font-size:13px; color:var(--mkv-text-muted);">
            <?php echo esc_html(mkv__('Tổng số:')); ?> <strong style="color:var(--mkv-text-main);"><?php echo count($orders); ?></strong> <?php echo esc_html(mkv__('đơn hàng')); ?>
        </div>
    </div>

    <div class="mkv-modern-table">
        <table class="mkv-table">
            <thead>
                <tr>
                    <th style="width:140px;"><?php echo esc_html(mkv__('Mã đơn hàng')); ?></th>
                    <th style="min-width:180px;"><?php echo esc_html(mkv__('Khách hàng')); ?></th>
                    <th style="width:130px;"><?php echo esc_html(mkv__('Kênh bán')); ?></th>
                    <th style="width:120px;"><?php echo esc_html(mkv__('Trạng thái')); ?></th>
                    <th style="width:130px;"><?php echo esc_html(mkv__('Tổng tiền')); ?></th>
                    <th style="width:130px;"><?php echo esc_html(mkv__('Thanh toán')); ?></th>
                    <th style="width:140px;"><?php echo esc_html(mkv__('Ngày tạo')); ?></th>
                    <th style="width:340px; text-align:right; padding-right:24px;"><?php echo esc_html(mkv__('Hành động')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--mkv-text-muted);">
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
                    <td>
                        <?php
                        $channel_labels = array(
                            'pos' => array('label' => mkv__('Tại quầy'), 'class' => 'mkv-badge-green'),
                            'online' => array('label' => mkv__('Online'), 'class' => 'mkv-badge-blue'),
                            'social' => array('label' => mkv__('Facebook / Zalo'), 'class' => 'mkv-badge-yellow'),
                            'marketplace' => array('label' => mkv__('Sàn TMĐT'), 'class' => 'mkv-badge-purple'),
                        );
                        $channel = $channel_labels[$o->sales_channel ?? 'pos'] ?? $channel_labels['pos'];
                        ?>
                        <span class="mkv-badge <?php echo esc_attr($channel['class']); ?>"><?php echo esc_html($channel['label']); ?></span>
                    </td>
                    <td><span class="mkv-badge <?php echo $s['badge']; ?>"><?php echo esc_html(mkv__($s['label'])); ?></span></td>
                    <td>
                        <strong style="color:var(--mkv-text-main); font-size:14px;"><?php echo number_format($o->total_amount, 0, ',', '.'); ?> ₫</strong>
                    </td>
                    <td>
                        <div style="color:var(--mkv-text-main); font-size:13px; font-weight:500; display:flex; align-items:center; gap:6px;">
                            <?php
                            $pm_map = array('cash' => mkv__('Tiền mặt'), 'transfer' => mkv__('Chuyển khoản'), 'card' => mkv__('Thẻ'));
                            $pm_icons = array(
                                'cash'     => '<i class="hgi-stroke hgi-money-receive-square" style="color:var(--mkv-green); font-size:16px;"></i>',
                                'transfer' => '<i class="hgi-stroke hgi-bank" style="color:var(--mkv-primary); font-size:16px;"></i>',
                                'card'     => '<i class="hgi-stroke hgi-credit-card" style="color:var(--mkv-purple); font-size:16px;"></i>',
                            );
                            echo ($pm_icons[$o->payment_method] ?? '') . ' ' . esc_html($pm_map[$o->payment_method] ?? $o->payment_method);
                            ?>
                        </div>
                        <?php
                        $is_cod_pending = ($o->payment_status ?? '') === 'cod_pending';
                        $unpaid_balance = max(0.0, (float) $o->total_amount - (float) $o->paid_amount);

                        if ($is_cod_pending) {
                            $payment_due = (float) ($o->cod_amount ?? 0) > 0 ? (float) $o->cod_amount : $unpaid_balance;
                        } else {
                            if ((float) ($o->customer_debt_amount ?? 0) > 0) {
                                $payment_due = (float) $o->customer_debt_amount;
                            } elseif ((float) ($o->debt_amount ?? 0) > 0) {
                                $payment_due = (float) $o->debt_amount;
                            } else {
                                $payment_due = $unpaid_balance;
                            }
                        }
                        if ($o->status === 'cancelled') {
                            $payment_due = 0.0;
                        }

                        if ($payment_due > 0 && $o->status !== 'cancelled'):
                            if ($is_cod_pending):
                                ?><span class="mkv-badge mkv-badge-purple" style="margin-top:4px; display:inline-flex; align-items:center; gap:3px;"><i class="hgi-stroke hgi-truck-delivery"></i> <?php echo esc_html(mkv__('COD: ')); ?><?php echo number_format($payment_due, 0, ',', '.'); ?> ₫</span><?php
                            else:
                                ?><span class="mkv-badge mkv-badge-red" style="margin-top:4px; display:inline-flex; align-items:center; gap:3px;"><i class="hgi-stroke hgi-alert-circle"></i> <?php echo esc_html(mkv__('Còn nợ: ')); ?><?php echo number_format($payment_due, 0, ',', '.'); ?> ₫</span><?php
                            endif;
                        else:
                            ?><span class="mkv-badge mkv-badge-green" style="margin-top:4px; display:inline-flex; align-items:center; gap:3px;"><i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Đã thanh toán đủ')); ?></span><?php
                        endif;
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
                                        <i class="hgi-stroke hgi-truck-delivery"></i> <?php echo esc_html(mkv__('Tạo vận đơn')); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="mkv-badge mkv-badge-purple" style="font-size:11px; height:28px; display:inline-flex; align-items:center; gap:4px;"><i class="hgi-stroke hgi-truck-delivery"></i> <?php echo esc_html($o->tracking_code); ?></span>
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
                                <i class="hgi-stroke hgi-arrow-reload-horizontal"></i> <?php echo esc_html(mkv__('Trả Hàng')); ?>
                            </a>
                            <?php endif; ?>

                            <?php if ($payment_due > 0 && $o->status !== 'cancelled'): ?>
                            <button type="button" 
                                class="mkv-action-btn mkv-action-success"
                                onclick='mkvOpenCollectModal(<?php echo htmlspecialchars(json_encode(array(
                                    "id" => (int) $o->id,
                                    "code" => $o->order_code,
                                    "customer" => $o->customer_name ?: mkv__("Khách lẻ"),
                                    "due" => (float) $payment_due,
                                    "dueFormatted" => number_format($payment_due, 0, ",", "."),
                                    "isCod" => $is_cod_pending,
                                    "nonce" => wp_create_nonce("mkv_collect_order_debt_" . $o->id)
                                )), ENT_QUOTES, "UTF-8"); ?>)'
                                title="<?php echo esc_attr($is_cod_pending ? mkv__('Đối soát tiền COD từ shipper') : mkv__('Thu tiền nợ từ khách hàng')); ?>">
                                <i class="hgi-stroke hgi-money-receive-02"></i> <?php echo esc_html($is_cod_pending ? mkv__('Đối soát') : mkv__('Thu nợ')); ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Thu Tiền / Đối Soát COD Chuyên Nghiệp -->
    <div class="mkv-modal-overlay" id="mkv-collect-debt-modal" style="display:none;">
        <div class="mkv-modal" style="width:460px;">
            <div class="mkv-modal-header">
                <h3 class="mkv-modal-title">
                    <i class="hgi-stroke hgi-money-receive-square" id="mkv-cdm-icon" style="color:var(--mkv-green);"></i>
                    <span id="mkv-cdm-title"><?php echo esc_html(mkv__('Thu Tiền Khách Nợ')); ?></span>
                </h3>
                <button type="button" class="mkv-modal-close" onclick="mkvCloseCollectModal()">&times;</button>
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

                    <!-- Input Số tiền thu có nút chip thu đủ -->
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

<script>
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
    
    // Reset radio to cash
    var radios = document.querySelectorAll('input[name="method"]');
    radios.forEach(function(r) {
        r.checked = (r.value === 'cash');
        mkvUpdateMethodCards(r);
    });
    
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

// Close modal on click outside
document.getElementById('mkv-collect-debt-modal')?.addEventListener('click', function(e) {
    if (e.target === this) mkvCloseCollectModal();
});

document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Đơn hàng')); ?>');
</script>
<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>