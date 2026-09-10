<?php
if (!defined('ABSPATH')) exit;

class MKV_Settings
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('updated_option', array($this, 'on_option_updated'), 10, 3);
        add_action('wp_ajax_mkv_generate_webhook_secret', array($this, 'ajax_generate_webhook_secret'));
        add_action('wp_ajax_mkv_export_audit_logs', array($this, 'ajax_export_audit_logs'));
        add_action('wp_ajax_mkv_clear_audit_logs', array($this, 'ajax_clear_audit_logs'));
    }

    public function register_admin_menu()
    {
        add_submenu_page(
            'mini-kiotviet',
            'Cài đặt Cửa hàng',
            'Cài đặt',
            'mkv_manage_settings',
            'mkv-settings',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'mini-kiotviet',
            'Nhật Ký Bảo Mật',
            'Nhật Ký Bảo Mật',
            'mkv_view_audit_logs',
            'mkv-audit-logs',
            array($this, 'render_audit_page')
        );
    }

    public function register_settings()
    {
        $group = 'mkv_settings_group';
        $cap = 'mkv_manage_settings';

        // Thông tin cửa hàng
        register_setting($group, 'mkv_store_name', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting($group, 'mkv_store_logo', array(
            'capability'        => $cap,
            'sanitize_callback' => 'esc_url_raw'
        ));
        register_setting($group, 'mkv_store_email', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_email'
        ));
        register_setting($group, 'mkv_store_phone', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting($group, 'mkv_store_address', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_textarea_field'
        ));

        // Tiền tệ & thuế
        register_setting($group, 'mkv_store_currency', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_currency')
        ));
        register_setting($group, 'mkv_store_vat', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_vat')
        ));
        register_setting($group, 'mkv_vat_included', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_boolean')
        ));

        // Chính sách bán hàng
        register_setting($group, 'mkv_points_rate', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_positive_int'),
            'default'           => 100
        ));
        register_setting($group, 'mkv_point_value', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_positive_int'),
            'default'           => 1
        ));
        register_setting($group, 'mkv_min_stock_threshold', array(
            'capability'        => $cap,
            'sanitize_callback' => 'absint',
            'default'           => 5
        ));
        register_setting($group, 'mkv_allow_negative_stock', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_boolean'),
            'default'           => 0
        ));
        register_setting($group, 'mkv_print_header', array(
            'capability'        => $cap,
            'sanitize_callback' => 'wp_kses_post',
            'default'           => ''
        ));
        register_setting($group, 'mkv_print_footer', array(
            'capability'        => $cap,
            'sanitize_callback' => 'wp_kses_post',
            'default'           => ''
        ));

        // Cài đặt ngân hàng VietQR
        register_setting($group, 'mkv_bank_id', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ));
        register_setting($group, 'mkv_bank_account', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ));
        register_setting($group, 'mkv_bank_name', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ));
        register_setting($group, 'mkv_enable_pos_qr', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_boolean'),
            'default'           => 1
        ));
        register_setting($group, 'mkv_receipt_enable_qr', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_boolean'),
            'default'           => 1
        ));

        // Cài đặt Vận chuyển
        register_setting($group, 'mkv_shipping_enable', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_boolean'),
            'default'           => 0
        ));
        register_setting($group, 'mkv_shipping_provider', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_shipping_provider'),
            'default'           => 'ghtk'
        ));
        register_setting($group, 'mkv_shipping_token', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_secret_token'),
            'default'           => ''
        ));
        register_setting($group, 'mkv_webhook_secret', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_secret_webhook'),
            'default'           => ''
        ));
        register_setting($group, 'mkv_shipping_address', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ));
        register_setting($group, 'mkv_shipping_province', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ));
        register_setting($group, 'mkv_shipping_district', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ));
        register_setting($group, 'mkv_shipping_ward', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ));
        register_setting($group, 'mkv_shipping_phone', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ));
        register_setting($group, 'mkv_webhook_ip_allowlist_enabled', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_boolean'),
            'default'           => 0
        ));
        register_setting($group, 'mkv_webhook_ip_allowlist', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_ip_allowlist'),
            'default'           => ''
        ));

        // Cài đặt Nhật ký Kiểm toán & Lưu trữ (Phase 3H)
        register_setting($group, 'mkv_audit_retention_days', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_retention_days'),
            'default'           => 365
        ));

        // Cài đặt Trợ lý AI
        register_setting($group, 'mkv_ai_enable', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_boolean'),
            'default'           => 1
        ));
        register_setting($group, 'mkv_gemini_api_key', array(
            'capability'        => $cap,
            'sanitize_callback' => array($this, 'sanitize_secret_ai_key'),
            'default'           => ''
        ));
        register_setting($group, 'mkv_ai_assistant_name', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'KiotViet Copilot'
        ));
        register_setting($group, 'mkv_gemini_model', array(
            'capability'        => $cap,
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'models/gemini-3.5-flash-lite'
        ));
    }

    /**
     * Sanitization helper for booleans (0 or 1)
     */
    public function sanitize_boolean($val)
    {
        return (!empty($val) && $val != '0' && $val !== false) ? 1 : 0;
    }

    /**
     * Sanitization helper for positive integers
     */
    public function sanitize_positive_int($val)
    {
        $int = intval($val);
        return max(1, $int);
    }

    /**
     * Sanitization helper for VAT percentage (0 - 100)
     */
    public function sanitize_vat($val)
    {
        $vat = floatval($val);
        if ($vat < 0) return 0.0;
        if ($vat > 100) return 100.0;
        return round($vat, 2);
    }

    /**
     * Sanitization helper for currency code
     */
    public function sanitize_currency($val)
    {
        $cur = strtoupper(sanitize_text_field($val));
        if (strlen($cur) > 10) {
            $cur = substr($cur, 0, 10);
        }
        return !empty($cur) ? $cur : 'VND';
    }

    /**
     * Sanitization helper for shipping provider allowlist
     */
    public function sanitize_shipping_provider($val)
    {
        $allowed = array('ghtk', 'ghn');
        $provider = sanitize_key($val);
        return in_array($provider, $allowed, true) ? $provider : 'ghtk';
    }

    /**
     * Secret preserving sanitizer for Shipping Token.
     */
    public function sanitize_secret_token($val)
    {
        $trimmed = trim((string)$val);
        // If empty or masked pattern, retain existing option from DB
        if ($trimmed === '' || preg_match('/^[•\*]+$/u', $trimmed)) {
            return get_option('mkv_shipping_token', '');
        }
        // Enforce maximum length of 500 characters
        if (strlen($trimmed) > 500) {
            $trimmed = substr($trimmed, 0, 500);
        }
        return sanitize_text_field($trimmed);
    }

    /**
     * Secret preserving sanitizer for Webhook Secret.
     */
    public function sanitize_secret_webhook($val)
    {
        $trimmed = trim((string)$val);
        // If empty or masked pattern, retain existing option from DB
        if ($trimmed === '' || preg_match('/^[•\*]+$/u', $trimmed)) {
            return get_option('mkv_webhook_secret', '');
        }
        // Enforce maximum length of 255 characters
        if (strlen($trimmed) > 255) {
            $trimmed = substr($trimmed, 0, 255);
        }
        return sanitize_text_field($trimmed);
    }

    /**
     * Secret preserving sanitizer for Gemini AI Key.
     */
    public function sanitize_secret_ai_key($val)
    {
        $trimmed = trim((string)$val);
        // If empty or masked pattern, retain existing option from DB
        if ($trimmed === '' || preg_match('/^[•\*]+$/u', $trimmed)) {
            return get_option('mkv_gemini_api_key', '');
        }
        if (strlen($trimmed) > 1000) {
            $trimmed = substr($trimmed, 0, 1000);
        }
        return sanitize_textarea_field($trimmed);
    }

    /**
     * Sanitize Webhook IP allowlist (IPv4 addresses and CIDR ranges).
     */
    public function sanitize_ip_allowlist($val)
    {
        $lines = explode("\n", str_replace("\r", "", (string)$val));
        $clean = array();
        foreach ($lines as $line) {
            $entry = trim($line);
            if ($entry === '') continue;
            // Validate single IPv4 or CIDR
            if (strpos($entry, '/') !== false) {
                list($subnet, $bits) = explode('/', $entry, 2);
                $bits = intval($bits);
                if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $bits >= 0 && $bits <= 32) {
                    $clean[] = $subnet . '/' . $bits;
                }
            } else {
                if (filter_var($entry, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $clean[] = $entry;
                }
            }
        }
        return implode("\n", array_unique($clean));
    }

    /**
     * Sanitize Audit Log Retention Days (30 - 3650 days, default 365).
     */
    public function sanitize_retention_days($val)
    {
        $int = absint($val);
        if ($int < 30) return 30;
        if ($int > 3650) return 3650;
        return $int;
    }

    /**
     * Audit log hook on option update.
     */
    public function on_option_updated($option, $old_value, $new_value)
    {
        if (strpos($option, 'mkv_') !== 0) {
            return;
        }

        // Ignore internal non-user settings
        if (in_array($option, array('mkv_db_version', 'mkv_last_cron_run'), true)) {
            return;
        }

        if ($old_value === $new_value) {
            return;
        }

        if (!class_exists('MKV_Audit_Logger')) {
            return;
        }

        if ($option === 'mkv_webhook_secret') {
            MKV_Audit_Logger::log('SETTINGS', 'CHANGE_WEBHOOK_SECRET', 'option', 'mkv_webhook_secret', 'Webhook secret updated');
        } elseif ($option === 'mkv_shipping_token') {
            MKV_Audit_Logger::log('SETTINGS', 'CHANGE_SHIPPING_CONFIG', 'option', 'mkv_shipping_token', 'Shipping API credentials updated');
        } elseif ($option === 'mkv_gemini_api_key') {
            MKV_Audit_Logger::log('SETTINGS', 'CHANGE_AI_CONFIG', 'option', 'mkv_gemini_api_key', 'AI API key updated');
        } elseif ($option === 'mkv_webhook_ip_allowlist_enabled' || $option === 'mkv_webhook_ip_allowlist') {
            MKV_Audit_Logger::log('SETTINGS', 'CHANGE_WEBHOOK_IP_ALLOWLIST', 'option', $option, "Webhook IP allowlist setting {$option} updated");
        } elseif ($option === 'mkv_audit_retention_days') {
            MKV_Audit_Logger::log('SETTINGS', 'CHANGE_AUDIT_RETENTION', 'option', $option, "Audit retention days updated to {$new_value}");
        } else {
            MKV_Audit_Logger::log('SETTINGS', 'UPDATE_SETTINGS', 'option', $option, "Setting {$option} updated");
        }
    }

    /**
     * AJAX endpoint to generate a cryptographically secure random Webhook Secret.
     */
    public function ajax_generate_webhook_secret()
    {
        if (!current_user_can('mkv_manage_settings')) {
            wp_send_json_error(array('message' => 'Bạn không có quyền thực hiện hành động này.'), 403);
        }

        $nonce = $_POST['nonce'] ?? ($_GET['nonce'] ?? '');
        if (!wp_verify_nonce($nonce, 'mkv_settings_nonce')) {
            wp_send_json_error(array('message' => 'Mã xác thực không hợp lệ hoặc đã hết hạn.'), 403);
        }

        $secret = wp_generate_password(32, false);
        wp_send_json_success(array('secret' => $secret));
    }

    /**
     * AJAX endpoint to export audit logs to CSV.
     */
    public function ajax_export_audit_logs()
    {
        if (!current_user_can('mkv_view_audit_logs') && !current_user_can('manage_options')) {
            wp_die('Bạn không có quyền xuất nhật ký kiểm toán.', 'Lỗi phân quyền', array('response' => 403));
        }

        $nonce = $_GET['nonce'] ?? ($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce, 'mkv_audit_nonce')) {
            wp_die('Mã xác thực không hợp lệ hoặc đã hết hạn.', 'Lỗi bảo mật', array('response' => 403));
        }

        if (!class_exists('MKV_Audit_Logger')) {
            wp_die('Hệ thống kiểm toán chưa sẵn sàng.');
        }

        $args = array(
            'limit'      => 2000,
            'offset'     => 0,
            'event_type' => sanitize_text_field($_GET['event_type'] ?? ''),
            'action'     => sanitize_text_field($_GET['action_filter'] ?? ''),
            'user_id'    => sanitize_text_field($_GET['user_id'] ?? ''),
            'date_from'  => sanitize_text_field($_GET['date_from'] ?? ''),
            'date_to'    => sanitize_text_field($_GET['date_to'] ?? ''),
            'search'     => sanitize_text_field($_GET['s'] ?? '')
        );

        $logs = MKV_Audit_Logger::get_logs($args);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=mkv_audit_logs_' . date('Y-m-d_His') . '.csv');
        $out = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out, array('ID', 'Thời gian', 'User ID', 'Loại sự kiện', 'Hành động', 'Đối tượng', 'Mã đối tượng', 'Mô tả', 'Địa chỉ IP'));

        foreach ($logs as $l) {
            $row = array(
                $l->id,
                $l->created_at,
                $l->user_id,
                $l->event_type,
                $l->action,
                $l->object_type,
                $l->object_id,
                $l->description,
                $l->ip_address
            );
            if (function_exists('mkv_sanitize_csv_cell')) {
                $row = array_map('mkv_sanitize_csv_cell', $row);
            }
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    /**
     * AJAX endpoint to clear audit logs.
     */
    public function ajax_clear_audit_logs()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            wp_send_json_error(array('message' => 'Phương thức không hợp lệ. Chỉ chấp nhận POST.'), 405);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Chỉ quản trị viên mới có thể xóa nhật ký kiểm toán.'), 403);
        }

        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'mkv_audit_nonce')) {
            wp_send_json_error(array('message' => 'Mã xác thực không hợp lệ.'), 403);
        }

        if (!class_exists('MKV_Audit_Logger')) {
            wp_send_json_error(array('message' => 'Lớp kiểm toán không tồn tại.'));
        }

        $cleared = MKV_Audit_Logger::clear_logs();
        if ($cleared) {
            wp_send_json_success(array('message' => 'Đã xóa toàn bộ nhật ký kiểm toán thành công.'));
        } else {
            wp_send_json_error(array('message' => 'Không thể xóa nhật ký kiểm toán.'));
        }
    }

    public function render_settings_page()
    {
        if (!current_user_can('mkv_manage_settings')) {
            wp_die('Bạn không có quyền truy cập trang cài đặt.', 'Lỗi phân quyền', array('response' => 403));
        }
        include MKV_DIR . 'includes/views/view-settings.php';
    }

    public function render_audit_page()
    {
        if (!current_user_can('mkv_view_audit_logs') && !current_user_can('manage_options')) {
            wp_die('Bạn không có quyền xem nhật ký kiểm toán.', 'Lỗi phân quyền', array('response' => 403));
        }
        include MKV_DIR . 'includes/views/view-audit-logs.php';
    }
}
