<?php
if (!defined('ABSPATH')) exit;

class MKV_Notifications
{
    public function __construct()
    {
        add_action('admin_menu',               array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts',    array($this, 'enqueue_polling'));
        add_action('admin_post_mkv_mark_read', array($this, 'handle_mark_read'));
    }

    public function add_admin_menu()
    {
        global $wpdb;
        $unread     = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mkv_notifications WHERE is_read=0");
        $badge_html = $unread > 0
            ? ' <span class="awaiting-mod"><span class="pending-count">' . $unread . '</span></span>'
            : '';

        add_submenu_page('mini-kiotviet', 'Thông Báo', 'Thông Báo' . $badge_html,
            'mkv_manage_notifications', 'mkv-notifications', array($this, 'render_notifications_page'));
    }

    public function enqueue_polling()
    {
        if (!current_user_can('mkv_manage_notifications')) return;
        wp_enqueue_script('mkv-notif-poll', MKV_URL . 'assets/js/notifications-poll.js', array('jquery'), MKV_VERSION, true);
        wp_localize_script('mkv-notif-poll', 'mkv_notif', array(
            'root'     => esc_url_raw(rest_url('mkv/v1/')),
            'nonce'    => wp_create_nonce('wp_rest'),
            'interval' => 45000, // 45 giây
        ));
    }

    public function handle_mark_read()
    {
        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'mkv_mark_read') || !current_user_can('mkv_manage_notifications'))
            wp_die('Không có quyền.');

        global $wpdb;
        $wpdb->update("{$wpdb->prefix}mkv_notifications", array('is_read' => 1), array('is_read' => 0));
        wp_redirect(admin_url('admin.php?page=mkv-notifications'));
        exit;
    }

    public function render_notifications_page()
    {
        global $wpdb;
        $today = current_time('Y-m-d');

        $today_orders = $wpdb->get_results($wpdb->prepare(
            "SELECT o.*, c.name as customer_name
             FROM {$wpdb->prefix}mkv_orders o
             LEFT JOIN {$wpdb->prefix}mkv_customers c ON o.customer_id = c.id
             WHERE DATE(o.created_at) = %s
             ORDER BY o.created_at DESC",
            $today
        ));

        $threshold  = (int) get_option('mkv_min_stock_threshold', 5);
        $low_stock_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT product_id, SUM(stock) as total_stock
             FROM {$wpdb->prefix}mkv_inventory_stock
             GROUP BY product_id
             HAVING total_stock <= %d
             ORDER BY total_stock ASC",
            $threshold
        ));
        $low_stock = array();
        foreach ($low_stock_rows as $row) {
            $post = get_post($row->product_id);
            if ($post) {
                $low_stock[] = array('ID' => $row->product_id, 'post_title' => $post->post_title, 'stock' => (int)$row->total_stock);
            }
        }

        $alerts = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}mkv_notifications ORDER BY created_at DESC LIMIT 30"
        );

        // Đánh dấu tất cả là đã đọc khi user truy cập trang này
        $wpdb->update("{$wpdb->prefix}mkv_notifications", array('is_read' => 1), array('is_read' => 0));

        require_once MKV_DIR . 'includes/views/view-notifications.php';
    }
}
