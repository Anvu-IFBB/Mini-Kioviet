<?php
if (!defined('ABSPATH')) exit;

class MKV_Purchases
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_mkv_add_supplier', array($this, 'handle_add_supplier'));
        add_action('admin_post_mkv_add_purchase_order', array($this, 'handle_add_purchase_order'));
        add_action('wp_ajax_mkv_get_po_detail', array($this, 'ajax_get_po_detail'));
    }

    public function add_admin_menu()
    {
        add_submenu_page('mini-kiotviet', 'Nhập Hàng', 'Nhập Hàng',
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
        $location_id = intval($_POST['location_id'] ?? 1);
        if ($location_id <= 0) $location_id = 1;

        $product_exists = $product_id > 0 && get_post_type($product_id) === 'mkv_product';
        $supplier_exists = $supplier_id <= 0 || (bool) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}mkv_suppliers WHERE id=%d",
            $supplier_id
        ));
        $location_exists = (bool) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}mkv_locations WHERE id=%d",
            $location_id
        ));
        
        if ($qty > 0 && $product_exists && $supplier_exists && $location_exists && $price >= 0) {
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
                'location_id' => $location_id,
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
                    'reference_id' => $po_id,
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

        // Đảm bảo cột location_id tồn tại an toàn
        if (!$wpdb->get_var("SHOW COLUMNS FROM {$wpdb->prefix}mkv_purchase_orders LIKE 'location_id'")) {
            $wpdb->query("ALTER TABLE {$wpdb->prefix}mkv_purchase_orders ADD COLUMN location_id bigint(20) DEFAULT 1 AFTER supplier_id");
        }

        if ($current_tab === 'suppliers') {
            $suppliers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mkv_suppliers ORDER BY id DESC");
        } elseif ($current_tab === 'create') {
            $suppliers = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}mkv_suppliers ORDER BY name ASC");
            $products = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_type='mkv_product' AND post_status='publish' ORDER BY post_title ASC");
            $locations = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}mkv_locations ORDER BY name ASC");

            // Ma trận tồn kho theo sản phẩm & kho để hiển thị tức thời cho người dùng
            $stock_matrix = array();
            $stock_rows = $wpdb->get_results("SELECT product_id, location_id, stock FROM {$wpdb->prefix}mkv_inventory_stock");
            foreach ($stock_rows as $sr) {
                $stock_matrix[$sr->product_id][$sr->location_id] = (int)$sr->stock;
            }

            $products_meta = array();
            foreach ($products as $p) {
                $products_meta[$p->ID] = array(
                    'price_in' => (float)get_post_meta($p->ID, '_mkv_price_in', true) ?: (float)get_post_meta($p->ID, '_mkv_cost_price', true),
                    'sku'      => get_post_meta($p->ID, '_mkv_sku', true) ?: '',
                    'unit'     => get_post_meta($p->ID, '_mkv_unit', true) ?: 'Cái'
                );
            }
        } else {
            // list POs với thông tin chi tiết: Nhà cung cấp, Kho, Người tạo
            $pos = $wpdb->get_results(
                "SELECT po.*, s.name as supplier_name, s.phone as supplier_phone, s.total_debt as supplier_debt,
                        loc.name as location_name, u.display_name as creator_name 
                 FROM {$wpdb->prefix}mkv_purchase_orders po
                 LEFT JOIN {$wpdb->prefix}mkv_suppliers s ON po.supplier_id = s.id
                 LEFT JOIN {$wpdb->prefix}mkv_locations loc ON po.location_id = loc.id
                 LEFT JOIN {$wpdb->users} u ON po.created_by = u.ID
                 ORDER BY po.id DESC"
            );

            // Lấy danh sách sản phẩm trong từng phiếu nhập
            $po_items = array();
            if (!empty($pos)) {
                $po_ids = wp_list_pluck($pos, 'id');
                $placeholders = implode(',', array_fill(0, count($po_ids), '%d'));
                $raw_items = $wpdb->get_results($wpdb->prepare(
                    "SELECT poi.*, p.post_title, 
                            m_sku.meta_value as sku,
                            m_unit.meta_value as unit
                     FROM {$wpdb->prefix}mkv_purchase_order_items poi
                     LEFT JOIN {$wpdb->posts} p ON poi.product_id = p.ID
                     LEFT JOIN {$wpdb->postmeta} m_sku ON p.ID = m_sku.post_id AND m_sku.meta_key = '_mkv_sku'
                     LEFT JOIN {$wpdb->postmeta} m_unit ON p.ID = m_unit.post_id AND m_unit.meta_key = '_mkv_unit'
                     WHERE poi.po_id IN ($placeholders)
                     ORDER BY poi.id ASC",
                    $po_ids
                ));
                foreach ($raw_items as $it) {
                    $po_items[$it->po_id][] = $it;
                }
            }
        }

        require_once MKV_DIR . 'includes/views/view-purchases.php';
    }

    public function ajax_get_po_detail()
    {
        if (!current_user_can('mkv_manage_purchases')) {
            wp_send_json_error(array('message' => 'Bạn không có quyền thực hiện thao tác này.'));
        }
        check_ajax_referer('mkv_po_detail_nonce', 'nonce');
        global $wpdb;

        $po_id = intval($_GET['po_id'] ?? 0);
        if ($po_id <= 0) {
            wp_send_json_error(array('message' => 'Mã phiếu nhập không hợp lệ.'));
        }

        $po = $wpdb->get_row($wpdb->prepare(
            "SELECT po.*, s.name as supplier_name, s.phone as supplier_phone, s.total_debt as supplier_debt,
                    loc.name as location_name, loc.address as location_address, u.display_name as creator_name
             FROM {$wpdb->prefix}mkv_purchase_orders po
             LEFT JOIN {$wpdb->prefix}mkv_suppliers s ON po.supplier_id = s.id
             LEFT JOIN {$wpdb->prefix}mkv_locations loc ON po.location_id = loc.id
             LEFT JOIN {$wpdb->users} u ON po.created_by = u.ID
             WHERE po.id = %d",
            $po_id
        ));

        if (!$po) {
            wp_send_json_error(array('message' => 'Không tìm thấy phiếu nhập này.'));
        }

        $location_id = $po->location_id ?: 1;

        // Chi tiết danh sách hàng hóa kèm tồn kho hiện tại ở kho này
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT poi.*, p.post_title,
                    m_sku.meta_value as sku,
                    m_unit.meta_value as unit,
                    COALESCE(stk.stock, 0) as current_stock
             FROM {$wpdb->prefix}mkv_purchase_order_items poi
             LEFT JOIN {$wpdb->posts} p ON poi.product_id = p.ID
             LEFT JOIN {$wpdb->postmeta} m_sku ON p.ID = m_sku.post_id AND m_sku.meta_key = '_mkv_sku'
             LEFT JOIN {$wpdb->postmeta} m_unit ON p.ID = m_unit.post_id AND m_unit.meta_key = '_mkv_unit'
             LEFT JOIN {$wpdb->prefix}mkv_inventory_stock stk ON stk.product_id = poi.product_id AND stk.location_id = %d
             WHERE poi.po_id = %d
             ORDER BY poi.id ASC",
            $location_id,
            $po_id
        ));

        // Lịch sử phiếu chi liên quan từ Sổ Quỹ
        $payments = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, u.display_name as creator_name
             FROM {$wpdb->prefix}mkv_cashbook c
             LEFT JOIN {$wpdb->users} u ON c.created_by = u.ID
             WHERE c.type = 'chi' AND (c.note LIKE %s OR (c.reference_id = %d AND c.supplier_id IS NOT NULL))
             ORDER BY c.id ASC",
            '%' . $wpdb->esc_like($po->code) . '%',
            $po_id
        ));

        // Lịch sử biến động thẻ kho
        $inventory_logs = $wpdb->get_results($wpdb->prepare(
            "SELECT l.*, loc.name as location_name, u.display_name as creator_name
             FROM {$wpdb->prefix}mkv_inventory_logs l
             LEFT JOIN {$wpdb->prefix}mkv_locations loc ON l.location_id = loc.id
             LEFT JOIN {$wpdb->users} u ON l.created_by = u.ID
             WHERE l.note LIKE %s
             ORDER BY l.id ASC",
            '%' . $wpdb->esc_like($po->code) . '%'
        ));

        wp_send_json_success(array(
            'po'       => $po,
            'items'    => $items,
            'payments' => $payments,
            'logs'     => $inventory_logs
        ));
    }
}
