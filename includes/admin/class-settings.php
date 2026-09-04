<?php
if (!defined('ABSPATH')) exit;

class MKV_Settings
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
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
    }

    public function register_settings()
    {
        $group = 'mkv_settings_group';

        // Thông tin cửa hàng
        register_setting($group, 'mkv_store_name',    array('sanitize_callback' => 'sanitize_text_field'));
        register_setting($group, 'mkv_store_logo',    array('sanitize_callback' => 'esc_url_raw'));
        register_setting($group, 'mkv_store_email',   array('sanitize_callback' => 'sanitize_email'));
        register_setting($group, 'mkv_store_phone',   array('sanitize_callback' => 'sanitize_text_field'));
        register_setting($group, 'mkv_store_address', array('sanitize_callback' => 'sanitize_textarea_field'));

        // Tiền tệ & thuế
        register_setting($group, 'mkv_store_currency',    array('sanitize_callback' => 'sanitize_text_field'));
        register_setting($group, 'mkv_store_vat',         array('sanitize_callback' => 'floatval'));
        register_setting($group, 'mkv_vat_included',      array('sanitize_callback' => 'intval'));

        // Chính sách bán hàng
        register_setting($group, 'mkv_points_rate',           array('sanitize_callback' => 'intval',  'default' => 100)); // 100 VNĐ = 1 điểm
        register_setting($group, 'mkv_point_value',           array('sanitize_callback' => 'intval',  'default' => 1));   // 1 điểm = 1 VNĐ
        register_setting($group, 'mkv_min_stock_threshold',   array('sanitize_callback' => 'intval',  'default' => 5));
        register_setting($group, 'mkv_allow_negative_stock',  array('sanitize_callback' => 'intval',  'default' => 0));
        register_setting($group, 'mkv_print_header',          array('sanitize_callback' => 'wp_kses_post', 'default' => ''));
        register_setting($group, 'mkv_print_footer',          array('sanitize_callback' => 'wp_kses_post', 'default' => ''));

        // Cài đặt ngân hàng VietQR
        register_setting($group, 'mkv_bank_id',               array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
        register_setting($group, 'mkv_bank_account',          array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
        register_setting($group, 'mkv_bank_name',             array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
        register_setting($group, 'mkv_enable_pos_qr',         array('sanitize_callback' => 'intval', 'default' => 1));
        register_setting($group, 'mkv_receipt_enable_qr',     array('sanitize_callback' => 'intval', 'default' => 1));

        // Cài đặt Vận chuyển
        register_setting($group, 'mkv_shipping_enable',       array('sanitize_callback' => 'intval', 'default' => 0));
        register_setting($group, 'mkv_shipping_provider',     array('sanitize_callback' => 'sanitize_text_field', 'default' => 'ghtk'));
        register_setting($group, 'mkv_shipping_token',        array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
        register_setting($group, 'mkv_webhook_secret',         array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
        register_setting($group, 'mkv_shipping_address',      array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
        register_setting($group, 'mkv_shipping_province',     array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
        register_setting($group, 'mkv_shipping_district',     array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
        register_setting($group, 'mkv_shipping_ward',         array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
        register_setting($group, 'mkv_shipping_phone',        array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));

        // Cài đặt Trợ lý AI
        register_setting($group, 'mkv_ai_enable',             array('sanitize_callback' => 'intval', 'default' => 1));
        register_setting($group, 'mkv_gemini_api_key',        array('sanitize_callback' => 'sanitize_textarea_field', 'default' => ''));
        register_setting($group, 'mkv_ai_assistant_name',     array('sanitize_callback' => 'sanitize_text_field', 'default' => 'KiotViet Copilot'));
        register_setting($group, 'mkv_gemini_model',          array('sanitize_callback' => 'sanitize_text_field', 'default' => 'models/gemini-3.5-flash-lite'));
    }

    public function render_settings_page()
    {
        include MKV_DIR . 'includes/views/view-settings.php';
    }
}
