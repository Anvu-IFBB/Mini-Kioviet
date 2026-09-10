<?php
if (!defined('ABSPATH')) exit;

$current_page = $_GET['page'] ?? '';
$post_type    = $_GET['post_type'] ?? '';
$user         = wp_get_current_user();

// Active states
$is_dashboard     = ($current_page === 'mini-kiotviet');
$is_pos           = ($current_page === 'mkv-pos');
$is_orders        = ($current_page === 'mkv-orders');
$is_customers     = ($current_page === 'mkv-customers');
$is_categories    = ($current_page === 'mkv-categories');
$is_products      = ($post_type === 'mkv_product' || strpos($current_page, 'mkv_product') !== false);
$is_inventory     = ($current_page === 'mkv-inventory');
$is_purchases     = ($current_page === 'mkv-purchases');
$is_cashbook      = ($current_page === 'mkv-cashbook');
$is_employees     = ($current_page === 'mkv-employees');
$is_reports       = ($current_page === 'mkv-reports');
$is_notifications = ($current_page === 'mkv-notifications');
$is_settings      = ($current_page === 'mkv-settings');
$is_audit         = ($current_page === 'mkv-audit-logs');

// User info
$user_initial  = mb_strtoupper(mb_substr($user->display_name, 0, 1, 'UTF-8'), 'UTF-8');
$role_labels   = [
    'administrator' => mkv__('Quản trị viên'),
    'mkv_manager'   => mkv__('Cửa hàng trưởng'),
    'mkv_sales'     => mkv__('Nhân viên Sales'),
    'mkv_warehouse' => mkv__('Thủ kho'),
];
$display_role = mkv__('Người dùng');
foreach ($user->roles as $r) {
    if (isset($role_labels[$r])) { $display_role = $role_labels[$r]; break; }
}

// Notification count
$unread_count = class_exists('MKV_Notifications')
    ? MKV_Notifications::get_unread_count()
    : (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mkv_notifications WHERE is_read=0");
?>
<div class="mkv-app-layout">
    <!-- Topbar (White) -->
    <div class="mkv-topbar">
        <div class="mkv-topbar-left">
            <a href="<?php echo admin_url('admin.php?page=mini-kiotviet'); ?>" class="mkv-logo">
                <?php $store_logo = get_option('mkv_store_logo', ''); ?>
                <?php if ($store_logo): ?>
                    <img src="<?php echo esc_url($store_logo); ?>" alt="Logo" style="height:32px; width:auto; object-fit:contain;">
                <?php else: ?>
                    <span class="mkv-logo-text"><?php echo esc_html(get_option('mkv_store_name', 'Mini KiotViet')); ?></span>
                <?php endif; ?>
            </a>
            <span class="mkv-topbar-title" id="mkv-topbar-title"><?php echo esc_html(mkv__('Tổng quan kinh doanh')); ?></span>
        </div>
        <div class="mkv-topbar-right">
            <!-- Global AJAX vars & Language Switcher early guard -->
            <script>
            window.mkv_global_vars = window.mkv_global_vars || {
                ajax_url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
                nonce: '<?php echo esc_js(wp_create_nonce('mkv_global_nonce')); ?>'
            };
            window.mkvSetLang = window.mkvSetLang || function(lang, e) {
                if (e && typeof e.preventDefault === 'function') e.preventDefault();
                if (!lang || (lang !== 'vi' && lang !== 'en')) return;

                // Immediately synchronize client-side cookie and localStorage
                document.cookie = "mkv_lang=" + encodeURIComponent(lang) + "; path=/; max-age=31536000; SameSite=Lax";
                try {
                    localStorage.setItem('mkv_lang', lang);
                } catch (err) {}

                var ajaxUrl = (window.mkv_global_vars && window.mkv_global_vars.ajax_url) || (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
                var nonce = (window.mkv_global_vars && window.mkv_global_vars.nonce) || '';

                if (typeof jQuery !== 'undefined') {
                    jQuery.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: { 
                            action: 'mkv_switch_lang', 
                            lang: lang,
                            nonce: nonce 
                        },
                        complete: function() {
                            location.reload();
                        }
                    });
                } else {
                    var formData = new FormData();
                    formData.append('action', 'mkv_switch_lang');
                    formData.append('lang', lang);
                    formData.append('nonce', nonce);
                    fetch(ajaxUrl, { method: 'POST', body: formData })
                        .finally(function() {
                            location.reload();
                        });
                }
            };
            </script>
            <!-- Icons -->
            <script>
            window.mkvOpenSupportModal = window.mkvOpenSupportModal || function() {
                var modal = document.getElementById('mkv-support-modal');
                var overlay = document.getElementById('mkv-support-overlay');
                if (modal && overlay) {
                    modal.classList.add('open');
                    overlay.classList.add('show');
                }
            };
            window.mkvCloseSupportModal = window.mkvCloseSupportModal || function() {
                var modal = document.getElementById('mkv-support-modal');
                var overlay = document.getElementById('mkv-support-overlay');
                if (modal && overlay) {
                    modal.classList.remove('open');
                    overlay.classList.remove('show');
                }
            };
            window.mkvOpenAIChat = window.mkvOpenAIChat || function() {
                var d = document.getElementById('mkv-ai-drawer');
                var o = document.getElementById('mkv-ai-overlay');
                document.body.classList.add('mkv-ai-drawer-open');
                if (d) {
                    d.classList.add('open');
                    d.style.setProperty('right', '0', 'important');
                    d.style.setProperty('display', 'flex', 'important');
                    d.style.setProperty('visibility', 'visible', 'important');
                }
                if (o) {
                    o.classList.add('show');
                    o.style.setProperty('display', 'block', 'important');
                    o.style.setProperty('opacity', '1', 'important');
                    o.style.setProperty('visibility', 'visible', 'important');
                }
                var inp = document.getElementById('mkv-ai-input');
                if (inp) setTimeout(function() { inp.focus(); }, 300);
                if (typeof window.mkvOnOpenAIChatHook === 'function') {
                    window.mkvOnOpenAIChatHook();
                }
            };
            window.mkvCloseAIChat = window.mkvCloseAIChat || function() {
                var d = document.getElementById('mkv-ai-drawer');
                var o = document.getElementById('mkv-ai-overlay');
                document.body.classList.remove('mkv-ai-drawer-open');
                if (d) {
                    d.classList.remove('open');
                    d.style.setProperty('right', '-850px', 'important');
                    d.classList.remove('history-open');
                }
                if (o) {
                    o.classList.remove('show');
                    o.style.setProperty('display', 'none', 'important');
                    o.style.setProperty('opacity', '0', 'important');
                }
                var p = document.getElementById('mkv-ai-history-panel');
                if (p) p.classList.remove('open');
            };
            </script>
            <script>
            window.mkvToggleHeaderDropdown = function(trigger) {
                var owner = trigger.closest('.mkv-header-user-dropdown, .mkv-nav-dropdown');
                if (!owner) return false;
                var isOpen = owner.classList.toggle('is-open');
                trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                document.querySelectorAll('.mkv-header-user-dropdown.is-open, .mkv-nav-dropdown.is-open').forEach(function(other) {
                    if (other !== owner) {
                        other.classList.remove('is-open');
                        var otherTrigger = other.querySelector('.mkv-header-trigger, .mkv-nav-trigger');
                        if (otherTrigger) otherTrigger.setAttribute('aria-expanded', 'false');
                    }
                });
                return false;
            };
            document.addEventListener('click', function(event) {
                if (event.defaultPrevented) return;
                var trigger = (event.target && typeof event.target.closest === 'function') ? event.target.closest('.mkv-header-trigger, .mkv-nav-trigger') : null;
                if (trigger) {
                    var owner = trigger.closest('.mkv-header-user-dropdown, .mkv-nav-dropdown');
                    if (!owner) return;
                    var isOpen = owner.classList.toggle('is-open');
                    trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    if (isOpen) {
                        document.querySelectorAll('.mkv-header-user-dropdown.is-open, .mkv-nav-dropdown.is-open').forEach(function(other) {
                            if (other !== owner) {
                                other.classList.remove('is-open');
                                var otherTrigger = other.querySelector('.mkv-header-trigger, .mkv-nav-trigger');
                                if (otherTrigger) otherTrigger.setAttribute('aria-expanded', 'false');
                            }
                        });
                    }
                    return;
                }
                document.querySelectorAll('.mkv-header-user-dropdown.is-open, .mkv-nav-dropdown.is-open').forEach(function(owner) {
                    owner.classList.remove('is-open');
                    var ownerTrigger = owner.querySelector('.mkv-header-trigger, .mkv-nav-trigger');
                    if (ownerTrigger) ownerTrigger.setAttribute('aria-expanded', 'false');
                });
            });
            document.addEventListener('keydown', function(event) {
                if (event.key !== 'Escape') return;
                document.querySelectorAll('.mkv-header-user-dropdown.is-open, .mkv-nav-dropdown.is-open').forEach(function(owner) {
                    owner.classList.remove('is-open');
                    var trigger = owner.querySelector('.mkv-header-trigger, .mkv-nav-trigger');
                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'false');
                        trigger.focus();
                    }
                });
            });
            </script>
            <button type="button" id="mkv-dark-mode-toggle" class="mkv-header-icon mkv-header-action" title="<?php echo esc_attr(mkv__('Giao diện')); ?>" aria-label="<?php echo esc_attr(mkv__('Chuyển đổi giao diện')); ?>"><i class="hgi-stroke hgi-moon-02" aria-hidden="true"></i></button>
            <button type="button" class="mkv-header-icon mkv-header-action" id="mkv-topbar-btn-support" title="<?php echo esc_attr(mkv__('Hỗ trợ')); ?>" aria-label="<?php echo esc_attr(mkv__('Mở trung tâm hỗ trợ')); ?>" onclick="if(typeof window.mkvOpenSupportModal === 'function'){ window.mkvOpenSupportModal(this); } else if(typeof window.mkvOpenAIChat === 'function'){ window.mkvOpenAIChat(); }"><i class="hgi-stroke hgi-help-circle" aria-hidden="true"></i> <?php echo esc_html(mkv__('Hỗ trợ')); ?></button>
            <a href="#" class="mkv-header-icon" title="<?php echo esc_attr(mkv__('Góp ý')); ?>"><i class="hgi-stroke hgi-message-02"></i> <?php echo esc_html(mkv__('Góp ý')); ?></a>
            
            <!-- Language Switcher -->
            <div class="mkv-header-user-dropdown mkv-lang-switcher" style="margin-left: 10px; border-left: 1px solid #e2e8f0; padding-left: 15px;">
                <button type="button" id="mkv-lang-btn" class="mkv-header-user mkv-header-trigger" aria-haspopup="true" aria-expanded="false" aria-controls="mkv-language-menu" onclick="return mkvToggleHeaderDropdown(this);" style="cursor: pointer; background: transparent; padding: 0;">
                    <i class="hgi-stroke hgi-earth" style="font-size: 18px; margin-right: 6px; color: var(--mkv-primary);"></i>
                    <span class="mkv-user-name" id="mkv-current-lang-text" style="font-weight: 500; color: #475569;"><?php echo mkv_get_current_lang() === 'en' ? 'English' : 'Tiếng Việt'; ?></span>
                    <i class="hgi-stroke hgi-arrow-down-01 mkv-header-arrow" style="color: #94a3b8;" aria-hidden="true"></i>
                </button>
                <div class="mkv-dropdown-menu right" id="mkv-language-menu" style="min-width: 140px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px;">
                    <a href="#" id="mkv-lang-opt-vi" onclick="mkvSetLang('vi', event)" style="display:flex; justify-content:space-between; align-items:center; padding: 8px 12px; border-radius: 6px; <?php echo mkv_get_current_lang() === 'vi' ? 'background: #f1f5f9; font-weight: 600;' : ''; ?>">
                        <span>Tiếng Việt</span>
                        <?php if(mkv_get_current_lang() === 'vi') echo '<i class="hgi-stroke hgi-tick-02" style="color:var(--mkv-primary); font-size: 16px;"></i>'; ?>
                    </a>
                    <a href="#" id="mkv-lang-opt-en" onclick="mkvSetLang('en', event)" style="display:flex; justify-content:space-between; align-items:center; padding: 8px 12px; border-radius: 6px; <?php echo mkv_get_current_lang() === 'en' ? 'background: #f1f5f9; font-weight: 600;' : ''; ?>">
                        <span>English</span>
                        <?php if(mkv_get_current_lang() === 'en') echo '<i class="hgi-stroke hgi-tick-02" style="color:var(--mkv-primary); font-size: 16px;"></i>'; ?>
                    </a>
                </div>
            </div>

            <a href="<?php echo admin_url('admin.php?page=mkv-notifications'); ?>" class="mkv-header-icon" title="<?php echo esc_attr(mkv__('Thông báo')); ?>" aria-label="<?php echo esc_attr(mkv__('Thông báo')); ?>">
                <i class="hgi-stroke hgi-notification-03" aria-hidden="true"></i>
                <?php if ($unread_count > 0): ?>
                <span class="mkv-notif-badge"><?php echo $unread_count > 9 ? '9+' : $unread_count; ?></span>
                <?php endif; ?>
            </a>

            <!-- User Profile -->
            <div class="mkv-header-user-dropdown" style="margin-left: 10px; border-left: 1px solid #e2e8f0; padding-left: 12px;">
                <button type="button" class="mkv-header-user mkv-header-trigger" aria-haspopup="true" aria-expanded="false" aria-controls="mkv-account-menu" onclick="return mkvToggleHeaderDropdown(this);">
                    <div class="mkv-user-avatar"><i class="hgi-stroke hgi-user"></i></div>
                    <span class="mkv-user-name" style="margin-right:4px; font-weight:600;"><?php echo esc_html($user->display_name); ?></span>
                    <i class="hgi-stroke hgi-arrow-down-01 mkv-header-arrow" aria-hidden="true"></i>
                </button>
                <div class="mkv-dropdown-menu right" id="mkv-account-menu">
                    <a href="#"><i class="hgi-stroke hgi-user"></i> <?php echo esc_html(mkv__('Tài khoản của tôi')); ?></a>
                    <a href="<?php echo wp_logout_url(admin_url()); ?>" class="text-danger"><i class="hgi-stroke hgi-logout-04"></i> <?php echo esc_html(mkv__('Đăng xuất')); ?></a>
                </div>
            </div>

            <!-- POS Button -->
            <?php if (current_user_can('mkv_manage_orders')): ?>
            <a href="<?php echo admin_url('admin.php?page=mkv-pos'); ?>" class="mkv-btn-pos">
                <i class="hgi-stroke hgi-shopping-cart-01"></i> <?php echo esc_html(mkv__('Bán Hàng (POS)')); ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Navigation Header (Blue) -->
    <header class="mkv-main-header">
        <nav class="mkv-nav">
            <!-- Tổng quan -->
            <?php if (current_user_can('mkv_manage_reports')): ?>
            <a href="<?php echo admin_url('admin.php?page=mini-kiotviet'); ?>"
               class="mkv-nav-link <?php echo $is_dashboard ? 'active' : ''; ?>">
                <span class="mkv-nav-label"><?php echo esc_html(mkv__('Tổng Quan')); ?></span>
            </a>
            <?php endif; ?>

            <!-- Hàng hóa -->
            <?php if (current_user_can('mkv_manage_products') || current_user_can('mkv_manage_inventory')): ?>
            <div class="mkv-nav-dropdown <?php echo ($is_products || $is_categories || $is_inventory) ? 'active' : ''; ?>">
                <button type="button" class="mkv-nav-link mkv-nav-trigger" aria-haspopup="true" aria-expanded="false" aria-controls="mkv-products-menu" onclick="return mkvToggleHeaderDropdown(this);">
                    <span class="mkv-nav-label"><?php echo esc_html(mkv__('Hàng Hóa')); ?></span><i class="hgi-stroke hgi-arrow-down-01 mkv-nav-arrow" aria-hidden="true"></i>
                </button>
                <div class="mkv-dropdown-menu" id="mkv-products-menu">
                    <?php if (current_user_can('mkv_manage_products')): ?>
                    <a href="<?php echo admin_url('edit.php?post_type=mkv_product'); ?>" class="<?php echo $is_products ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Sản phẩm')); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=mkv-categories'); ?>" class="<?php echo $is_categories ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Danh mục')); ?></a>
                    <?php endif; ?>
                    <?php if (current_user_can('mkv_manage_inventory')): ?>
                            <a href="<?php echo admin_url('admin.php?page=mkv-inventory'); ?>" class="<?php echo $is_inventory ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Kho & Tồn')); ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

                    <!-- Giao dịch -->
                    <?php if (current_user_can('mkv_manage_orders') || current_user_can('mkv_manage_purchases')): ?>
                    <div class="mkv-nav-dropdown <?php echo ($is_orders || $is_purchases || $is_pos) ? 'active' : ''; ?>">
                        <button type="button" class="mkv-nav-link mkv-nav-trigger" aria-haspopup="true" aria-expanded="false" aria-controls="mkv-transactions-menu" onclick="return mkvToggleHeaderDropdown(this);">
                            <span class="mkv-nav-label"><?php echo esc_html(mkv__('Giao Dịch')); ?></span><i class="hgi-stroke hgi-arrow-down-01 mkv-nav-arrow" aria-hidden="true"></i>
                        </button>
                        <div class="mkv-dropdown-menu" id="mkv-transactions-menu">
                            <?php if (current_user_can('mkv_manage_orders')): ?>
                            <a href="<?php echo admin_url('admin.php?page=mkv-pos'); ?>" class="<?php echo $is_pos ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Bán hàng tại quầy')); ?></a>
                            <a href="<?php echo admin_url('admin.php?page=mkv-orders'); ?>" class="<?php echo $is_orders ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Đơn hàng')); ?></a>
                            <?php endif; ?>
                            <?php if (current_user_can('mkv_manage_purchases')): ?>
                            <a href="<?php echo admin_url('admin.php?page=mkv-purchases'); ?>" class="<?php echo $is_purchases ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Nhập hàng')); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Đối tác -->
                    <?php if (current_user_can('mkv_manage_customers') || current_user_can('mkv_manage_purchases')): ?>
                    <div class="mkv-nav-dropdown <?php echo ($is_customers || $is_purchases) ? 'active' : ''; ?>">
                        <button type="button" class="mkv-nav-link mkv-nav-trigger" aria-haspopup="true" aria-expanded="false" aria-controls="mkv-partners-menu" onclick="return mkvToggleHeaderDropdown(this);">
                            <span class="mkv-nav-label"><?php echo esc_html(mkv__('Đối Tác')); ?></span><i class="hgi-stroke hgi-arrow-down-01 mkv-nav-arrow" aria-hidden="true"></i>
                        </button>
                        <div class="mkv-dropdown-menu" id="mkv-partners-menu">
                            <?php if (current_user_can('mkv_manage_customers')): ?>
                            <a href="<?php echo admin_url('admin.php?page=mkv-customers'); ?>" class="<?php echo $is_customers ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Khách hàng')); ?></a>
                            <?php endif; ?>
                            <?php if (current_user_can('mkv_manage_purchases')): ?>
                            <a href="<?php echo admin_url('admin.php?page=mkv-purchases&tab=suppliers'); ?>" class="<?php echo $is_purchases ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Nhà cung cấp')); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

            <!-- Sổ quỹ -->
            <?php if (current_user_can('mkv_manage_cashbook')): ?>
            <a href="<?php echo admin_url('admin.php?page=mkv-cashbook'); ?>"
               class="mkv-nav-link <?php echo $is_cashbook ? 'active' : ''; ?>">
                <span class="mkv-nav-label"><?php echo esc_html(mkv__('Sổ Quỹ')); ?></span>
            </a>
            <?php endif; ?>

            <!-- Báo cáo -->
            <?php if (current_user_can('mkv_manage_reports')): ?>
            <div class="mkv-nav-dropdown <?php echo $is_reports ? 'active' : ''; ?>">
                <a href="<?php echo admin_url('admin.php?page=mkv-reports'); ?>" class="mkv-nav-link">
                    <span class="mkv-nav-label"><?php echo esc_html(mkv__('Báo Cáo')); ?></span><i class="hgi-stroke hgi-arrow-down-01 mkv-nav-arrow" aria-hidden="true"></i>
                </a>
                <div class="mkv-dropdown-menu">
                    <a href="<?php echo admin_url('admin.php?page=mkv-reports&tab=sales'); ?>"><?php echo esc_html(mkv__('Báo cáo bán hàng')); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=mkv-reports&tab=profit'); ?>"><?php echo esc_html(mkv__('Báo cáo lợi nhuận')); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=mkv-reports&tab=end_of_day'); ?>"><?php echo esc_html(mkv__('Sổ quỹ cuối ngày')); ?></a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Nhân viên -->
            <?php if (current_user_can('mkv_manage_employees')): ?>
            <div class="mkv-nav-dropdown <?php echo $is_employees ? 'active' : ''; ?>">
                <a href="<?php echo admin_url('admin.php?page=mkv-employees'); ?>" class="mkv-nav-link">
                    <span class="mkv-nav-label"><?php echo esc_html(mkv__('Nhân Viên')); ?></span><i class="hgi-stroke hgi-arrow-down-01 mkv-nav-arrow" aria-hidden="true"></i>
                </a>
                <div class="mkv-dropdown-menu">
                    <a href="<?php echo admin_url('admin.php?page=mkv-employees&tab=list'); ?>"><?php echo esc_html(mkv__('Danh sách nhân viên')); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=mkv-employees&tab=timesheets'); ?>"><?php echo esc_html(mkv__('Bảng chấm công')); ?></a>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (current_user_can('mkv_manage_settings') || current_user_can('mkv_view_audit_logs')): ?>
            <!-- Thiết lập -->
            <div class="mkv-nav-dropdown <?php echo ($is_settings || $is_audit) ? 'active' : ''; ?>">
                <a href="<?php echo admin_url('admin.php?page=mkv-settings'); ?>" class="mkv-nav-link">
                    <span class="mkv-nav-label"><?php echo esc_html(mkv__('Thiết Lập')); ?></span><i class="hgi-stroke hgi-arrow-down-01 mkv-nav-arrow" aria-hidden="true"></i>
                </a>
                <div class="mkv-dropdown-menu right">
                    <?php if (current_user_can('mkv_manage_settings')): ?>
                    <a href="<?php echo admin_url('admin.php?page=mkv-settings'); ?>"><?php echo esc_html(mkv__('Cửa hàng & mẫu in')); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=mkv-settings#payment'); ?>"><?php echo esc_html(mkv__('Thanh toán & VietQR')); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=mkv-settings#shipping'); ?>"><?php echo esc_html(mkv__('Vận chuyển & Webhook')); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=mkv-settings#ai'); ?>"><?php echo esc_html(mkv__('Trợ lý AI Copilot')); ?></a>
                    <?php endif; ?>
                    <?php if (current_user_can('mkv_view_audit_logs') || current_user_can('manage_options')): ?>
                    <a href="<?php echo admin_url('admin.php?page=mkv-audit-logs'); ?>"><?php echo esc_html(mkv__('Nhật ký bảo mật')); ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </nav>
    </header>

    <?php if (!empty($mkv_cpt_standalone_mode)): ?>
    </div><!-- /.mkv-app-layout (standalone CPT mode) -->
    <?php else: ?>
    <div class="mkv-app-body">
        <!-- Vùng cuộn nội dung trang -->
        <div class="mkv-page-scroll">
    <?php endif; ?>
