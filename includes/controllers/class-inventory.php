<?php
if (!defined('ABSPATH')) exit;

class MKV_Inventory
{
    public function __construct()
    {
        add_action('admin_menu',                         array($this, 'add_admin_menu'));
        add_action('admin_post_mkv_add_location',        array($this, 'handle_add_location'));
        add_action('admin_post_mkv_adjust_stock',        array($this, 'handle_adjust_stock'));
        add_action('admin_post_mkv_transfer_stock',      array($this, 'handle_transfer_stock'));
        add_action('admin_post_mkv_create_stocktake',    array($this, 'handle_create_stocktake'));
    }

    public function add_admin_menu()
    {
        add_submenu_page(
            'mini-kiotviet', 'Quản lý Kho', 'Quản lý Kho',
            'mkv_manage_inventory', 'mkv-inventory',
            array($this, 'render_inventory_page')
        );
    }

    public function render_inventory_page()
    {
        global $wpdb;
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'locations';

        $locations = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mkv_locations ORDER BY id ASC");
        $products  = array();
        $logs      = array();

        if ($active_tab === 'stock') {
            $products = get_posts(array('post_type' => 'mkv_product', 'numberposts' => -1, 'post_status' => 'publish'));
        } elseif ($active_tab === 'logs') {
            $all_products = get_posts(array('post_type' => 'mkv_product', 'numberposts' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC'));
            
            $filter_product_id  = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
            $filter_location_id = isset($_GET['location_id']) ? intval($_GET['location_id']) : 0;
            $filter_type        = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
            $filter_date_from   = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
            $filter_date_to     = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

            $where = array("1=1");
            if ($filter_product_id > 0) {
                $where[] = $wpdb->prepare("l.product_id = %d", $filter_product_id);
            }
            if ($filter_location_id > 0) {
                $where[] = $wpdb->prepare("(l.location_id = %d OR l.location_id_to = %d)", $filter_location_id, $filter_location_id);
            }
            if (!empty($filter_type)) {
                $where[] = $wpdb->prepare("l.type = %s", $filter_type);
            }
            if (!empty($filter_date_from)) {
                $where[] = $wpdb->prepare("l.created_at >= %s", $filter_date_from . ' 00:00:00');
            }
            if (!empty($filter_date_to)) {
                $where[] = $wpdb->prepare("l.created_at <= %s", $filter_date_to . ' 23:59:59');
            }

            $where_sql = implode(' AND ', $where);

            // Thống kê Thẻ kho
            $ledger_stats = array(
                'total_in'   => 0,
                'total_out'  => 0,
                'current_stock' => 0,
                'total_trans'   => 0,
            );

            if ($filter_product_id > 0) {
                $loc_cond = $filter_location_id > 0 ? $wpdb->prepare("AND location_id = %d", $filter_location_id) : "";
                $ledger_stats['current_stock'] = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(stock) FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id = %d {$loc_cond}",
                    $filter_product_id
                ));
            }

            $in_sum = (int) $wpdb->get_var("SELECT SUM(l.qty) FROM {$wpdb->prefix}mkv_inventory_logs l WHERE {$where_sql} AND l.type = 'in'");
            $out_sum = (int) $wpdb->get_var("SELECT SUM(l.qty) FROM {$wpdb->prefix}mkv_inventory_logs l WHERE {$where_sql} AND l.type = 'out'");
            $ledger_stats['total_in'] = $in_sum;
            $ledger_stats['total_out'] = $out_sum;

            $logs = $wpdb->get_results(
                "SELECT l.*, p.post_title as product_name, lf.name as location_from, lt.name as location_to, u.display_name as user_name
                 FROM {$wpdb->prefix}mkv_inventory_logs l
                 LEFT JOIN {$wpdb->prefix}posts p        ON l.product_id     = p.ID
                 LEFT JOIN {$wpdb->prefix}mkv_locations lf ON l.location_id  = lf.id
                 LEFT JOIN {$wpdb->prefix}mkv_locations lt ON l.location_id_to = lt.id
                 LEFT JOIN {$wpdb->prefix}users u         ON l.created_by    = u.ID
                 WHERE {$where_sql}
                 ORDER BY l.created_at DESC, l.id DESC LIMIT 200"
            );
            $ledger_stats['total_trans'] = count($logs);
        } elseif ($active_tab === 'transfer') {
            // data for transfer form already in $locations
        } elseif ($active_tab === 'stocktake') {
            $products = get_posts(array('post_type' => 'mkv_product', 'numberposts' => -1, 'post_status' => 'publish'));
            $stocktakes = $wpdb->get_results(
                "SELECT s.*, l.name as loc_name, u.display_name as user_name
                 FROM {$wpdb->prefix}mkv_stocktakes s
                 LEFT JOIN {$wpdb->prefix}mkv_locations l ON s.location_id = l.id
                 LEFT JOIN {$wpdb->prefix}users u ON s.created_by = u.ID
                 ORDER BY s.id DESC LIMIT 50"
            );
        }

        require_once MKV_DIR . 'includes/views/view-inventory.php';
    }

    public function handle_add_location()
    {
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mkv_add_location_nonce') || !current_user_can('mkv_manage_inventory'))
            wp_die('Không có quyền.');

        global $wpdb;
        $name    = sanitize_text_field($_POST['loc_name'] ?? '');
        $code    = sanitize_text_field($_POST['loc_code'] ?? '');
        $address = sanitize_text_field($_POST['loc_address'] ?? '');
        if (!empty($name)) {
            $wpdb->insert("{$wpdb->prefix}mkv_locations", array('name' => $name, 'code' => $code, 'address' => $address, 'status' => 'active'));
        }
        wp_redirect(admin_url('admin.php?page=mkv-inventory&tab=locations&added=1'));
        exit;
    }

    public function handle_adjust_stock()
    {
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mkv_adjust_stock_nonce') || !current_user_can('mkv_manage_inventory'))
            wp_die('Không có quyền.');

        global $wpdb;
        $product_id  = intval($_POST['product_id'] ?? 0);
        $location_id = intval($_POST['location_id'] ?? 0);
        $type        = sanitize_text_field($_POST['type'] ?? 'in');
        $qty         = intval($_POST['qty'] ?? 0);
        $note        = sanitize_text_field($_POST['note'] ?? '');
        $user_id     = get_current_user_id();

        if ($product_id > 0 && $qty > 0 && $location_id > 0) {
            $wpdb->query('START TRANSACTION');
            $wpdb->suppress_errors(true);
            
            if ($type === 'in') {
                $res1 = $wpdb->query($wpdb->prepare(
                    "INSERT INTO {$wpdb->prefix}mkv_inventory_stock (product_id, location_id, stock) VALUES (%d, %d, %d) ON DUPLICATE KEY UPDATE stock = stock + %d",
                    $product_id, $location_id, $qty, $qty
                ));
            } else {
                $res1 = $wpdb->query($wpdb->prepare(
                    "INSERT INTO {$wpdb->prefix}mkv_inventory_stock (product_id, location_id, stock) VALUES (%d, %d, -%d) ON DUPLICATE KEY UPDATE stock = stock - %d",
                    $product_id, $location_id, $qty, $qty
                ));
            }

            // Tổng tồn kho tất cả kho → đồng bộ meta
            $total = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(stock) FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d FOR UPDATE", $product_id
            ));
            update_post_meta($product_id, '_mkv_stock', $total);

            $res2 = $wpdb->insert("{$wpdb->prefix}mkv_inventory_logs", array(
                'product_id'  => $product_id,
                'location_id' => $location_id,
                'type'        => $type,
                'qty'         => $qty,
                'note'        => $note,
                'created_by'  => $user_id,
                'created_at'  => current_time('mysql'),
            ));
            
            if ($res1 !== false && $res2 !== false && empty($wpdb->last_error)) {
                $wpdb->query('COMMIT');
            } else {
                $wpdb->query('ROLLBACK');
                $error_msg = $wpdb->last_error ? $wpdb->last_error : 'Không thể thực thi CSDL';
                wp_die('Lỗi hệ thống: ' . $error_msg);
            }
            $wpdb->suppress_errors(false);
        }
        wp_redirect(admin_url('admin.php?page=mkv-inventory&tab=stock&updated=1'));
        exit;
    }

    /**
     * Chuyển kho — dùng DB Transaction để đảm bảo toàn vẹn dữ liệu
     */
    public function handle_transfer_stock()
    {
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mkv_transfer_stock_nonce') || !current_user_can('mkv_manage_inventory'))
            wp_die('Không có quyền.');

        global $wpdb;
        $product_id   = intval($_POST['product_id'] ?? 0);
        $location_from = intval($_POST['location_from'] ?? 0);
        $location_to   = intval($_POST['location_to'] ?? 0);
        $qty          = intval($_POST['qty'] ?? 0);
        $note         = sanitize_text_field($_POST['note'] ?? '');
        $user_id      = get_current_user_id();

        if ($product_id <= 0 || $qty <= 0 || $location_from <= 0 || $location_to <= 0 || $location_from === $location_to) {
            wp_redirect(admin_url('admin.php?page=mkv-inventory&tab=transfer&error=invalid'));
            exit;
        }

        // === BẮT ĐẦU TRANSACTION ===
        $wpdb->query('START TRANSACTION');
        $error = false;

        // Trừ kho nguồn bằng Atomic Update. Ta dùng WHERE stock >= qty để đảm bảo không bị âm.
        $r1 = $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}mkv_inventory_stock SET stock = stock - %d WHERE product_id = %d AND location_id = %d AND stock >= %d",
            $qty, $product_id, $location_from, $qty
        ));

        if ($r1 === false || $r1 === 0) {
            $wpdb->query('ROLLBACK');
            wp_redirect(admin_url('admin.php?page=mkv-inventory&tab=transfer&error=insufficient'));
            exit;
        }

        // Cộng kho đích bằng Atomic Update
        $r2 = $wpdb->query($wpdb->prepare(
            "INSERT INTO {$wpdb->prefix}mkv_inventory_stock (product_id, location_id, stock) VALUES (%d, %d, %d) ON DUPLICATE KEY UPDATE stock = stock + %d",
            $product_id, $location_to, $qty, $qty
        ));
        
        if ($r2 === false) { $error = true; }

        if ($error) {
            $wpdb->query('ROLLBACK');
            wp_redirect(admin_url('admin.php?page=mkv-inventory&tab=transfer&error=db'));
            exit;
        }

        // Ghi log chuyển kho
        $wpdb->insert("{$wpdb->prefix}mkv_inventory_logs", array(
            'product_id'    => $product_id,
            'location_id'   => $location_from,
            'location_id_to'=> $location_to,
            'type'          => 'transfer',
            'qty'           => $qty,
            'note'          => $note,
            'created_by'    => $user_id,
            'created_at'    => current_time('mysql'),
        ));

        // Cập nhật meta tổng tồn kho
        $total = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(stock) FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d FOR UPDATE", $product_id
        ));
        update_post_meta($product_id, '_mkv_stock', $total);

        $wpdb->query('COMMIT');
        // === KẾT THÚC TRANSACTION ===

        wp_redirect(admin_url('admin.php?page=mkv-inventory&tab=transfer&success=1'));
        exit;
    }

    public function handle_create_stocktake()
    {
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mkv_create_stocktake_nonce') || !current_user_can('mkv_manage_inventory'))
            wp_die('Không có quyền.');

        global $wpdb;
        $location_id = intval($_POST['location_id'] ?? 0);
        $product_id  = intval($_POST['product_id'] ?? 0);
        $actual_qty  = intval($_POST['actual_qty'] ?? 0);
        $note        = sanitize_text_field($_POST['note'] ?? '');
        
        if ($location_id <= 0 || $product_id <= 0 || $actual_qty < 0) {
            wp_die('Dữ liệu không hợp lệ.');
        }

        $wpdb->query('START TRANSACTION');
        $wpdb->suppress_errors(true);
        $error = false;
        
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT id, stock FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d AND location_id=%d FOR UPDATE",
            $product_id, $location_id
        ));
        $sys_qty = $row ? (int)$row->stock : 0;
        $diff = $actual_qty - $sys_qty;

        $code = 'KK' . date('ymdHis');
        $r1 = $wpdb->insert("{$wpdb->prefix}mkv_stocktakes", array(
            'code'        => $code,
            'location_id' => $location_id,
            'status'      => 'completed',
            'total_diff'  => $diff,
            'note'        => $note,
            'created_by'  => get_current_user_id(),
            'created_at'  => current_time('mysql'),
        ));
        if ($r1 === false) $error = true;
        
        $st_id = $wpdb->insert_id;

        $r2 = $wpdb->insert("{$wpdb->prefix}mkv_stocktake_items", array(
            'stocktake_id' => $st_id,
            'product_id'   => $product_id,
            'sys_qty'      => $sys_qty,
            'actual_qty'   => $actual_qty,
            'diff_qty'     => $diff
        ));
        if ($r2 === false) $error = true;

        // Cập nhật tồn kho thực tế
        if ($row) {
            $r3 = $wpdb->update("{$wpdb->prefix}mkv_inventory_stock", array('stock' => $actual_qty), array('id' => $row->id));
        } else {
            $r3 = $wpdb->insert("{$wpdb->prefix}mkv_inventory_stock", array('product_id' => $product_id, 'location_id' => $location_id, 'stock' => $actual_qty));
        }
        if ($r3 === false) $error = true;

        // Cập nhật meta tổng
        $total = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(stock) FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d FOR UPDATE", $product_id
        ));
        update_post_meta($product_id, '_mkv_stock', $total);

        // Ghi log
        if ($diff !== 0) {
            $r4 = $wpdb->insert("{$wpdb->prefix}mkv_inventory_logs", array(
                'product_id'  => $product_id,
                'location_id' => $location_id,
                'type'        => $diff > 0 ? 'in' : 'out',
                'qty'         => abs($diff),
                'note'        => 'Cân bằng kho (Kiểm kho ' . $code . ')',
                'created_by'  => get_current_user_id(),
                'created_at'  => current_time('mysql'),
            ));
            if ($r4 === false) $error = true;
        }

        if (!$error && empty($wpdb->last_error)) {
            $wpdb->query('COMMIT');
        } else {
            $wpdb->query('ROLLBACK');
            $error_msg = $wpdb->last_error ? $wpdb->last_error : 'Không thể thực thi CSDL';
            wp_die('Lỗi hệ thống: ' . $error_msg);
        }
        $wpdb->suppress_errors(false);

        wp_redirect(admin_url('admin.php?page=mkv-inventory&tab=stocktake&success=1'));
        exit;
    }
}
