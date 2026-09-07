<?php
if (!defined('ABSPATH')) exit;
require_once MKV_DIR . 'includes/views/header-kiotviet.php';
?>
<div style="padding: 24px;">
    <form method="post" action="options.php" id="mkv-settings-form">
        <?php settings_fields('mkv_settings_group'); ?>
        <div class="mkv-page-header">
            <h1 class="mkv-page-title"><i class="hgi-stroke hgi-settings-02"></i> <?php echo esc_html(mkv__('Cài Đặt Hệ Thống')); ?></h1>
            <div class="mkv-page-actions">
                <button type="submit" class="mkv-btn mkv-btn-primary" style="font-size:14px;padding:8px 24px;">
                    <i class="hgi-stroke hgi-floppy-disk"></i> <?php echo esc_html(mkv__('Lưu Cài Đặt')); ?>
                </button>
            </div>
        </div>

        <?php if (isset($_GET['settings-updated'])): ?>
            <div class="mkv-alert mkv-alert-success"><i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Đã lưu cài đặt thành công!')); ?></div>
        <?php endif; ?>

        <!-- Tab navigation -->
        <div class="mkv-tabs-bar" style="margin-bottom:20px;">
            <a href="#" class="mkv-tab-link active" onclick="return mkvShowTab(this, 'tab-store');"><i class="hgi-stroke hgi-store-01"></i> <?php echo esc_html(mkv__('Cửa hàng')); ?></a>
            <a href="#" class="mkv-tab-link" onclick="return mkvShowTab(this, 'tab-finance');"><i class="hgi-stroke hgi-money-bag-02"></i> <?php echo esc_html(mkv__('Tài chính')); ?></a>
            <a href="#" class="mkv-tab-link" onclick="return mkvShowTab(this, 'tab-policy');"><i class="hgi-stroke hgi-book-01"></i> <?php echo esc_html(mkv__('Chính sách')); ?></a>
            <a href="#" class="mkv-tab-link" onclick="return mkvShowTab(this, 'tab-payment');"><i class="hgi-stroke hgi-wallet-01"></i> <?php echo esc_html(mkv__('Thanh toán (VietQR)')); ?></a>
            <a href="#" class="mkv-tab-link" onclick="return mkvShowTab(this, 'tab-receipt');"><i class="hgi-stroke hgi-invoice-02"></i> <?php echo esc_html(mkv__('Hóa đơn in')); ?></a>
            <a href="#" class="mkv-tab-link" onclick="return mkvShowTab(this, 'tab-shipping');"><i class="hgi-stroke hgi-truck-delivery"></i> <?php echo esc_html(mkv__('Vận chuyển')); ?></a>
            <a href="#" class="mkv-tab-link" onclick="return mkvShowTab(this, 'tab-ai');"><i class="hgi-stroke hgi-ai-chat-02"></i> <?php echo esc_html(mkv__('Trợ lý AI')); ?></a>
        </div>

        <!-- Tab: Cửa hàng -->
        <div id="tab-store" class="mkv-tab-content">
            <div class="mkv-card" style="max-width:800px; padding: 24px;">
                <h3 class="mkv-card-title" style="margin-bottom: 20px;"><i class="hgi-stroke hgi-store-01"></i> <?php echo esc_html(mkv__('Thông tin cửa hàng')); ?></h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Tên cửa hàng')); ?></label>
                        <input type="text" name="mkv_store_name" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_store_name','Mini KiotViet')); ?>">
                    </div>
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Số điện thoại')); ?></label>
                        <input type="text" name="mkv_store_phone" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_store_phone','')); ?>">
                    </div>
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Email liên hệ')); ?></label>
                        <input type="email" name="mkv_store_email" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_store_email','')); ?>">
                    </div>
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('URL Logo')); ?></label>
                        <input type="text" name="mkv_store_logo" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_store_logo','')); ?>" placeholder="https://...">
                    </div>
                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Địa chỉ cửa hàng')); ?></label>
                        <textarea name="mkv_store_address" class="mkv-textarea" style="width: 100%;" rows="2"><?php echo esc_textarea(get_option('mkv_store_address','')); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Tài chính -->
        <div id="tab-finance" class="mkv-tab-content" style="display:none;">
            <div class="mkv-card" style="max-width:800px; padding: 24px;">
                <h3 class="mkv-card-title" style="margin-bottom: 20px;"><i class="hgi-stroke hgi-money-bag-02"></i> <?php echo esc_html(mkv__('Cấu hình tiền tệ & thuế')); ?></h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Tiền tệ')); ?></label>
                        <select name="mkv_store_currency" class="mkv-select" style="width: 100%;">
                            <option value="VND" <?php selected(get_option('mkv_store_currency','VND'),'VND'); ?>><?php echo esc_html(mkv__('VNĐ — Việt Nam Đồng')); ?></option>
                            <option value="USD" <?php selected(get_option('mkv_store_currency','VND'),'USD'); ?>><?php echo esc_html(mkv__('USD — Đô la Mỹ')); ?></option>
                        </select>
                    </div>
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Thuế VAT mặc định (%)')); ?></label>
                        <input type="number" name="mkv_store_vat" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_store_vat','8')); ?>" min="0" max="100" step="0.5">
                    </div>
                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Cách tính VAT')); ?></label>
                        <select name="mkv_vat_included" class="mkv-select" style="width: 100%; max-width: 400px;">
                            <option value="0" <?php selected(get_option('mkv_vat_included',0),0); ?>><?php echo esc_html(mkv__('Cộng thêm vào giá bán')); ?></option>
                            <option value="1" <?php selected(get_option('mkv_vat_included',0),1); ?>><?php echo esc_html(mkv__('Giá bán đã bao gồm VAT')); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Chính sách -->
        <div id="tab-policy" class="mkv-tab-content" style="display:none;">
            <div class="mkv-card" style="max-width:800px; padding: 24px;">
                <h3 class="mkv-card-title" style="margin-bottom: 20px;"><i class="hgi-stroke hgi-book-01"></i> <?php echo esc_html(mkv__('Chính sách bán hàng')); ?></h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Tỷ lệ quy đổi điểm thưởng')); ?></label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="number" name="mkv_points_rate" class="mkv-input" value="<?php echo esc_attr(get_option('mkv_points_rate',100)); ?>" min="1" style="width:130px;">
                            <span style="color:var(--mkv-text-muted); font-weight: 500;"><?php echo esc_html(mkv__('VNĐ = 1 điểm')); ?></span>
                        </div>
                    </div>
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Giá trị quy đổi (Khi thanh toán)')); ?></label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="color:var(--mkv-text-muted); font-weight: 500;"><?php echo esc_html(mkv__('1 điểm = ')); ?></span>
                            <input type="number" name="mkv_point_value" class="mkv-input" value="<?php echo esc_attr(get_option('mkv_point_value',1)); ?>" min="1" style="width:100px;">
                            <span style="color:var(--mkv-text-muted); font-weight: 500;"><?php echo esc_html(mkv__('VNĐ')); ?></span>
                        </div>
                    </div>
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Ngưỡng tồn kho cảnh báo (mặc định)')); ?></label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="number" name="mkv_min_stock_threshold" class="mkv-input" value="<?php echo esc_attr(get_option('mkv_min_stock_threshold',5)); ?>" min="0" style="width:130px;">
                            <span style="color:var(--mkv-text-muted); font-weight: 500;"><?php echo esc_html(mkv__('sản phẩm')); ?></span>
                        </div>
                    </div>
                    <div class="mkv-form-group" style="grid-column:span 2; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                            <input type="hidden" name="mkv_allow_negative_stock" value="0">
                            <input type="checkbox" name="mkv_allow_negative_stock" value="1" <?php checked(get_option('mkv_allow_negative_stock',0),1); ?>>
                            <span class="mkv-label" style="margin-bottom:0; font-weight: 600;"><?php echo esc_html(mkv__('Cho phép bán âm kho (bán khi hết hàng)')); ?></span>
                        </label>
                        <p style="color:var(--mkv-text-muted);font-size:13px;margin-top:6px; margin-left: 28px;"><i class="hgi-stroke hgi-information-circle"></i> <?php echo esc_html(mkv__('Chỉ bật tính năng này nếu bạn chắc chắn về tồn kho thực tế.')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Thanh toan -->
        <div id="tab-payment" class="mkv-tab-content" style="display:none;">
            <?php
            $current_bank = get_option('mkv_bank_id', '');
            $current_acc  = get_option('mkv_bank_account', '');
            $current_name = get_option('mkv_bank_name', '');
            $enable_pos_qr = (int) get_option('mkv_enable_pos_qr', 1);
            $enable_receipt_qr = (int) get_option('mkv_receipt_enable_qr', 1);

            $napas_banks = array(
                'VCB'        => 'Vietcombank (Ngoại Thương Việt Nam)',
                'MB'         => 'MBBank (Quân Đội)',
                'TCB'        => 'Techcombank (Kỹ Thương)',
                'ACB'        => 'ACB (Á Châu)',
                'VPB'        => 'VPBank (Việt Nam Thịnh Vượng)',
                'TPB'        => 'TPBank (Tiên Phong)',
                'BIDV'       => 'BIDV (Đầu Tư và Phát Triển)',
                'CTG'        => 'VietinBank (Công Thương Việt Nam)',
                'VBA'        => 'Agribank (Nông Nghiệp & PTNT)',
                'STB'        => 'Sacombank (Sài Gòn Thương Tín)',
                'HDB'        => 'HDBank (Phát Triển TP.HCM)',
                'OCB'        => 'OCB (Phương Đông)',
                'VIB'        => 'VIB (Quốc Tế)',
                'SHB'        => 'SHB (Sài Gòn - Hà Nội)',
                'LPB'        => 'LPBank (Lộc Phát Việt Nam)',
                'MSB'        => 'MSB (Hàng Hải)',
                'SEAB'       => 'SeABank (Đông Nam Á)',
                'ABB'        => 'ABBANK (An Bình)',
                'BAB'        => 'BacABank (Bắc Á)',
                'NAB'        => 'NamABank (Nam Á)',
                'KLB'        => 'Kienlongbank (Kiên Long)',
                'PGB'        => 'PGBank (Thịnh Vượng & PT)',
                'BVB'        => 'BVBank (Bản Việt)',
                'BAOVIET'    => 'BaoVietBank (Bảo Việt)',
                'SAIGONBANK' => 'SaigonBank (Sài Gòn Công Thương)',
                'COOPBANK'   => 'Co-opBank (Hợp Tác Xã)',
                'VIETBANK'   => 'VietBank (Việt Nam Thương Tín)',
                'NCB'        => 'NCB (Quốc Dân)',
            );
            $is_custom_bank = !empty($current_bank) && !array_key_exists(strtoupper($current_bank), $napas_banks);
            ?>
            <div style="display:grid; grid-template-columns:1fr 340px; gap:24px; max-width:1050px; align-items:start;">
                <div class="mkv-card" style="padding:24px;">
                    <h3 class="mkv-card-title" style="margin-bottom:20px;"><i class="hgi-stroke hgi-wallet-01"></i> <?php echo esc_html(mkv__('Cấu hình Thanh toán Chuyển khoản (VietQR)')); ?></h3>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        <div class="mkv-form-group">
                            <label class="mkv-label"><?php echo esc_html(mkv__('Chọn ngân hàng')); ?></label>
                            <select id="mkv_bank_select" class="mkv-select" onchange="onBankSelectChange(this)" style="width:100%;">
                                <option value=""><?php echo esc_html(mkv__('-- Chọn ngân hàng thụ hưởng --')); ?></option>
                                <?php foreach ($napas_banks as $b_code => $b_label): ?>
                                    <option value="<?php echo esc_attr($b_code); ?>" <?php selected(strtoupper($current_bank), $b_code); ?>>
                                        <?php echo esc_html($b_code . ' - ' . $b_label); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="__custom__" <?php selected($is_custom_bank, true); ?>><?php echo esc_html(mkv__('Khác (Tự nhập mã NAPAS/BIN)')); ?></option>
                            </select>
                        </div>
                        <div class="mkv-form-group">
                            <label class="mkv-label"><?php echo esc_html(mkv__('Mã ngân hàng (NAPAS Code)')); ?></label>
                            <input type="text" id="mkv_bank_id" name="mkv_bank_id" class="mkv-input" value="<?php echo esc_attr($current_bank); ?>" placeholder="Ví dụ: VCB, MB, TCB..." oninput="updateSettingsQrPreview();">
                        </div>
                        <div class="mkv-form-group">
                            <label class="mkv-label"><?php echo esc_html(mkv__('Số tài khoản ngân hàng')); ?></label>
                            <input type="text" id="mkv_bank_account" name="mkv_bank_account" class="mkv-input" value="<?php echo esc_attr($current_acc); ?>" placeholder="Ví dụ: 0123456789" oninput="updateSettingsQrPreview();">
                        </div>
                        <div class="mkv-form-group">
                            <label class="mkv-label"><?php echo esc_html(mkv__('Tên chủ tài khoản (Viết IN HOA không dấu)')); ?></label>
                            <input type="text" id="mkv_bank_name" name="mkv_bank_name" class="mkv-input" value="<?php echo esc_attr($current_name); ?>" placeholder="Ví dụ: NGUYEN VAN A" oninput="updateSettingsQrPreview();">
                        </div>
                        <div class="mkv-form-group" style="grid-column: span 2; padding-top: 12px; border-top: 1px dashed var(--mkv-border);">
                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:14px; font-weight:600; color:var(--mkv-text-main);">
                                <input type="checkbox" name="mkv_enable_pos_qr" value="1" <?php checked($enable_pos_qr, 1); ?> style="width:18px; height:18px; accent-color:var(--mkv-primary);">
                                <span><?php echo esc_html(mkv__('Tự động hiển thị mã QR trên màn hình POS khi chọn Chuyển khoản')); ?></span>
                            </label>
                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:14px; font-weight:600; color:var(--mkv-text-main); margin-top:10px;">
                                <input type="checkbox" name="mkv_receipt_enable_qr" value="1" <?php checked($enable_receipt_qr, 1); ?> style="width:18px; height:18px; accent-color:var(--mkv-primary);">
                                <span><?php echo esc_html(mkv__('In mã QR thanh toán VietQR ở chân Hóa đơn (Bill 80mm/K58/A4)')); ?></span>
                            </label>
                            <p style="color:var(--mkv-text-muted); font-size:12px; margin-top:8px;">
                                <i class="hgi-stroke hgi-information-circle"></i> <?php echo esc_html(mkv__('Mã QR sinh ra tương thích 100% với hơn 40 ứng dụng ngân hàng (Vietcombank, MB, Techcombank, VPBank, ACB...) và ví điện tử VNPay, MoMo.')); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Preview QR Card -->
                <div class="mkv-card" style="padding:20px; text-align:center; background:#ffffff; border:1px solid var(--mkv-border);">
                    <h4 style="margin:0 0 12px; font-size:14px; font-weight:700; color:var(--mkv-text-main);">
                        <i class="hgi-stroke hgi-qr-code"></i> <?php echo esc_html(mkv__('Mẫu VietQR Thực Tế')); ?>
                    </h4>
                    <div id="settings-qr-box" style="padding:16px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0; display:inline-block; width:100%;">
                        <img id="settings-qr-img" src="" alt="VietQR Live Preview" style="width:180px; height:180px; object-fit:contain; display:block; margin:0 auto; background:#fff; border-radius:8px; padding:4px; box-shadow:var(--mkv-shadow-sm);">
                        <div id="settings-qr-info" style="margin-top:12px; font-size:12px; line-height:1.5; color:var(--mkv-text-main);">
                            <div id="qr-preview-bank" style="font-weight:700; color:var(--mkv-primary);">—</div>
                            <div id="qr-preview-acc" style="font-family:monospace; font-size:13px; font-weight:bold;">—</div>
                            <div id="qr-preview-name" style="color:var(--mkv-text-muted); text-transform:uppercase;">—</div>
                        </div>
                    </div>
                    <div style="margin-top:12px; font-size:11px; color:#64748b;">
                        <?php echo esc_html(mkv__('Bạn có thể mở app ngân hàng để quét thử mã phía trên để kiểm tra tài khoản nhận tiền.')); ?>
                    </div>
                </div>
            </div>

            <script>
            function onBankSelectChange(sel) {
                const val = sel.value;
                const idInput = document.getElementById('mkv_bank_id');
                if (val && val !== '__custom__') {
                    idInput.value = val;
                } else if (val === '__custom__') {
                    idInput.value = '';
                    idInput.focus();
                }
                updateSettingsQrPreview();
            }

            function updateSettingsQrPreview() {
                const bank = (document.getElementById('mkv_bank_id').value || '').trim().toUpperCase();
                const acc  = (document.getElementById('mkv_bank_account').value || '').trim();
                const name = (document.getElementById('mkv_bank_name').value || '').trim().toUpperCase();

                const qrImg   = document.getElementById('settings-qr-img');
                const pBank   = document.getElementById('qr-preview-bank');
                const pAcc    = document.getElementById('qr-preview-acc');
                const pName   = document.getElementById('qr-preview-name');

                pBank.textContent = bank || '<?php echo esc_js(mkv__('Chưa chọn ngân hàng')); ?>';
                pAcc.textContent  = acc ? 'STK: ' + acc : '<?php echo esc_js(mkv__('Chưa nhập số tài khoản')); ?>';
                pName.textContent = name || '<?php echo esc_js(mkv__('Chưa nhập chủ tài khoản')); ?>';

                if (bank && acc) {
                    const encodedBank = encodeURIComponent(bank);
                    const encodedAcc  = encodeURIComponent(acc);
                    const encodedName = encodeURIComponent(name);
                    qrImg.src = `https://img.vietqr.io/image/${encodedBank}-${encodedAcc}-compact2.png?amount=50000&addInfo=MINI+KIOTVIET+TEST&accountName=${encodedName}`;
                    qrImg.style.display = 'block';
                } else {
                    qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=MiniKiotViet`;
                }
            }
            document.addEventListener('DOMContentLoaded', updateSettingsQrPreview);
            </script>
        </div>

        <!-- Tab: Hóa đơn in -->
        <div id="tab-receipt" class="mkv-tab-content" style="display:none;">
            <div class="mkv-card" style="max-width:800px; padding: 24px;">
                <h3 class="mkv-card-title" style="margin-bottom: 20px;"><i class="hgi-stroke hgi-invoice-02"></i> <?php echo esc_html(mkv__('Mẫu hóa đơn in')); ?></h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Đầu hóa đơn (Header)')); ?></label>
                        <?php
                            $default_header = '<strong>' . get_option('mkv_store_name', mkv__('Cửa hàng')) . '</strong><br>' . get_option('mkv_store_address','') . '<br>Tel: ' . get_option('mkv_store_phone','');
                            wp_editor(get_option('mkv_print_header', $default_header), 'mkv_print_header', array(
                                'textarea_name' => 'mkv_print_header',
                                'textarea_rows' => 6,
                                'media_buttons' => false,
                                'teeny'         => true,
                                'quicktags'     => false
                            ));
                        ?>
                    </div>
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Chân hóa đơn (Footer)')); ?></label>
                        <?php
                            wp_editor(get_option('mkv_print_footer','<em>'.mkv__('Cảm ơn quý khách! Hẹn gặp lại.').'</em>'), 'mkv_print_footer', array(
                                'textarea_name' => 'mkv_print_footer',
                                'textarea_rows' => 6,
                                'media_buttons' => false,
                                'teeny'         => true,
                                'quicktags'     => false
                            ));
                        ?>
                    </div>
                </div>
                <div style="margin-top:24px;padding:24px;background:#f8fafc;border-radius:12px; border: 1px solid #e2e8f0;">
                    <h4 style="margin:0 0 16px;font-size:14px;color:#1e293b;"><i class="hgi-stroke hgi-view"></i> <?php echo esc_html(mkv__('Xem trước (Preview) mẫu in')); ?></h4>
                    <div id="mkv-receipt-preview" style="font-family:monospace;font-size:12px;width:320px;margin:0 auto;padding:16px;border:1px dashed #cbd5e1;background:#fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        <div style="text-align:center;"><?php echo wp_kses_post(get_option('mkv_print_header', '<strong>' . get_option('mkv_store_name', mkv__('Cửa hàng')) . '</strong><br>' . get_option('mkv_store_address','') . '<br>Tel: ' . get_option('mkv_store_phone',''))); ?></div>
                        <hr style="border-top:1px dashed #cbd5e1; margin: 12px 0;">
                        <table style="width:100%;font-size:12px;"><tr><td>Sản phẩm A</td><td style="text-align:right;">2 x 50,000</td><td style="text-align:right;">100,000</td></tr></table>
                        <hr style="border-top:1px dashed #cbd5e1; margin: 12px 0;">
                        <div style="text-align:right;font-weight:bold; font-size: 14px;">Tổng: 100,000 ₫</div>
                        <hr style="border-top:1px dashed #cbd5e1; margin: 12px 0;">
                        <div style="text-align:center; color: #64748b;"><?php echo wp_kses_post(get_option('mkv_print_footer','')); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Tab: Vận chuyển -->
        <div id="tab-shipping" class="mkv-tab-content" style="display:none;">
            <div class="mkv-card" style="max-width:800px; padding: 24px;">
                <h3 class="mkv-card-title" style="margin-bottom: 20px;"><i class="hgi-stroke hgi-truck-delivery"></i> <?php echo esc_html(mkv__('Cấu hình Vận chuyển (GHTK / GHN)')); ?></h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <label class="mkv-label" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="hidden" name="mkv_shipping_enable" value="0">
                            <input type="checkbox" name="mkv_shipping_enable" value="1" <?php checked(get_option('mkv_shipping_enable', 0), 1); ?>>
                            <?php echo esc_html(mkv__('Kích hoạt tính năng liên kết vận chuyển')); ?>
                        </label>
                    </div>
                    
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Đối tác vận chuyển')); ?></label>
                        <select name="mkv_shipping_provider" class="mkv-select" style="width: 100%;">
                            <option value="ghtk" <?php selected(get_option('mkv_shipping_provider','ghtk'),'ghtk'); ?>><?php echo esc_html(mkv__('Giao Hàng Tiết Kiệm (GHTK)')); ?></option>
                            <option value="ghn" <?php selected(get_option('mkv_shipping_provider','ghtk'),'ghn'); ?>><?php echo esc_html(mkv__('Giao Hàng Nhanh (GHN)')); ?></option>
                        </select>
                    </div>

                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('API Token / Client Secret')); ?></label>
                        <input type="password" name="mkv_shipping_token" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_shipping_token','')); ?>">
                    </div>

                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Webhook Secret')); ?></label>
                        <input type="password" name="mkv_webhook_secret" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_webhook_secret','')); ?>">
                    </div>

                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Địa chỉ lấy hàng mặc định')); ?></label>
                        <input type="text" name="mkv_shipping_address" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_shipping_address','')); ?>" placeholder="<?php echo esc_attr(mkv__('Số nhà, tên đường...')); ?>">
                    </div>
                    
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Tỉnh / Thành phố')); ?></label>
                        <input type="text" name="mkv_shipping_province" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_shipping_province','')); ?>">
                    </div>
                    
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Quận / Huyện')); ?></label>
                        <input type="text" name="mkv_shipping_district" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_shipping_district','')); ?>">
                    </div>

                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Phường / Xã')); ?></label>
                        <input type="text" name="mkv_shipping_ward" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_shipping_ward','')); ?>">
                    </div>
                    
                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Số điện thoại kho lấy hàng')); ?></label>
                        <input type="text" name="mkv_shipping_phone" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_shipping_phone','')); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Trợ lý AI -->
        <div id="tab-ai" class="mkv-tab-content" style="display:none;">
            <div class="mkv-card" style="max-width:800px; padding: 24px;">
                <h3 class="mkv-card-title" style="margin-bottom: 20px;"><i class="hgi-stroke hgi-ai-chat-02"></i> <?php echo esc_html(mkv__('Cấu hình Trợ lý AI (KiotViet Copilot)')); ?></h3>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <label class="mkv-label" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="hidden" name="mkv_ai_enable" value="0">
                            <input type="checkbox" name="mkv_ai_enable" value="1" <?php checked(get_option('mkv_ai_enable', 1), 1); ?>>
                            <?php echo esc_html(mkv__('Kích hoạt Trợ lý ảo AI (Mini KiotViet Copilot)')); ?>
                        </label>
                    </div>
                    
                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Google Gemini API Key')); ?></label>
                        <textarea name="mkv_gemini_api_key" class="mkv-input" style="width: 100%; height: 60px; font-family: monospace; font-size: 13px;" placeholder="AIzaSy... (Hỗ trợ nhập nhiều key, mỗi key 1 dòng)"><?php echo esc_textarea(get_option('mkv_gemini_api_key','')); ?></textarea>
                    </div>

                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Tên Trợ lý')); ?></label>
                        <input type="text" name="mkv_ai_assistant_name" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_ai_assistant_name','KiotViet Copilot')); ?>">
                    </div>

                    <div class="mkv-form-group">
                        <label class="mkv-label"><?php echo esc_html(mkv__('Mô hình Gemini (Model)')); ?></label>
                        <?php $curr_model = get_option('mkv_gemini_model', 'models/gemini-3.5-flash-lite'); ?>
                        <select name="mkv_gemini_model" class="mkv-input" style="width: 100%;">
                            <option value="models/gemini-3.5-flash-lite" <?php selected($curr_model, 'models/gemini-3.5-flash-lite'); ?>>gemini-3.5-flash-lite</option>
                            <option value="models/gemini-3.5-flash" <?php selected($curr_model, 'models/gemini-3.5-flash'); ?>>gemini-3.5-flash</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
<script>
function mkvShowTab(el, tabId) {
    document.querySelectorAll('.mkv-tab-content').forEach(function(t) { t.style.display = 'none'; });
    document.querySelectorAll('.mkv-tab-link').forEach(function(t) { t.classList.remove('active'); });
    document.getElementById(tabId).style.display = 'block';
    el.classList.add('active');
    return false;
}
</script>

<script>document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Thiết lập')); ?>');</script>
<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>