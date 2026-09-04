<?php
if (!defined('ABSPATH')) exit;
require_once MKV_DIR . 'includes/views/header-kiotviet.php';
?>

<div class="mkv-page-header">
    <h1 class="mkv-page-title">
        <i class="hgi-stroke hgi-wallet-02"></i> <?php echo esc_html(mkv__('Sổ quỹ Tiền mặt & Ngân hàng')); ?>
    </h1>
    <div class="mkv-page-actions">
        <button class="mkv-btn mkv-btn-primary" onclick="document.getElementById('mkv-modal-thu').style.display='flex'">
            <i class="hgi-stroke hgi-add-circle"></i> <?php echo esc_html(mkv__('Lập phiếu thu')); ?>
        </button>
        <button class="mkv-btn mkv-btn-danger" onclick="document.getElementById('mkv-modal-chi').style.display='flex'">
            <i class="hgi-stroke hgi-remove-circle"></i> <?php echo esc_html(mkv__('Lập phiếu chi')); ?>
        </button>
    </div>
</div>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success'): ?>
<div class="mkv-alert mkv-alert-success"><i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Lưu giao dịch thành công!')); ?></div>
<?php endif; ?>

<!-- Summary Cards -->
<div class="mkv-stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="mkv-stat-card blue">
        <h3><?php echo esc_html(mkv__('Tổng tồn quỹ')); ?></h3>
        <p><?php echo number_format($ton_quy); ?> ₫</p>
        <i class="hgi-stroke hgi-wallet-02 stat-icon"></i>
    </div>
    <div class="mkv-stat-card green">
        <h3><?php echo esc_html(mkv__('Tổng thu trong kỳ')); ?></h3>
        <p><?php echo number_format($ky_thu); ?> ₫</p>
        <i class="hgi-stroke hgi-money-receive-square stat-icon"></i>
    </div>
    <div class="mkv-stat-card" style="border-left:3px solid var(--mkv-red);">
        <h3><?php echo esc_html(mkv__('Tổng chi trong kỳ')); ?></h3>
        <p style="color:var(--mkv-red) !important;"><?php echo number_format($ky_chi); ?> ₫</p>
        <i class="hgi-stroke hgi-money-send-square stat-icon"></i>
    </div>
</div>

<!-- Filter Bar -->
<div class="mkv-filter-bar">
    <form method="GET" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; width:100%;">
        <input type="hidden" name="page" value="mkv-cashbook">
        <div class="mkv-form-group">
            <label class="mkv-label"><?php echo esc_html(mkv__('Từ ngày')); ?></label>
            <input type="date" name="start_date" class="mkv-input" value="<?php echo esc_attr($start_date); ?>" style="width:150px;">
        </div>
        <div class="mkv-form-group">
            <label class="mkv-label"><?php echo esc_html(mkv__('Đến ngày')); ?></label>
            <input type="date" name="end_date" class="mkv-input" value="<?php echo esc_attr($end_date); ?>" style="width:150px;">
        </div>
        <div class="mkv-form-group" style="margin-bottom:0;">
            <button type="submit" class="mkv-btn mkv-btn-secondary"><i class="hgi-stroke hgi-filter-01"></i> <?php echo esc_html(mkv__('Lọc dữ liệu')); ?></button>
        </div>
    </form>
</div>

<!-- Table -->
<div class="mkv-table-wrap">
    <table class="mkv-table">
        <thead>
            <tr>
                <th><?php echo esc_html(mkv__('Ngày tạo')); ?></th>
                <th><?php echo esc_html(mkv__('Loại phiếu')); ?></th>
                <th><?php echo esc_html(mkv__('Phương thức')); ?></th>
                <th><?php echo esc_html(mkv__('Giá trị')); ?></th>
                <th><?php echo esc_html(mkv__('Người tạo')); ?></th>
                <th><?php echo esc_html(mkv__('Ghi chú')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($transactions)): ?>
                <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($t->created_at)); ?></td>
                        <td>
                            <?php if ($t->type === 'thu'): ?>
                                <span class="mkv-badge mkv-badge-green"><?php echo esc_html(mkv__('Phiếu Thu')); ?></span>
                            <?php else: ?>
                                <span class="mkv-badge mkv-badge-red"><?php echo esc_html(mkv__('Phiếu Chi')); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($t->customer_name)): ?>
                                <div style="font-size:12px; margin-top:4px; color:#6b7280;"><i class="hgi-stroke hgi-user"></i> <?php echo esc_html($t->customer_name); ?></div>
                            <?php elseif (!empty($t->supplier_name)): ?>
                                <div style="font-size:12px; margin-top:4px; color:#6b7280;"><i class="hgi-stroke hgi-building-02"></i> <?php echo esc_html($t->supplier_name); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $t->method === 'cash' ? esc_html(mkv__('Tiền mặt')) : esc_html(mkv__('Ngân hàng / CK')); ?></td>
                        <td style="font-weight:600; color:<?php echo $t->type === 'thu' ? '#389e0d' : 'var(--mkv-red)'; ?>;">
                            <?php echo $t->type === 'thu' ? '+' : '−'; ?><?php echo number_format($t->amount); ?> ₫
                        </td>
                        <td><?php echo esc_html($t->display_name); ?></td>
                        <td style="color:#6b7280;"><?php echo esc_html($t->note); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">
                        <div class="mkv-empty">
                            <i class="hgi-stroke hgi-file-not-found"></i>
                            <p><?php echo esc_html(mkv__('Chưa có giao dịch nào trong kỳ.')); ?></p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Lập phiếu Thu -->
<div class="mkv-modal-overlay" id="mkv-modal-thu" style="display:none;">
    <div class="mkv-modal">
        <div class="mkv-modal-header">
            <h3 class="mkv-modal-title"><i class="hgi-stroke hgi-money-receive-square" style="color:var(--mkv-green);"></i> <?php echo esc_html(mkv__('Lập Phiếu Thu')); ?></h3>
            <button class="mkv-modal-close" onclick="document.getElementById('mkv-modal-thu').style.display='none'">&times;</button>
        </div>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
            <div class="mkv-modal-body">
                <input type="hidden" name="action" value="mkv_add_cashbook">
                <input type="hidden" name="cb_type" value="thu">
                <?php wp_nonce_field('mkv_cashbook_action'); ?>
                <div class="mkv-form-group">
                    <label class="mkv-label"><?php echo esc_html(mkv__('Giá trị (VND)')); ?> <span style="color:var(--mkv-red)">*</span></label>
                    <input type="number" name="cb_amount" class="mkv-input mkv-currency-input" required min="1" placeholder="<?php echo esc_attr(mkv__('Nhập số tiền...')); ?>">
                </div>
                <div class="mkv-form-group">
                    <label class="mkv-label"><?php echo esc_html(mkv__('Thu nợ khách hàng (Tùy chọn)')); ?></label>
                    <select name="cb_customer_id" class="mkv-input mkv-select2" style="width:100%">
                        <option value="0"><?php echo esc_html(mkv__('-- Khác / Không chọn --')); ?></option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?php echo esc_attr($c->id); ?>"><?php echo esc_html($c->name . ' - Nợ: ' . number_format($c->total_debt) . ' ₫'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mkv-form-group">
                    <label class="mkv-label"><?php echo esc_html(mkv__('Phương thức')); ?></label>
                    <select name="cb_method" class="mkv-input">
                        <option value="cash"><?php echo esc_html(mkv__('Tiền mặt')); ?></option>
                        <option value="bank"><?php echo esc_html(mkv__('Chuyển khoản / Ngân hàng')); ?></option>
                    </select>
                </div>
                <div class="mkv-form-group">
                    <label class="mkv-label"><?php echo esc_html(mkv__('Ghi chú')); ?></label>
                    <textarea name="cb_note" class="mkv-input" rows="3" placeholder="<?php echo esc_attr(mkv__('Nội dung giao dịch...')); ?>"></textarea>
                </div>
            </div>
            <div class="mkv-modal-footer">
                <button type="button" class="mkv-btn mkv-btn-secondary" onclick="document.getElementById('mkv-modal-thu').style.display='none'"><?php echo esc_html(mkv__('Hủy')); ?></button>
                <button type="submit" class="mkv-btn mkv-btn-success"><i class="hgi-stroke hgi-save-01"></i> <?php echo esc_html(mkv__('Lưu phiếu thu')); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Lập phiếu Chi -->
<div class="mkv-modal-overlay" id="mkv-modal-chi" style="display:none;">
    <div class="mkv-modal">
        <div class="mkv-modal-header">
            <h3 class="mkv-modal-title"><i class="hgi-stroke hgi-money-send-square" style="color:var(--mkv-red);"></i> <?php echo esc_html(mkv__('Lập Phiếu Chi')); ?></h3>
            <button class="mkv-modal-close" onclick="document.getElementById('mkv-modal-chi').style.display='none'">&times;</button>
        </div>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
            <div class="mkv-modal-body">
                <input type="hidden" name="action" value="mkv_add_cashbook">
                <input type="hidden" name="cb_type" value="chi">
                <?php wp_nonce_field('mkv_cashbook_action'); ?>
                <div class="mkv-form-group">
                    <label class="mkv-label"><?php echo esc_html(mkv__('Giá trị (VND)')); ?> <span style="color:var(--mkv-red)">*</span></label>
                    <input type="number" name="cb_amount" class="mkv-input mkv-currency-input" required min="1" placeholder="<?php echo esc_attr(mkv__('Nhập số tiền...')); ?>">
                </div>
                <div class="mkv-form-group">
                    <label class="mkv-label"><?php echo esc_html(mkv__('Trả nợ NCC (Tùy chọn)')); ?></label>
                    <select name="cb_supplier_id" class="mkv-input mkv-select2" style="width:100%">
                        <option value="0"><?php echo esc_html(mkv__('-- Khác / Không chọn --')); ?></option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?php echo esc_attr($s->id); ?>"><?php echo esc_html($s->name . ' - Nợ: ' . number_format($s->total_debt) . ' ₫'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mkv-form-group">
                    <label class="mkv-label"><?php echo esc_html(mkv__('Phương thức')); ?></label>
                    <select name="cb_method" class="mkv-input">
                        <option value="cash"><?php echo esc_html(mkv__('Tiền mặt')); ?></option>
                        <option value="bank"><?php echo esc_html(mkv__('Chuyển khoản / Ngân hàng')); ?></option>
                    </select>
                </div>
                <div class="mkv-form-group">
                    <label class="mkv-label"><?php echo esc_html(mkv__('Ghi chú / Lý do chi')); ?></label>
                    <textarea name="cb_note" class="mkv-input" rows="3" placeholder="<?php echo esc_attr(mkv__('Lý do chi tiền...')); ?>"></textarea>
                </div>
            </div>
            <div class="mkv-modal-footer">
                <button type="button" class="mkv-btn mkv-btn-secondary" onclick="document.getElementById('mkv-modal-chi').style.display='none'"><?php echo esc_html(mkv__('Hủy')); ?></button>
                <button type="submit" class="mkv-btn mkv-btn-danger"><i class="hgi-stroke hgi-save-01"></i> <?php echo esc_html(mkv__('Lưu phiếu chi')); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Sổ quỹ')); ?>');
jQuery(document).ready(function($) {
    if ($.fn.select2) {
        $('.mkv-select2').select2({
            dropdownParent: $('#mkv-modal-thu')
        });
        $('#mkv-modal-chi .mkv-select2').select2({
            dropdownParent: $('#mkv-modal-chi')
        });
    }
});
</script>

<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>
