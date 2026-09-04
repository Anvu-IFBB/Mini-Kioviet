<?php
if (!defined('ABSPATH')) exit;

class MKV_Purchases
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_mkv_add_supplier', array($this, 'handle_add_supplier'));
        add_action('admin_post_mkv_add_purchase_order', array($this, 'handle_add_purchase_order'));
    }

    public function add_admin_menu()
    {
        add_submenu_page('mini-kiotviet', 'Mua Hàng', 'Mua Hàng',
            'mkv_manage_purchases', 'mkv-purchases', array($this, 'render_page'));
    }

    public function handle_add_supplier()
    {
        if (!current_user_can('mkv_manage_purchases')) wp_die('No permission');
        check_admin_referer('mkv_supplier_action');
        global $wpdb;

        $name = sanitize_text_field($_POST['sup_name'] ?? '');
        $phone = sanitize_text_field($_POST['sup_phone'] ?? '');

        if (!empty($name)) {
            $wpdb->insert("{$wpdb->prefix}mkv_suppliers", array('name' => $name, 'phone' => $phone));
        }

        wp_redirect(admin_url('admin.php?page=mkv-purchases&tab=suppliers&message=success'));
        exit;
    }

    public function handle_add_purchase_order()
    {
        if (!current_user_can('mkv_manage_purchases')) wp_die('No permission');
        check_admin_referer('mkv_po_action');
        global $wpdb;

        $supplier_id = intval($_POST['supplier_id'] ?? 0);
        $product_id = intval($_POST['product_id'] ?? 0);
        $qty = intval($_POST['qty'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        
        if ($qty > 0 && $product_id > 0 && $price >= 0) {
            $total = $qty * $price;
            $paid_amount = isset($_POST['paid_amount']) && $_POST['paid_amount'] !== '' ? floatval($_POST['paid_amount']) : $total;
            $paid_amount = max(0, min($total, $paid_amount)); // Không được trả hơn số phải trả và không được âm

            $code = 'PON' . date('ymdHis');
            
            $wpdb->query('START TRANSACTION');
            $wpdb->suppress_errors(true);
            $error = false;
            
            // Create PO
            $r1 = $wpdb->insert("{$wpdb->prefix}mkv_purchase_orders", array(
                'code' => $code,
                'supplier_id' => $supplier_id ?: null,
                'total_amount' => $total,
                'paid_amount' => $paid_amount,
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ));
            if ($r1 === false) $error = true;
            
            $po_id = $wpdb->insert_id;

            // Create PO Item
            $r2 = $wpdb->insert("{$wpdb->prefix}mkv_purchase_order_items", array(
                'po_id' => $po_id,
                'product_id' => $product_id,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $total
            ));
            if ($r2 === false) $error = true;

            $location_id = intval($_POST['location_id'] ?? 1);
            if ($location_id <= 0) $location_id = 1;

            // Update Stock
            $r3 = $wpdb->query($wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}mkv_inventory_stock (product_id, location_id, stock) 
                 VALUES (%d, %d, %d) 
                 ON DUPLICATE KEY UPDATE stock = stock + %d",
                $product_id, $location_id, $qty, $qty
            ));
            if ($r3 === false) $error = true;

            // Log the inventory change
            $r4 = $wpdb->insert("{$wpdb->prefix}mkv_inventory_logs", array(
                'product_id'  => $product_id,
                'location_id' => $location_id,
                'type'        => 'in',
                'qty'         => $qty,
                'note'        => 'Nhập hàng từ phiếu ' . $code,
                'created_by'  => get_current_user_id(),
                'created_at'  => current_time('mysql')
            ));
            if ($r4 === false) $error = true;

            // Update product meta cost price and total stock
            update_post_meta($product_id, '_mkv_price_in', $price);
            
            $total_stock = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(stock) FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d FOR UPDATE", $product_id
            ));
            update_post_meta($product_id, '_mkv_stock', $total_stock);

            // Add Cashbook entry (Chi tiền nhập hàng)
            if ($paid_amount > 0) {
                $r5 = $wpdb->insert("{$wpdb->prefix}mkv_cashbook", array(
                    'type' => 'chi',
                    'method' => 'cash',
                    'amount' => $paid_amount,
                    'supplier_id' => $supplier_id ?: null,
                    'note' => 'Thanh toán phiếu nhập ' . $code,
                    'created_by' => get_current_user_id(),
                    'created_at' => current_time('mysql')
                ));
                if ($r5 === false) $error = true;
            }

            // Ghi nhận công nợ nếu có
            if ($supplier_id > 0 && $paid_amount < $total) {
                $debt = $total - $paid_amount;
                $r6 = $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}mkv_suppliers SET total_debt = total_debt + %f WHERE id = %d",
                    $debt, $supplier_id
                ));
                if ($r6 === false) $error = true;
            }

            // Notification
            $r7 = $wpdb->insert("{$wpdb->prefix}mkv_notifications", array(
                'type' => 'success',
                'title' => 'Nhập hàng thành công',
                'message' => 'Phiếu nhập ' . $code . ' đã hoàn tất. Tồn kho đã được cập nhật.',
                'is_read' => 0,
                'created_at' => current_time('mysql')
            ));
            if ($r7 === false) $error = true;

            if (!$error && empty($wpdb->last_error)) {
                $wpdb->query('COMMIT');
            } else {
                $wpdb->query('ROLLBACK');
                $error_msg = $wpdb->last_error ? $wpdb->last_error : 'Không thể thực thi CSDL';
                wp_die('Lỗi hệ thống: ' . $error_msg);
            }
            $wpdb->suppress_errors(false);
        }

        wp_redirect(admin_url('admin.php?page=mkv-purchases&tab=list&message=success'));
        exit;
    }

    public function render_page()
    {
        global $wpdb;
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'list';

        if ($current_tab === 'suppliers') {
            $suppliers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mkv_suppliers ORDER BY id DESC");
        } elseif ($current_tab === 'create') {
            $suppliers = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}mkv_suppliers ORDER BY name ASC");
            $products = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_type='mkv_product' AND post_status='publish'");
            $locations = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}mkv_locations ORDER BY name ASC");
        } else {
            // list POs
            $pos = $wpdb->get_results(
                "SELECT po.*, s.name as supplier_name 
                 FROM {$wpdb->prefix}mkv_purchase_orders po
                 LEFT JOIN {$wpdb->prefix}mkv_suppliers s ON po.supplier_id = s.id
                 ORDER BY po.id DESC"
            );
        }

        require_once MKV_DIR . 'includes/views/view-purchases.php';
    }
}
