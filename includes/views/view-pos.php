<?php if (!defined('ABSPATH')) exit; 
$vat_rate   = (float) get_option('mkv_store_vat', 0);
$store_name = get_option('mkv_store_name', 'Mini KiotViet');
$store_addr = get_option('mkv_store_address', '');
$store_tel  = get_option('mkv_store_phone', '');
$print_hdr  = get_option('mkv_print_header', '<strong>' . esc_html($store_name) . '</strong><br>' . esc_html($store_addr) . '<br>Tel: ' . esc_html($store_tel));
$print_ftr  = get_option('mkv_print_footer', '<em>Cảm ơn quý khách! Hẹn gặp lại.</em>');
$bank_id    = get_option('mkv_bank_id', '');
$bank_acc   = get_option('mkv_bank_account', '');
$bank_name  = get_option('mkv_bank_name', '');
$enable_pos_qr = (int) get_option('mkv_enable_pos_qr', 1);
$enable_receipt_qr = (int) get_option('mkv_receipt_enable_qr', 1);
?>


<div class="mkv-pos-container" style="padding-bottom: 60px;">
    <div class="mkv-pos-header">
        <div style="display:flex; align-items:center; gap: 16px;">
            <a href="javascript:history.back()" class="button" style="display:inline-flex; align-items:center; gap:4px; padding:4px 12px; border-radius:6px;">
                <i class="hgi-stroke hgi-arrow-left-01"></i> <?php echo esc_html(mkv__('Quay lại')); ?>
            </a>
            <h1 style="margin:0; font-size:22px; font-weight:700; color:#1e293b;"><i class="hgi-stroke hgi-shopping-cart-01" style="color:var(--mkv-primary);"></i> <?php echo esc_html(mkv__('Bán Hàng (POS)')); ?></h1>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="window.mkvOpenAIChat && window.mkvOpenAIChat()" class="button" style="padding: 6px 16px; border-radius: 8px; font-weight: 600; color: #8b5cf6; border-color: #8b5cf6;"><i class="hgi-stroke hgi-ai-chat-02"></i> <?php echo esc_html(mkv__('Trợ lý AI')); ?></button>
            <a href="<?php echo admin_url('admin.php?page=mkv-orders'); ?>" class="button button-primary" style="padding: 6px 16px; border-radius: 8px; font-weight: 600;"><i class="hgi-stroke hgi-invoice-01"></i> <?php echo esc_html(mkv__('Danh sách đơn hàng')); ?></a>
        </div>
    </div>

    <?php if (isset($_GET['created'])): ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html(mkv__('Đơn hàng đã được thanh toán và lưu thành công!')); ?></p></div>
    <?php endif; ?>

    <div class="mkv-pos-layout" style="align-items:start;">

        <!-- Cột trái: Tìm kiếm & Danh sách sản phẩm -->
        <div class="mkv-card" style="padding: 24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 style="margin:0; padding:0; border:none; display:flex; align-items:center; gap:8px; font-size:16px;">
                    <i class="hgi-stroke hgi-package"></i> <?php echo esc_html(mkv__('Chọn Sản Phẩm')); ?>
                </h3>
                <span style="font-size:13px; color:#64748b; font-weight:500;"><?php echo esc_html(mkv__('Tổng cộng: ')); ?><strong style="color:#1e293b;"><?php echo count($products); ?></strong> <?php echo esc_html(mkv__(' mặt hàng')); ?></span>
            </div>

            <div id="pos-search-box" style="margin-bottom:24px;">
                <div style="position:relative;">
                    <i class="hgi-stroke hgi-search-01" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:18px;"></i>
                    <input type="text" id="pos-search" class="mkv-input" placeholder="<?php echo esc_attr(mkv__('Tìm theo tên, SKU, mã vạch...')); ?>" 
                           style="width:100%; font-size:15px; padding:12px 16px 12px 48px !important; border-radius:10px; border:1px solid #e2e8f0; outline:none; box-shadow:none;" onkeyup="filterProducts(this.value)">
                </div>
            </div>

            <div id="pos-product-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(170px, 1fr)); gap:20px; max-height:calc(100vh - 240px); overflow-y:auto; padding:4px;">
                <?php foreach ($products as $p):
                    $price   = (float) get_post_meta($p->ID, '_mkv_price_out', true);
                    $stock   = (int) get_post_meta($p->ID, '_mkv_stock', true);
                    $sku     = get_post_meta($p->ID, '_mkv_sku', true);
                    $barcode = get_post_meta($p->ID, '_mkv_barcode', true);
                    $search_text = strtolower($p->post_title . ' ' . $sku . ' ' . $barcode);
                ?>
                <div class="pos-product-item <?php echo $stock <= 0 ? 'out-of-stock' : ''; ?>"
                    style="padding: 16px; border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; background: #fff;"
                    data-id="<?php echo $p->ID; ?>"
                    data-name="<?php echo esc_attr($p->post_title); ?>"
                    data-price="<?php echo esc_attr($price); ?>"
                    data-stock="<?php echo $stock; ?>"
                    data-search="<?php echo esc_attr($search_text); ?>"
                    onclick="<?php echo $stock > 0 ? 'addToCart(this)' : 'mkvToast(\'' . esc_js(mkv__('Sản phẩm đã hết hàng!')) . '\', \'warning\')'; ?>">
                    <?php
                    $img_url = '';
                    if (has_post_thumbnail($p->ID)) {
                        $img_url = get_the_post_thumbnail_url($p->ID, array(80, 80));
                    } else {
                        preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $p->post_content, $matches);
                        if (!empty($matches[1])) {
                            $img_url = $matches[1];
                        }
                    }
                    ?>
                    <?php if ($img_url): ?>
                        <div style="position:relative; width:100%; padding-top:100%; border-radius:8px; overflow:hidden; margin-bottom:10px; background:#f8fafc;">
                            <img src="<?php echo esc_url($img_url); ?>" alt="" style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover;">
                        </div>
                    <?php else: ?>
                        <div style="position:relative; width:100%; padding-top:100%; border-radius:8px; overflow:hidden; margin-bottom:10px; background:#f1f5f9; display:flex; align-items:center; justify-content:center;">
                            <i class="hgi-stroke hgi-image-02" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); font-size:32px; color:#cbd5e1;"></i>
                        </div>
                    <?php endif; ?>
                    <strong style="font-size:14px; line-height:1.3; height:36px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; white-space:normal; margin-bottom:6px;"><?php echo esc_html($p->post_title); ?></strong>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:15px; color:var(--mkv-primary); font-weight:700;"><?php echo number_format($price, 0, ',', '.'); ?> ₫</span>
                        <?php if ($stock <= 0): ?>
                            <small style="color:var(--mkv-red); background:#fef2f2; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:600;"><?php echo esc_html(mkv__('Hết')); ?></small>
                        <?php else: ?>
                            <small style="color:#64748b; background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:600;"><?php echo $stock; ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cột phải: Giỏ hàng & Thanh toán -->
        <div class="mkv-card" style="position:sticky; top:20px; padding:24px; max-height:calc(100vh - 40px); overflow-y:auto;">
            <h3 style="margin-top:0;"><i class="hgi-stroke hgi-shopping-basket-01"></i> <?php echo esc_html(mkv__('Đơn Hàng Mới')); ?></h3>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="pos-form">
                <input type="hidden" name="action" value="mkv_create_order">
                <?php wp_nonce_field('mkv_create_order_nonce', '_wpnonce'); ?>

                <!-- Chọn kho bán -->
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px; font-weight:600; color:var(--mkv-text-muted); text-transform:uppercase;"><?php echo esc_html(mkv__('Kho xuất bán')); ?></label>
                    <select name="location_id" id="pos-warehouse-select" class="mkv-select" style="width:100%; margin-top:4px;">
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo $loc->id; ?>"><?php echo esc_html($loc->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Chọn khách hàng -->
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px; font-weight:600; color:var(--mkv-text-muted); text-transform:uppercase;"><?php echo esc_html(mkv__('Khách hàng')); ?></label>
                    <select name="customer_id" id="pos-customer-select" class="mkv-select" style="width:100%; margin-top:4px;" onchange="onCustomerChange(this)">
                        <option value="0" data-points="0"><?php echo esc_html(mkv__('— Khách lẻ (Không tích điểm) —')); ?></option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?php echo $c->id; ?>" data-points="<?php echo (int)$c->points; ?>">
                                <?php echo esc_html($c->name . ($c->phone ? ' - ' . $c->phone : '') . ' (' . (int)$c->points . mkv__(' điểm)')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Khung trừ điểm (hiển thị khi khách có điểm) -->
                <div id="pos-points-box" style="display:none; background:#f8fafc; padding:12px; border-radius:10px; margin-bottom:16px; border:1px solid #e2e8f0;">
                    <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:8px;">
                        <span style="color:#475569;"><?php echo esc_html(mkv__('Điểm hiện có: ')); ?><strong id="pos-cust-points" style="color:var(--mkv-green);">0</strong><?php echo esc_html(mkv__(' pts')); ?></span>
                        <span style="color:#475569;"><?php echo esc_html(mkv__('Giảm tối đa: ')); ?><strong id="pos-max-points-val">0</strong> ₫</span>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="number" name="points_used" class="mkv-input" id="pos-points-used" value="0" min="0" placeholder="<?php echo esc_attr(mkv__('Số điểm trừ')); ?>" style="flex:1;" oninput="renderCart()">
                        <button type="button" class="mkv-btn mkv-btn-outline" style="padding:6px 12px; font-size:13px;" onclick="useAllPoints()"><?php echo esc_html(mkv__('Dùng hết')); ?></button>
                    </div>
                    <div style="font-size: 11px; color: var(--mkv-text-muted); margin-top: 4px;">
                        <?php echo esc_html(mkv__('Tỷ giá hiện tại: 1 điểm = ' . number_format(get_option('mkv_point_value', 1), 0, ',', '.') . ' ₫')); ?>
                    </div>
                </div>

                <!-- Danh sách món trong giỏ -->
                <div id="cart-container" style="min-height:120px; max-height:22vh; overflow-y:auto; border:1px solid #e2e8f0; border-radius:12px; padding:12px; margin-bottom:16px;">
                    <p id="cart-empty" style="color:#94a3b8; text-align:center; padding:20px 0; margin:0;">
                        <i class="hgi-stroke hgi-shopping-cart-01" style="font-size:32px; display:block; margin-bottom:8px; opacity:.4;"></i>
                        <?php echo esc_html(mkv__('Chưa chọn sản phẩm')); ?>
                    </p>
                    <div id="cart-items"></div>
                </div>

                <!-- Tuỳ chọn Giao hàng (Nếu có bật trong cài đặt) -->
                <?php if (get_option('mkv_shipping_enable', 0) == 1): ?>
                <div style="margin-bottom:16px; background:#f8fafc; padding:12px; border-radius:10px; border:1px solid #e2e8f0;">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:600; cursor:pointer; color:#1e293b; font-size:13px;">
                        <input type="checkbox" name="is_enable_shipping" value="1" id="pos-enable-shipping" onchange="toggleShippingFields()">
                        <i class="hgi-stroke hgi-truck-01" style="color:var(--mkv-primary);"></i> <?php echo esc_html(mkv__('Giao hàng tận nơi')); ?>
                    </label>
                    <div id="pos-shipping-fields" style="display:none; margin-top:12px; border-top:1px dashed #cbd5e1; padding-top:12px;">
                        <div style="margin-bottom:8px;">
                            <label style="font-size:12px; color:var(--mkv-text-muted); display:block; margin-bottom:4px;"><?php echo esc_html(mkv__('Phí giao hàng (Thu khách)')); ?></label>
                            <input type="number" name="shipping_fee" id="pos-shipping-fee" class="mkv-input" style="width:100%;" value="0" min="0" oninput="renderCart()">
                        </div>
                        <div style="margin-bottom:8px;">
                            <label style="font-size:12px; color:var(--mkv-text-muted); display:block; margin-bottom:4px;"><?php echo esc_html(mkv__('Địa chỉ nhận hàng chi tiết (Kèm Tỉnh/Thành)')); ?></label>
                            <textarea name="customer_address" id="pos-customer-address" class="mkv-textarea" style="width:100%;" rows="2" placeholder="Số nhà, Đường, Phường/Xã, Quận/Huyện, Tỉnh/Thành..."></textarea>
                        </div>
                        <input type="hidden" name="shipping_provider" value="<?php echo esc_attr(get_option('mkv_shipping_provider', 'ghtk')); ?>">
                    </div>
                </div>
                <script>
                function toggleShippingFields() {
                    const isChecked = document.getElementById('pos-enable-shipping').checked;
                    document.getElementById('pos-shipping-fields').style.display = isChecked ? 'block' : 'none';
                    if (!isChecked) {
                        document.getElementById('pos-shipping-fee').value = 0;
                        document.getElementById('pos-customer-address').value = '';
                    }
                    renderCart();
                }
                </script>
                <?php endif; ?>

                <!-- Chọn phương thức thanh toán -->
                <div style="margin-bottom:16px;">
                    <label style="font-size:12px; font-weight:600; color:var(--mkv-text-muted); text-transform:uppercase; margin-bottom:8px; display:block;"><?php echo esc_html(mkv__('Phương thức thanh toán')); ?></label>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
                        <label class="pos-payment-radio">
                            <input type="radio" name="payment_method" value="cash" checked style="display:none;"> 
                            <div class="pos-payment-box"><?php echo esc_html(mkv__('Tiền mặt')); ?></div>
                        </label>
                        <label class="pos-payment-radio">
                            <input type="radio" name="payment_method" value="transfer" style="display:none;"> 
                            <div class="pos-payment-box"><?php echo esc_html(mkv__('C.Khoản')); ?></div>
                        </label>
                        <label class="pos-payment-radio">
                            <input type="radio" name="payment_method" value="card" style="display:none;"> 
                            <div class="pos-payment-box"><?php echo esc_html(mkv__('Quẹt thẻ')); ?></div>
                        </label>
                    </div>
                </div>
                
                <script>
                document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (typeof updateQR === 'function') updateQR();
                    });
                });
                </script>

                <!-- Tổng kết tiền -->
                <div style="border-top:1px dashed #cbd5e1; padding-top:16px; margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:8px; color:#64748b;">
                        <span><?php echo esc_html(mkv__('Tạm tính:')); ?></span>
                        <span id="cart-subtotal" style="font-weight:600; color:#1e293b;">0 ₫</span>
                    </div>
                    <div id="cart-discount-row" style="display:none; justify-content:space-between; font-size:14px; margin-bottom:8px; color:var(--mkv-green);">
                        <span><?php echo esc_html(mkv__('Giảm điểm thưởng:')); ?></span>
                        <span id="cart-discount" style="font-weight:600;">-0 ₫</span>
                    </div>
                    <?php if ($vat_rate > 0): ?>
                    <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:8px; color:#64748b;">
                        <span><?php echo esc_html(mkv__('Thuế VAT (')); ?><?php echo $vat_rate; ?><?php echo esc_html(mkv__('%):')); ?></span>
                        <span id="cart-vat" style="font-weight:600; color:#1e293b;">0 ₫</span>
                    </div>
                    <?php endif; ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding-top:12px; border-top:1px solid #f1f5f9;">
                        <span style="font-size:16px; font-weight:700; color:#0f172a;"><?php echo esc_html(mkv__('Khách phải trả:')); ?></span>
                        <span id="cart-total" style="font-size:24px; font-weight:800; color:var(--mkv-primary);">0 ₫</span>
                    </div>
                    
                    <div style="margin-top:12px; padding-top:12px; border-top:1px dashed #cbd5e1;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <span style="font-size:14px; color:#64748b; font-weight:600;"><?php echo esc_html(mkv__('Khách thanh toán:')); ?></span>
                            <input type="text" id="pos-paid-amount" name="paid_amount" style="width:130px; text-align:right; font-size:16px; font-weight:bold; padding:6px 8px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc;" placeholder="0" onkeyup="mkvFormatCurrency(this); calculateDebtChange();">
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:14px;">
                            <span id="debt-change-label" style="color:#64748b;"><?php echo esc_html(mkv__('Tiền thừa:')); ?></span>
                            <span id="debt-change-amount" style="font-weight:600; color:#0f172a;">0 ₫</span>
                        </div>
                    </div>
                </div>

                <!-- QR Code Container (VietQR POS Terminal Box) -->
                <div id="pos-qr-container" style="display:none; margin-bottom:16px; padding:14px; background:#f8fafc; border:1.5px solid var(--mkv-primary); border-radius:12px; box-shadow:0 4px 12px rgba(0,111,255,0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <span style="font-size:12px; font-weight:700; color:var(--mkv-primary); text-transform:uppercase; letter-spacing:0.5px;">
                            <i class="hgi-stroke hgi-qr-code"></i> <?php echo esc_html(mkv__('Thanh toán VietQR')); ?>
                        </span>
                        <button type="button" onclick="mkvZoomQrCode()" class="button button-small" style="font-size:11px; padding:2px 8px; border-radius:4px; display:inline-flex; align-items:center; gap:4px;">
                            <i class="hgi-stroke hgi-view"></i> <?php echo esc_html(mkv__('Phóng to')); ?>
                        </button>
                    </div>
                    <div style="text-align:center; background:#ffffff; border-radius:8px; padding:8px; border:1px solid #e2e8f0;">
                        <img id="pos-qr-img" src="" style="width:160px; height:160px; display:inline-block; object-fit:contain;" alt="VietQR" />
                        <div id="pos-qr-amount" style="font-size:15px; font-weight:800; color:var(--mkv-primary); margin-top:6px;">0 ₫</div>
                    </div>
                    <div style="margin-top:10px; font-size:12px; color:#475569; line-height:1.6;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span><?php echo esc_html(mkv__('STK:')); ?> <strong id="pos-qr-acc-text"><?php echo esc_html($bank_acc); ?></strong></span>
                            <button type="button" onclick="mkvCopyText('<?php echo esc_js($bank_acc); ?>', 'Đã copy STK')" style="background:none; border:none; color:var(--mkv-primary); font-size:11px; font-weight:600; cursor:pointer; padding:0;">
                                <i class="hgi-stroke hgi-copy-01"></i> <?php echo esc_html(mkv__('Copy')); ?>
                            </button>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span><?php echo esc_html(mkv__('Nội dung:')); ?> <strong id="pos-qr-desc-text">—</strong></span>
                            <button type="button" onclick="mkvCopyPosDesc()" style="background:none; border:none; color:var(--mkv-primary); font-size:11px; font-weight:600; cursor:pointer; padding:0;">
                                <i class="hgi-stroke hgi-copy-01"></i> <?php echo esc_html(mkv__('Copy')); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Nút hành động -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <button type="button" onclick="clearCart()" class="pos-action-btn pos-action-btn-outline" style="padding:10px; font-size:13px;">
                        <i class="hgi-stroke hgi-delete-02"></i> <?php echo esc_html(mkv__('Xóa giỏ')); ?>
                    </button>
                    <button type="button" onclick="previewReceipt()" class="pos-action-btn pos-action-btn-outline" id="pos-print-btn" disabled style="padding:10px; font-size:13px;">
                        <i class="hgi-stroke hgi-printer"></i> <?php echo esc_html(mkv__('In Bill')); ?>
                    </button>
                </div>
                <div style="display:grid; grid-template-columns:1fr 2fr; gap:10px;">
                    <button type="button" onclick="saveDraft()" id="pos-draft-btn" class="pos-action-btn pos-action-btn-outline" style="font-size:14px; padding:14px; font-weight:700;" disabled>
                        <i class="hgi-stroke hgi-floppy-disk"></i> <?php echo esc_html(mkv__('LƯU NHÁP')); ?>
                    </button>
                    <button type="submit" id="pos-submit-btn" class="pos-action-btn pos-action-btn-primary" style="font-size:15px; padding:14px; font-weight:700;" disabled>
                        <i class="hgi-stroke hgi-credit-card"></i> <?php echo esc_html(mkv__('THANH TOÁN (F9)')); ?>
                    </button>
                </div>
                <input type="hidden" name="is_draft" id="is_draft_input" value="0">
            </form>
        </div>
    </div>
</div>

<!-- Modal xem trước hóa đơn in -->
<div id="mkv-receipt-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:340px; padding:20px; max-height:90vh; overflow-y:auto; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h4 style="margin:0;"><?php echo esc_html(mkv__('Hóa đơn in (80mm)')); ?></h4>
            <button onclick="closeReceiptModal()" style="background:none; border:none; font-size:18px; cursor:pointer;">×</button>
        </div>
        <div id="mkv-printable-receipt" style="font-family:monospace; font-size:12px; padding:12px; border:1px dashed #ccc; background:#fafafa;">
            <div style="text-align:center;"><?php echo wp_kses_post($print_hdr); ?></div>
            <hr style="border:none; border-top:1px dashed #999; margin:8px 0;">
            <div style="font-size:11px; margin-bottom:6px;">
                <div><?php echo esc_html(mkv__('Ngày: ')); ?><?php echo date('d/m/Y H:i'); ?></div>
                <div id="receipt-customer-name"><?php echo esc_html(mkv__('Khách hàng: Khách lẻ')); ?></div>
            </div>
            <hr style="border:none; border-top:1px dashed #999; margin:8px 0;">
            <div id="receipt-items-list" style="font-size:11px;"></div>
            <hr style="border:none; border-top:1px dashed #999; margin:8px 0;">
            <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:13px;">
                <span><?php echo esc_html(mkv__('TỔNG CỘNG:')); ?></span>
                <span id="receipt-total-amount">0 ₫</span>
            </div>
            <div id="receipt-qr-container" style="display:none; text-align:center; margin-top:12px;">
                <div style="font-size:12px; font-weight:bold; margin-bottom:4px;">Chuyển khoản VietQR</div>
                <img id="receipt-qr-img" src="" alt="VietQR" style="max-width:180px; height:auto; display:block; margin:0 auto;" />
                <div id="receipt-qr-text" style="font-size:10px; margin-top:4px; color:#555;"></div>
            </div>
            <hr style="border:none; border-top:1px dashed #999; margin:8px 0;">
            <div style="text-align:center; font-size:11px;"><?php echo wp_kses_post($print_ftr); ?></div>
        </div>
        <div style="display:flex; gap:10px; margin-top:16px;">
            <button type="button" class="button" onclick="closeReceiptModal()" style="flex:1; justify-content:center;"><?php echo esc_html(mkv__('Tiếp tục tạo đơn')); ?></button>
            <button type="button" class="button button-primary" onclick="printReceipt()" style="flex:1; justify-content:center;"><i class="hgi-stroke hgi-printer"></i> <?php echo esc_html(mkv__('In ngay')); ?></button>
        </div>
    </div>
</div>

<!-- Modal phóng to VietQR cho khách hàng quét từ xa -->
<div id="mkv-pos-qr-zoom-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.65); z-index:999999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:#ffffff; border-radius:16px; width:380px; max-width:92vw; padding:24px; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,0.3); animation:fadeIn 0.2s ease;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <div style="text-align:left;">
                <h3 style="margin:0; font-size:16px; font-weight:800; color:var(--mkv-primary);"><i class="hgi-stroke hgi-qr-code"></i> Quét Mã VietQR</h3>
                <span style="font-size:12px; color:#64748b;">Mở ứng dụng ngân hàng hoặc ví điện tử để quét</span>
            </div>
            <button onclick="mkvCloseZoomQr()" style="background:none; border:none; font-size:24px; color:#94a3b8; cursor:pointer; line-height:1;">×</button>
        </div>
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:16px;">
            <img id="pos-qr-zoom-img" src="" style="width:250px; height:250px; object-fit:contain; display:block; margin:0 auto; background:#fff; border-radius:8px; padding:8px; box-shadow:0 2px 8px rgba(0,0,0,0.06);" alt="VietQR Zoom" />
            <div id="pos-qr-zoom-amount" style="font-size:22px; font-weight:900; color:var(--mkv-primary); margin-top:12px;">0 ₫</div>
        </div>
        <div style="background:#eff6ff; border-radius:8px; padding:10px; font-size:13px; color:#1e40af; text-align:left; line-height:1.5;">
            <div><strong>Ngân hàng:</strong> <?php echo esc_html($bank_id); ?><?php echo $bank_name ? ' - ' . esc_html($bank_name) : ''; ?></div>
            <div><strong>Số tài khoản:</strong> <?php echo esc_html($bank_acc); ?></div>
            <div><strong>Nội dung:</strong> <span id="pos-qr-zoom-desc">—</span></div>
        </div>
        <button type="button" onclick="mkvCloseZoomQr()" class="button button-primary" style="width:100%; margin-top:16px; height:42px; font-size:14px; font-weight:700; border-radius:8px;">
            <?php echo esc_html(mkv__('Đã nhận được tiền / Đóng')); ?>
        </button>
    </div>
</div>

<script>
window.mkv_pos_vat_rate = <?php echo $vat_rate; ?> / 100;
window.mkv_point_value  = <?php echo (float) get_option('mkv_point_value', 1); ?>;
window.mkv_allow_negative_stock = <?php echo (int) get_option('mkv_allow_negative_stock', 0); ?>;
window.mkv_enable_pos_qr = <?php echo $enable_pos_qr; ?>;
window.mkv_receipt_enable_qr = <?php echo $enable_receipt_qr; ?>;
window.mkv_bank_info = {
    id: "<?php echo esc_js($bank_id); ?>",
    account: "<?php echo esc_js($bank_acc); ?>",
    name: "<?php echo esc_js($bank_name); ?>"
};
window.mkv_pos_i18n = {
    out_of_stock: '<?php echo esc_js(mkv__('Không đủ tồn kho!')); ?>',
    customer: '<?php echo esc_js(mkv__('Khách hàng')); ?>'
};

function mkvZoomQrCode() {
    const modal = document.getElementById('mkv-pos-qr-zoom-modal');
    const mainImg = document.getElementById('pos-qr-img');
    const zoomImg = document.getElementById('pos-qr-zoom-img');
    const amtEl   = document.getElementById('pos-qr-zoom-amount');
    const descEl  = document.getElementById('pos-qr-zoom-desc');
    const mainAmt = document.getElementById('pos-qr-amount');
    const mainDesc= document.getElementById('pos-qr-desc-text');

    if (modal && mainImg) {
        zoomImg.src = mainImg.src;
        if (amtEl && mainAmt) amtEl.textContent = mainAmt.textContent;
        if (descEl && mainDesc) descEl.textContent = mainDesc.textContent;
        modal.style.display = 'flex';
    }
}

function mkvCloseZoomQr() {
    const modal = document.getElementById('mkv-pos-qr-zoom-modal');
    if (modal) modal.style.display = 'none';
}

function mkvCopyText(text, successMsg) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        if (typeof mkvToast === 'function') mkvToast(successMsg || 'Đã sao chép');
    });
}

function mkvCopyPosDesc() {
    const descEl = document.getElementById('pos-qr-desc-text');
    if (descEl && descEl.textContent) {
        mkvCopyText(descEl.textContent, 'Đã copy nội dung chuyển khoản');
    }
}
</script>
