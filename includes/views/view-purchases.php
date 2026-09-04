<?php
if (!defined('ABSPATH')) exit;
require_once MKV_DIR . 'includes/views/header-kiotviet.php';
?>

<div class="mkv-page-header">
    <h1 class="mkv-page-title">
        <i class="hgi-stroke hgi-truck-02"></i> <?php echo esc_html(mkv__('Quản lý Mua Hàng')); ?>
    </h1>
    <div class="mkv-page-actions">
        <?php if ($current_tab === 'suppliers'): ?>
            <button class="mkv-btn mkv-btn-primary" onclick="document.getElementById('mkv-modal-sup').style.display='flex'">
                <i class="hgi-stroke hgi-user-add-02"></i> <?php echo esc_html(mkv__('Thêm Nhà cung cấp')); ?>
            </button>
        <?php else: ?>
            <a href="?page=mkv-purchases&tab=create" class="mkv-btn mkv-btn-primary">
                <i class="hgi-stroke hgi-add-circle"></i> <?php echo esc_html(mkv__('Lập phiếu nhập')); ?>
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success'): ?>
<div class="mkv-alert mkv-alert-success"><i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Thao tác thành công!')); ?></div>
<?php endif; ?>

<!-- Tabs -->
<div class="mkv-tabs-bar">
    <a href="?page=mkv-purchases&tab=list"
       class="mkv-tab-link <?php echo $current_tab === 'list' ? 'active' : ''; ?>">
        <i class="hgi-stroke hgi-invoice-01"></i> <?php echo esc_html(mkv__('Danh sách phiếu nhập')); ?>
    </a>
    <a href="?page=mkv-purchases&tab=suppliers"
       class="mkv-tab-link <?php echo $current_tab === 'suppliers' ? 'active' : ''; ?>">
        <i class="hgi-stroke hgi-user-group"></i> <?php echo esc_html(mkv__('Nhà cung cấp')); ?>
    </a>
</div>

<!-- Tab: Danh sách Phiếu nhập -->
<?php if ($current_tab === 'list'): ?>
<div class="mkv-table-wrap">
    <table class="mkv-table">
        <thead>
            <tr>
                <th><?php echo esc_html(mkv__('Mã phiếu')); ?></th>
                <th><?php echo esc_html(mkv__('Thời gian')); ?></th>
                <th><?php echo esc_html(mkv__('Nhà cung cấp')); ?></th>
                <th><?php echo esc_html(mkv__('Tổng cộng')); ?></th>
                <th><?php echo esc_html(mkv__('Trạng thái')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($pos)): ?>
                <?php foreach ($pos as $p): ?>
                    <tr>
                        <td><strong style="color:var(--mkv-primary);"><?php echo esc_html($p->code); ?></strong></td>
                        <td style="color:#6b7280;"><?php echo date('d/m/Y H:i', strtotime($p->created_at)); ?></td>
                        <td><?php echo $p->supplier_name ? esc_html($p->supplier_name) : '<span style="color:#9ca3af">'.esc_html(mkv__('Không rõ')).'</span>'; ?></td>
                        <td><strong><?php echo number_format($p->total_amount); ?> ₫</strong></td>
                        <td><span class="mkv-badge mkv-badge-green"><?php echo esc_html(mkv__('Đã hoàn thành')); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5"><div class="mkv-empty"><i class="hgi-stroke hgi-file-not-found"></i><p><?php echo esc_html(mkv__('Chưa có phiếu nhập hàng nào.')); ?></p></div></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Tab: Nhà cung cấp -->
<?php if ($current_tab === 'suppliers'): ?>
<div class="mkv-table-wrap">
    <table class="mkv-table">
        <thead>
            <tr>
                <th><?php echo esc_html(mkv__('Mã NCC')); ?></th>
                <th><?php echo esc_html(mkv__('Tên nhà cung cấp')); ?></th>
                <th><?php echo esc_html(mkv__('Điện thoại')); ?></th>
                <th><?php echo esc_html(mkv__('Nợ cần trả')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($suppliers)): ?>
                <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td><code style="font-size:12px;">NCC<?php echo str_pad($s->id, 5, '0', STR_PAD_LEFT); ?></code></td>
                        <td><strong><?php echo esc_html($s->name); ?></strong></td>
                        <td style="color:#6b7280;"><?php echo esc_html($s->phone ?: '—'); ?></td>
                        <td><?php echo $s->total_debt > 0
                            ? '<span class="mkv-badge mkv-badge-red">'.number_format($s->total_debt).' ₫</span>'
                            : '<span class="mkv-badge mkv-badge-green">'.esc_html(mkv__('Không nợ')).'</span>'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4"><div class="mkv-empty"><i class="hgi-stroke hgi-user-group"></i><p><?php echo esc_html(mkv__('Chưa có nhà cung cấp nào.')); ?></p></div></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal thêm NCC -->
<div class="mkv-modal-overlay" id="mkv-modal-sup" style="display:none;">
    <div class="mkv-modal">
        <div class="mkv-modal-header">
            <h3 class="mkv-modal-title"><i class="hgi-stroke hgi-user-add-02" style="color:var(--mkv-primary);"></i> <?php echo esc_html(mkv__('Thêm Nhà cung cấp')); ?></h3>
            <button class="mkv-modal-close" onclick="document.getElementById('mkv-modal-sup').style.display='none'">&times;</button>
        </div>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
            <div class="mkv-modal-body">
                <input type="hidden" name="action" value="mkv_add_supplier">
                <?php wp_nonce_field('mkv_supplier_action'); ?>
                <div class="mkv-form-group">
                    <label class="mkv-label"><?php echo esc_html(mkv__('Tên nhà cung cấp')); ?> <span style="color:var(--mkv-red)">*</span></label>
                    <input type="text" name="sup_name" class="mkv-input" required placeholder="<?php echo esc_attr(mkv__('VD: Công ty TNHH ABC...')); ?>">
                </div>
                <div class="mkv-form-group">
                    <label class="mkv-label"><?php echo esc_html(mkv__('Điện thoại')); ?></label>
                    <input type="text" name="sup_phone" class="mkv-input" placeholder="VD: 0901 234 567">
                </div>
            </div>
            <div class="mkv-modal-footer">
                <button type="button" class="mkv-btn mkv-btn-secondary" onclick="document.getElementById('mkv-modal-sup').style.display='none'"><?php echo esc_html(mkv__('Hủy')); ?></button>
                <button type="submit" class="mkv-btn mkv-btn-primary"><i class="hgi-stroke hgi-save-01"></i> <?php echo esc_html(mkv__('Lưu')); ?></button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Tab: Lập phiếu nhập -->
<?php if ($current_tab === 'create'): ?>
<div class="mkv-grid-sidebar" style="grid-template-columns: 1fr;">
    <div class="mkv-card">
        <div class="mkv-card-header">
            <h3 class="mkv-card-title"><i class="hgi-stroke hgi-add-circle"></i> <?php echo esc_html(mkv__('Tạo Phiếu Nhập Hàng Mới')); ?></h3>
        </div>
        <div class="mkv-card-body">
            <p style="color:#6b7280; font-size:13px; margin:0 0 16px;">
                <?php echo esc_html(mkv__('Tồn kho sẽ')); ?> <strong><?php echo esc_html(mkv__('tự động cộng thêm')); ?></strong><?php echo esc_html(mkv__(', đồng thời tự sinh')); ?> <strong><?php echo esc_html(mkv__('Phiếu Chi')); ?></strong> <?php echo esc_html(mkv__('bên Sổ Quỹ sau khi lưu.')); ?>
            </p>
            <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
                <input type="hidden" name="action" value="mkv_add_purchase_order">
                <?php wp_nonce_field('mkv_po_action'); ?>
                <div class="mkv-form-row">
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Nhà cung cấp')); ?></label>
                        <select name="supplier_id" class="mkv-input">
                            <option value="0"><?php echo esc_html(mkv__('— Chọn nhà cung cấp —')); ?></option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?php echo $s->id; ?>"><?php echo esc_html($s->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mkv-form-group" style="flex:2;">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Sản phẩm')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <select name="product_id" class="mkv-input" required>
                            <option value=""><?php echo esc_html(mkv__('— Chọn một sản phẩm —')); ?></option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?php echo $p->ID; ?>"><?php echo esc_html($p->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mkv-form-row">
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Kho nhập hàng')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <select name="location_id" class="mkv-input" required>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?php echo $loc->id; ?>"><?php echo esc_html($loc->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Số lượng nhập')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <input type="number" name="qty" class="mkv-input" required min="1" value="1" placeholder="0">
                    </div>
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Đơn giá nhập (VNĐ)')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <input type="number" name="price" class="mkv-input mkv-currency-input" required min="0" value="0" placeholder="0">
                    </div>
                </div>
                <div class="mkv-form-row">
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Số tiền đã trả NCC (VNĐ)')); ?></label>
                        <input type="number" name="paid_amount" class="mkv-input mkv-currency-input" min="0" value="0" placeholder="0">
                        <p style="font-size:12px; color:#6b7280; margin-top:4px;">Nếu nhập nhỏ hơn Tổng cộng, phần còn lại sẽ ghi nợ.</p>
                    </div>
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end; border-top:1px solid var(--mkv-border-light); padding-top:16px; margin-top:4px;">
                    <a href="?page=mkv-purchases&tab=list" class="mkv-btn mkv-btn-secondary"><?php echo esc_html(mkv__('Hủy')); ?></a>
                    <button type="submit" class="mkv-btn mkv-btn-primary">
                        <i class="hgi-stroke hgi-package-delivered"></i> <?php echo esc_html(mkv__('Hoàn thành & Nhập kho')); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Mua hàng')); ?>');
</script>

<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>
