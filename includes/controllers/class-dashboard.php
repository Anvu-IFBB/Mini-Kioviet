<?php
if (!defined('ABSPATH')) exit;

class MKV_Dashboard
{
    public function __construct()
    {
        add_action('admin_menu',            array($this, 'register_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_dashboard_scripts'));
        add_action('admin_init',            array($this, 'redirect_to_custom_dashboard'));
        add_action('rest_api_init',         array($this, 'register_rest_routes'));
        add_action('wp_dashboard_setup',    array($this, 'register_wp_dashboard_widget'));
        add_action('wp_ajax_mkv_get_ai_history',    array($this, 'handle_get_ai_history'));
        add_action('wp_ajax_mkv_delete_ai_history', array($this, 'handle_delete_ai_history'));
    }

    public function redirect_to_custom_dashboard()
    {
        global $pagenow;

        // 1. Chuyển hướng khi người dùng truy cập trang chủ WP Admin (index.php)
        if ($pagenow === 'index.php' && !isset($_GET['page'])) {
            if (current_user_can('mkv_manage_reports') || current_user_can('manage_options')) {
                wp_safe_redirect(admin_url('admin.php?page=mini-kiotviet'));
                exit;
            } elseif (current_user_can('mkv_manage_orders')) {
                wp_safe_redirect(admin_url('admin.php?page=mkv-pos'));
                exit;
            } elseif (current_user_can('mkv_manage_inventory')) {
                wp_safe_redirect(admin_url('admin.php?page=mkv-inventory'));
                exit;
            }
        }

        // 2. Chuyển hướng an toàn khi người dùng không có quyền xem báo cáo cố truy cập mini-kiotviet
        if ($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'mini-kiotviet') {
            if (!current_user_can('mkv_manage_reports') && !current_user_can('manage_options')) {
                if (current_user_can('mkv_manage_orders')) {
                    wp_safe_redirect(admin_url('admin.php?page=mkv-pos'));
                    exit;
                } elseif (current_user_can('mkv_manage_inventory')) {
                    wp_safe_redirect(admin_url('admin.php?page=mkv-inventory'));
                    exit;
                } else {
                    wp_die(mkv__('Bạn không có quyền truy cập trang này.'));
                }
            }
        }
    }

    public function register_wp_dashboard_widget()
    {
        if (current_user_can('mkv_manage_reports')) {
            wp_add_dashboard_widget(
                'mkv_dashboard_widget',
                'Mini KiotViet - Thống Kê Nhanh',
                array($this, 'render_wp_dashboard_widget')
            );
        }
    }

    public function render_wp_dashboard_widget()
    {
        $stats = MKV_Analytics_Service::get_dashboard_stats('today');
        echo '<div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px;">';
        echo '<div style="width:48%; background:#f0f6ff; padding:10px; border-radius:6px;"><strong>Đơn hôm nay:</strong> <br><span style="font-size:18px; font-weight:700; color:#0052cc;">' . esc_html($stats['today_orders']) . '</span></div>';
        echo '<div style="width:48%; background:#ebfff0; padding:10px; border-radius:6px;"><strong>Doanh thu hôm nay:</strong> <br><span style="font-size:18px; font-weight:700; color:#00875a;">' . number_format($stats['today_revenue'], 0, ',', '.') . ' ₫</span></div>';
        echo '<div style="width:48%; background:#e8f5e9; padding:10px; border-radius:6px;"><strong>Thực thu hôm nay:</strong> <br><span style="font-size:18px; font-weight:700; color:#2e7d32;">' . number_format($stats['today_actual_collected'], 0, ',', '.') . ' ₫</span></div>';
        echo '<div style="width:48%; background:#fff3e0; padding:10px; border-radius:6px;"><strong>Chưa thu (Nợ + COD):</strong> <br><span style="font-size:18px; font-weight:700; color:#e65100;">' . number_format($stats['today_pending_debt'], 0, ',', '.') . ' ₫</span></div>';
        echo '<div style="width:48%; background:#f4f5f7; padding:10px; border-radius:6px;"><strong>Tổng khách hàng:</strong> <br><span style="font-size:18px; font-weight:700;">' . esc_html($stats['total_customers'] ?? 0) . '</span></div>';
        echo '<div style="width:48%; background:#f4f5f7; padding:10px; border-radius:6px;"><strong>Tồn quỹ hiện tại:</strong> <br><span style="font-size:18px; font-weight:700; color:#0070f3;">' . number_format($stats['total_cash_balance'] ?? 0, 0, ',', '.') . ' ₫</span></div>';
        echo '<div style="width:100%; background:#e6fcff; padding:10px; border-radius:6px;"><strong>Tổng doanh thu (Toàn TG):</strong> <br><span style="font-size:18px; font-weight:700; color:#00b8d9;">' . number_format($stats['total_revenue'] ?? 0, 0, ',', '.') . ' ₫</span></div>';
        echo '</div>';
        echo '<div style="margin-top:10px;"><a href="' . esc_url(admin_url('admin.php?page=mini-kiotviet')) . '" class="button button-primary">Xem Dashboard Chi Tiết</a></div>';
    }

    public function register_admin_menu()
    {
        add_menu_page(
            'Mini KiotViet',
            'Mini KiotViet',
            'mkv_manage_dashboard',
            'mini-kiotviet',
            array($this, 'render_dashboard_page'),
            'dashicons-store',
            2
        );

        add_submenu_page(
            'mini-kiotviet',
            mkv__('Tổng Quan') . ' Dashboard',
            mkv__('Tổng Quan'),
            'mkv_manage_reports',
            'mini-kiotviet',
            array($this, 'render_dashboard_page')
        );
    }

    public function enqueue_dashboard_scripts($hook)
    {
        if ($hook !== 'toplevel_page_mini-kiotviet') return;

        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.0', true);

        $js_ver = file_exists(MKV_DIR . 'assets/js/dashboard-chart.js') ? filemtime(MKV_DIR . 'assets/js/dashboard-chart.js') : MKV_VERSION;
        wp_enqueue_script('mkv-dashboard-js', MKV_URL . 'assets/js/dashboard-chart.js', array('chart-js', 'jquery'), $js_ver, true);

        wp_localize_script('mkv-dashboard-js', 'mkv_api', array(
            'root'  => esc_url_raw(rest_url('mkv/v1/')),
            'nonce' => wp_create_nonce('wp_rest')
        ));
    }

    public function register_rest_routes()
    {
        register_rest_route('mkv/v1', '/stats', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_stats_data'),
            'permission_callback' => function () {
                return current_user_can('mkv_manage_reports');
            }
        ));
    }

    public function get_stats_data()
    {
        $period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'today'; // today, week, month, year
        $stats = MKV_Analytics_Service::get_dashboard_stats($period);
        return rest_ensure_response($stats);
    }

    public function render_dashboard_page()
    {
        if (!current_user_can('mkv_manage_reports') && !current_user_can('manage_options')) {
            $target = current_user_can('mkv_manage_orders')
                ? admin_url('admin.php?page=mkv-pos')
                : (current_user_can('mkv_manage_inventory') ? admin_url('admin.php?page=mkv-inventory') : admin_url());

            if (!headers_sent()) {
                wp_safe_redirect($target);
                exit;
            } else {
                echo '<script>window.location.replace(' . json_encode($target) . ');</script>';
                echo '<div class="wrap"><p>' . esc_html(mkv__('Đang chuyển hướng...')) . ' <a href="' . esc_url($target) . '">' . esc_html(mkv__('Bấm vào đây nếu trang không tự tải lại.')) . '</a></p></div>';
                return;
            }
        }
        
        require_once MKV_DIR . 'includes/views/view-dashboard.php';
    }

    public function handle_get_ai_history()
    {
        check_ajax_referer('mkv_ai_chat_nonce', 'nonce');

        global $wpdb;
        $table_logs = $wpdb->prefix . 'mkv_ai_logs';

        $all_users   = isset($_POST['all_users']) && $_POST['all_users'] === '1' && current_user_can('manage_options');
        $current_uid = get_current_user_id();

        if ($all_users) {
            $logs = $wpdb->get_results(
                "SELECT l.id, l.user_id, l.interaction_id, l.message, l.message_type, l.created_at,
                        u.display_name AS user_name,
                        (SELECT a.message FROM $table_logs a 
                         WHERE l.interaction_id != '' AND a.interaction_id = l.interaction_id AND a.message_type = 'ai' 
                         LIMIT 1) AS ai_reply
                 FROM $table_logs l
                 LEFT JOIN {$wpdb->users} u ON u.ID = l.user_id
                 WHERE l.message_type = 'user'
                 ORDER BY l.created_at DESC
                 LIMIT 200"
            );
        } else {
            $logs = $wpdb->get_results($wpdb->prepare(
                "SELECT l.id, l.user_id, l.interaction_id, l.message, l.message_type, l.created_at,
                        NULL AS user_name,
                        (SELECT a.message FROM $table_logs a 
                         WHERE l.interaction_id != '' AND a.interaction_id = l.interaction_id AND a.message_type = 'ai' 
                         LIMIT 1) AS ai_reply
                 FROM $table_logs l
                 WHERE l.user_id = %d AND l.message_type = 'user'
                 ORDER BY l.created_at DESC
                 LIMIT 200",
                $current_uid
            ));
        }

        if ($logs === null) {
            wp_send_json_error('db_error');
            return;
        }

        wp_send_json_success($logs);
    }

    public function handle_delete_ai_history()
    {
        check_ajax_referer('mkv_ai_chat_nonce', 'nonce');

        global $wpdb;
        $table_logs = $wpdb->prefix . 'mkv_ai_logs';
        $log_id     = intval($_POST['log_id'] ?? 0);

        if (!$log_id) {
            wp_send_json_error('missing_id');
            return;
        }

        $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_logs WHERE id = %d", $log_id));
        if (!$log) {
            wp_send_json_error('not_found');
            return;
        }

        // Kiểm tra quyền: chỉ xóa được của chính mình trừ khi là quản trị viên
        if (!current_user_can('manage_options') && intval($log->user_id) !== get_current_user_id()) {
            wp_send_json_error('forbidden');
            return;
        }

        // Xóa cả tin nhắn hỏi và câu trả lời tương ứng
        if (!empty($log->interaction_id)) {
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $table_logs WHERE interaction_id = %s",
                $log->interaction_id
            ));
        } else {
            $wpdb->delete($table_logs, array('id' => $log_id));
        }

        wp_send_json_success();
    }
}
