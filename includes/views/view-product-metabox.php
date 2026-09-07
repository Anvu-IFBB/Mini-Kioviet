<?php
if (!defined('ABSPATH')) exit;
?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:15px;">
    <div>
        <label for="mkv_sku"><strong><?php echo esc_html(mkv__('Mã hàng (SKU)')); ?> <span style="color:red">*</span></strong></label>
        <input type="text" id="mkv_sku" name="mkv_sku" value="<?php echo esc_attr($sku); ?>" style="width:100%;margin-top:6px;" required>
    </div>
    <div>
        <label for="mkv_barcode"><strong><?php echo esc_html(mkv__('Mã vạch (Barcode)')); ?></strong> <em style="color:#888;font-weight:400;">(<?php echo esc_html(mkv__('để trống = tự sinh')); ?>)</em></label>
        <div style="display:flex;gap:8px;margin-top:6px;">
            <input type="text" id="mkv_barcode" name="mkv_barcode" value="<?php echo esc_attr($barcode); ?>" style="flex:1;">
            <button type="button" onclick="mkvGenBarcode()" class="button" style="white-space:nowrap; display:inline-flex; align-items:center; gap:4px;">
                <i class="hgi-stroke hgi-bar-code-01"></i> <?php echo esc_html(mkv__('Sinh mã')); ?>
            </button>
        </div>
        <?php if ($barcode): ?>
        <div style="margin-top:10px;text-align:center;">
            <svg id="mkv-barcode-preview"></svg>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                JsBarcode('#mkv-barcode-preview', '<?php echo esc_js($barcode); ?>', {
                    format: 'CODE128', lineColor: '#000', width: 1.5, height: 40,
                    displayValue: true, fontSize: 11
                });
            });
        </script>
        <?php endif; ?>
    </div>

    <?php if ($can_cost): ?>
    <div>
        <label for="mkv_price_in"><strong><?php echo esc_html(mkv__('Giá nhập')); ?> (VNĐ) <span style="color:red">*</span></strong></label>
        <input type="number" id="mkv_price_in" name="mkv_price_in" value="<?php echo esc_attr($price_in); ?>" style="width:100%;margin-top:6px;" required min="0">
    </div>
    <?php else: ?>
    <div><label><strong><?php echo esc_html(mkv__('Giá nhập')); ?></strong></label>
        <p style="padding:8px 12px;background:#f0f0f1;border-radius:6px;margin-top:6px;color:#64748b;display:flex;align-items:center;gap:6px;"><i class="hgi-stroke hgi-circle-lock-01"></i> <?php echo esc_html(mkv__('Bạn không có quyền xem giá nhập')); ?></p>
        <input type="hidden" name="mkv_price_in" value="<?php echo esc_attr($price_in); ?>">
    </div>
    <?php endif; ?>

    <div>
        <label for="mkv_price_out"><strong><?php echo esc_html(mkv__('Giá bán')); ?> (VNĐ) <span style="color:red">*</span></strong></label>
        <input type="number" id="mkv_price_out" name="mkv_price_out" value="<?php echo esc_attr($price_out); ?>" style="width:100%;margin-top:6px;" required min="0">
    </div>

    <div>
        <label for="mkv_stock"><strong><?php echo esc_html(mkv__('Tồn kho ban đầu')); ?></strong></label>
        <input type="number" id="mkv_stock" name="mkv_stock" value="<?php echo esc_attr($stock !== '' ? $stock : '0'); ?>" style="width:100%;margin-top:6px;" min="0">
    </div>
    <div>
        <label for="mkv_min_stock"><strong><?php echo esc_html(mkv__('Ngưỡng cảnh báo hết hàng')); ?></strong></label>
        <input type="number" id="mkv_min_stock" name="mkv_min_stock"
            value="<?php echo esc_attr($min_stock !== '' ? $min_stock : get_option('mkv_min_stock_threshold', 5)); ?>"
            style="width:100%;margin-top:6px;" min="0" placeholder="<?php echo esc_attr(mkv__('Mặc định từ Cài đặt')); ?>">
    </div>
</div>

<script>
function mkvGenBarcode() {
    var id = '<?php echo isset($post->ID) ? intval($post->ID) : 0; ?>';
    var prefix = '893';
    var rand   = Math.floor(Math.random() * 900000000) + 100000000;
    var code   = prefix + rand;
    document.getElementById('mkv_barcode').value = code;
    try {
        JsBarcode('#mkv-barcode-preview', code, {
            format: 'CODE128', lineColor: '#000', width: 1.5, height: 40,
            displayValue: true, fontSize: 11
        });
        document.getElementById('mkv-barcode-preview').style.display = 'block';
    } catch(e){}
}
</script>
