<?php
if (!defined('ABSPATH')) exit;

class MKV_Customers
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_post_mkv_save_customer', array($this, 'handle_save_customer'));
        add_action('admin_post_mkv_delete_customer', array($this, 'handle_delete_customer'));
    }

    public function register_admin_menu()
    {
        add_submenu_page(
            'mini-kiotviet',
            'Khách hàng',
            'Khách hàng',
            'mkv_manage_customers',
            'mkv-customers',
            array($this, 'render_customers_page')
        );
    }

    public function render_customers_page()
    {
        global $wpdb;
        $table_customers = $wpdb->prefix . 'mkv_customers';

        $action      = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $search      = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $where       = '';

        if (!empty($search)) {
            $where = $wpdb->prepare(
                "WHERE name LIKE %s OR phone LIKE %s OR email LIKE %s",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        $paged       = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $limit       = 15;
        $offset      = ($paged - 1) * $limit;
        $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_customers} {$where}");
        $total_pages = (int) ceil($total_items / $limit);

        // Sorting
        $orderby_raw = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'id';
        $order_raw   = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';
        $allowed_orderby = array('id', 'name', 'points', 'total_spent');
        $orderby     = in_array($orderby_raw, $allowed_orderby) ? $orderby_raw : 'id';

        $customers = array();
        if ($action === 'list') {
            $customers = $wpdb->get_results("SELECT * FROM {$table_customers} {$where} ORDER BY {$orderby} {$order_raw} LIMIT {$limit} OFFSET {$offset}");
        }

        $customer     = null;
        $order_history = array();
        if ($action === 'edit' && $customer_id > 0) {
            $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_customers} WHERE id = %d", $customer_id));
            if ($customer) {
                // Lấy lịch sử mua hàng của khách
                $order_history = $wpdb->get_results($wpdb->prepare(
                    "SELECT o.*, 
                     (SELECT COUNT(*) FROM {$wpdb->prefix}mkv_order_items oi WHERE oi.order_id = o.id) as item_count
                     FROM {$wpdb->prefix}mkv_orders o
                     WHERE o.customer_id = %d
                     ORDER BY o.created_at DESC
                     LIMIT 50",
                    $customer_id
                ));
            }
        }

        require_once MKV_DIR . 'includes/views/view-customers.php';
    }

    public function handle_save_customer()
    {
        if (!current_user_can('mkv_manage_customers') || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'mkv_save_customer_nonce')) {
            wp_die('Bạn không có quyền thực hiện.');
        }

        global $wpdb;
        $table_customers = $wpdb->prefix . 'mkv_customers';

        $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
        $name        = sanitize_text_field($_POST['c_name'] ?? '');
        $phone       = sanitize_text_field($_POST['c_phone'] ?? '');
        $email       = sanitize_email($_POST['c_email'] ?? '');
        $address     = sanitize_textarea_field($_POST['c_address'] ?? '');

        if (empty($name)) {
            wp_die('Tên khách hàng là bắt buộc.');
        }

        // Validate SĐT Unique nếu có nhập
        if (!empty($phone)) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_customers} WHERE phone = %s AND id != %d",
                $phone,
                $customer_id
            ));
            if ($existing) {
                wp_die('Số điện thoại này đã được đăng ký cho khách hàng khác.');
            }
        }

        $data = array('name' => $name, 'phone' => $phone, 'email' => $email, 'address' => $address);

        if ($customer_id > 0) {
            $wpdb->update($table_customers, $data, array('id' => $customer_id));
        } else {
            $wpdb->insert($table_customers, $data);
        }

        wp_redirect(admin_url('admin.php?page=mkv-customers&saved=1'));
        exit;
    }

    public function handle_delete_customer()
    {
        $customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!current_user_can('mkv_manage_customers') || !isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'mkv_del_cust_' . $customer_id)) {
            wp_die('Bạn không có quyền thực hiện.');
        }

        global $wpdb;
        
        // Check if customer has debt
        $customer = $wpdb->get_row($wpdb->prepare("SELECT total_debt, total_spent FROM {$wpdb->prefix}mkv_customers WHERE id = %d", $customer_id));
        if ($customer && ($customer->total_debt != 0 || $customer->total_spent > 0)) {
            wp_die('Không thể xóa khách hàng đã phát sinh giao dịch hoặc công nợ. Vui lòng vô hiệu hóa thay vì xóa.');
        }

        // Check if customer has orders
        $order_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$wpdb->prefix}mkv_orders WHERE customer_id = %d", $customer_id));
        if ($order_count > 0) {
            wp_die('Không thể xóa khách hàng đã có đơn hàng trong hệ thống.');
        }

        $wpdb->delete($wpdb->prefix . 'mkv_customers', array('id' => $customer_id));

        wp_redirect(admin_url('admin.php?page=mkv-customers&deleted=1'));
        exit;
    }
}
