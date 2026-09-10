<?php
if (!defined('ABSPATH')) exit;
require_once MKV_DIR . 'includes/views/header-kiotviet.php';
?>

<div class="mkv-page-header">
    <h1 class="mkv-page-title">
        <i class="hgi-stroke hgi-container-truck-01"></i> <?php echo esc_html(mkv__('Quản lý Mua Hàng')); ?>
    </h1>
    <div class="mkv-page-actions">
        <?php if ($current_tab === 'suppliers'): ?>
            <button type="button" class="mkv-btn mkv-btn-primary" onclick="document.getElementById('mkv-modal-sup').style.display='flex'">
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
                <th style="width:140px;"><?php echo esc_html(mkv__('Mã phiếu')); ?></th>
                <th style="width:130px;"><?php echo esc_html(mkv__('Thời gian')); ?></th>
                <th><?php echo esc_html(mkv__('Nhà cung cấp')); ?></th>
                <th><?php echo esc_html(mkv__('Sản phẩm & SL')); ?></th>
                <th><?php echo esc_html(mkv__('Kho nhập')); ?></th>
                <th style="text-align:right;"><?php echo esc_html(mkv__('Tổng cộng')); ?></th>
                <th style="text-align:center; width:160px;"><?php echo esc_html(mkv__('Thanh toán')); ?></th>
                <th style="text-align:center; width:100px;"><?php echo esc_html(mkv__('Thao tác')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($pos)): ?>
                <?php foreach ($pos as $p): 
                    $items = $po_items[$p->id] ?? array();
                    $total_items_qty = 0;
                    $item_summary = '';
                    if (!empty($items)) {
                        foreach ($items as $it) {
                            $total_items_qty += (int)$it->qty;
                        }
                        $first_title = $items[0]->post_title ?: (mkv__('Sản phẩm #') . $items[0]->product_id);
                        if (count($items) === 1) {
                            $item_summary = '<strong style="color:var(--mkv-text-main);">' . esc_html($first_title) . '</strong> <span class="mkv-badge mkv-badge-blue" style="font-size:11px; margin-left:4px; font-weight:600;">x' . number_format($items[0]->qty) . '</span>';
                        } else {
                            $item_summary = '<strong style="color:var(--mkv-text-main);">' . esc_html($first_title) . '</strong> <span style="color:#64748b; font-size:12px;">(+' . (count($items) - 1) . ' SP)</span> <span class="mkv-badge mkv-badge-blue" style="font-size:11px; margin-left:4px; font-weight:600;">Tổng x' . number_format($total_items_qty) . '</span>';
                        }
                    } else {
                        $item_summary = '<span style="color:#94a3b8;">—</span>';
                    }

                    $total_amt = (float)$p->total_amount;
                    $paid_amt = (float)$p->paid_amount;
                    $is_paid_full = ($paid_amt >= $total_amt && $total_amt > 0);
                    $is_unpaid = ($paid_amt <= 0);
                    $remaining_debt = max(0, $total_amt - $paid_amt);
                ?>
                    <tr>
                        <td>
                            <button type="button" class="mkv-po-code-btn" onclick="mkvOpenPoDetail(<?php echo (int)$p->id; ?>)" style="color:var(--mkv-primary); font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:5px;" title="<?php echo esc_attr(mkv__('Bấm để xem chi tiết phiếu nhập')); ?>">
                                <i class="hgi-stroke hgi-invoice-03" style="font-size:15px;"></i> <?php echo esc_html($p->code); ?>
                            </a>
                        </td>
                        <td style="color:#64748b; font-size:12.5px;">
                            <?php echo date('d/m/Y H:i', strtotime($p->created_at)); ?>
                        </td>
                        <td>
                            <div style="font-weight:600; color:var(--mkv-text-main);"><?php echo $p->supplier_name ? esc_html($p->supplier_name) : '<span style="color:#9ca3af">'.esc_html(mkv__('Không rõ')).'</span>'; ?></div>
                            <?php if (!empty($p->supplier_phone)): ?>
                                <div style="color:#94a3b8; font-size:11.5px;"><?php echo esc_html($p->supplier_phone); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item_summary; ?></td>
                        <td>
                            <span style="display:inline-flex; align-items:center; gap:5px; font-size:13px; color:#475569;">
                                <i class="hgi-stroke hgi-store-01" style="color:var(--mkv-primary); font-size:15px;"></i>
                                <?php echo esc_html($p->location_name ?: mkv__('Kho mặc định')); ?>
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <strong style="font-size:13.5px; color:var(--mkv-text-main);"><?php echo number_format($total_amt); ?> ₫</strong>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($is_paid_full): ?>
                                <span class="mkv-badge mkv-badge-green" style="display:inline-flex; align-items:center; gap:4px; font-size:11.5px; padding:3px 8px;">
                                    <i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Đã thanh toán')); ?>
                                </span>
                            <?php elseif ($is_unpaid): ?>
                                <span class="mkv-badge mkv-badge-red" style="display:inline-flex; align-items:center; gap:4px; font-size:11.5px; padding:3px 8px;" title="<?php echo esc_attr(mkv__('Chưa thanh toán cho nhà cung cấp')); ?>">
                                    <i class="hgi-stroke hgi-alert-circle"></i> <?php echo esc_html(mkv__('Còn nợ')); ?> <?php echo number_format($remaining_debt); ?> ₫
                                </span>
                            <?php else: ?>
                                <span class="mkv-badge mkv-badge-yellow" style="display:inline-flex; align-items:center; gap:4px; font-size:11.5px; padding:3px 8px;" title="<?php echo esc_attr('Đã trả: ' . number_format($paid_amt) . ' ₫'); ?>">
                                    <i class="hgi-stroke hgi-money-exchange-01"></i> <?php echo esc_html(mkv__('Nợ')); ?> <?php echo number_format($remaining_debt); ?> ₫
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <button type="button" class="mkv-btn mkv-btn-secondary" onclick="mkvOpenPoDetail(<?php echo (int)$p->id; ?>)" style="padding:4px 10px; font-size:12px; gap:5px; border-radius:6px; margin:0 auto; line-height:1.4;" title="<?php echo esc_attr(mkv__('Xem chi tiết phiếu nhập')); ?>">
                                <i class="hgi-stroke hgi-view" style="font-size:15px; color:var(--mkv-primary);"></i> <?php echo esc_html(mkv__('Chi tiết')); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8"><div class="mkv-empty"><i class="hgi-stroke hgi-file-not-found"></i><p><?php echo esc_html(mkv__('Chưa có phiếu nhập hàng nào.')); ?></p></div></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($total_pages) && $total_pages > 1): ?>
<div class="mkv-pagination">
    <?php
    $base_url = admin_url('admin.php?page=mkv-purchases&tab=list');
    echo paginate_links(array(
        'base'      => add_query_arg('paged', '%#%', $base_url),
        'format'    => '',
        'prev_text' => '<i class="hgi-stroke hgi-arrow-left-01" aria-hidden="true"></i><span class="screen-reader-text">' . esc_html(mkv__('Trang trước')) . '</span>',
        'next_text' => '<i class="hgi-stroke hgi-arrow-right-01" aria-hidden="true"></i><span class="screen-reader-text">' . esc_html(mkv__('Trang sau')) . '</span>',
        'total'     => $total_pages,
        'current'   => $paged,
        'type'      => 'plain',
    ));
    ?>
</div>
<?php endif; ?>

<!-- Modal Chi Tiết Phiếu Nhập Hàng -->
<div class="mkv-modal-overlay" id="mkv-modal-po-detail" role="dialog" aria-modal="true" aria-labelledby="mkv-modal-po-detail-title" style="display:none;">
    <div class="mkv-modal mkv-modal-xl" style="width:900px; max-width:96%; border-radius:14px; overflow:hidden;">
        <div class="mkv-modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:16px 22px;">
            <h3 class="mkv-modal-title" id="mkv-modal-po-detail-title" style="font-size:16px; font-weight:700; color:var(--mkv-text-main); display:flex; align-items:center; gap:8px;">
                <i class="hgi-stroke hgi-invoice-03" style="color:var(--mkv-primary); font-size:20px;"></i>
                <span><?php echo esc_html(mkv__('Chi Tiết Phiếu Nhập Hàng')); ?></span>
                <span id="modal-po-code" style="font-family:monospace; font-size:14px; background:#eff6ff; color:var(--mkv-primary); padding:2px 8px; border-radius:6px; font-weight:700; border:1px solid #bfdbfe;"></span>
                <span id="modal-po-badge"></span>
            </h3>
            <button type="button" class="mkv-modal-close" aria-label="<?php echo esc_attr(mkv__('Đóng')); ?>" onclick="mkvClosePoDetail()">&times;</button>
        </div>
        
        <div class="mkv-modal-body" style="padding:22px; max-height:calc(85vh - 130px); overflow-y:auto;">
            <!-- Loading Indicator -->
            <div id="modal-po-loading" style="text-align:center; padding:50px 20px;">
                <i class="hgi-stroke hgi-loading-03" style="font-size:32px; color:var(--mkv-primary); animation:mkvSpin 1s linear infinite; display:inline-block;"></i>
                <p style="margin-top:12px; color:#64748b; font-size:13.5px;"><?php echo esc_html(mkv__('Đang nạp dữ liệu phiếu nhập...')); ?></p>
            </div>

            <!-- Detail Content -->
            <div id="modal-po-content" style="display:none;">
                <!-- 4 KPI Cards -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)); gap:12px; margin-bottom:20px;">
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px;">
                        <div style="font-size:12px; color:#64748b; font-weight:600; display:flex; align-items:center; gap:5px; margin-bottom:4px;">
                            <i class="hgi-stroke hgi-user" style="color:var(--mkv-primary);"></i> <?php echo esc_html(mkv__('Nhà cung cấp')); ?>
                        </div>
                        <div id="modal-po-supplier" style="font-size:14px; font-weight:700; color:var(--mkv-text-main);"></div>
                        <div id="modal-po-sup-phone" style="font-size:12px; color:#64748b; margin-top:2px;"></div>
                    </div>

                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px;">
                        <div style="font-size:12px; color:#64748b; font-weight:600; display:flex; align-items:center; gap:5px; margin-bottom:4px;">
                            <i class="hgi-stroke hgi-store-01" style="color:#0284c7;"></i> <?php echo esc_html(mkv__('Kho nhập hàng')); ?>
                        </div>
                        <div id="modal-po-location" style="font-size:14px; font-weight:700; color:var(--mkv-text-main);"></div>
                        <div id="modal-po-loc-addr" style="font-size:12px; color:#64748b; margin-top:2px;"></div>
                    </div>

                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px;">
                        <div style="font-size:12px; color:#64748b; font-weight:600; display:flex; align-items:center; gap:5px; margin-bottom:4px;">
                            <i class="hgi-stroke hgi-calendar-01" style="color:#8b5cf6;"></i> <?php echo esc_html(mkv__('Thời gian & Người lập')); ?>
                        </div>
                        <div id="modal-po-time" style="font-size:14px; font-weight:700; color:var(--mkv-text-main);"></div>
                        <div id="modal-po-creator" style="font-size:12px; color:#64748b; margin-top:2px;"></div>
                    </div>

                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px;">
                        <div style="font-size:12px; color:#64748b; font-weight:600; display:flex; align-items:center; gap:5px; margin-bottom:4px;">
                            <i class="hgi-stroke hgi-money-bag-02" style="color:#10b981;"></i> <?php echo esc_html(mkv__('Tổng tiền phiếu')); ?>
                        </div>
                        <div id="modal-po-total" style="font-size:15px; font-weight:800; color:var(--mkv-primary);"></div>
                        <div id="modal-po-debt-sub" style="font-size:12px; margin-top:2px;"></div>
                    </div>
                </div>

                <!-- Products Table -->
                <div style="margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <h4 style="margin:0; font-size:14px; font-weight:700; color:var(--mkv-text-main); display:flex; align-items:center; gap:6px;">
                            <i class="hgi-stroke hgi-package" style="color:var(--mkv-primary);"></i>
                            <?php echo esc_html(mkv__('Danh sách hàng hóa nhập kho')); ?>
                        </h4>
                        <span id="modal-po-item-count" style="font-size:12px; color:#64748b;"></span>
                    </div>

                    <div style="border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
                        <table class="mkv-table" style="margin:0;">
                            <thead>
                                <tr style="background:#f1f5f9;">
                                    <th style="width:40px; text-align:center;">#</th>
                                    <th><?php echo esc_html(mkv__('Mã SKU / Tên sản phẩm')); ?></th>
                                    <th style="width:70px; text-align:center;"><?php echo esc_html(mkv__('ĐVT')); ?></th>
                                    <th style="width:90px; text-align:right;"><?php echo esc_html(mkv__('SL nhập')); ?></th>
                                    <th style="width:120px; text-align:right;"><?php echo esc_html(mkv__('Đơn giá nhập')); ?></th>
                                    <th style="width:130px; text-align:right;"><?php echo esc_html(mkv__('Thành tiền')); ?></th>
                                    <th style="width:120px; text-align:center;"><?php echo esc_html(mkv__('Tồn kho tại kho')); ?></th>
                                </tr>
                            </thead>
                            <tbody id="modal-po-items-body">
                                <!-- Populated dynamically -->
                            </tbody>
                            <tfoot>
                                <tr style="background:#f8fafc; font-weight:700;">
                                    <td colspan="3" style="text-align:right; color:#475569;"><?php echo esc_html(mkv__('Tổng cộng')); ?>:</td>
                                    <td id="modal-po-foot-qty" style="text-align:right; color:var(--mkv-primary);"></td>
                                    <td></td>
                                    <td id="modal-po-foot-total" style="text-align:right; color:var(--mkv-primary); font-size:14px;"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Related System Documents (Sổ Quỹ & Thẻ Kho) -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px;">
                    <!-- Cashbook payments -->
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px;">
                        <h5 style="margin:0 0 10px; font-size:13px; font-weight:700; color:var(--mkv-text-main); display:flex; align-items:center; gap:6px;">
                            <i class="hgi-stroke hgi-money-send-square" style="color:#f59e0b;"></i>
                            <?php echo esc_html(mkv__('Chứng từ thanh toán (Sổ Quỹ)')); ?>
                        </h5>
                        <div id="modal-po-payments-list">
                            <!-- Populated dynamically -->
                        </div>
                    </div>

                    <!-- Inventory Movement Log -->
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px;">
                        <h5 style="margin:0 0 10px; font-size:13px; font-weight:700; color:var(--mkv-text-main); display:flex; align-items:center; gap:6px;">
                            <i class="hgi-stroke hgi-clipboard" style="color:#0284c7;"></i>
                            <?php echo esc_html(mkv__('Biến động thẻ kho')); ?>
                        </h5>
                        <div id="modal-po-logs-list">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>

                <!-- Debt Callout if unpaid -->
                <div id="modal-po-debt-callout" style="display:none; margin-top:16px; background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:12px 16px; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <i class="hgi-stroke hgi-alert-circle" style="color:#ef4444; font-size:20px;"></i>
                        <span style="font-size:13px; color:#991b1b;">
                            <?php echo esc_html(mkv__('Phiếu này còn nợ nhà cung cấp')); ?>: <strong id="modal-debt-alert-val" style="color:#b91c1c;"></strong>
                        </span>
                    </div>
                    <a href="?page=mkv-cashbook" class="mkv-btn mkv-btn-danger" style="text-decoration:none; padding:5px 12px; font-size:12px; border-radius:6px;">
                        <i class="hgi-stroke hgi-money-send-square"></i> <?php echo esc_html(mkv__('Lập phiếu chi trả nợ')); ?>
                    </a>
                </div>

                <!-- Note -->
                <div id="modal-po-note-box" style="display:none; margin-top:14px; font-size:12.5px; color:#64748b; background:#f1f5f9; padding:8px 12px; border-radius:6px;">
                    <strong><?php echo esc_html(mkv__('Ghi chú:')); ?></strong> <span id="modal-po-note-text"></span>
                </div>
            </div>
        </div>

        <div class="mkv-modal-footer" style="padding:14px 22px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <button type="button" class="mkv-btn mkv-btn-secondary" onclick="mkvPrintPoDetail()" style="gap:6px; font-size:13px;">
                    <i class="hgi-stroke hgi-printer"></i> <?php echo esc_html(mkv__('In phiếu nhập')); ?>
                </button>
            </div>
            <div>
                <button type="button" class="mkv-btn mkv-btn-secondary" onclick="mkvClosePoDetail()" style="min-width:80px;"><?php echo esc_html(mkv__('Đóng')); ?></button>
            </div>
        </div>
    </div>
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
<div class="mkv-modal-overlay" id="mkv-modal-sup" role="dialog" aria-modal="true" aria-labelledby="mkv-modal-sup-title" style="display:none;">
    <div class="mkv-modal">
        <div class="mkv-modal-header">
            <h3 class="mkv-modal-title" id="mkv-modal-sup-title"><i class="hgi-stroke hgi-user-add-02" style="color:var(--mkv-primary);"></i> <?php echo esc_html(mkv__('Thêm Nhà cung cấp')); ?></h3>
            <button type="button" class="mkv-modal-close" aria-label="<?php echo esc_attr(mkv__('Đóng')); ?>" onclick="document.getElementById('mkv-modal-sup').style.display='none'">&times;</button>
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
                <button type="submit" class="mkv-btn mkv-btn-primary"><i class="hgi-stroke hgi-floppy-disk"></i> <?php echo esc_html(mkv__('Lưu')); ?></button>
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
            <!-- ERP Process Clarification Banner -->
            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px 16px; margin-bottom:20px; display:flex; gap:12px; align-items:flex-start;">
                <i class="hgi-stroke hgi-invoice-01" style="color:var(--mkv-primary); font-size:22px; flex-shrink:0; margin-top:2px;"></i>
                <div style="font-size:13px; color:#1e3a8a; line-height:1.5;">
                    <strong><?php echo esc_html(mkv__('Quy trình nhập kho chuẩn ERP:')); ?></strong>
                    <?php echo esc_html(mkv__('Khi lưu phiếu, số lượng nhập sẽ')); ?> <strong><?php echo esc_html(mkv__('tự động cộng ngay vào kho')); ?></strong> <?php echo esc_html(mkv__('đã chọn (bất kể bạn thanh toán ngay hay ghi nợ).')); ?>
                    <br>
                    <span style="color:#475569; font-size:12px;">
                        <?php echo esc_html(mkv__('Nếu ghi nợ, việc thanh toán sau này qua mục Sổ Quỹ là nghiệp vụ tài chính (trừ nợ NCC), không cộng thêm tồn kho lần 2.')); ?>
                    </span>
                </div>
            </div>

            <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" id="mkv-create-po-form">
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
                        <label class="mkv-label"><?php echo esc_html(mkv__('Sản phẩm nhập')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <select name="product_id" id="po-input-product" class="mkv-input" required>
                            <option value=""><?php echo esc_html(mkv__('— Chọn một sản phẩm —')); ?></option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?php echo $p->ID; ?>"><?php echo esc_html($p->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mkv-form-row">
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Kho nhận hàng')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <select name="location_id" id="po-input-location" class="mkv-input" required>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?php echo $loc->id; ?>"><?php echo esc_html($loc->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Số lượng nhập')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <input type="number" name="qty" id="po-input-qty" class="mkv-input" required min="1" value="1" placeholder="0">
                    </div>
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Đơn giá nhập (VNĐ)')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <input type="text" name="price" id="po-input-price" class="mkv-input mkv-currency-input" required inputmode="numeric" value="0" placeholder="0">
                    </div>
                </div>

                <!-- Real-time Product & Warehouse Stock Preview Box -->
                <div id="po-stock-preview-box" style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px 14px; margin-bottom:16px; font-size:13px; color:#475569;">
                    <span style="display:inline-flex; align-items:center; gap:5px;">
                        <i class="hgi-stroke hgi-package" style="color:var(--mkv-primary);"></i>
                        <?php echo esc_html(mkv__('Tồn kho hiện tại tại')); ?> <strong id="preview-loc-name">...</strong>:
                        <span class="mkv-badge mkv-badge-blue" id="preview-stock-qty" style="font-size:12px; font-weight:700;">0</span>
                    </span>
                    <span style="margin-left:14px; color:#64748b;">
                        <?php echo esc_html(mkv__('Dự kiến sau khi nhập:')); ?> <strong id="preview-after-stock" style="color:var(--mkv-green);">0</strong>
                    </span>
                </div>

                <div class="mkv-form-row">
                    <div class="mkv-form-group" style="flex:1;">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Tổng giá trị phiếu (VNĐ)')); ?></label>
                        <div id="po-calc-total" style="font-size:18px; font-weight:800; color:var(--mkv-primary); padding:8px 0;">0 ₫</div>
                    </div>
                    <div class="mkv-form-group" style="flex:1.5;">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Số tiền trả ngay cho NCC (VNĐ)')); ?></label>
                        <input type="text" name="paid_amount" id="po-input-paid" class="mkv-input mkv-currency-input" inputmode="numeric" min="0" value="0" placeholder="0">
                        <p style="font-size:12px; color:#6b7280; margin-top:4px;">
                            <?php echo esc_html(mkv__('Nếu trả nhỏ hơn Tổng cộng, phần còn lại sẽ tự động ghi nợ nhà cung cấp.')); ?>
                        </p>
                    </div>
                </div>

                <div style="display:flex; gap:8px; justify-content:flex-end; border-top:1px solid var(--mkv-border-light); padding-top:16px; margin-top:10px;">
                    <a href="?page=mkv-purchases&tab=list" class="mkv-btn mkv-btn-secondary"><?php echo esc_html(mkv__('Hủy')); ?></a>
                    <button type="submit" class="mkv-btn mkv-btn-primary">
                        <i class="hgi-stroke hgi-package"></i> <?php echo esc_html(mkv__('Hoàn thành & Nhập kho')); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.mkvStockMatrix = <?php echo json_encode($stock_matrix ?? array()); ?>;
window.mkvProductsMeta = <?php echo json_encode($products_meta ?? array()); ?>;

function mkvUpdatePoPreview() {
    var prodSelect = document.getElementById('po-input-product');
    var locSelect = document.getElementById('po-input-location');
    var qtyInput = document.getElementById('po-input-qty');
    var priceInput = document.getElementById('po-input-price');
    var previewBox = document.getElementById('po-stock-preview-box');

    if (!prodSelect || !locSelect) return;

    var prodId = prodSelect.value;
    var locId = locSelect.value;
    var qty = parseInt(qtyInput ? qtyInput.value : 0) || 0;

    // Get actual raw numeric price
    var hiddenPrice = priceInput ? priceInput.parentNode.querySelector('input[type="hidden"][name="price"]') : null;
    var rawPrice = hiddenPrice ? parseFloat(hiddenPrice.value) : (parseFloat(priceInput ? priceInput.value.replace(/[^\d]/g, '') : 0) || 0);

    var total = Math.max(0, qty * rawPrice);
    var totalDisplay = document.getElementById('po-calc-total');
    if (totalDisplay) {
        totalDisplay.textContent = total.toLocaleString('vi-VN') + ' ₫';
    }

    if (prodId && locId && window.mkvStockMatrix) {
        var currentStock = (window.mkvStockMatrix[prodId] && window.mkvStockMatrix[prodId][locId]) ? window.mkvStockMatrix[prodId][locId] : 0;
        var locName = locSelect.options[locSelect.selectedIndex] ? locSelect.options[locSelect.selectedIndex].text : '';
        
        document.getElementById('preview-loc-name').textContent = locName;
        document.getElementById('preview-stock-qty').textContent = currentStock.toLocaleString('vi-VN');
        document.getElementById('preview-after-stock').textContent = (currentStock + qty).toLocaleString('vi-VN');
        previewBox.style.display = 'block';
    } else if (previewBox) {
        previewBox.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var prodSelect = document.getElementById('po-input-product');
    var locSelect = document.getElementById('po-input-location');
    var qtyInput = document.getElementById('po-input-qty');
    var priceInput = document.getElementById('po-input-price');

    if (prodSelect) {
        prodSelect.addEventListener('change', function() {
            var prodId = this.value;
            if (prodId && window.mkvProductsMeta && window.mkvProductsMeta[prodId]) {
                var meta = window.mkvProductsMeta[prodId];
                if (meta.price_in > 0 && priceInput) {
                    var hiddenPrice = priceInput.parentNode.querySelector('input[type="hidden"][name="price"]');
                    if (hiddenPrice) hiddenPrice.value = meta.price_in;
                    priceInput.value = meta.price_in.toLocaleString('vi-VN');
                }
            }
            mkvUpdatePoPreview();
        });
    }

    if (locSelect) locSelect.addEventListener('change', mkvUpdatePoPreview);
    if (qtyInput) qtyInput.addEventListener('input', mkvUpdatePoPreview);
    if (priceInput) {
        priceInput.addEventListener('input', mkvUpdatePoPreview);
        priceInput.addEventListener('blur', mkvUpdatePoPreview);
    }
});
</script>
<?php endif; ?>

<script>
document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Nhập hàng')); ?>');

var mkvPoNonce = '<?php echo wp_create_nonce("mkv_po_detail_nonce"); ?>';

// Active PO data for printing
var currentPoData = null;

function mkvOpenPoDetail(poId) {
    var modal = document.getElementById('mkv-modal-po-detail');
    if (!modal) return;

    modal.style.display = 'flex';
    document.getElementById('modal-po-loading').style.display = 'block';
    document.getElementById('modal-po-content').style.display = 'none';
    document.getElementById('modal-po-code').textContent = '...';
    document.getElementById('modal-po-badge').innerHTML = '';

    fetch(ajaxurl + '?action=mkv_get_po_detail&po_id=' + encodeURIComponent(poId) + '&nonce=' + encodeURIComponent(mkvPoNonce))
        .then(function(res) { return res.json(); })
        .then(function(response) {
            if (!response || !response.success || !response.data) {
                alert(response && response.data && response.data.message ? response.data.message : 'Không thể tải chi tiết phiếu.');
                mkvClosePoDetail();
                return;
            }

            var data = response.data;
            currentPoData = data;
            var po = data.po;
            var items = data.items || [];
            var payments = data.payments || [];
            var logs = data.logs || [];

            // Header info
            document.getElementById('modal-po-code').textContent = '#' + po.code;
            
            var total = parseFloat(po.total_amount) || 0;
            var paid = parseFloat(po.paid_amount) || 0;
            var debt = Math.max(0, total - paid);

            var badgeHtml = '';
            if (paid >= total && total > 0) {
                badgeHtml = '<span class="mkv-badge mkv-badge-green" style="font-size:11.5px; display:inline-flex; align-items:center; gap:4px;"><i class="hgi-stroke hgi-checkmark-circle-02"></i> Đã thanh toán đủ</span>';
            } else if (paid <= 0) {
                badgeHtml = '<span class="mkv-badge mkv-badge-red" style="font-size:11.5px; display:inline-flex; align-items:center; gap:4px;"><i class="hgi-stroke hgi-alert-circle"></i> Chưa thanh toán</span>';
            } else {
                badgeHtml = '<span class="mkv-badge mkv-badge-yellow" style="font-size:11.5px; display:inline-flex; align-items:center; gap:4px;"><i class="hgi-stroke hgi-money-exchange-01"></i> Thanh toán 1 phần</span>';
            }
            document.getElementById('modal-po-badge').innerHTML = badgeHtml;

            // KPI Cards
            document.getElementById('modal-po-supplier').textContent = po.supplier_name || 'Không rõ';
            document.getElementById('modal-po-sup-phone').textContent = po.supplier_phone ? 'SĐT: ' + po.supplier_phone : '';

            document.getElementById('modal-po-location').textContent = po.location_name || 'Kho mặc định';
            document.getElementById('modal-po-loc-addr').textContent = po.location_address || '';

            document.getElementById('modal-po-time').textContent = po.created_at ? mkvFormatDate(po.created_at) : '—';
            document.getElementById('modal-po-creator').textContent = po.creator_name ? 'Người lập: ' + po.creator_name : '';

            document.getElementById('modal-po-total').textContent = total.toLocaleString('vi-VN') + ' ₫';
            if (debt > 0) {
                document.getElementById('modal-po-debt-sub').innerHTML = '<span style="color:#dc2626; font-weight:600;">Còn nợ: ' + debt.toLocaleString('vi-VN') + ' ₫</span>';
            } else {
                document.getElementById('modal-po-debt-sub').innerHTML = '<span style="color:#16a34a; font-weight:600;">Đã trả đủ tiền</span>';
            }

            // Items Table
            var tbody = document.getElementById('modal-po-items-body');
            tbody.innerHTML = '';
            var sumQty = 0;
            var sumTotal = 0;

            items.forEach(function(item, idx) {
                var qty = parseInt(item.qty, 10) || 0;
                var price = parseFloat(item.price) || 0;
                var subtotal = parseFloat(item.subtotal) || (qty * price);
                var currentStock = parseInt(item.current_stock, 10) || 0;
                sumQty += qty;
                sumTotal += subtotal;

                var tr = document.createElement('tr');
                tr.innerHTML = 
                    '<td style="text-align:center; color:#94a3b8; font-size:12px;">' + (idx + 1) + '</td>' +
                    '<td>' +
                        '<div style="font-weight:700; color:var(--mkv-text-main);">' + mkvEscapeHtml(item.post_title || ('Sản phẩm #' + item.product_id)) + '</div>' +
                        (item.sku ? '<small style="color:#64748b;">SKU: ' + mkvEscapeHtml(item.sku) + '</small>' : '') +
                    '</td>' +
                    '<td style="text-align:center; color:#64748b;">' + mkvEscapeHtml(item.unit || 'Cái') + '</td>' +
                    '<td style="text-align:right;"><strong style="color:var(--mkv-primary); font-size:13.5px;">' + qty.toLocaleString('vi-VN') + '</strong></td>' +
                    '<td style="text-align:right;">' + price.toLocaleString('vi-VN') + ' ₫</td>' +
                    '<td style="text-align:right;"><strong>' + subtotal.toLocaleString('vi-VN') + ' ₫</strong></td>' +
                    '<td style="text-align:center;"><span class="mkv-badge mkv-badge-blue" style="font-size:11px; padding:2px 8px;" title="Tồn kho thực tế hiện tại tại kho này">' + currentStock.toLocaleString('vi-VN') + '</span></td>';
                tbody.appendChild(tr);
            });

            document.getElementById('modal-po-item-count').textContent = items.length + ' mặt hàng';
            document.getElementById('modal-po-foot-qty').textContent = sumQty.toLocaleString('vi-VN');
            document.getElementById('modal-po-foot-total').textContent = sumTotal.toLocaleString('vi-VN') + ' ₫';

            // Cashbook Payments list
            var payList = document.getElementById('modal-po-payments-list');
            if (payments.length > 0) {
                var payHtml = '<ul style="margin:0; padding:0; list-style:none; display:flex; flex-direction:column; gap:6px;">';
                payments.forEach(function(pm) {
                    var pmAmt = parseFloat(pm.amount) || 0;
                    var methodText = pm.method === 'bank' ? 'Chuyển khoản / NH' : 'Tiền mặt';
                    payHtml += '<li style="background:#fff; border:1px solid #e2e8f0; border-radius:6px; padding:8px 10px; font-size:12.5px; display:flex; justify-content:space-between; align-items:center;">' +
                        '<div>' +
                            '<strong>Chi ' + pmAmt.toLocaleString('vi-VN') + ' ₫</strong> (' + methodText + ')' +
                            '<div style="color:#64748b; font-size:11px;">' + mkvFormatDate(pm.created_at) + (pm.creator_name ? ' • ' + pm.creator_name : '') + '</div>' +
                        '</div>' +
                        '<span class="mkv-badge mkv-badge-green" style="font-size:10.5px;">Đã chi</span>' +
                    '</li>';
                });
                payHtml += '</ul>';
                payList.innerHTML = payHtml;
            } else {
                payList.innerHTML = '<div style="font-size:12.5px; color:#94a3b8; font-style:italic;">Chưa có khoản chi nào ghi nhận trong Sổ Quỹ cho phiếu này.</div>';
            }

            // Inventory logs list
            var logList = document.getElementById('modal-po-logs-list');
            if (logs.length > 0) {
                var logHtml = '<ul style="margin:0; padding:0; list-style:none; display:flex; flex-direction:column; gap:6px;">';
                logs.forEach(function(lg) {
                    var lQty = parseInt(lg.qty, 10) || 0;
                    logHtml += '<li style="background:#fff; border:1px solid #e2e8f0; border-radius:6px; padding:8px 10px; font-size:12.5px; display:flex; justify-content:space-between; align-items:center;">' +
                        '<div>' +
                            '<strong>+ ' + lQty.toLocaleString('vi-VN') + ' sản phẩm</strong> vào ' + (lg.location_name || 'Kho') +
                            '<div style="color:#64748b; font-size:11px;">' + mkvFormatDate(lg.created_at) + (lg.creator_name ? ' • ' + lg.creator_name : '') + '</div>' +
                        '</div>' +
                        '<span class="mkv-badge mkv-badge-blue" style="font-size:10.5px;">Nhập kho</span>' +
                    '</li>';
                });
                logHtml += '</ul>';
                logList.innerHTML = logHtml;
            } else {
                logList.innerHTML = '<div style="font-size:12.5px; color:#94a3b8; font-style:italic;">Đã tự động tăng tồn kho theo phiếu nhập.</div>';
            }

            // Debt callout
            var debtBox = document.getElementById('modal-po-debt-callout');
            if (debt > 0) {
                document.getElementById('modal-debt-alert-val').textContent = debt.toLocaleString('vi-VN') + ' ₫';
                debtBox.style.display = 'flex';
            } else {
                debtBox.style.display = 'none';
            }

            // Note
            var noteBox = document.getElementById('modal-po-note-box');
            if (po.note && po.note.trim() !== '') {
                document.getElementById('modal-po-note-text').textContent = po.note;
                noteBox.style.display = 'block';
            } else {
                noteBox.style.display = 'none';
            }

            document.getElementById('modal-po-loading').style.display = 'none';
            document.getElementById('modal-po-content').style.display = 'block';
        })
        .catch(function(err) {
            console.error('Error fetching PO detail:', err);
            alert('Có lỗi xảy ra khi kết nối máy chủ.');
            mkvClosePoDetail();
        });
}

function mkvClosePoDetail() {
    var modal = document.getElementById('mkv-modal-po-detail');
    if (modal) modal.style.display = 'none';
}

function mkvFormatDate(dtStr) {
    if (!dtStr) return '';
    var d = new Date(dtStr.replace(/-/g, '/'));
    if (isNaN(d.getTime())) return dtStr;
    var day = ('0' + d.getDate()).slice(-2);
    var month = ('0' + (d.getMonth() + 1)).slice(-2);
    var year = d.getFullYear();
    var hours = ('0' + d.getHours()).slice(-2);
    var mins = ('0' + d.getMinutes()).slice(-2);
    return day + '/' + month + '/' + year + ' ' + hours + ':' + mins;
}

function mkvEscapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function mkvPrintPoDetail() {
    if (!currentPoData || !currentPoData.po) {
        alert('Chưa có dữ liệu để in.');
        return;
    }
    var po = currentPoData.po;
    var items = currentPoData.items || [];
    var total = parseFloat(po.total_amount) || 0;
    var paid = parseFloat(po.paid_amount) || 0;
    var debt = Math.max(0, total - paid);

    var printWin = window.open('', '_blank', 'width=800,height=900');
    if (!printWin) {
        alert('Trình duyệt đã chặn cửa sổ in (Popup). Vui lòng cho phép popup để in.');
        return;
    }

    var itemsRows = '';
    items.forEach(function(it, idx) {
        var qty = parseInt(it.qty, 10) || 0;
        var price = parseFloat(it.price) || 0;
        var subtotal = parseFloat(it.subtotal) || (qty * price);
        itemsRows += '<tr>' +
            '<td style="text-align:center; padding:6px 8px; border-bottom:1px solid #ddd;">' + (idx + 1) + '</td>' +
            '<td style="padding:6px 8px; border-bottom:1px solid #ddd;"><strong>' + (it.post_title || '') + '</strong>' + (it.sku ? '<br><small>SKU: ' + it.sku + '</small>' : '') + '</td>' +
            '<td style="text-align:center; padding:6px 8px; border-bottom:1px solid #ddd;">' + (it.unit || 'Cái') + '</td>' +
            '<td style="text-align:right; padding:6px 8px; border-bottom:1px solid #ddd;">' + qty.toLocaleString('vi-VN') + '</td>' +
            '<td style="text-align:right; padding:6px 8px; border-bottom:1px solid #ddd;">' + price.toLocaleString('vi-VN') + ' ₫</td>' +
            '<td style="text-align:right; padding:6px 8px; border-bottom:1px solid #ddd;"><strong>' + subtotal.toLocaleString('vi-VN') + ' ₫</strong></td>' +
        '</tr>';
    });

    var html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Phiếu Nhập Hàng ' + po.code + '</title>' +
        '<style>' +
        'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; font-size: 13px; color: #111; margin: 30px; line-height: 1.4; }' +
        '.header { display: flex; justify-content: space-between; border-bottom: 2px solid #0056b3; padding-bottom: 12px; margin-bottom: 20px; }' +
        '.title { font-size: 20px; font-weight: bold; color: #0056b3; margin: 0; }' +
        '.meta-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }' +
        '.meta-table td { padding: 4px 6px; }' +
        '.items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }' +
        '.items-table th { background: #f0f4f8; text-align: left; padding: 8px; border-top: 1px solid #ccc; border-bottom: 2px solid #0056b3; }' +
        '.summary { float: right; width: 280px; margin-bottom: 30px; }' +
        '.summary td { padding: 4px 0; }' +
        '.footer-sign { clear: both; display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }' +
        '.sign-box { width: 180px; }' +
        '</style></head><body>' +
        '<div class="header">' +
            '<div><h1 class="title">PHIẾU NHẬP HÀNG</h1><div style="font-size:12px; color:#666; margin-top:4px;">Mini KiotViet Retail Management</div></div>' +
            '<div style="text-align:right;"><strong>Mã: ' + po.code + '</strong><br><small>Ngày: ' + mkvFormatDate(po.created_at) + '</small></div>' +
        '</div>' +
        '<table class="meta-table">' +
            '<tr><td style="width:120px; color:#666;">Nhà cung cấp:</td><td><strong>' + (po.supplier_name || 'Không rõ') + '</strong> ' + (po.supplier_phone ? '(' + po.supplier_phone + ')' : '') + '</td></tr>' +
            '<tr><td style="color:#666;">Kho nhập hàng:</td><td><strong>' + (po.location_name || 'Kho mặc định') + '</strong> ' + (po.location_address ? '- ' + po.location_address : '') + '</td></tr>' +
            '<tr><td style="color:#666;">Người lập phiếu:</td><td>' + (po.creator_name || 'Admin') + '</td></tr>' +
        '</table>' +
        '<table class="items-table">' +
            '<thead><tr>' +
                '<th style="width:40px; text-align:center;">STT</th>' +
                '<th>Tên hàng hóa</th>' +
                '<th style="width:60px; text-align:center;">ĐVT</th>' +
                '<th style="width:80px; text-align:right;">SL</th>' +
                '<th style="width:110px; text-align:right;">Đơn giá</th>' +
                '<th style="width:120px; text-align:right;">Thành tiền</th>' +
            '</tr></thead>' +
            '<tbody>' + itemsRows + '</tbody>' +
        '</table>' +
        '<div class="summary"><table style="width:100%;">' +
            '<tr><td>Tổng tiền hàng:</td><td style="text-align:right; font-weight:bold;">' + total.toLocaleString('vi-VN') + ' ₫</td></tr>' +
            '<tr><td>Đã thanh toán:</td><td style="text-align:right;">' + paid.toLocaleString('vi-VN') + ' ₫</td></tr>' +
            '<tr style="font-size:14px; color:#d9534f;"><td>Còn nợ NCC:</td><td style="text-align:right; font-weight:bold;">' + debt.toLocaleString('vi-VN') + ' ₫</td></tr>' +
        '</table></div>' +
        '<div class="footer-sign">' +
            '<div class="sign-box"><strong>Người lập phiếu</strong><br><br><br><br>(' + (po.creator_name || 'Ký ghi rõ họ tên') + ')</div>' +
            '<div class="sign-box"><strong>Thủ kho nhận</strong><br><br><br><br>(Ký ghi rõ họ tên)</div>' +
            '<div class="sign-box"><strong>Đại diện NCC</strong><br><br><br><br>(Ký ghi rõ họ tên)</div>' +
        '</div>' +
        '</body></html>';

    printWin.document.open();
    printWin.document.write(html);
    printWin.document.close();
    printWin.focus();
    setTimeout(function() {
        printWin.print();
    }, 400);
}

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    var poModal = document.getElementById('mkv-modal-po-detail');
    if (e.target === poModal) {
        mkvClosePoDetail();
    }
});
</script>

<style>
@keyframes mkvSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.mkv-po-code-btn:hover {
    text-decoration: underline !important;
}
</style>

<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>
