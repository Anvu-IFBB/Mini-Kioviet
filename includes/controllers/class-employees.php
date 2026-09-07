<?php
if (!defined('ABSPATH')) exit;

class MKV_Employees
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_mkv_employee_checkin', array($this, 'handle_checkin_checkout'));
        add_action('admin_post_mkv_add_employee', array($this, 'handle_add_employee'));
        add_action('admin_post_mkv_edit_employee', array($this, 'handle_edit_employee'));
        add_action('admin_post_mkv_delete_employee', array($this, 'handle_delete_employee'));
    }

    public function add_admin_menu()
    {
        add_submenu_page('mini-kiotviet', 'Nhân Viên', 'Nhân Viên',
            'mkv_manage_employees', 'mkv-employees', array($this, 'render_page'));
    }

    public function handle_checkin_checkout()
    {
        if (!is_user_logged_in()) wp_die('No permission');
        check_admin_referer('mkv_checkin_action');

        global $wpdb;
        $user_id = get_current_user_id();
        $action_type = sanitize_text_field($_POST['check_action'] ?? '');
        $today = current_time('Y-m-d');
        $now = current_time('mysql');

        // Check if there is already a record for today
        $record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mkv_timesheets WHERE user_id = %d AND work_date = %s",
            $user_id, $today
        ));

        if ($action_type === 'checkin') {
            if (!$record) {
                $wpdb->insert("{$wpdb->prefix}mkv_timesheets", array(
                    'user_id'       => $user_id,
                    'work_date'     => $today,
                    'check_in_time' => $now
                ));
            }
        } elseif ($action_type === 'checkout') {
            if ($record && !$record->check_out_time) {
                $wpdb->update(
                    "{$wpdb->prefix}mkv_timesheets",
                    array('check_out_time' => $now),
                    array('id' => $record->id)
                );
            }
        }

        wp_redirect(admin_url('admin.php?page=mkv-employees&tab=timesheets&message=success'));
        exit;
    }

    public function handle_add_employee()
    {
        if (!current_user_can('mkv_manage_employees')) wp_die('No permission');
        check_admin_referer('mkv_add_employee_action');

        $username = sanitize_user($_POST['username'] ?? '');
        $email    = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = sanitize_text_field($_POST['role'] ?? 'mkv_sales');
        $display_name = sanitize_text_field($_POST['display_name'] ?? '');

        if (!in_array($role, array('mkv_sales', 'mkv_warehouse', 'mkv_manager'), true)) {
            $role = 'mkv_sales';
        }

        if (username_exists($username) || email_exists($email)) {
            wp_redirect(admin_url('admin.php?page=mkv-employees&error=exists'));
            exit;
        }

        $user_id = wp_insert_user(array(
            'user_login' => $username,
            'user_pass'  => $password,
            'user_email' => $email,
            'display_name' => $display_name,
            'role'       => $role
        ));

        if (is_wp_error($user_id)) {
            wp_redirect(admin_url('admin.php?page=mkv-employees&error=failed'));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=mkv-employees&message=created'));
        exit;
    }

    public function handle_edit_employee()
    {
        if (!current_user_can('mkv_manage_employees')) wp_die('No permission');
        check_admin_referer('mkv_edit_employee_action');

        $user_id = intval($_POST['user_id'] ?? 0);
        if ($user_id <= 0) wp_die('Invalid user ID');

        $email    = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = sanitize_text_field($_POST['role'] ?? 'mkv_sales');
        $display_name = sanitize_text_field($_POST['display_name'] ?? '');

        if (!in_array($role, array('mkv_sales', 'mkv_warehouse', 'mkv_manager'), true)) {
            $role = 'mkv_sales';
        }

        // Check email uniqueness if email changed
        $current_user_info = get_userdata($user_id);
        if (!$current_user_info) {
            wp_die('Nhân viên không tồn tại.');
        }
        if (user_can($current_user_info, 'administrator') && !current_user_can('manage_options')) {
            wp_die('Không được phép chỉnh sửa tài khoản quản trị viên.');
        }
        if ($email !== $current_user_info->user_email && email_exists($email)) {
            wp_redirect(admin_url('admin.php?page=mkv-employees&error=exists'));
            exit;
        }

        $userdata = array(
            'ID'           => $user_id,
            'user_email'   => $email,
            'display_name' => $display_name,
            'role'         => $role
        );

        if (!empty($password)) {
            $userdata['user_pass'] = $password;
        }

        $result = wp_update_user($userdata);

        if (is_wp_error($result)) {
            wp_redirect(admin_url('admin.php?page=mkv-employees&error=failed'));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=mkv-employees&message=updated'));
        exit;
    }

    public function handle_delete_employee()
    {
        if (!current_user_can('administrator')) wp_die('No permission');
        check_admin_referer('mkv_delete_employee_action');

        $user_id = intval($_GET['user_id'] ?? 0);
        if ($user_id <= 0 || $user_id === get_current_user_id()) {
            wp_die('Invalid user ID or cannot delete yourself');
        }

        require_once(ABSPATH . 'wp-admin/includes/user.php');

        global $wpdb;
        // Check if employee has transactions
        $has_orders = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}mkv_orders WHERE created_by = %d LIMIT 1", $user_id));
        $has_cashbook = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}mkv_cashbook WHERE created_by = %d LIMIT 1", $user_id));
        
        if ($has_orders || $has_cashbook) {
            wp_die('Không thể xóa nhân viên đã tạo đơn hàng hoặc phiếu thu/chi. Vui lòng đổi mật khẩu hoặc phân quyền lại thay vì xóa để giữ nguyên lịch sử.');
        }
        
        // Reassign all posts/data to the current user (the one deleting)
        $reassign_id = get_current_user_id();
        
        if (wp_delete_user($user_id, $reassign_id)) {
            wp_redirect(admin_url('admin.php?page=mkv-employees&message=deleted'));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=mkv-employees&error=delete_failed'));
        exit;
    }

    public function render_page()
    {
        global $wpdb;
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'list';

        // Lấy danh sách user thuộc hệ thống MKV
        $args = array(
            'role__in' => array('administrator', 'mkv_manager', 'mkv_sales', 'mkv_warehouse')
        );
        $users = get_users($args);

        // Lấy dữ liệu chấm công
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : date('Y-m-01');
        $end_date   = isset($_GET['end_date'])   ? sanitize_text_field($_GET['end_date'])   : date('Y-m-t');
        
        $timesheets = array();
        if ($current_tab === 'timesheets') {
            $timesheets = $wpdb->get_results($wpdb->prepare(
                "SELECT t.*, u.display_name 
                 FROM {$wpdb->prefix}mkv_timesheets t
                 JOIN {$wpdb->prefix}users u ON t.user_id = u.ID
                 WHERE t.work_date BETWEEN %s AND %s
                 ORDER BY t.work_date DESC, t.check_in_time DESC",
                $start_date, $end_date
            ));
        }

        // Trạng thái chấm công hôm nay của user hiện tại
        $current_user_id = get_current_user_id();
        $today = current_time('Y-m-d');
        $my_today_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mkv_timesheets WHERE user_id = %d AND work_date = %s",
            $current_user_id, $today
        ));

        require_once MKV_DIR . 'includes/views/view-employees.php';
    }
}
