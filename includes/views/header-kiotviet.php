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

// User info
$user_initial  = mb_strtoupper(mb_substr($user->display_name, 0, 1, 'UTF-8'), 'UTF-8');
$role_labels   = [
    'administrator' => 'Quản trị viên',
    'mkv_manager'   => 'Cửa hàng trưởng',
    'mkv_sales'     => 'Nhân viên Sales',
    'mkv_warehouse' => 'Thủ kho',
];
$display_role = 'Người dùng';
foreach ($user->roles as $r) {
    if (isset($role_labels[$r])) { $display_role = $role_labels[$r]; break; }
}

// Notification count
global $wpdb;
$unread_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mkv_notifications WHERE is_read=0");
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
            <!-- Icons -->
            <script>
            window.mkvOpenAIChat = window.mkvOpenAIChat || function() {
                var d = document.getElementById('mkv-ai-drawer');
                var o = document.getElementById('mkv-ai-overlay');
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
                if (d) {
                    d.classList.remove('open');
                    d.style.setProperty('right', '-450px', 'important');
                }
                if (o) {
                    o.classList.remove('show');
                    o.style.setProperty('display', 'none', 'important');
                    o.style.setProperty('opacity', '0', 'important');
                }
            };
            </script>
            <a href="#" id="mkv-dark-mode-toggle" class="mkv-header-icon" title="<?php echo esc_attr(mkv__('Giao diện')); ?>"><i class="hgi-stroke hgi-moon-02"></i></a>
            <a href="javascript:void(0);" class="mkv-header-icon" id="mkv-topbar-btn-support" title="<?php echo esc_attr(mkv__('Hỗ trợ')); ?>" onclick="window.mkvOpenAIChat(); return false;"><i class="hgi-stroke hgi-help-circle"></i> <?php echo esc_html(mkv__('Hỗ trợ')); ?></a>
            <a href="#" class="mkv-header-icon" title="<?php echo esc_attr(mkv__('Góp ý')); ?>"><i class="hgi-stroke hgi-message-02"></i> <?php echo esc_html(mkv__('Góp ý')); ?></a>
            
            <!-- Language Switcher -->
            <div class="mkv-header-user-dropdown mkv-lang-switcher" style="margin-left: 10px; border-left: 1px solid #e2e8f0; padding-left: 15px;">
                <div class="mkv-header-user" style="cursor: pointer; background: transparent; padding: 0;">
                    <i class="hgi-stroke hgi-earth" style="font-size: 18px; margin-right: 6px; color: var(--mkv-primary);"></i>
                    <span class="mkv-user-name" style="font-weight: 500; color: #475569;"><?php echo mkv_get_current_lang() === 'en' ? 'English' : 'Tiếng Việt'; ?></span>
                    <i class="hgi-stroke hgi-arrow-down-01 arrow" style="color: #94a3b8;"></i>
                </div>
                <div class="mkv-dropdown-menu right" style="min-width: 140px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px;">
                    <a href="#" onclick="mkvSetLang('vi', event)" style="display:flex; justify-content:space-between; align-items:center; padding: 8px 12px; border-radius: 6px; <?php echo mkv_get_current_lang() === 'vi' ? 'background: #f1f5f9; font-weight: 600;' : ''; ?>">
                        <span>Tiếng Việt</span>
                        <?php if(mkv_get_current_lang() === 'vi') echo '<i class="hgi-stroke hgi-tick-02" style="color:var(--mkv-primary); font-size: 16px;"></i>'; ?>
                    </a>
                    <a href="#" onclick="mkvSetLang('en', event)" style="display:flex; justify-content:space-between; align-items:center; padding: 8px 12px; border-radius: 6px; <?php echo mkv_get_current_lang() === 'en' ? 'background: #f1f5f9; font-weight: 600;' : ''; ?>">
                        <span>English</span>
                        <?php if(mkv_get_current_lang() === 'en') echo '<i class="hgi-stroke hgi-tick-02" style="color:var(--mkv-primary); font-size: 16px;"></i>'; ?>
                    </a>
                </div>
            </div>

            <a href="<?php echo admin_url('admin.php?page=mkv-notifications'); ?>" class="mkv-header-icon" title="<?php echo esc_attr(mkv__('Thông báo')); ?>">
                <i class="hgi-stroke hgi-notification-03"></i>
                <?php if ($unread_count > 0): ?>
                <span class="mkv-notif-badge"><?php echo $unread_count > 9 ? '9+' : $unread_count; ?></span>
                <?php endif; ?>
            </a>

            <!-- User Profile -->
            <div class="mkv-header-user-dropdown" style="margin-left: 10px; border-left: 1px solid #e2e8f0; padding-left: 12px;">
                <div class="mkv-header-user">
                    <div class="mkv-user-avatar"><i class="hgi-stroke hgi-user"></i></div>
                    <span class="mkv-user-name" style="margin-right:4px; font-weight:600;"><?php echo esc_html($user->display_name); ?></span>
                    <i class="hgi-stroke hgi-arrow-down-01 arrow"></i>
                </div>
                <div class="mkv-dropdown-menu right">
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
            <?php if (current_user_can('mkv_manage_dashboard')): ?>
            <a href="<?php echo admin_url('admin.php?page=mini-kiotviet'); ?>"
               class="mkv-nav-link <?php echo $is_dashboard ? 'active' : ''; ?>">
                <?php echo esc_html(mkv__('Tổng Quan')); ?>
            </a>
            <?php endif; ?>

            <!-- Hàng hóa -->
            <?php if (current_user_can('mkv_manage_products') || current_user_can('mkv_manage_inventory')): ?>
            <div class="mkv-nav-dropdown <?php echo ($is_products || $is_categories || $is_inventory) ? 'active' : ''; ?>">
                <a href="#" class="mkv-nav-link">
                    <?php echo esc_html(mkv__('Hàng Hóa')); ?> <i class="hgi-stroke hgi-arrow-down-01 arrow"></i>
                </a>
                <div class="mkv-dropdown-menu">
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
                        <a href="#" class="mkv-nav-link">
                            <?php echo esc_html(mkv__('Giao Dịch')); ?> <i class="hgi-stroke hgi-arrow-down-01 arrow"></i>
                        </a>
                        <div class="mkv-dropdown-menu">
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
                        <a href="#" class="mkv-nav-link">
                            <?php echo esc_html(mkv__('Đối Tác')); ?> <i class="hgi-stroke hgi-arrow-down-01 arrow"></i>
                        </a>
                        <div class="mkv-dropdown-menu">
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
                <?php echo esc_html(mkv__('Sổ Quỹ')); ?>
            </a>
            <?php endif; ?>

            <!-- Báo cáo -->
            <?php if (current_user_can('mkv_manage_reports')): ?>
            <div class="mkv-nav-dropdown <?php echo $is_reports ? 'active' : ''; ?>">
                <a href="<?php echo admin_url('admin.php?page=mkv-reports'); ?>" class="mkv-nav-link">
                    <?php echo esc_html(mkv__('Báo Cáo')); ?> <i class="hgi-stroke hgi-arrow-down-01 arrow"></i>
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
                    <?php echo esc_html(mkv__('Nhân Viên')); ?> <i class="hgi-stroke hgi-arrow-down-01 arrow"></i>
                </a>
                <div class="mkv-dropdown-menu">
                    <a href="<?php echo admin_url('admin.php?page=mkv-employees'); ?>"><?php echo esc_html(mkv__('Danh sách nhân viên')); ?></a>
                    <?php if (current_user_can('manage_options')): ?><a href="<?php echo admin_url('admin.php?page=mkv-ai-logs'); ?>"><?php echo esc_html(mkv__('Lịch sử AI')); ?></a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (current_user_can('mkv_manage_settings')): ?>
            <!-- Thiết lập -->
            <div class="mkv-nav-dropdown <?php echo $is_settings ? 'active' : ''; ?>">
                <a href="<?php echo admin_url('admin.php?page=mkv-settings'); ?>" class="mkv-nav-link">
                    <?php echo esc_html(mkv__('Thiết Lập')); ?> <i class="hgi-stroke hgi-arrow-down-01 arrow"></i>
                </a>
                <div class="mkv-dropdown-menu">
                    <a href="<?php echo admin_url('admin.php?page=mkv-settings'); ?>"><?php echo esc_html(mkv__('Cửa hàng & mẫu in')); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=mkv-settings#payment'); ?>"><?php echo esc_html(mkv__('Thanh toán & VietQR')); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=mkv-settings#shipping'); ?>"><?php echo esc_html(mkv__('Vận chuyển & Webhook')); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=mkv-settings#ai'); ?>"><?php echo esc_html(mkv__('Trợ lý AI Copilot')); ?></a>
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
