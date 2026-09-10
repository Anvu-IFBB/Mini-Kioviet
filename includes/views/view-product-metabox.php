<?php
/**
 * View: Chi tiết sản phẩm (Metabox) - Tái thiết kế chuẩn KiotViet UI
 */
if (!defined('ABSPATH')) exit;

$unit = isset($unit) && !empty($unit) ? $unit : (get_post_meta($post->ID, '_mkv_unit', true) ?: 'Cái');
?>
<div class="mkv-product-form-wrap">
    <!-- SECTION 1: MÃ HÀNG & NHẬN DIỆN -->
    <div class="mkv-form-section">
        <div class="mkv-form-section-title">
            <i class="hgi-stroke hgi-tag-01"></i>
            <span><?php echo esc_html(mkv__('Mã hàng & Nhận diện')); ?></span>
        </div>
        
        <div class="mkv-form-grid-3">
            <div class="mkv-field-group">
                <label for="mkv_sku" class="mkv-field-label">
                    <?php echo esc_html(mkv__('Mã hàng (SKU)')); ?> <span class="required">*</span>
                </label>
                <div class="mkv-input-prefix-wrap">
                    <span class="mkv-input-prefix"><i class="hgi-stroke hgi-bar-code-02"></i></span>
                    <input type="text" id="mkv_sku" name="mkv_sku" value="<?php echo esc_attr($sku); ?>" 
                           class="mkv-form-control" placeholder="VD: SP0001" required 
                           oninput="this.value = this.value.toUpperCase()">
                </div>
                <span class="mkv-field-hint"><?php echo esc_html(mkv__('Mã định danh duy nhất của sản phẩm')); ?></span>
            </div>

            <div class="mkv-field-group">
                <label for="mkv_barcode" class="mkv-field-label">
                    <?php echo esc_html(mkv__('Mã vạch (Barcode)')); ?>
                </label>
                <div class="mkv-barcode-input-row">
                    <div class="mkv-input-prefix-wrap" style="flex:1;">
                        <span class="mkv-input-prefix"><i class="hgi-stroke hgi-bar-code-01"></i></span>
                        <input type="text" id="mkv_barcode" name="mkv_barcode" value="<?php echo esc_attr($barcode); ?>" 
                               class="mkv-form-control" placeholder="VD: 893601234567" oninput="mkvRenderBarcodePreview(this.value)">
                    </div>
                    <button type="button" onclick="mkvGenBarcode()" class="mkv-btn mkv-btn-secondary" title="<?php echo esc_attr(mkv__('Tự động sinh mã vạch ngẫu nhiên')); ?>">
                        <i class="hgi-stroke hgi-refresh"></i> <?php echo esc_html(mkv__('Sinh mã')); ?>
                    </button>
                </div>
                <span class="mkv-field-hint"><?php echo esc_html(mkv__('Để trống hệ thống sẽ tự sinh khi lưu')); ?></span>
            </div>

            <div class="mkv-field-group">
                <label for="mkv_unit" class="mkv-field-label">
                    <?php echo esc_html(mkv__('Đơn vị tính')); ?>
                </label>
                <div class="mkv-input-prefix-wrap">
                    <span class="mkv-input-prefix"><i class="hgi-stroke hgi-package"></i></span>
                    <input type="text" id="mkv_unit" name="mkv_unit" list="mkv-units-list" value="<?php echo esc_attr($unit); ?>" 
                           class="mkv-form-control" placeholder="Cái, Chiếc, Bộ...">
                    <datalist id="mkv-units-list">
                        <option value="Cái">
                        <option value="Chiếc">
                        <option value="Bộ">
                        <option value="Hộp">
                        <option value="Chai">
                        <option value="Thùng">
                        <option value="Gói">
                        <option value="Kg">
                        <option value="Lon">
                        <option value="Mét">
                    </datalist>
                </div>
                <span class="mkv-field-hint"><?php echo esc_html(mkv__('Đơn vị bán hàng và tính tồn')); ?></span>
            </div>
        </div>

        <!-- Barcode Preview Container -->
        <div id="mkv-barcode-preview-box" class="mkv-barcode-preview-box" style="<?php echo empty($barcode) ? 'display:none;' : ''; ?>">
            <div class="mkv-barcode-svg-wrap">
                <svg id="mkv-barcode-preview"></svg>
            </div>
            <span class="mkv-barcode-note"><?php echo esc_html(mkv__('Mã vạch chuẩn Code128 - Tương thích máy quét laser & camera POS')); ?></span>
        </div>
    </div>

    <!-- SECTION 2: THIẾT LẬP GIÁ -->
    <div class="mkv-form-section">
        <div class="mkv-form-section-title">
            <i class="hgi-stroke hgi-coins-01"></i>
            <span><?php echo esc_html(mkv__('Thiết lập Giá & Doanh thu')); ?></span>
        </div>

        <div class="mkv-form-grid-2">
            <div class="mkv-field-group">
                <label for="mkv_price_out" class="mkv-field-label">
                    <?php echo esc_html(mkv__('Giá bán')); ?> <span class="required">*</span>
                </label>
                <div class="mkv-price-input-wrap">
                    <input type="number" id="mkv_price_out" name="mkv_price_out" value="<?php echo esc_attr($price_out); ?>" 
                           class="mkv-form-control price-out-input" required min="0" step="1000"
                           oninput="mkvFormatMoneyHint(this, 'mkv-price-out-hint')">
                    <span class="mkv-currency-badge">₫</span>
                </div>
                <div class="mkv-price-formatted-hint" id="mkv-price-out-hint">
                    <?php echo !empty($price_out) ? number_format((float)$price_out, 0, ',', '.') . ' ₫' : '0 ₫'; ?>
                </div>
            </div>

            <?php if ($can_cost): ?>
            <div class="mkv-field-group">
                <label for="mkv_price_in" class="mkv-field-label">
                    <?php echo esc_html(mkv__('Giá vốn (Giá nhập)')); ?> <span class="required">*</span>
                </label>
                <div class="mkv-price-input-wrap">
                    <input type="number" id="mkv_price_in" name="mkv_price_in" value="<?php echo esc_attr($price_in); ?>" 
                           class="mkv-form-control" required min="0" step="1000"
                           oninput="mkvFormatMoneyHint(this, 'mkv-price-in-hint')">
                    <span class="mkv-currency-badge">₫</span>
                </div>
                <div class="mkv-price-formatted-hint" id="mkv-price-in-hint">
                    <?php echo !empty($price_in) ? number_format((float)$price_in, 0, ',', '.') . ' ₫' : '0 ₫'; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="mkv-field-group">
                <label class="mkv-field-label"><?php echo esc_html(mkv__('Giá vốn (Giá nhập)')); ?></label>
                <div class="mkv-shield-notice">
                    <i class="hgi-stroke hgi-shield-user"></i>
                    <span><?php echo esc_html(mkv__('Thông tin bảo mật: Bạn không có quyền xem giá vốn')); ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SECTION 3: TỒN KHO & ĐỊNH MỨC -->
    <div class="mkv-form-section">
        <div class="mkv-form-section-title">
            <i class="hgi-stroke hgi-warehouse"></i>
            <span><?php echo esc_html(mkv__('Quản lý Tồn kho & Định mức')); ?></span>
        </div>

        <div class="mkv-form-grid-2">
            <div class="mkv-field-group">
                <label for="mkv_stock" class="mkv-field-label">
                    <?php echo esc_html(mkv__('Tồn kho ban đầu')); ?>
                </label>
                <div class="mkv-input-prefix-wrap">
                    <span class="mkv-input-prefix"><i class="hgi-stroke hgi-package"></i></span>
                    <input type="number" id="mkv_stock" name="mkv_stock" value="<?php echo esc_attr($stock !== '' ? $stock : '0'); ?>" 
                           class="mkv-form-control" min="0">
                </div>
                <span class="mkv-field-hint"><?php echo esc_html(mkv__('Số lượng tồn tại kho chính khi bắt đầu tạo sản phẩm')); ?></span>
            </div>

            <div class="mkv-field-group">
                <label for="mkv_min_stock" class="mkv-field-label">
                    <?php echo esc_html(mkv__('Định mức tồn tối thiểu (Cảnh báo hết hàng)')); ?>
                </label>
                <div class="mkv-input-prefix-wrap">
                    <span class="mkv-input-prefix"><i class="hgi-stroke hgi-alert-circle"></i></span>
                    <input type="number" id="mkv_min_stock" name="mkv_min_stock" 
                           value="<?php echo esc_attr($min_stock !== '' ? $min_stock : get_option('mkv_min_stock_threshold', 5)); ?>" 
                           class="mkv-form-control" min="0" placeholder="<?php echo esc_attr(mkv__('Mặc định từ Cài đặt')); ?>">
                </div>
                <span class="mkv-field-hint"><?php echo esc_html(mkv__('Hệ thống sẽ báo động đỏ khi tồn kho giảm xuống dưới mức này')); ?></span>
            </div>
        </div>
    </div>
</div>

<script>
function mkvFormatMoneyHint(input, hintId) {
    var val = parseFloat(input.value) || 0;
    var hint = document.getElementById(hintId);
    if (hint) {
        hint.textContent = new Intl.NumberFormat('vi-VN').format(val) + ' ₫';
    }
}

function mkvRenderBarcodePreview(code) {
    var previewBox = document.getElementById('mkv-barcode-preview-box');
    var svg = document.getElementById('mkv-barcode-preview');
    if (!code || !code.trim()) {
        if (previewBox) previewBox.style.display = 'none';
        return;
    }
    if (previewBox) previewBox.style.display = 'flex';
    try {
        if (typeof JsBarcode === 'function' && svg) {
            JsBarcode(svg, String(code).trim(), {
                format: 'CODE128',
                lineColor: '#0f172a',
                width: 1.6,
                height: 44,
                displayValue: true,
                fontSize: 12,
                font: 'Inter, monospace',
                margin: 6
            });
        }
    } catch(e) {
        console.warn('Invalid barcode for preview:', e);
    }
}

function mkvGenBarcode() {
    var prefix = '893';
    var rand = Math.floor(Math.random() * 900000000) + 100000000;
    var code = prefix + String(rand);
    var input = document.getElementById('mkv_barcode');
    if (input) {
        input.value = code;
        mkvRenderBarcodePreview(code);
    }
}

// Render on DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    var currentBarcode = '<?php echo esc_js($barcode); ?>';
    if (currentBarcode) {
        mkvRenderBarcodePreview(currentBarcode);
    }
});
</script>
