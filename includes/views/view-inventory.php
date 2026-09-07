<?php if (!defined('ABSPATH')) exit; ?>
<?php require_once MKV_DIR . 'includes/views/header-kiotviet.php'; ?>
<div class="mkv-page-header">
    <h1 class="mkv-page-title"><i class="hgi-stroke hgi-layout-grid"></i> <?php echo esc_html(mkv__('Quản Lý Kho')); ?></h1>
</div>

    <div class="mkv-tabs-bar">
        <a href="?page=mkv-inventory&tab=locations" class="mkv-tab-link <?php echo $active_tab==='locations'?'active':'';?>"><i class="hgi-stroke hgi-store-01"></i> <?php echo esc_html(mkv__('Danh sách kho')); ?></a>
        <a href="?page=mkv-inventory&tab=stock"     class="mkv-tab-link <?php echo $active_tab==='stock'    ?'active':'';?>"><i class="hgi-stroke hgi-package"></i> <?php echo esc_html(mkv__('Tồn kho')); ?></a>
        <a href="?page=mkv-inventory&tab=transfer"  class="mkv-tab-link <?php echo $active_tab==='transfer' ?'active':'';?>"><i class="hgi-stroke hgi-arrow-data-transfer-horizontal"></i> <?php echo esc_html(mkv__('Chuyển kho')); ?></a>
        <a href="?page=mkv-inventory&tab=stocktake" class="mkv-tab-link <?php echo $active_tab==='stocktake'?'active':'';?>"><i class="hgi-stroke hgi-task-edit-01"></i> <?php echo esc_html(mkv__('Kiểm kho')); ?></a>
        <a href="?page=mkv-inventory&tab=logs"      class="mkv-tab-link <?php echo $active_tab==='logs'     ?'active':'';?>"><i class="hgi-stroke hgi-clipboard"></i> <?php echo esc_html(mkv__('Thẻ kho (Lịch sử)')); ?></a>
    </div>

    <?php if (isset($_GET['added'])):    ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html(mkv__('Đã thêm kho mới!')); ?></p></div><?php endif; ?>
    <?php if (isset($_GET['updated'])):  ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html(mkv__('Đã cập nhật tồn kho!')); ?></p></div><?php endif; ?>
    <?php if (isset($_GET['success'])):  ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html(mkv__('Chuyển kho thành công!')); ?></p></div><?php endif; ?>
    <?php if (isset($_GET['error'])):
        $err_map = array('invalid'=>mkv__('Thông tin không hợp lệ.'),'insufficient'=>mkv__('Kho nguồn không đủ tồn kho.'),'db'=>mkv__('Lỗi cơ sở dữ liệu, vui lòng thử lại.'));
        $err_msg = $err_map[$_GET['error']] ?? mkv__('Có lỗi xảy ra.'); ?>
        <div class="notice notice-error is-dismissible"><p><?php echo esc_html($err_msg); ?></p></div>
    <?php endif; ?>

    <?php /* ==== TAB: Locations ==== */ if ($active_tab === 'locations'): ?>
    <div class="mkv-two-col-form" style="display:grid; grid-template-columns:350px 1fr; gap:20px; align-items:start;">
        <div class="mkv-card">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title"><i class="hgi-stroke hgi-add-square"></i> <?php echo esc_html(mkv__('Thêm Kho / Chi Nhánh')); ?></h3>
            </div>
            <div class="mkv-card-body">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="mkv_add_location">
                    <?php wp_nonce_field('mkv_add_location_nonce'); ?>
                    <p><label style="font-size:13px; font-weight:600; color:var(--mkv-text-main);"><?php echo esc_html(mkv__('Tên kho')); ?> <span style="color:var(--mkv-red)">*</span></label>
                    <input type="text" name="loc_name" required class="mkv-input" style="margin-top:6px;"></p>
                    <p><label style="font-size:13px; font-weight:600; color:var(--mkv-text-main);"><?php echo esc_html(mkv__('Mã kho')); ?></label>
                    <input type="text" name="loc_code" placeholder="<?php echo esc_attr(mkv__('VD: KHN, KSG')); ?>" class="mkv-input" style="margin-top:6px;"></p>
                    <p><label style="font-size:13px; font-weight:600; color:var(--mkv-text-main);"><?php echo esc_html(mkv__('Địa chỉ')); ?></label>
                    <input type="text" name="loc_address" class="mkv-input" style="margin-top:6px;"></p>
                    <button type="submit" class="mkv-btn mkv-btn-primary" style="width:100%; justify-content:center; margin-top:8px; height:38px; border-radius:8px;"><i class="hgi-stroke hgi-floppy-disk"></i> <?php echo esc_html(mkv__('Thêm kho')); ?></button>
                </form>
            </div>
        </div>
        <div class="mkv-table-wrap" style="flex:1;">
            <table class="mkv-table" style="min-width: 600px;">
                <thead><tr><th><?php echo esc_html(mkv__('Tên kho')); ?></th><th><?php echo esc_html(mkv__('Mã kho')); ?></th><th><?php echo esc_html(mkv__('Địa chỉ')); ?></th><th style="width:90px;"><?php echo esc_html(mkv__('Trạng thái')); ?></th></tr></thead>
                <tbody>
                <?php if (empty($locations)): ?>
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Chưa có kho nào.')); ?></td></tr>
                <?php else: foreach ($locations as $loc): ?>
                    <tr>
                        <td><strong><?php echo esc_html($loc->name); ?></strong></td>
                        <td><code><?php echo esc_html($loc->code ?: '—'); ?></code></td>
                        <td><?php echo esc_html($loc->address ?: '—'); ?></td>
                        <td><span class="mkv-badge <?php echo $loc->status==='active'?'mkv-badge-green':'mkv-badge-red'; ?>"><?php echo $loc->status==='active'?esc_html(mkv__('Hoạt động')):esc_html(mkv__('Tạm ngừng')); ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php /* ==== TAB: Stock ==== */ elseif ($active_tab === 'stock'): ?>
    <div class="mkv-table-wrap">
        <table class="mkv-table" style="min-width: 850px;">
            <thead><tr>
                <th><?php echo esc_html(mkv__('Sản phẩm')); ?></th>
                <?php foreach ($locations as $loc): ?><th style="width:90px;"><?php echo esc_html($loc->name); ?></th><?php endforeach; ?>
                <th style="width:90px;"><?php echo esc_html(mkv__('Tổng')); ?></th>
                <th style="width:350px;"><?php echo esc_html(mkv__('Điều chỉnh nhanh')); ?></th>
            </tr></thead>
            <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="10" style="text-align:center;padding:30px;color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Chưa có sản phẩm.')); ?></td></tr>
            <?php else: foreach ($products as $p):
                global $wpdb;
                $stocks = array();
                $total  = 0;
                foreach ($locations as $loc) {
                    $s = (int) $wpdb->get_var($wpdb->prepare(
                        "SELECT stock FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d AND location_id=%d",
                        $p->ID, $loc->id
                    ));
                    $stocks[$loc->id] = $s;
                    $total += $s;
                }
                $min_stock = (int) get_post_meta($p->ID, '_mkv_min_stock', true) ?: (int) get_option('mkv_min_stock_threshold', 5);
            ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($p->post_title); ?></strong>
                        <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                            <small style="color:var(--mkv-text-muted);">SKU: <?php echo esc_html(get_post_meta($p->ID,'_mkv_sku',true)?:'—'); ?></small>
                            <a href="?page=mkv-inventory&tab=logs&product_id=<?php echo $p->ID; ?>" class="mkv-badge mkv-badge-blue" style="text-decoration:none; font-size:11px; padding:2px 8px; cursor:pointer;" title="<?php echo esc_attr(mkv__('Xem thẻ kho của sản phẩm này')); ?>">
                                <i class="hgi-stroke hgi-clipboard"></i> <?php echo esc_html(mkv__('Thẻ kho')); ?>
                            </a>
                        </div>
                    </td>
                    <?php foreach ($locations as $loc): ?>
                    <td><?php $s = $stocks[$loc->id];
                        if ($s <= 0) echo '<span class="mkv-badge mkv-badge-red">'.$s.'</span>';
                        elseif ($s <= $min_stock) echo '<span class="mkv-badge mkv-badge-yellow">'.$s.'</span>';
                        else echo '<span class="mkv-badge mkv-badge-green">'.$s.'</span>';
                    ?></td>
                    <?php endforeach; ?>
                    <td><strong><?php echo $total; ?></strong></td>
                    <td>
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline-flex; gap:6px; align-items:center; background:var(--mkv-border-light); padding:4px 6px; border-radius:8px; border:1px solid var(--mkv-border);">
                            <input type="hidden" name="action" value="mkv_adjust_stock">
                            <input type="hidden" name="product_id" value="<?php echo $p->ID; ?>">
                            <?php wp_nonce_field('mkv_adjust_stock_nonce'); ?>
                            <select name="location_id" class="mkv-select" style="height:28px; padding:0 8px; font-size:12px; border-radius:6px;" title="<?php echo esc_attr(mkv__('Chọn kho')); ?>">
                                <?php foreach ($locations as $loc): ?><option value="<?php echo $loc->id; ?>"><?php echo esc_html($loc->name); ?></option><?php endforeach; ?>
                            </select>
                            <select name="type" class="mkv-select" style="height:28px; padding:0 6px; font-size:12px; border-radius:6px;" title="<?php echo esc_attr(mkv__('Loại điều chỉnh')); ?>">
                                <option value="in" style="color:var(--mkv-green); font-weight:600;"><?php echo esc_html(mkv__('+ Nhập')); ?></option>
                                <option value="out" style="color:var(--mkv-red); font-weight:600;"><?php echo esc_html(mkv__('− Xuất')); ?></option>
                            </select>
                            <input type="number" name="qty" min="1" placeholder="<?php echo esc_attr(mkv__('SL')); ?>" required class="mkv-input" style="width:55px; height:28px; padding:0 6px; font-size:12.5px; text-align:center; border-radius:6px;" title="<?php echo esc_attr(mkv__('Số lượng')); ?>">
                            <input type="text" name="note" placeholder="<?php echo esc_attr(mkv__('Lý do')); ?>" class="mkv-input" style="width:110px; height:28px; padding:0 8px; font-size:12px; border-radius:6px;" title="<?php echo esc_attr(mkv__('Ghi chú lý do')); ?>">
                            <button type="submit" class="mkv-btn mkv-btn-primary" style="height:28px; width:28px; padding:0; justify-content:center; border-radius:6px;" title="<?php echo esc_attr(mkv__('Xác nhận điều chỉnh')); ?>"><i class="hgi-stroke hgi-tick-02"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php /* ==== TAB: Transfer ==== */ elseif ($active_tab === 'transfer'): ?>
    <div style="display:grid;grid-template-columns:420px 1fr;gap:24px;align-items:start;">
        <div class="mkv-card">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title"><i class="hgi-stroke hgi-arrow-data-transfer-horizontal"></i> <?php echo esc_html(mkv__('Chuyển kho')); ?></h3>
            </div>
            <div class="mkv-card-body">
                <p style="color:var(--mkv-text-muted);font-size:13px;margin-top:0;"><?php echo esc_html(mkv__('Sử dụng Database Transaction — đảm bảo dữ liệu không bị thất thoát.')); ?></p>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="mkv_transfer_stock">
                    <?php wp_nonce_field('mkv_transfer_stock_nonce'); ?>
                    <p><label style="font-size:13px; font-weight:600; color:var(--mkv-text-main);"><?php echo esc_html(mkv__('Sản phẩm')); ?> <span style="color:var(--mkv-red)">*</span></label>
                    <select name="product_id" required class="mkv-select" style="margin-top:6px;">
                        <option value=""><?php echo esc_html(mkv__('— Chọn sản phẩm —')); ?></option>
                        <?php $prods = get_posts(array('post_type'=>'mkv_product','numberposts'=>-1,'post_status'=>'publish'));
                        foreach ($prods as $pp): ?>
                            <option value="<?php echo $pp->ID; ?>"><?php echo esc_html($pp->post_title); ?></option>
                        <?php endforeach; ?>
                    </select></p>
                    <div style="display:grid;grid-template-columns:1fr 32px 1fr;align-items:center;gap:8px;">
                        <div><label style="font-size:13px; font-weight:600; color:var(--mkv-text-main);"><?php echo esc_html(mkv__('Kho nguồn')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <select name="location_from" required class="mkv-select" style="margin-top:6px;">
                            <?php foreach ($locations as $loc): ?><option value="<?php echo $loc->id; ?>"><?php echo esc_html($loc->name); ?></option><?php endforeach; ?>
                        </select></div>
                        <div style="text-align:center;margin-top:22px;font-size:20px; color:var(--mkv-text-muted);">→</div>
                        <div><label style="font-size:13px; font-weight:600; color:var(--mkv-text-main);"><?php echo esc_html(mkv__('Kho đích')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <select name="location_to" required class="mkv-select" style="margin-top:6px;">
                            <?php foreach ($locations as $loc): ?><option value="<?php echo $loc->id; ?>"><?php echo esc_html($loc->name); ?></option><?php endforeach; ?>
                        </select></div>
                    </div>
                    <p><label style="font-size:13px; font-weight:600; color:var(--mkv-text-main);"><?php echo esc_html(mkv__('Số lượng chuyển')); ?> <span style="color:var(--mkv-red)">*</span></label>
                    <input type="number" name="qty" min="1" required class="mkv-input" style="margin-top:6px;"></p>
                    <p><label style="font-size:13px; font-weight:600; color:var(--mkv-text-main);"><?php echo esc_html(mkv__('Ghi chú')); ?></label>
                    <input type="text" name="note" class="mkv-input" style="margin-top:6px;"></p>
                    <button type="submit" class="mkv-btn mkv-btn-primary" style="width:100%; justify-content:center; margin-top:8px; height:38px; border-radius:8px;"><i class="hgi-stroke hgi-arrow-data-transfer-horizontal"></i> <?php echo esc_html(mkv__('Xác nhận Chuyển kho')); ?></button>
                </form>
            </div>
        </div>
        <div class="mkv-card mkv-widget">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title"><i class="hgi-stroke hgi-information-circle"></i> <?php echo esc_html(mkv__('Tồn kho hiện tại')); ?></h3>
            </div>
            <div class="mkv-card-body">
                <p style="color:var(--mkv-text-muted);font-size:13px;margin:0;"><?php echo esc_html(mkv__('Chọn sản phẩm để xem chi tiết tồn kho theo từng kho.')); ?></p>
            </div>
        </div>
    </div>

    <?php /* ==== TAB: Stocktake ==== */ elseif ($active_tab === 'stocktake'): ?>
    <div style="display:grid;grid-template-columns:420px 1fr;gap:24px;align-items:start;">
        <div class="mkv-card">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title"><i class="hgi-stroke hgi-task-edit-01"></i> <?php echo esc_html(mkv__('Tạo Phiếu Kiểm Kho')); ?></h3>
            </div>
            <div class="mkv-card-body">
                <p style="color:var(--mkv-text-muted);font-size:13px;margin-top:0;"><?php echo esc_html(mkv__('Nhập số lượng thực tế. Phần mềm sẽ tự động so sánh và tạo phiếu cân bằng kho nếu có chênh lệch.')); ?></p>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="mkv_create_stocktake">
                    <?php wp_nonce_field('mkv_create_stocktake_nonce'); ?>
                    
                    <p><label style="font-size:13px; font-weight:600; color:var(--mkv-text-main);"><?php echo esc_html(mkv__('Kho kiểm')); ?> <span style="color:var(--mkv-red)">*</span></label>
                    <select name="location_id" required class="mkv-select" style="margin-top:6px;" onchange="updateSysQty()">
                        <option value=""><?php echo esc_html(mkv__('— Chọn kho —')); ?></option>
                        <?php foreach ($locations as $loc): ?><option value="<?php echo $loc->id; ?>"><?php echo esc_html($loc->name); ?></option><?php endforeach; ?>
                    </select></p>

                    <p><label style="font-size:13px; font-weight:600; color:var(--mkv-text-main);"><?php echo esc_html(mkv__('Sản phẩm kiểm')); ?> <span style="color:var(--mkv-red)">*</span></label>
                    <select name="product_id" required class="mkv-select" style="margin-top:6px;" id="mkv_stocktake_product" onchange="updateSysQty()">
                        <option value=""><?php echo esc_html(mkv__('— Chọn sản phẩm —')); ?></option>
                        <?php foreach ($products as $pp): ?>
                            <option value="<?php echo $pp->ID; ?>"><?php echo esc_html($pp->post_title); ?></option>
                        <?php endforeach; ?>
                    </select></p>

                    <div style="background:#f8fafc; padding:12px; border-radius:8px; margin: 16px 0; border:1px solid #e2e8f0;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="font-size:13px; color:#64748b;"><?php echo esc_html(mkv__('Tồn hệ thống:')); ?></span>
                            <strong id="st_sys_qty">0</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:13px; color:#0f172a; font-weight:600;"><?php echo esc_html(mkv__('Tồn thực tế:')); ?> <span style="color:var(--mkv-red)">*</span></span>
                            <input type="number" name="actual_qty" id="st_actual_qty" min="0" required class="mkv-input" style="width:100px; text-align:right;" oninput="updateDiff()" placeholder="0">
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-top:8px; padding-top:8px; border-top:1px dashed #cbd5e1;">
                            <span style="font-size:13px; color:#64748b;"><?php echo esc_html(mkv__('Chênh lệch:')); ?></span>
                            <strong id="st_diff_qty">0</strong>
                        </div>
                    </div>

                    <p><label style="font-size:13px; font-weight:600; color:var(--mkv-text-main);"><?php echo esc_html(mkv__('Ghi chú')); ?></label>
                    <input type="text" name="note" class="mkv-input" style="margin-top:6px;"></p>

                    <button type="submit" class="mkv-btn mkv-btn-primary" style="width:100%; justify-content:center; margin-top:8px; height:38px; border-radius:8px;"><i class="hgi-stroke hgi-floppy-disk"></i> <?php echo esc_html(mkv__('Hoàn thành & Cân bằng kho')); ?></button>
                </form>
            </div>
        </div>
        
        <div class="mkv-table-wrap">
            <table class="mkv-table" style="min-width: 700px;">
                <thead><tr><th><?php echo esc_html(mkv__('Mã Phiếu')); ?></th><th><?php echo esc_html(mkv__('Kho')); ?></th><th><?php echo esc_html(mkv__('Chênh lệch')); ?></th><th><?php echo esc_html(mkv__('Thời gian')); ?></th></tr></thead>
                <tbody>
                <?php if (empty($stocktakes)): ?>
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Chưa có phiếu kiểm kho.')); ?></td></tr>
                <?php else: foreach ($stocktakes as $st): ?>
                    <tr>
                        <td><strong><?php echo esc_html($st->code); ?></strong></td>
                        <td><?php echo esc_html($st->loc_name); ?></td>
                        <td><?php echo intval($st->total_diff) > 0 ? '<span style="color:#16a34a">+'.$st->total_diff.'</span>' : (intval($st->total_diff) < 0 ? '<span style="color:#dc2626">'.$st->total_diff.'</span>' : '0'); ?></td>
                        <td><?php echo esc_html(date('d/m/Y H:i', strtotime($st->created_at))); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
    function updateSysQty() {
        const loc = document.querySelector('select[name="location_id"]').value;
        const prod = document.querySelector('select[name="product_id"]').value;
        const sysLabel = document.getElementById('st_sys_qty');
        
        if (!loc || !prod) {
            sysLabel.textContent = '0';
            document.getElementById('st_sys_qty_val').value = 0;
            updateDiff();
            return;
        }
        
        sysLabel.innerHTML = '<i class="hgi-stroke hgi-loading-02" style="animation: spin 1s linear infinite;"></i>';
        
        fetch('<?php echo rest_url('mkv/v1/stock/'); ?>' + prod + '?warehouse_id=' + loc)
            .then(r => r.json())
            .then(data => {
                sysLabel.textContent = data.stock || 0;
                sysLabel.setAttribute('data-val', data.stock || 0);
                updateDiff();
            })
            .catch(() => {
                sysLabel.textContent = 'Lỗi';
            });
    }
    
    function updateDiff() {
        const sys = parseInt(document.getElementById('st_sys_qty').getAttribute('data-val') || 0);
        const actual = parseInt(document.getElementById('st_actual_qty').value || 0);
        const diff = actual - sys;
        const diffLabel = document.getElementById('st_diff_qty');
        
        if (diff > 0) {
            diffLabel.innerHTML = '<span style="color:#16a34a">+' + diff + ' (Dư)</span>';
        } else if (diff < 0) {
            diffLabel.innerHTML = '<span style="color:#dc2626">' + diff + ' (Thiếu)</span>';
        } else {
            diffLabel.innerHTML = '0 (Khớp)';
        }
    }
    </script>
    <style>@keyframes spin { 100% { transform: rotate(360deg); } }</style>

    <?php /* ==== TAB: Logs / Thẻ kho ==== */ elseif ($active_tab === 'logs'): ?>
    <!-- Bộ lọc Thẻ kho -->
    <div class="mkv-card" style="padding:16px; margin-bottom:20px;">
        <form method="get" action="" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
            <input type="hidden" name="page" value="mkv-inventory">
            <input type="hidden" name="tab" value="logs">
            <div style="flex:2; min-width:220px;">
                <label style="font-size:12px; font-weight:600; color:var(--mkv-text-muted); display:block; margin-bottom:4px;">
                    <i class="hgi-stroke hgi-package"></i> <?php echo esc_html(mkv__('Chọn sản phẩm')); ?>
                </label>
                <select name="product_id" class="mkv-select" style="width:100%;">
                    <option value="0"><?php echo esc_html(mkv__('-- Tất cả sản phẩm --')); ?></option>
                    <?php if (!empty($all_products)): foreach ($all_products as $prod_item): ?>
                        <option value="<?php echo $prod_item->ID; ?>" <?php selected($filter_product_id, $prod_item->ID); ?>>
                            <?php echo esc_html($prod_item->post_title); ?> (SKU: <?php echo esc_html(get_post_meta($prod_item->ID, '_mkv_sku', true) ?: '—'); ?>)
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div style="flex:1; min-width:140px;">
                <label style="font-size:12px; font-weight:600; color:var(--mkv-text-muted); display:block; margin-bottom:4px;">
                    <i class="hgi-stroke hgi-store-01"></i> <?php echo esc_html(mkv__('Kho / Chi nhánh')); ?>
                </label>
                <select name="location_id" class="mkv-select" style="width:100%;">
                    <option value="0"><?php echo esc_html(mkv__('-- Tất cả kho --')); ?></option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?php echo $loc->id; ?>" <?php selected($filter_location_id, $loc->id); ?>>
                            <?php echo esc_html($loc->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1; min-width:130px;">
                <label style="font-size:12px; font-weight:600; color:var(--mkv-text-muted); display:block; margin-bottom:4px;">
                    <i class="hgi-stroke hgi-exchange-01"></i> <?php echo esc_html(mkv__('Loại biến động')); ?>
                </label>
                <select name="type" class="mkv-select" style="width:100%;">
                    <option value=""><?php echo esc_html(mkv__('-- Tất cả loại --')); ?></option>
                    <option value="in" <?php selected($filter_type, 'in'); ?>><?php echo esc_html(mkv__('Nhập kho (+)')); ?></option>
                    <option value="out" <?php selected($filter_type, 'out'); ?>><?php echo esc_html(mkv__('Xuất kho (−)')); ?></option>
                    <option value="transfer" <?php selected($filter_type, 'transfer'); ?>><?php echo esc_html(mkv__('Chuyển kho')); ?></option>
                    <option value="stocktake" <?php selected($filter_type, 'stocktake'); ?>><?php echo esc_html(mkv__('Kiểm kho')); ?></option>
                </select>
            </div>
            <div style="min-width:130px;">
                <label style="font-size:12px; font-weight:600; color:var(--mkv-text-muted); display:block; margin-bottom:4px;">
                    <?php echo esc_html(mkv__('Từ ngày')); ?>
                </label>
                <input type="date" name="date_from" value="<?php echo esc_attr($filter_date_from); ?>" class="mkv-input" style="height:38px;">
            </div>
            <div style="min-width:130px;">
                <label style="font-size:12px; font-weight:600; color:var(--mkv-text-muted); display:block; margin-bottom:4px;">
                    <?php echo esc_html(mkv__('Đến ngày')); ?>
                </label>
                <input type="date" name="date_to" value="<?php echo esc_attr($filter_date_to); ?>" class="mkv-input" style="height:38px;">
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="mkv-btn mkv-btn-primary" style="height:38px; border-radius:8px;">
                    <i class="hgi-stroke hgi-filter"></i> <?php echo esc_html(mkv__('Lọc thẻ kho')); ?>
                </button>
                <a href="?page=mkv-inventory&tab=logs" class="mkv-btn mkv-btn-outline" style="height:38px; border-radius:8px;">
                    <i class="hgi-stroke hgi-rotate-left-01"></i> <?php echo esc_html(mkv__('Đặt lại')); ?>
                </a>
            </div>
        </form>
    </div>

    <!-- Thống kê KPI Thẻ kho -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:20px;">
        <?php if ($filter_product_id > 0): ?>
        <div class="mkv-card" style="padding:16px; display:flex; align-items:center; gap:14px; border-left:4px solid var(--mkv-primary);">
            <div style="width:44px; height:44px; border-radius:10px; background:var(--mkv-primary-light); color:var(--mkv-primary); display:flex; align-items:center; justify-content:center; font-size:22px;">
                <i class="hgi-stroke hgi-package"></i>
            </div>
            <div>
                <div style="font-size:12px; font-weight:600; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Tồn kho hiện tại')); ?></div>
                <div style="font-size:22px; font-weight:800; color:var(--mkv-primary);"><?php echo number_format($ledger_stats['current_stock']); ?></div>
            </div>
        </div>
        <?php endif; ?>
        <div class="mkv-card" style="padding:16px; display:flex; align-items:center; gap:14px; border-left:4px solid var(--mkv-green);">
            <div style="width:44px; height:44px; border-radius:10px; background:var(--mkv-green-light); color:var(--mkv-green); display:flex; align-items:center; justify-content:center; font-size:22px;">
                <i class="hgi-stroke hgi-arrow-down-02"></i>
            </div>
            <div>
                <div style="font-size:12px; font-weight:600; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Tổng nhập trong kỳ')); ?></div>
                <div style="font-size:22px; font-weight:800; color:var(--mkv-green);">+<?php echo number_format($ledger_stats['total_in']); ?></div>
            </div>
        </div>
        <div class="mkv-card" style="padding:16px; display:flex; align-items:center; gap:14px; border-left:4px solid var(--mkv-red);">
            <div style="width:44px; height:44px; border-radius:10px; background:var(--mkv-red-light); color:var(--mkv-red); display:flex; align-items:center; justify-content:center; font-size:22px;">
                <i class="hgi-stroke hgi-arrow-up-02"></i>
            </div>
            <div>
                <div style="font-size:12px; font-weight:600; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Tổng xuất trong kỳ')); ?></div>
                <div style="font-size:22px; font-weight:800; color:var(--mkv-red);">-<?php echo number_format($ledger_stats['total_out']); ?></div>
            </div>
        </div>
        <div class="mkv-card" style="padding:16px; display:flex; align-items:center; gap:14px; border-left:4px solid #6366f1;">
            <div style="width:44px; height:44px; border-radius:10px; background:#e0e7ff; color:#4f46e5; display:flex; align-items:center; justify-content:center; font-size:22px;">
                <i class="hgi-stroke hgi-exchange-01"></i>
            </div>
            <div>
                <div style="font-size:12px; font-weight:600; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Số lượt giao dịch')); ?></div>
                <div style="font-size:22px; font-weight:800; color:#1e293b;"><?php echo number_format($ledger_stats['total_trans']); ?></div>
            </div>
        </div>
    </div>

    <!-- Bảng Thẻ kho chi tiết -->
    <div class="mkv-table-wrap">
        <table class="mkv-table" style="min-width: 950px;">
            <thead><tr>
                <th style="width:140px;"><?php echo esc_html(mkv__('Thời gian')); ?></th>
                <th><?php echo esc_html(mkv__('Sản phẩm & SKU')); ?></th>
                <th style="width:120px;"><?php echo esc_html(mkv__('Nghiệp vụ')); ?></th>
                <th style="width:110px; text-align:right;"><?php echo esc_html(mkv__('Biến động')); ?></th>
                <th style="width:160px;"><?php echo esc_html(mkv__('Kho hàng')); ?></th>
                <th><?php echo esc_html(mkv__('Chứng từ / Lý do')); ?></th>
                <th style="width:130px;"><?php echo esc_html(mkv__('Người thực hiện')); ?></th>
            </tr></thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="7" style="text-align:center;padding:36px;color:var(--mkv-text-muted);"><i class="hgi-stroke hgi-file-search" style="font-size:28px; display:block; margin-bottom:8px;"></i><?php echo esc_html(mkv__('Không tìm thấy bản ghi thẻ kho nào phù hợp.')); ?></td></tr>
            <?php else: foreach ($logs as $log): ?>
                <tr>
                    <td style="color:#64748b; font-size:13px;"><?php echo esc_html(date('d/m/Y H:i', strtotime($log->created_at))); ?></td>
                    <td>
                        <strong><?php echo esc_html($log->product_name ?? '#'.$log->product_id); ?></strong>
                        <?php $p_sku = get_post_meta($log->product_id, '_mkv_sku', true); if ($p_sku): ?>
                            <br><small style="color:var(--mkv-text-muted);">SKU: <?php echo esc_html($p_sku); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php
                        $type_map = array(
                            'in'        => array(mkv__('Nhập kho'), 'mkv-badge-green'),
                            'out'       => array(mkv__('Xuất kho'), 'mkv-badge-red'),
                            'transfer'  => array(mkv__('Chuyển kho'), 'mkv-badge-blue'),
                            'stocktake' => array(mkv__('Kiểm kho'), 'mkv-badge-yellow'),
                        );
                        $t = $type_map[$log->type] ?? array($log->type, 'mkv-badge-blue');
                        echo '<span class="mkv-badge '.$t[1].'"><i class="hgi-stroke ' . ($log->type==='in'?'hgi-arrow-down-02':($log->type==='out'?'hgi-arrow-up-02':'hgi-exchange-01')) . '"></i> '.$t[0].'</span>';
                    ?></td>
                    <td style="text-align:right;">
                        <?php if ($log->type === 'in'): ?>
                            <span style="color:var(--mkv-green); font-weight:800; font-size:14px;">+<?php echo intval($log->qty); ?></span>
                        <?php elseif ($log->type === 'out'): ?>
                            <span style="color:var(--mkv-red); font-weight:800; font-size:14px;">−<?php echo intval($log->qty); ?></span>
                        <?php else: ?>
                            <span style="color:var(--mkv-primary); font-weight:700; font-size:14px;"><?php echo intval($log->qty); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo esc_html($log->location_from ?? '—'); ?></strong>
                        <?php if ($log->location_to): ?>
                            <span style="color:var(--mkv-text-muted);">→</span> <strong><?php echo esc_html($log->location_to); ?></strong>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        $note_text = esc_html($log->note ?: '—');
                        // Nếu note chứa mã đơn hàng e.g. #ORD hoặc Đơn hàng
                        echo $note_text;
                        ?>
                    </td>
                    <td style="color:#475569; font-size:13px;"><i class="hgi-stroke hgi-user" style="font-size:13px;"></i> <?php echo esc_html($log->user_name ?? ($log->created_by ? '#'.$log->created_by : 'Hệ thống')); ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

<script>document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Quản lý kho')); ?>');</script>
<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>