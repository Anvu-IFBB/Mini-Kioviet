<?php
/**
 * View: Mini KiotViet Support & Help Center Modal
 * Cung cấp tài liệu hướng dẫn chuyên sâu cho 3 mục con Hàng hóa (Sản phẩm, Danh mục, Kho & Tồn),
 * Hotline kỹ thuật, Phím tắt hệ thống và tích hợp gọi nhanh Trợ lý AI Copilot.
 */
if (!defined('ABSPATH')) exit;

$store_name = get_option('mkv_store_name', 'Mini KiotViet');
$store_phone = get_option('mkv_store_phone', '1900 6522');
?>
<!-- Support Center Overlay -->
<div class="mkv-support-overlay" id="mkv-support-overlay" onclick="mkvCloseSupportModal()"></div>

<!-- Support Center Modal -->
<div class="mkv-support-modal" id="mkv-support-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="mkv-support-modal-title" tabindex="-1">
    <div class="mkv-support-header">
        <div class="mkv-support-header-left">
            <div class="mkv-support-icon-badge">
                <i class="hgi-stroke hgi-help-circle"></i>
            </div>
            <div>
                <h3 id="mkv-support-modal-title"><?php echo esc_html(mkv__('Trung Tâm Trợ Giúp & Hỗ Trợ')); ?></h3>
                <p><?php echo esc_html(mkv__('Tài liệu hướng dẫn nghiệp vụ & trợ giúp kỹ thuật')); ?> - <?php echo esc_html($store_name); ?></p>
            </div>
        </div>
        <button type="button" class="mkv-support-close" onclick="mkvCloseSupportModal()" title="<?php echo esc_attr(mkv__('Đóng')); ?>">
            <i class="hgi-stroke hgi-cancel-01"></i>
        </button>
    </div>

    <!-- Navigation Tabs inside Modal -->
    <div class="mkv-support-tabs">
        <button type="button" class="mkv-support-tab-btn active" data-tab="goods-guide" onclick="mkvSwitchSupportTab('goods-guide', this)">
            <i class="hgi-stroke hgi-package-open"></i> <?php echo esc_html(mkv__('Hướng dẫn 3 mục Hàng Hóa')); ?>
        </button>
        <button type="button" class="mkv-support-tab-btn" data-tab="hotline-contact" onclick="mkvSwitchSupportTab('hotline-contact', this)">
            <i class="hgi-stroke hgi-call-02"></i> <?php echo esc_html(mkv__('Tổng đài & Liên hệ')); ?>
        </button>
        <button type="button" class="mkv-support-tab-btn" data-tab="shortcuts-guide" onclick="mkvSwitchSupportTab('shortcuts-guide', this)">
            <i class="hgi-stroke hgi-keyboard"></i> <?php echo esc_html(mkv__('Phím tắt thao tác')); ?>
        </button>
    </div>

    <div class="mkv-support-body">
        <!-- TAB 1: 3 MỤC CON HÀNG HÓA -->
        <div class="mkv-support-tab-content active" id="mkv-support-tab-goods-guide">
            <div class="mkv-guide-card">
                <div class="mkv-guide-card-header">
                    <span class="mkv-guide-num">1</span>
                    <div class="mkv-guide-title">
                        <h4><?php echo esc_html(mkv__('Mục 1: Quản lý Sản phẩm')); ?></h4>
                        <span><?php echo esc_html(mkv__('Thêm mới, sửa thông tin, giá vốn, giá bán, barcode và import/export')); ?></span>
                    </div>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=mkv_product')); ?>" class="mkv-guide-link">
                        <?php echo esc_html(mkv__('Đến trang')); ?> <i class="hgi-stroke hgi-arrow-right-01"></i>
                    </a>
                </div>
                <div class="mkv-guide-card-body">
                    <ul>
                        <li><strong><?php echo esc_html(mkv__('Thêm sản phẩm mới')); ?>:</strong> <?php echo esc_html(mkv__('Bấm nút "Thêm sản phẩm mới". Nhập Tên hàng, Mã SKU (bắt buộc, duy nhất) và Mã vạch Barcode.')); ?></li>
                        <li><strong><?php echo esc_html(mkv__('Tự động sinh Barcode')); ?>:</strong> <?php echo esc_html(mkv__('Nếu chưa có mã vạch riêng, bấm "Sinh mã", hệ thống sẽ tạo mã vạch chuẩn kèm vạch quét SVG để in tem dán.')); ?></li>
                        <li><strong><?php echo esc_html(mkv__('Quản lý giá')); ?>:</strong> <?php echo esc_html(mkv__('Nhập Giá bán và Giá vốn (chỉ tài khoản được phân quyền mới có thể xem/sửa giá vốn). Giá bán không được nhỏ hơn giá vốn.')); ?></li>
                        <li><strong><?php echo esc_html(mkv__('Tồn kho & Định mức')); ?>:</strong> <?php echo esc_html(mkv__('Khai báo Tồn kho ban đầu và Ngưỡng cảnh báo hết hàng (mặc định 5). Hệ thống tự động báo động khi tồn kho xuống thấp.')); ?></li>
                        <li><strong><?php echo esc_html(mkv__('Xuất/Nhập CSV')); ?>:</strong> <?php echo esc_html(mkv__('Dễ dàng sao lưu dữ liệu ra file CSV hoặc tải hàng loạt sản phẩm từ file Excel lên hệ thống chỉ với 1 cú nhấp chuột.')); ?></li>
                    </ul>
                </div>
            </div>

            <div class="mkv-guide-card">
                <div class="mkv-guide-card-header">
                    <span class="mkv-guide-num">2</span>
                    <div class="mkv-guide-title">
                        <h4><?php echo esc_html(mkv__('Mục 2: Quản lý Danh mục hàng')); ?></h4>
                        <span><?php echo esc_html(mkv__('Phân loại nhóm hàng, cấu trúc danh mục cha - con trực quan')); ?></span>
                    </div>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mkv-categories')); ?>" class="mkv-guide-link">
                        <?php echo esc_html(mkv__('Đến trang')); ?> <i class="hgi-stroke hgi-arrow-right-01"></i>
                    </a>
                </div>
                <div class="mkv-guide-card-body">
                    <ul>
                        <li><strong><?php echo esc_html(mkv__('Tạo nhóm hàng')); ?>:</strong> <?php echo esc_html(mkv__('Nhập tên danh mục (ví dụ: Áo sơ mi, Quần Jean, Giày dép...). Bấm "Thêm danh mục" để lưu ngay lập tức.')); ?></li>
                        <li><strong><?php echo esc_html(mkv__('Phân cấp cha - con')); ?>:</strong> <?php echo esc_html(mkv__('Chọn "Danh mục cha" khi thêm để tạo cấu trúc cây thư mục phân cấp rõ ràng (ví dụ: Thời trang nam -> Áo thun).')); ?></li>
                        <li><strong><?php echo esc_html(mkv__('Đồng bộ trên POS')); ?>:</strong> <?php echo esc_html(mkv__('Mỗi danh mục được tạo sẽ lập tức hiển thị thành tab chọn nhanh trên màn hình Bán hàng (POS) giúp thu ngân tìm hàng trong tích tắc.')); ?></li>
                    </ul>
                </div>
            </div>

            <div class="mkv-guide-card">
                <div class="mkv-guide-card-header">
                    <span class="mkv-guide-num">3</span>
                    <div class="mkv-guide-title">
                        <h4><?php echo esc_html(mkv__('Mục 3: Quản lý Kho & Tồn')); ?></h4>
                        <span><?php echo esc_html(mkv__('Kiểm kho, cân bằng kho, điều chỉnh số lượng thực tế và theo dõi thẻ kho')); ?></span>
                    </div>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mkv-inventory')); ?>" class="mkv-guide-link">
                        <?php echo esc_html(mkv__('Đến trang')); ?> <i class="hgi-stroke hgi-arrow-right-01"></i>
                    </a>
                </div>
                <div class="mkv-guide-card-body">
                    <ul>
                        <li><strong><?php echo esc_html(mkv__('Kiểm kho định kỳ')); ?>:</strong> <?php echo esc_html(mkv__('So sánh số lượng tồn kho trên phần mềm với số lượng kiểm đếm thực tế tại cửa hàng.')); ?></li>
                        <li><strong><?php echo esc_html(mkv__('Cân bằng kho')); ?>:</strong> <?php echo esc_html(mkv__('Khi có chênh lệch thừa/thiếu, nhập số lượng thực tế và bấm Cân bằng kho. Hệ thống tự động ghi nhận nhật ký điều chỉnh.')); ?></li>
                        <li><strong><?php echo esc_html(mkv__('Cảnh báo hết hàng')); ?>:</strong> <?php echo esc_html(mkv__('Các mặt hàng có tồn kho <= ngưỡng định mức sẽ được gắn nhãn đỏ cảnh báo để quản lý kịp thời nhập thêm hàng.')); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- TAB 2: HOTLINE & LIÊN HỆ -->
        <div class="mkv-support-tab-content" id="mkv-support-tab-hotline-contact" style="display:none;">
            <div class="mkv-support-contact-grid">
                <div class="mkv-contact-card">
                    <div class="mkv-contact-icon blue"><i class="hgi-stroke hgi-call-02"></i></div>
                    <h5><?php echo esc_html(mkv__('Tổng đài hỗ trợ kỹ thuật')); ?></h5>
                    <p class="phone-highlight"><?php echo esc_html($store_phone); ?></p>
                    <span class="mkv-contact-note"><?php echo esc_html(mkv__('Từ 8:00 đến 22:00 (Tất cả các ngày trong tuần)')); ?></span>
                </div>

                <div class="mkv-contact-card">
                    <div class="mkv-contact-icon green"><i class="hgi-stroke hgi-message-01"></i></div>
                    <h5><?php echo esc_html(mkv__('Kênh Zalo CSKH')); ?></h5>
                    <p class="phone-highlight">0909.123.456</p>
                    <span class="mkv-contact-note"><?php echo esc_html(mkv__('Hỗ trợ tư vấn giải pháp & phản hồi nhanh')); ?></span>
                </div>

                <div class="mkv-contact-card">
                    <div class="mkv-contact-icon purple"><i class="hgi-stroke hgi-mail-01"></i></div>
                    <h5><?php echo esc_html(mkv__('Hòm thư hỗ trợ')); ?></h5>
                    <p class="email-highlight">hotro@minikiotviet.vn</p>
                    <span class="mkv-contact-note"><?php echo esc_html(mkv__('Tiếp nhận yêu cầu nâng cấp & xử lý lỗi 24/7')); ?></span>
                </div>
            </div>

            <!-- Copilot Promo banner inside Support -->
            <div class="mkv-support-copilot-banner">
                <div class="mkv-copilot-banner-info">
                    <div class="mkv-copilot-avatar"><i class="hgi-stroke hgi-ai-chat-02"></i></div>
                    <div>
                        <h4><?php echo esc_html(mkv__('Cần giải đáp thắc mắc ngay lập tức?')); ?></h4>
                        <p><?php echo esc_html(mkv__('Trợ lý AI Copilot có thể tra cứu tồn kho, doanh thu, kiểm tra đơn hàng và tư vấn trực tiếp 24/7.')); ?></p>
                    </div>
                </div>
                <button type="button" class="mkv-btn mkv-btn-primary" onclick="mkvCloseSupportModal(); window.mkvOpenAIChat && window.mkvOpenAIChat();">
                    <i class="hgi-stroke hgi-ai-chat-02"></i> <?php echo esc_html(mkv__('Hỏi Trợ lý AI ngay')); ?>
                </button>
            </div>
        </div>

        <!-- TAB 3: PHÍM TẮT -->
        <div class="mkv-support-tab-content" id="mkv-support-tab-shortcuts-guide" style="display:none;">
            <div class="mkv-shortcuts-table-wrap">
                <table class="mkv-shortcuts-table">
                    <thead>
                        <tr>
                            <th style="width:150px;"><?php echo esc_html(mkv__('Phím tắt')); ?></th>
                            <th><?php echo esc_html(mkv__('Chức năng')); ?></th>
                            <th><?php echo esc_html(mkv__('Phạm vi áp dụng')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><kbd>F1</kbd></td>
                            <td><?php echo esc_html(mkv__('Mở nhanh ô tìm kiếm sản phẩm hoặc mã đơn')); ?></td>
                            <td><?php echo esc_html(mkv__('Bán hàng (POS) & Danh sách')); ?></td>
                        </tr>
                        <tr>
                            <td><kbd>F2</kbd></td>
                            <td><?php echo esc_html(mkv__('Mở màn hình Thêm sản phẩm mới')); ?></td>
                            <td><?php echo esc_html(mkv__('Quản lý Hàng hóa')); ?></td>
                        </tr>
                        <tr>
                            <td><kbd>F9</kbd></td>
                            <td><?php echo esc_html(mkv__('Mở thanh toán đơn hàng POS')); ?></td>
                            <td><?php echo esc_html(mkv__('Màn hình POS Bán hàng')); ?></td>
                        </tr>
                        <tr>
                            <td><kbd>ESC</kbd></td>
                            <td><?php echo esc_html(mkv__('Đóng Modal hỗ trợ / Đóng cửa sổ đang mở')); ?></td>
                            <td><?php echo esc_html(mkv__('Toàn hệ thống')); ?></td>
                        </tr>
                        <tr>
                            <td><kbd>Enter</kbd></td>
                            <td><?php echo esc_html(mkv__('Gửi câu hỏi cho Trợ lý AI Copilot')); ?></td>
                            <td><?php echo esc_html(mkv__('Cửa sổ AI Assistant')); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mkv-support-footer">
        <div class="mkv-support-footer-left">
            <span><i class="hgi-stroke hgi-information-circle"></i> <?php echo esc_html(mkv__('Phiên bản hệ thống: Mini KiotViet v3.0')); ?></span>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" class="mkv-btn mkv-btn-secondary" onclick="mkvCloseSupportModal()">
                <?php echo esc_html(mkv__('Đóng')); ?>
            </button>
            <button type="button" class="mkv-btn mkv-btn-primary" onclick="mkvCloseSupportModal(); window.mkvOpenAIChat && window.mkvOpenAIChat();">
                <i class="hgi-stroke hgi-ai-chat-02"></i> <?php echo esc_html(mkv__('Mở AI Copilot')); ?>
            </button>
        </div>
    </div>
</div>

<script>
function mkvOpenSupportModal() {
    var modal = document.getElementById('mkv-support-modal');
    var overlay = document.getElementById('mkv-support-overlay');
    if (modal && overlay) {
        window.mkvSupportReturnFocus = document.activeElement;
        modal.classList.add('open');
        overlay.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        var closeButton = modal.querySelector('.mkv-support-close');
        if (closeButton) closeButton.focus();
    }
}

function mkvCloseSupportModal() {
    var modal = document.getElementById('mkv-support-modal');
    var overlay = document.getElementById('mkv-support-overlay');
    if (modal && overlay) {
        modal.classList.remove('open');
        overlay.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        if (window.mkvSupportReturnFocus && typeof window.mkvSupportReturnFocus.focus === 'function') {
            window.mkvSupportReturnFocus.focus();
        }
        window.mkvSupportReturnFocus = null;
    }
}

function mkvSwitchSupportTab(tabId, btn) {
    var tabs = document.querySelectorAll('.mkv-support-tab-content');
    tabs.forEach(function(el) { el.style.display = 'none'; el.classList.remove('active'); });
    var target = document.getElementById('mkv-support-tab-' + tabId);
    if (target) {
        target.style.display = 'block';
        target.classList.add('active');
    }
    var buttons = document.querySelectorAll('.mkv-support-tab-btn');
    buttons.forEach(function(b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
}

// ESC Key closes modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('mkv-support-modal')?.classList.contains('open')) {
        mkvCloseSupportModal();
    }
});
</script>
