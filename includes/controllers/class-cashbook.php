<?php
if (!defined('ABSPATH')) exit;

class MKV_Cashbook
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_mkv_add_cashbook', array($this, 'handle_add_transaction'));
    }

    public function add_admin_menu()
    {
        add_submenu_page('mini-kiotviet', 'Sổ Quỹ', 'Sổ Quỹ',
            'mkv_manage_cashbook', 'mkv-cashbook', array($this, 'render_page'));
    }

    public function handle_add_transaction()
    {
        if (!current_user_can('mkv_manage_cashbook')) wp_die('No permission');
        check_admin_referer('mkv_cashbook_action');

        global $wpdb;

        $type   = sanitize_text_field($_POST['cb_type'] ?? 'thu');
        $method = sanitize_text_field($_POST['cb_method'] ?? 'cash');
        $amount = round(MKV_Orders::sanitize_money($_POST['cb_amount'] ?? 0));
        $note   = sanitize_textarea_field($_POST['cb_note'] ?? '');
        $customer_id = intval($_POST['cb_customer_id'] ?? 0);
        $supplier_id = intval($_POST['cb_supplier_id'] ?? 0);

        if ($amount > 0) {
            $wpdb->query('START TRANSACTION');
            $wpdb->suppress_errors(true);
            
            $res1 = $wpdb->insert("{$wpdb->prefix}mkv_cashbook", array(
                'type'       => $type,
                'method'     => $method,
                'amount'     => $amount,
                'customer_id'=> $customer_id > 0 ? $customer_id : null,
                'supplier_id'=> $supplier_id > 0 ? $supplier_id : null,
                'note'       => $note,
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ));
            
            $res2 = true;
            $res3 = true;
            
            // Xử lý trừ nợ khách hàng (nếu là Phiếu Thu)
            if ($type === 'thu' && $customer_id > 0) {
                $res2 = $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}mkv_customers SET total_debt = GREATEST(0, total_debt - %f) WHERE id = %d",
                    $amount, $customer_id
                ));

                // Tự động phân bổ số tiền thu nợ vào các đơn hàng còn nợ của Khách hàng (FIFO)
                $unpaid_orders = $wpdb->get_results($wpdb->prepare(
                    "SELECT id, total_amount, paid_amount, debt_amount, customer_debt_amount 
                     FROM {$wpdb->prefix}mkv_orders 
                     WHERE customer_id = %d AND (debt_amount > 0 OR customer_debt_amount > 0) AND status NOT IN ('cancelled', 'draft', 'returned') 
                     ORDER BY id ASC FOR UPDATE",
                    $customer_id
                ));
                if (!empty($unpaid_orders)) {
                    $rem_payment = (float) $amount;
                    foreach ($unpaid_orders as $uord) {
                        if ($rem_payment <= 0) break;
                        $cur_debt = (float) ($uord->customer_debt_amount > 0 ? $uord->customer_debt_amount : $uord->debt_amount);
                        if ($cur_debt <= 0) continue;

                        $pay = min($rem_payment, $cur_debt);
                        $new_paid = (float) $uord->paid_amount + $pay;
                        $new_debt = max(0.0, (float) $uord->total_amount - $new_paid);
                        $payment_status = ($new_paid >= (float) $uord->total_amount) ? 'paid' : 'partially_paid';

                        $wpdb->query($wpdb->prepare(
                            "UPDATE {$wpdb->prefix}mkv_orders 
                             SET paid_amount = %f, debt_amount = %f, customer_debt_amount = %f, payment_status = %s 
                             WHERE id = %d",
                            $new_paid, $new_debt, $new_debt, $payment_status, $uord->id
                        ));
                        $rem_payment -= $pay;
                    }
                }
            }
            
            // Xử lý trừ nợ nhà cung cấp (nếu là Phiếu Chi)
            if ($type === 'chi' && $supplier_id > 0) {
                $res3 = $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}mkv_suppliers SET total_debt = GREATEST(0, total_debt - %f) WHERE id = %d",
                    $amount, $supplier_id
                ));

                // Tự động phân bổ số tiền chi trả vào các phiếu nhập còn nợ của NCC (FIFO)
                $unpaid_pos = $wpdb->get_results($wpdb->prepare(
                    "SELECT id, total_amount, paid_amount 
                     FROM {$wpdb->prefix}mkv_purchase_orders 
                     WHERE supplier_id = %d AND paid_amount < total_amount 
                     ORDER BY id ASC FOR UPDATE",
                    $supplier_id
                ));
                if (!empty($unpaid_pos)) {
                    $rem_payment = (float) $amount;
                    foreach ($unpaid_pos as $upo) {
                        if ($rem_payment <= 0) break;
                        $needed = (float)$upo->total_amount - (float)$upo->paid_amount;
                        $pay = min($rem_payment, $needed);
                        $wpdb->query($wpdb->prepare(
                            "UPDATE {$wpdb->prefix}mkv_purchase_orders SET paid_amount = paid_amount + %f WHERE id = %d",
                            $pay, $upo->id
                        ));
                        $rem_payment -= $pay;
                    }
                }
            }
            
            // Add notification
            $title = $type === 'thu' ? 'Phiếu thu mới' : 'Phiếu chi mới';
            $res4 = $wpdb->insert("{$wpdb->prefix}mkv_notifications", array(
                'type'       => 'info',
                'title'      => $title,
                'message'    => "Đã ghi nhận 1 phiếu $type số tiền " . number_format($amount) . " ₫",
                'is_read'    => 0,
                'created_at' => current_time('mysql')
            ));

            if ($res1 !== false && $res2 !== false && $res3 !== false && $res4 !== false && empty($wpdb->last_error)) {
                $wpdb->query('COMMIT');
            } else {
                $wpdb->query('ROLLBACK');
                if (!empty($wpdb->last_error)) {
                    error_log('[Mini-KiotViet] Cashbook transaction error: ' . $wpdb->last_error);
                }
                wp_die('Đã xảy ra lỗi trong quá trình xử lý giao dịch. Vui lòng thử lại.');
            }
            
            $wpdb->suppress_errors(false);
        }

        wp_redirect(admin_url('admin.php?page=mkv-cashbook&message=success'));
        exit;
    }

    public function render_page()
    {
        global $wpdb;
        
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : current_time('Y-m-01');
        $end_date   = isset($_GET['end_date'])   ? sanitize_text_field($_GET['end_date'])   : current_time('Y-m-t');
        $start_dt   = $start_date . ' 00:00:00';
        $end_dt     = $end_date   . ' 23:59:59';

        $per_page = 20;
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($paged - 1) * $per_page;

        $total_items = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}mkv_cashbook WHERE created_at BETWEEN %s AND %s", $start_dt, $end_dt));
        $total_pages = ceil($total_items / $per_page);

        // Lấy danh sách giao dịch (nối thêm tên KH/NCC)
        $transactions = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, u.display_name, cust.name as customer_name, sup.name as supplier_name
             FROM {$wpdb->prefix}mkv_cashbook c
             LEFT JOIN {$wpdb->prefix}users u ON c.created_by = u.ID
             LEFT JOIN {$wpdb->prefix}mkv_customers cust ON c.customer_id = cust.id
             LEFT JOIN {$wpdb->prefix}mkv_suppliers sup ON c.supplier_id = sup.id
             WHERE c.created_at BETWEEN %s AND %s
             ORDER BY c.created_at DESC LIMIT %d OFFSET %d",
            $start_dt, $end_dt, $per_page, $offset
        ));

        // Lấy danh sách KH và NCC để hiển thị trong dropdown lập phiếu
        $customers = $wpdb->get_results("SELECT id, name, phone, total_debt FROM {$wpdb->prefix}mkv_customers WHERE total_debt > 0 ORDER BY name ASC");
        $suppliers = $wpdb->get_results("SELECT id, name, phone, total_debt FROM {$wpdb->prefix}mkv_suppliers WHERE total_debt > 0 ORDER BY name ASC");

        // Tính tổng tồn
        $total_thu = (float) $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}mkv_cashbook WHERE type='thu'");
        $total_chi = (float) $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}mkv_cashbook WHERE type='chi'");
        $ton_quy   = $total_thu - $total_chi;

        // Tính tồn quỹ trong kỳ
        $ky_thu = (float) $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}mkv_cashbook WHERE type='thu' AND created_at BETWEEN %s AND %s", $start_dt, $end_dt));
        $ky_chi = (float) $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}mkv_cashbook WHERE type='chi' AND created_at BETWEEN %s AND %s", $start_dt, $end_dt));

        require_once MKV_DIR . 'includes/views/view-cashbook.php';
    }
}
