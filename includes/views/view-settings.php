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
        <div class="mkv-tabs-bar" role="tablist" aria-label="<?php echo esc_attr(mkv__('Cài đặt')); ?>" style="margin-bottom:20px;">
            <button type="button" id="mkv-tab-store-trigger" class="mkv-tab-link active" role="tab" aria-selected="true" aria-controls="tab-store" tabindex="0" onclick="return mkvShowTab(this, 'tab-store');"><i class="hgi-stroke hgi-store-01" aria-hidden="true"></i> <?php echo esc_html(mkv__('Cửa hàng')); ?></button>
            <button type="button" id="mkv-tab-finance-trigger" class="mkv-tab-link" role="tab" aria-selected="false" aria-controls="tab-finance" tabindex="-1" onclick="return mkvShowTab(this, 'tab-finance');"><i class="hgi-stroke hgi-money-bag-02" aria-hidden="true"></i> <?php echo esc_html(mkv__('Tài chính')); ?></button>
            <button type="button" id="mkv-tab-policy-trigger" class="mkv-tab-link" role="tab" aria-selected="false" aria-controls="tab-policy" tabindex="-1" onclick="return mkvShowTab(this, 'tab-policy');"><i class="hgi-stroke hgi-book-01" aria-hidden="true"></i> <?php echo esc_html(mkv__('Chính sách')); ?></button>
            <button type="button" id="mkv-tab-payment-trigger" class="mkv-tab-link" role="tab" aria-selected="false" aria-controls="tab-payment" tabindex="-1" onclick="return mkvShowTab(this, 'tab-payment');"><i class="hgi-stroke hgi-wallet-01" aria-hidden="true"></i> <?php echo esc_html(mkv__('Thanh toán (VietQR)')); ?></button>
            <button type="button" id="mkv-tab-receipt-trigger" class="mkv-tab-link" role="tab" aria-selected="false" aria-controls="tab-receipt" tabindex="-1" onclick="return mkvShowTab(this, 'tab-receipt');"><i class="hgi-stroke hgi-invoice-02" aria-hidden="true"></i> <?php echo esc_html(mkv__('Hóa đơn in')); ?></button>
            <button type="button" id="mkv-tab-shipping-trigger" class="mkv-tab-link" role="tab" aria-selected="false" aria-controls="tab-shipping" tabindex="-1" onclick="return mkvShowTab(this, 'tab-shipping');"><i class="hgi-stroke hgi-truck-delivery" aria-hidden="true"></i> <?php echo esc_html(mkv__('Vận chuyển')); ?></button>
            <button type="button" id="mkv-tab-ai-trigger" class="mkv-tab-link" role="tab" aria-selected="false" aria-controls="tab-ai" tabindex="-1" onclick="return mkvShowTab(this, 'tab-ai');"><i class="hgi-stroke hgi-ai-chat-02" aria-hidden="true"></i> <?php echo esc_html(mkv__('Trợ lý AI')); ?></button>
        </div>

        <!-- Tab: Cửa hàng -->
        <div id="tab-store" class="mkv-tab-content" role="tabpanel" aria-labelledby="mkv-tab-store-trigger">
            <div class="mkv-card" style="max-width:800px; padding: 24px;">
                <h3 class="mkv-card-title" style="margin-bottom: 20px;"><i class="hgi-stroke hgi-store-01"></i> <?php echo esc_html(mkv__('Thông tin cửa hàng')); ?></h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <div class="mkv-form-group">
                        <label for="mkv-store-name" class="mkv-label"><?php echo esc_html(mkv__('Tên cửa hàng')); ?></label>
                        <input type="text" id="mkv-store-name" name="mkv_store_name" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_store_name','Mini KiotViet')); ?>">
                    </div>
                    <div class="mkv-form-group">
                        <label for="mkv-store-phone" class="mkv-label"><?php echo esc_html(mkv__('Số điện thoại')); ?></label>
                        <input type="text" id="mkv-store-phone" name="mkv_store_phone" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_store_phone','')); ?>">
                    </div>
                    <div class="mkv-form-group">
                        <label for="mkv-store-email" class="mkv-label"><?php echo esc_html(mkv__('Email liên hệ')); ?></label>
                        <input type="email" id="mkv-store-email" name="mkv_store_email" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_store_email','')); ?>">
                    </div>
                    <div class="mkv-form-group">
                        <label for="mkv-store-logo" class="mkv-label"><?php echo esc_html(mkv__('URL Logo')); ?></label>
                        <input type="text" id="mkv-store-logo" name="mkv_store_logo" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_store_logo','')); ?>" placeholder="https://...">
                    </div>
                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <label for="mkv-store-address" class="mkv-label"><?php echo esc_html(mkv__('Địa chỉ cửa hàng')); ?></label>
                        <textarea id="mkv-store-address" name="mkv_store_address" class="mkv-textarea" style="width: 100%;" rows="2"><?php echo esc_textarea(get_option('mkv_store_address','')); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Tài chính -->
        <div id="tab-finance" class="mkv-tab-content" role="tabpanel" aria-labelledby="mkv-tab-finance-trigger" style="display:none;">
            <div class="mkv-card" style="max-width:800px; padding: 24px;">
                <h3 class="mkv-card-title" style="margin-bottom: 20px;"><i class="hgi-stroke hgi-money-bag-02"></i> <?php echo esc_html(mkv__('Cấu hình tiền tệ & thuế')); ?></h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <div class="mkv-form-group">
                        <label for="mkv-store-currency" class="mkv-label"><?php echo esc_html(mkv__('Tiền tệ')); ?></label>
                        <select id="mkv-store-currency" name="mkv_store_currency" class="mkv-select" style="width: 100%;">
                            <option value="VND" <?php selected(get_option('mkv_store_currency','VND'),'VND'); ?>><?php echo esc_html(mkv__('VNĐ — Việt Nam Đồng')); ?></option>
                            <option value="USD" <?php selected(get_option('mkv_store_currency','VND'),'USD'); ?>><?php echo esc_html(mkv__('USD — Đô la Mỹ')); ?></option>
                        </select>
                    </div>
                    <div class="mkv-form-group">
                        <label for="mkv-store-vat" class="mkv-label"><?php echo esc_html(mkv__('Thuế VAT mặc định (%)')); ?></label>
                        <input type="number" id="mkv-store-vat" name="mkv_store_vat" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_store_vat','8')); ?>" min="0" max="100" step="0.5">
                    </div>
                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <label for="mkv-vat-included" class="mkv-label"><?php echo esc_html(mkv__('Cách tính VAT')); ?></label>
                        <select id="mkv-vat-included" name="mkv_vat_included" class="mkv-select" style="width: 100%; max-width: 400px;">
                            <option value="0" <?php selected(get_option('mkv_vat_included',0),0); ?>><?php echo esc_html(mkv__('Cộng thêm vào giá bán')); ?></option>
                            <option value="1" <?php selected(get_option('mkv_vat_included',0),1); ?>><?php echo esc_html(mkv__('Giá bán đã bao gồm VAT')); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Chính sách -->
        <div id="tab-policy" class="mkv-tab-content" role="tabpanel" aria-labelledby="mkv-tab-policy-trigger" style="display:none;">
            <div class="mkv-card" style="max-width:800px; padding: 24px;">
                <h3 class="mkv-card-title" style="margin-bottom: 20px;"><i class="hgi-stroke hgi-book-01"></i> <?php echo esc_html(mkv__('Chính sách bán hàng')); ?></h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <div class="mkv-form-group">
                        <label for="mkv-points-rate" class="mkv-label"><?php echo esc_html(mkv__('Tỷ lệ quy đổi điểm thưởng')); ?></label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="number" id="mkv-points-rate" name="mkv_points_rate" class="mkv-input" value="<?php echo esc_attr(get_option('mkv_points_rate',100)); ?>" min="1" style="width:130px;">
                            <span style="color:var(--mkv-text-muted); font-weight: 500;"><?php echo esc_html(mkv__('VNĐ = 1 điểm')); ?></span>
                        </div>
                    </div>
                    <div class="mkv-form-group">
                        <label for="mkv-point-value" class="mkv-label"><?php echo esc_html(mkv__('Giá trị quy đổi (Khi thanh toán)')); ?></label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="color:var(--mkv-text-muted); font-weight: 500;"><?php echo esc_html(mkv__('1 điểm = ')); ?></span>
                            <input type="number" id="mkv-point-value" name="mkv_point_value" class="mkv-input" value="<?php echo esc_attr(get_option('mkv_point_value',1)); ?>" min="1" style="width:100px;">
                            <span style="color:var(--mkv-text-muted); font-weight: 500;"><?php echo esc_html(mkv__('VNĐ')); ?></span>
                        </div>
                    </div>
                    <div class="mkv-form-group">
                        <label for="mkv-min-stock-threshold" class="mkv-label"><?php echo esc_html(mkv__('Ngưỡng tồn kho cảnh báo (mặc định)')); ?></label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="number" id="mkv-min-stock-threshold" name="mkv_min_stock_threshold" class="mkv-input" value="<?php echo esc_attr(get_option('mkv_min_stock_threshold',5)); ?>" min="0" style="width:130px;">
                            <span style="color:var(--mkv-text-muted); font-weight: 500;"><?php echo esc_html(mkv__('sản phẩm')); ?></span>
                        </div>
                    </div>
                    <div class="mkv-form-group">
                        <label for="mkv-audit-retention-days" class="mkv-label"><?php echo esc_html(mkv__('Thời gian lưu trữ nhật ký kiểm toán')); ?></label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="number" id="mkv-audit-retention-days" name="mkv_audit_retention_days" class="mkv-input" value="<?php echo esc_attr(get_option('mkv_audit_retention_days', 365)); ?>" min="30" max="3650" style="width:130px;">
                            <span style="color:var(--mkv-text-muted); font-weight: 500;"><?php echo esc_html(mkv__('ngày (30 - 3650)')); ?></span>
                        </div>
                        <small style="color:#64748b; font-size:12px; display:block; margin-top:4px;"><?php echo esc_html(mkv__('Nhật ký cũ hơn sẽ tự động dọn dẹp hàng ngày.')); ?></small>
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
        <div id="tab-payment" class="mkv-tab-content" role="tabpanel" aria-labelledby="mkv-tab-payment-trigger" style="display:none;">
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
                            <label for="mkv_bank_select" class="mkv-label"><?php echo esc_html(mkv__('Chọn ngân hàng')); ?></label>
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
                            <label for="mkv_bank_id" class="mkv-label"><?php echo esc_html(mkv__('Mã ngân hàng (NAPAS Code)')); ?></label>
                            <input type="text" id="mkv_bank_id" name="mkv_bank_id" class="mkv-input" value="<?php echo esc_attr($current_bank); ?>" placeholder="Ví dụ: VCB, MB, TCB..." oninput="updateSettingsQrPreview();">
                        </div>
                        <div class="mkv-form-group">
                            <label for="mkv_bank_account" class="mkv-label"><?php echo esc_html(mkv__('Số tài khoản ngân hàng')); ?></label>
                            <input type="text" id="mkv_bank_account" name="mkv_bank_account" class="mkv-input" value="<?php echo esc_attr($current_acc); ?>" placeholder="Ví dụ: 0123456789" oninput="updateSettingsQrPreview();">
                        </div>
                        <div class="mkv-form-group">
                            <label for="mkv_bank_name" class="mkv-label"><?php echo esc_html(mkv__('Tên chủ tài khoản (Viết IN HOA không dấu)')); ?></label>
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
        <div id="tab-receipt" class="mkv-tab-content" role="tabpanel" aria-labelledby="mkv-tab-receipt-trigger" style="display:none;">
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
        <div id="tab-shipping" class="mkv-tab-content" role="tabpanel" aria-labelledby="mkv-tab-shipping-trigger" style="display:none;">
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
                        <label for="mkv-shipping-provider" class="mkv-label"><?php echo esc_html(mkv__('Đối tác vận chuyển')); ?></label>
                        <select id="mkv-shipping-provider" name="mkv_shipping_provider" class="mkv-select" style="width: 100%;">
                            <option value="ghtk" <?php selected(get_option('mkv_shipping_provider','ghtk'),'ghtk'); ?>><?php echo esc_html(mkv__('Giao Hàng Tiết Kiệm (GHTK)')); ?></option>
                            <option value="ghn" <?php selected(get_option('mkv_shipping_provider','ghtk'),'ghn'); ?>><?php echo esc_html(mkv__('Giao Hàng Nhanh (GHN)')); ?></option>
                        </select>
                    </div>

                    <?php
                    $has_shipping_token = (string) get_option('mkv_shipping_token', '') !== '';
                    $has_webhook_secret = (string) get_option('mkv_webhook_secret', '') !== '';
                    ?>

                    <?php if (!$has_webhook_secret): ?>
                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <div class="mkv-alert-security-warning" style="background:#fffbeb; border:1px solid #fde68a; border-left:4px solid #f59e0b; padding:12px 16px; border-radius:6px; color:#92400e; font-size:13px; line-height:1.5;">
                            <strong><i class="hgi-stroke hgi-alert-circle"></i> <?php echo esc_html(mkv__('Cảnh báo bảo mật')); ?>:</strong>
                            <?php echo esc_html(mkv__('Webhook Secret chưa được cấu hình. Các webhook từ đối tác vận chuyển gửi tới hệ thống sẽ không có chữ ký xác thực. Vui lòng tạo Secret để bảo vệ an toàn cho hệ thống.')); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mkv-form-group">
                        <label for="mkv-shipping-token" class="mkv-label">
                            <?php echo esc_html(mkv__('API Token / Client Secret')); ?>
                            <?php if ($has_shipping_token): ?>
                                <span style="color:#16a34a; font-size:12px; margin-left:6px; font-weight:normal;">✓ <?php echo esc_html(mkv__('Đã cấu hình')); ?></span>
                            <?php else: ?>
                                <span style="color:#dc2626; font-size:12px; margin-left:6px; font-weight:normal;">✗ <?php echo esc_html(mkv__('Chưa cấu hình')); ?></span>
                            <?php endif; ?>
                        </label>
                        <input type="password" id="mkv-shipping-token" name="mkv_shipping_token" class="mkv-input" style="width: 100%;" value="" autocomplete="new-password" placeholder="<?php echo $has_shipping_token ? esc_attr('•••••••••••••••• (Để trống nếu không đổi)') : esc_attr(mkv__('Nhập API token...')); ?>">
                        <small style="color:#64748b; font-size:12px;"><?php echo esc_html(mkv__('Mã bảo mật lưu trữ server-side, không hiển thị trực tiếp.')); ?></small>
                    </div>

                    <div class="mkv-form-group">
                        <label for="mkv_webhook_secret_input" class="mkv-label">
                            <?php echo esc_html(mkv__('Webhook Secret')); ?>
                            <?php if ($has_webhook_secret): ?>
                                <span style="color:#16a34a; font-size:12px; margin-left:6px; font-weight:normal;">✓ <?php echo esc_html(mkv__('Đã cấu hình')); ?></span>
                            <?php else: ?>
                                <span style="color:#dc2626; font-size:12px; margin-left:6px; font-weight:normal;">✗ <?php echo esc_html(mkv__('Chưa cấu hình')); ?></span>
                            <?php endif; ?>
                        </label>
                        <div style="display:flex; gap:8px;">
                            <input type="password" id="mkv_webhook_secret_input" name="mkv_webhook_secret" class="mkv-input" style="flex:1;" value="" autocomplete="new-password" placeholder="<?php echo $has_webhook_secret ? esc_attr('•••••••••••••••• (Để trống nếu không đổi)') : esc_attr(mkv__('Nhập hoặc bấm Tạo ngẫu nhiên...')); ?>">
                            <button type="button" class="mkv-btn mkv-btn-outline" id="btn-generate-webhook-secret" style="white-space:nowrap; font-size:13px; padding:0 12px; height:38px; cursor:pointer;">
                                <i class="hgi-stroke hgi-key-01"></i> <?php echo esc_html(mkv__('Tạo Secret ngẫu nhiên')); ?>
                            </button>
                        </div>
                        <small style="color:#64748b; font-size:12px;"><?php echo esc_html(mkv__('Dùng để xác thực chữ ký Webhook từ GHTK / GHN.')); ?></small>
                    </div>

                    <?php
                    $ip_allowlist_enabled = (int) get_option('mkv_webhook_ip_allowlist_enabled', 0);
                    $ip_allowlist_val = (string) get_option('mkv_webhook_ip_allowlist', '');
                    $ip_allowlist_empty = empty(trim($ip_allowlist_val));
                    ?>

                    <div class="mkv-form-group" style="grid-column:span 2; border-top:1px dashed var(--mkv-border); padding-top:16px;">
                        <label class="mkv-label" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="hidden" name="mkv_webhook_ip_allowlist_enabled" value="0">
                            <input type="checkbox" name="mkv_webhook_ip_allowlist_enabled" value="1" <?php checked($ip_allowlist_enabled, 1); ?>>
                            <?php echo esc_html(mkv__('Kích hoạt danh sách IP cho phép nhận Webhook (IP Allowlist)')); ?>
                        </label>
                        <small style="color:#64748b; font-size:12px; display:block; margin-top:4px;">
                            <?php echo esc_html(mkv__('Tùy chọn. Để trống hoặc vô hiệu hóa để nhận webhook sau khi xác thực secret.')); ?>
                        </small>
                    </div>

                    <?php if ($ip_allowlist_enabled && $ip_allowlist_empty): ?>
                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <div class="mkv-alert-security-warning" style="background:#fef2f2; border:1px solid #fecaca; border-left:4px solid #ef4444; padding:12px 16px; border-radius:6px; color:#991b1b; font-size:13px; line-height:1.5;">
                            <strong><i class="hgi-stroke hgi-alert-circle"></i> <?php echo esc_html(mkv__('Cảnh báo an ninh')); ?>:</strong>
                            <?php echo esc_html(mkv__('Danh sách IP cho phép đang bật nhưng chưa có địa chỉ IP nào được nhập. Mọi webhook gửi tới sẽ bị từ chối (403 Forbidden).')); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <label for="mkv-webhook-ip-allowlist" class="mkv-label"><?php echo esc_html(mkv__('Danh sách địa chỉ IP / Dải mạng CIDR cho phép')); ?></label>
                        <textarea id="mkv-webhook-ip-allowlist" name="mkv_webhook_ip_allowlist" class="mkv-input" style="width:100%; height:75px; font-family:monospace; font-size:13px;" placeholder="<?php echo esc_attr("Ví dụ:\n192.168.1.10\n10.0.0.0/24"); ?>"><?php echo esc_textarea($ip_allowlist_val); ?></textarea>
                        <small style="color:#64748b; font-size:12px;"><?php echo esc_html(mkv__('Nhập mỗi địa chỉ IP hoặc dải mạng CIDR (IPv4) trên một dòng.')); ?></small>
                    </div>

                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <label for="mkv-shipping-address" class="mkv-label"><?php echo esc_html(mkv__('Địa chỉ lấy hàng mặc định')); ?></label>
                        <input type="text" id="mkv-shipping-address" name="mkv_shipping_address" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_shipping_address','')); ?>" placeholder="<?php echo esc_attr(mkv__('Số nhà, tên đường...')); ?>">
                    </div>
                    
                    <div class="mkv-form-group">
                        <label for="mkv-shipping-province" class="mkv-label"><?php echo esc_html(mkv__('Tỉnh / Thành phố')); ?></label>
                        <input type="text" id="mkv-shipping-province" name="mkv_shipping_province" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_shipping_province','')); ?>">
                    </div>
                    
                    <div class="mkv-form-group">
                        <label for="mkv-shipping-district" class="mkv-label"><?php echo esc_html(mkv__('Quận / Huyện')); ?></label>
                        <input type="text" id="mkv-shipping-district" name="mkv_shipping_district" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_shipping_district','')); ?>">
                    </div>

                    <div class="mkv-form-group">
                        <label for="mkv-shipping-ward" class="mkv-label"><?php echo esc_html(mkv__('Phường / Xã')); ?></label>
                        <input type="text" id="mkv-shipping-ward" name="mkv_shipping_ward" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_shipping_ward','')); ?>">
                    </div>
                    
                    <div class="mkv-form-group">
                        <label for="mkv-shipping-phone" class="mkv-label"><?php echo esc_html(mkv__('Số điện thoại kho lấy hàng')); ?></label>
                        <input type="text" id="mkv-shipping-phone" name="mkv_shipping_phone" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_shipping_phone','')); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Trợ lý AI -->
        <div id="tab-ai" class="mkv-tab-content" role="tabpanel" aria-labelledby="mkv-tab-ai-trigger" style="display:none;">
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
                    
                    <?php
                    $has_gemini_key = (string) get_option('mkv_gemini_api_key', '') !== '';
                    ?>
                    <div class="mkv-form-group" style="grid-column:span 2;">
                        <label for="mkv-gemini-api-key" class="mkv-label">
                            <?php echo esc_html(mkv__('Google Gemini API Key')); ?>
                            <?php if ($has_gemini_key): ?>
                                <span style="color:#16a34a; font-size:12px; margin-left:6px; font-weight:normal;">✓ <?php echo esc_html(mkv__('Đã cấu hình API key')); ?></span>
                            <?php else: ?>
                                <span style="color:#dc2626; font-size:12px; margin-left:6px; font-weight:normal;">✗ <?php echo esc_html(mkv__('Chưa cấu hình')); ?></span>
                            <?php endif; ?>
                        </label>
                        <textarea id="mkv-gemini-api-key" name="mkv_gemini_api_key" class="mkv-input" style="width: 100%; height: 60px; font-family: monospace; font-size: 13px;" autocomplete="new-password" placeholder="<?php echo $has_gemini_key ? esc_attr('•••••••••••••••• (API key đã lưu server-side - để trống nếu không đổi)') : esc_attr('AIzaSy... (Hỗ trợ nhập nhiều key, mỗi key 1 dòng)'); ?>"></textarea>
                        <small style="color:#64748b; font-size:12px;"><?php echo esc_html(mkv__('API key được lưu trữ an toàn server-side, không xuất ra HTML.')); ?></small>
                    </div>

                    <div class="mkv-form-group">
                        <label for="mkv-ai-assistant-name" class="mkv-label"><?php echo esc_html(mkv__('Tên Trợ lý')); ?></label>
                        <input type="text" id="mkv-ai-assistant-name" name="mkv_ai_assistant_name" class="mkv-input" style="width: 100%;" value="<?php echo esc_attr(get_option('mkv_ai_assistant_name','KiotViet Copilot')); ?>">
                    </div>

                    <div class="mkv-form-group">
                        <label for="mkv-gemini-model" class="mkv-label"><?php echo esc_html(mkv__('Mô hình Gemini (Model)')); ?></label>
                        <?php $curr_model = get_option('mkv_gemini_model', 'models/gemini-3.5-flash-lite'); ?>
                        <select id="mkv-gemini-model" name="mkv_gemini_model" class="mkv-input" style="width: 100%;">
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
    document.querySelectorAll('.mkv-tab-content').forEach(function(t) {
        t.style.display = 'none';
        t.hidden = true;
    });
    document.querySelectorAll('.mkv-tab-link').forEach(function(t) {
        t.classList.remove('active');
        t.setAttribute('aria-selected', 'false');
        t.setAttribute('tabindex', '-1');
    });
    document.getElementById(tabId).style.display = 'block';
    document.getElementById(tabId).hidden = false;
    el.classList.add('active');
    el.setAttribute('aria-selected', 'true');
    el.setAttribute('tabindex', '0');
    return false;
}

document.querySelectorAll('.mkv-tab-link').forEach(function(tab) {
    tab.addEventListener('keydown', function(event) {
        var tabs = Array.from(document.querySelectorAll('.mkv-tab-link'));
        var currentIndex = tabs.indexOf(event.currentTarget);
        var nextIndex = currentIndex;

        if (event.key === 'ArrowRight' || event.key === 'ArrowDown') nextIndex = (currentIndex + 1) % tabs.length;
        if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') nextIndex = (currentIndex - 1 + tabs.length) % tabs.length;
        if (event.key === 'Home') nextIndex = 0;
        if (event.key === 'End') nextIndex = tabs.length - 1;
        if (nextIndex === currentIndex) return;

        event.preventDefault();
        var nextTab = tabs[nextIndex];
        mkvShowTab(nextTab, nextTab.getAttribute('aria-controls'));
        nextTab.focus();
    });
});

jQuery(document).ready(function($) {
    $('#btn-generate-webhook-secret').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var origText = $btn.html();
        $btn.prop('disabled', true).text('Đang tạo...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mkv_generate_webhook_secret',
                nonce: '<?php echo wp_create_nonce("mkv_settings_nonce"); ?>'
            },
            success: function(res) {
                $btn.prop('disabled', false).html(origText);
                if (res && res.success && res.data && res.data.secret) {
                    var $inp = $('#mkv_webhook_secret_input');
                    $inp.attr('type', 'text').val(res.data.secret);
                    alert('Đã tạo Secret mới ngẫu nhiên! Vui lòng nhấn "Lưu thiết lập" để lưu vào hệ thống.');
                } else {
                    alert((res && res.data && res.data.message) ? res.data.message : 'Không thể tạo Secret.');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html(origText);
                alert('Không thể kết nối máy chủ để tạo Secret.');
            }
        });
    });
});
</script>

<script>document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Thiết lập')); ?>');</script>
<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>
