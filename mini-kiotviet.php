<?php
/**
 * Plugin Name: Mini KiotViet Dashboard
 * Description: Xây dựng giao diện Dashboard quản lý bán hàng kiểu KiotViet trên WordPress.
 * Version: 3.0
 * Author: An Vũ
 * Text Domain: mini-kiotviet
 */

if (!defined('ABSPATH')) exit;

define('MKV_DIR', plugin_dir_path(__FILE__));
define('MKV_URL', plugin_dir_url(__FILE__));
define('MKV_VERSION', '3.0');

// Enqueue assets
add_action('admin_enqueue_scripts', function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $is_mkv_screen = $screen && (
        strpos((string) $screen->id, 'mini-kiotviet') !== false ||
        strpos((string) $screen->id, 'mkv-') !== false ||
        $screen->post_type === 'mkv_product' ||
        strpos((string) $screen->taxonomy, 'mkv_') === 0
    );
    if (!$is_mkv_screen) return;

    wp_enqueue_style('google-fonts-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap', array(), null);
    wp_enqueue_style('hugeicons', 'https://cdn.hugeicons.com/font/hgi-stroke-rounded.css', array(), '1.0');

    $css_layout_ver = file_exists(MKV_DIR . 'assets/css/admin-layout.css') ? filemtime(MKV_DIR . 'assets/css/admin-layout.css') : MKV_VERSION;
    $css_dashboard_ver = file_exists(MKV_DIR . 'assets/css/admin-dashboard.css') ? filemtime(MKV_DIR . 'assets/css/admin-dashboard.css') : MKV_VERSION;
    $css_pos_ver = file_exists(MKV_DIR . 'assets/css/admin-pos.css') ? filemtime(MKV_DIR . 'assets/css/admin-pos.css') : MKV_VERSION;
    $css_wp_overrides_ver = file_exists(MKV_DIR . 'assets/css/admin-wp-overrides.css') ? filemtime(MKV_DIR . 'assets/css/admin-wp-overrides.css') : MKV_VERSION;
    $css_ai_assistant_ver = file_exists(MKV_DIR . 'assets/css/admin-ai-assistant.css') ? filemtime(MKV_DIR . 'assets/css/admin-ai-assistant.css') : MKV_VERSION;

    wp_enqueue_style('mkv-tailwind-admin', MKV_URL . 'assets/css/tailwind-admin.css', array(), filemtime(MKV_DIR . 'assets/css/tailwind-admin.css'));
    wp_enqueue_style('mkv-admin-layout', MKV_URL . 'assets/css/admin-layout.css', array('mkv-tailwind-admin'), $css_layout_ver);
    wp_enqueue_style('mkv-admin-dashboard', MKV_URL . 'assets/css/admin-dashboard.css', array('mkv-admin-layout'), $css_dashboard_ver);
    wp_enqueue_style('mkv-admin-pos', MKV_URL . 'assets/css/admin-pos.css', array('mkv-admin-layout'), $css_pos_ver);
    wp_enqueue_style('mkv-admin-wp-overrides', MKV_URL . 'assets/css/admin-wp-overrides.css', array('mkv-admin-layout'), $css_wp_overrides_ver);
    wp_enqueue_style('mkv-admin-ai-assistant', MKV_URL . 'assets/css/admin-ai-assistant.css', array('mkv-admin-layout'), $css_ai_assistant_ver);

    // JS & Libraries
    $js_global_ver = file_exists(MKV_DIR . 'assets/js/admin-global.js') ? filemtime(MKV_DIR . 'assets/js/admin-global.js') : MKV_VERSION;
    $js_ai_assistant_ver = file_exists(MKV_DIR . 'assets/js/admin-ai-assistant.js') ? filemtime(MKV_DIR . 'assets/js/admin-ai-assistant.js') : MKV_VERSION;

    wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0');
    wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
    wp_enqueue_script('jsbarcode', 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js', array(), '3.11.6', true);
    wp_enqueue_script('mkv-admin-global', MKV_URL . 'assets/js/admin-global.js', array('jquery'), $js_global_ver, true);
    
    // AI Assistant
    wp_enqueue_script('marked', 'https://cdn.jsdelivr.net/npm/marked/marked.min.js', array(), '4.3.0', true);
    wp_enqueue_script('dompurify', 'https://cdn.jsdelivr.net/npm/dompurify@3.2.6/dist/purify.min.js', array(), '3.2.6', true);
    wp_enqueue_script('mkv-admin-ai-assistant', MKV_URL . 'assets/js/admin-ai-assistant.js', array('jquery', 'dompurify'), $js_ai_assistant_ver, true);
    wp_localize_script('mkv-admin-ai-assistant', 'mkv_ai_data', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('mkv_ai_chat_nonce'),
    ));
    
    wp_localize_script('mkv-admin-global', 'mkv_i18n', array(
        'compared_to_yesterday' => mkv__('So với hôm qua'),
        'same_as_yesterday'     => mkv__('Bằng hôm qua'),
        'no_activities'         => mkv__('Chưa có hoạt động nào.'),
        'all_in_stock'          => mkv__('Tất cả sản phẩm đều đủ tồn kho.'),
        'out_of_stock'          => mkv__('Hết hàng'),
        'in_stock_prefix'       => mkv__('Còn'),
        'revenue_label'         => mkv__('Doanh thu:'),
        'period_today'          => mkv__('Hôm nay'),
        'period_week'           => mkv__('Tuần này'),
        'period_month'          => mkv__('Tháng này'),
        'period_year'           => mkv__('Năm nay'),
        'sales_result_prefix'   => mkv__('Kết quả bán hàng'),
        'guest_customer'        => mkv__('Khách lẻ'),
        'product_unit'          => mkv__('sản phẩm'),
        'activity_template'     => mkv__('vừa mua đơn hàng <strong>{order_code}</strong> với giá trị <strong style="color:var(--mkv-primary);">{amount}</strong>'),
    ));
}); 

// Render AI Assistant Drawer at root body level on all admin pages
add_action('admin_footer', function () {
    if (file_exists(MKV_DIR . 'includes/views/view-ai-drawer.php')) {
        require_once MKV_DIR . 'includes/views/view-ai-drawer.php';
    }
});

// Thêm body class để nhận diện trang của Mini KiotViet
add_filter('admin_body_class', function ($classes) {
    global $pagenow, $plugin_page;
    $is_mkv_post_type = isset($_GET['post_type']) && $_GET['post_type'] === 'mkv_product';
    $is_mkv_post = isset($_GET['post']) && get_post_type($_GET['post']) === 'mkv_product';
    $is_mkv_tax = isset($_GET['taxonomy']) && strpos($_GET['taxonomy'], 'mkv_') === 0;
    
    if (
        (isset($plugin_page) && strpos($plugin_page, 'mkv') !== false) || 
        (isset($plugin_page) && strpos($plugin_page, 'mini-kiotviet') !== false) ||
        $is_mkv_post_type || $is_mkv_post || $is_mkv_tax
    ) {
        $classes .= ' mkv-admin-page';
    }
    return $classes;
});

// Includes
require_once MKV_DIR . 'includes/mkv-i18n.php';
require_once MKV_DIR . 'includes/models/class-db-schema.php';
require_once MKV_DIR . 'includes/models/class-analytics-service.php';
require_once MKV_DIR . 'includes/models/class-shipping-service.php';
require_once MKV_DIR . 'includes/models/class-ai-service.php';
require_once MKV_DIR . 'includes/admin/class-settings.php';
require_once MKV_DIR . 'includes/controllers/class-dashboard.php';
require_once MKV_DIR . 'includes/controllers/class-products.php';
require_once MKV_DIR . 'includes/controllers/class-inventory.php';
require_once MKV_DIR . 'includes/controllers/class-customers.php';
require_once MKV_DIR . 'includes/controllers/class-orders.php';
require_once MKV_DIR . 'includes/controllers/class-reports.php';
require_once MKV_DIR . 'includes/controllers/class-notifications.php';
require_once MKV_DIR . 'includes/controllers/class-cashbook.php';
require_once MKV_DIR . 'includes/controllers/class-employees.php';
require_once MKV_DIR . 'includes/controllers/class-purchases.php';
require_once MKV_DIR . 'includes/controllers/class-webhook.php';
require_once MKV_DIR . 'includes/controllers/class-ai-controller.php';
require_once MKV_DIR . 'includes/controllers/class-frontend.php';

// Initialize
add_action('plugins_loaded', function () {
    MKV_DB_Schema::init();
    new MKV_Dashboard();
    new MKV_Products();
    new MKV_Inventory();
    new MKV_Customers();
    new MKV_Orders();
    new MKV_Reports();
    new MKV_Notifications();
    new MKV_Cashbook();
    new MKV_Employees();
    new MKV_Purchases();
    new MKV_Settings();
    new MKV_Webhook();
    new MKV_Frontend();
    new MKV_AI_Controller();
    mkv_register_roles_and_caps();
    mkv_register_cron();
});

// Plugin activation
register_activation_hook(__FILE__, function ()
{
    require_once MKV_DIR . 'includes/models/class-db-schema.php';
    MKV_DB_Schema::create_tables();
    mkv_register_roles_and_caps();
    // Force DB re-init on next load
    delete_option('mkv_db_version');
});



function mkv_register_roles_and_caps()
{
    $all_caps = array(
        'mkv_manage_dashboard', 'mkv_manage_products', 'mkv_manage_inventory',
        'mkv_manage_customers', 'mkv_manage_orders', 'mkv_manage_reports',
        'mkv_manage_settings', 'mkv_manage_notifications', 'mkv_view_cost_price',
        'mkv_manage_cashbook', 'mkv_manage_employees', 'mkv_manage_purchases'
    );

    // Admin — toàn quyền (luôn đồng bộ)
    $admin = get_role('administrator');
    if ($admin) {
        foreach ($all_caps as $cap) $admin->add_cap($cap);
    }

    // --- Manager: toàn quyền trừ Settings ---
    $manager_caps = array_diff($all_caps, array('mkv_manage_settings'));
    $manager_perms = array('read' => true);
    foreach ($manager_caps as $c) $manager_perms[$c] = true;
    // Xóa và tạo lại để đảm bảo caps luôn đúng
    remove_role('mkv_manager');
    add_role('mkv_manager', 'Cửa hàng trưởng', $manager_perms);

    // --- Sales: POS + Khách hàng + Đơn hàng ---
    remove_role('mkv_sales');
    add_role('mkv_sales', 'Nhân viên Sales', array(
        'read'                     => true,
        'mkv_manage_dashboard'     => true,
        'mkv_manage_customers'     => true,
        'mkv_manage_orders'        => true,
        'mkv_manage_cashbook'      => true,
        'mkv_manage_notifications' => true,
    ));

    // --- Warehouse: Kho + Sản phẩm ---
    remove_role('mkv_warehouse');
    add_role('mkv_warehouse', 'Thủ kho', array(
        'read'                     => true,
        'mkv_manage_dashboard'     => true,
        'mkv_manage_products'      => true,
        'mkv_manage_inventory'     => true,
        'mkv_manage_notifications' => true,
        'mkv_manage_purchases'     => true,
        'mkv_view_cost_price'      => true,
    ));
}

// Luôn cấp toàn quyền MKV cho Administrator mà không lo lỗi phân quyền
add_filter('user_has_cap', function($allcaps, $caps, $args, $user) {
    if (!empty($allcaps['administrator']) || !empty($allcaps['manage_options'])) {
        $mkv_caps = array(
            'mkv_manage_dashboard', 'mkv_manage_products', 'mkv_manage_inventory',
            'mkv_manage_customers', 'mkv_manage_orders', 'mkv_manage_reports',
            'mkv_manage_settings', 'mkv_manage_notifications', 'mkv_view_cost_price',
            'mkv_manage_cashbook', 'mkv_manage_employees', 'mkv_manage_purchases'
        );
        foreach ($mkv_caps as $c) {
            $allcaps[$c] = true;
        }
    }
    return $allcaps;
}, 10, 4);

// Kích hoạt chế độ Dark Mode ngay trong thẻ head để tránh giật giao diện
add_action('admin_head', function() {
    ?>
    <script>
    (function() {
        if (localStorage.getItem('mkv-dark-mode') === 'true') {
            document.documentElement.classList.add('dark');
            document.addEventListener('DOMContentLoaded', function() {
                if (document.body) document.body.classList.add('dark');
            });
        }
    })();
    </script>
    <?php
});

function mkv_register_cron()
{
    if (!wp_next_scheduled('mkv_daily_stock_scan')) {
        // 08:00 giờ server mỗi ngày
        wp_schedule_event(strtotime('08:00:00'), 'daily', 'mkv_daily_stock_scan');
    }
    add_action('mkv_daily_stock_scan', 'mkv_cron_stock_scan');
}

function mkv_cron_stock_scan()
{
    global $wpdb;
    $threshold = (int) get_option('mkv_min_stock_threshold', 5);

    $low = $wpdb->get_results($wpdb->prepare(
        "SELECT s.product_id, SUM(s.stock) as total_stock, p.post_title
         FROM {$wpdb->prefix}mkv_inventory_stock s
         JOIN {$wpdb->prefix}posts p ON s.product_id = p.ID
         WHERE p.post_status = 'publish'
         GROUP BY s.product_id
         HAVING total_stock <= %d",
        $threshold
    ));

    if (empty($low)) return;

    $email   = get_option('mkv_store_email', get_option('admin_email'));
    $store   = get_option('mkv_store_name', 'Mini KiotViet');
    $subject = "[{$store}] Cảnh báo tồn kho thấp - " . date('d/m/Y');

    $body = "Báo cáo tồn kho thấp ngày " . date('d/m/Y') . ":\n\n";
    foreach ($low as $item) {
        $body .= "- {$item->post_title}: còn {$item->total_stock} sản phẩm\n";
    }
    $body .= "\nVui lòng nhập hàng sớm để tránh gián đoạn kinh doanh.";

    wp_mail($email, $subject, $body);

    // Ghi vào bảng notifications
    foreach ($low as $item) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}mkv_notifications WHERE title = %s AND DATE(created_at) = CURDATE()",
            'Sắp hết hàng: ' . $item->post_title
        ));
        if (!$exists) {
            $wpdb->insert("{$wpdb->prefix}mkv_notifications", array(
                'type'    => 'warning',
                'title'   => 'Sắp hết hàng: ' . $item->post_title,
                'message' => "Tồn kho hiện tại: {$item->total_stock}. Vui lòng nhập thêm hàng.",
                'is_read' => 0,
            ));
        }
    }
}

// Ẩn menu mặc định WP với user không phải admin
add_action('admin_menu', function () {
    if (!current_user_can('administrator')) {
        remove_menu_page('index.php');
        remove_menu_page('edit.php');
        remove_menu_page('edit-comments.php');
        remove_menu_page('themes.php');
        remove_menu_page('plugins.php');
        remove_menu_page('users.php');
        remove_menu_page('tools.php');
        remove_menu_page('options-general.php');
        remove_menu_page('upload.php');
        remove_menu_page('profile.php');
    }
}, 999);

// Keep the WordPress submenu aligned with the POS workflow, regardless of controller load order.
add_action('admin_menu', function () {
    global $submenu;
    if (empty($submenu['mini-kiotviet'])) return;

    $menu_order = array(
        'mini-kiotviet' => 10,
        'mkv-pos' => 20,
        'mkv-orders' => 30,
        'edit.php?post_type=mkv_product' => 40,
        'mkv-categories' => 50,
        'mkv-inventory' => 60,
        'mkv-purchases' => 70,
        'mkv-customers' => 80,
        'mkv-cashbook' => 90,
        'mkv-reports' => 100,
        'mkv-employees' => 110,
        'mkv-notifications' => 120,
        'mkv-settings' => 130,
        'mkv-ai-logs' => 140,
    );

    usort($submenu['mini-kiotviet'], function ($left, $right) use ($menu_order) {
        $left_order = $menu_order[$left[2]] ?? 999;
        $right_order = $menu_order[$right[2]] ?? 999;
        return $left_order <=> $right_order;
    });
}, 9999);

// Redirect Dashboard mặc định -> MKV Dashboard
add_action('admin_init', function () {
    global $pagenow;
    if ($pagenow === 'index.php' && !isset($_GET['page']) && current_user_can('mkv_manage_dashboard')) {
        wp_redirect(admin_url('admin.php?page=mini-kiotviet'));
        exit;
    }
});

// REST API: kiểm tra tồn kho realtime (cho POS)
add_action('rest_api_init', function () {
    register_rest_route('mkv/v1', '/stock/(?P<id>\d+)', array(
        'methods'             => 'GET',
        'callback'            => function ($req) {
            global $wpdb;
            $pid   = intval($req['id']);
            $wid   = intval($req->get_param('warehouse_id') ?? 0);
            $where = $wid ? $wpdb->prepare("AND location_id = %d", $wid) : '';
            $stock = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(stock) FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id = %d $where",
                $pid
            ));
            return rest_ensure_response(array('product_id' => $pid, 'stock' => $stock));
        },
        'permission_callback' => function () {
            return current_user_can('mkv_manage_orders') || current_user_can('mkv_manage_inventory');
        },
    ));

    // Thông báo chưa đọc (polling)
    register_rest_route('mkv/v1', '/notifications/unread-count', array(
        'methods'             => 'GET',
        'callback'            => function () {
            global $wpdb;
            $today  = current_time('Y-m-d');
            $orders = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}mkv_orders WHERE DATE(created_at) = %s",
                $today
            ));
            $alerts = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}mkv_notifications WHERE is_read = 0"
            );
            return rest_ensure_response(array(
                'orders' => $orders,
                'alerts' => $alerts,
                'total'  => $alerts, // Chỉ đếm số thông báo thực sự cho badge
            ));
        },
        'permission_callback' => function () {
            return current_user_can('mkv_manage_notifications');
        },
    ));
});

// Auto-migrate deprecated gemini model
add_action('init', function () {
    $curr = get_option('mkv_gemini_model');
    if (empty($curr) || $curr === 'gemini-2.5-flash' || $curr === 'gemini-2.5-flash-lite' || strpos($curr, 'gemini-3.8') === 0 || strpos($curr, 'gemini-3.7') === 0) {
        update_option('mkv_gemini_model', 'models/gemini-3.5-flash-lite');
    }
});

// Force IPv4 on external HTTP requests to prevent Windows cURL IPv6 DNS timeouts
add_action('http_api_curl', function ($handle) {
    if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
        curl_setopt($handle, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }
});
