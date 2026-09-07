<?php
if (!defined('ABSPATH')) exit;

class MKV_Reports
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets($hook)
    {
        if ($hook === 'mini-kiotviet_page_mkv-reports') {
            wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.0', true);
        }
    }

    public function add_admin_menu()
    {
        add_submenu_page('mini-kiotviet', 'Báo Cáo', 'Báo Cáo',
            'mkv_manage_reports', 'mkv-reports', array($this, 'render_reports_page'));
    }

    public function render_reports_page()
    {
        global $wpdb;

        // Tabs
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'sales';

        // Quick filter presets
        $quick = isset($_GET['quick']) ? sanitize_text_field($_GET['quick']) : '';
        switch ($quick) {
            case 'today':
                $start_date = $end_date = current_time('Y-m-d');
                break;
            case 'yesterday':
                $start_date = $end_date = gmdate('Y-m-d', current_time('timestamp') - DAY_IN_SECONDS);
                break;
            case 'week':
                $start_date = gmdate('Y-m-d', strtotime('monday this week', current_time('timestamp')));
                $end_date   = gmdate('Y-m-d', strtotime('sunday this week', current_time('timestamp')));
                break;
            case 'month':
                $start_date = current_time('Y-m-01');
                $end_date   = current_time('Y-m-t');
                break;
            case 'year':
                $start_date = current_time('Y-01-01');
                $end_date   = current_time('Y-12-31');
                break;
            default:
                $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : current_time('Y-m-01');
                $end_date   = isset($_GET['end_date'])   ? sanitize_text_field($_GET['end_date'])   : current_time('Y-m-t');
        }

        $start_dt = $start_date . ' 00:00:00';
        $end_dt   = $end_date   . ' 23:59:59';

        // --- Xuất CSV ---
        if (!empty($_GET['export_csv'])) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="bao-cao-' . $active_tab . '-' . date('Ymd') . '.csv"');
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($active_tab === 'sales') {
                fputcsv($output, array('Mã đơn', 'Khách hàng', 'Tổng tiền', 'Trạng thái', 'Ngày tạo'));
                $orders_export = $wpdb->get_results($wpdb->prepare(
                    "SELECT o.*, c.name as customer_name FROM {$wpdb->prefix}mkv_orders o
                     LEFT JOIN {$wpdb->prefix}mkv_customers c ON o.customer_id = c.id
                     WHERE o.created_at BETWEEN %s AND %s ORDER BY o.created_at DESC",
                    $start_dt, $end_dt
                ));
                foreach ($orders_export as $o) {
                    fputcsv($output, array($o->order_code, $o->customer_name ?: 'Khách lẻ', $o->total_amount, $o->status, $o->created_at));
                }
            } elseif ($active_tab === 'profit') {
                fputcsv($output, array('Sản phẩm', 'Số lượng bán', 'Doanh thu', 'Tiền vốn', 'Lợi nhuận gộp'));
                $top_products_export = $wpdb->get_results($wpdb->prepare(
                    "SELECT p.post_title as name, SUM(oi.qty) as total_sold, SUM(oi.subtotal) as total_revenue,
                            SUM(oi.qty * oi.cost_price) as total_cost
                     FROM {$wpdb->prefix}mkv_order_items oi
                     JOIN {$wpdb->prefix}posts p ON oi.product_id = p.ID
                     JOIN {$wpdb->prefix}mkv_orders o ON oi.order_id = o.id
                     WHERE o.created_at BETWEEN %s AND %s AND o.status IN ('paid', 'shipping', 'completed')
                     GROUP BY oi.product_id ORDER BY total_sold DESC",
                    $start_dt, $end_dt
                ));
                foreach ($top_products_export as $tp) {
                    fputcsv($output, array($tp->name, $tp->total_sold, $tp->total_revenue, $tp->total_cost, $tp->total_revenue - $tp->total_cost));
                }
            } elseif ($active_tab === 'end_of_day') {
                fputcsv($output, array('Phương thức', 'Loại', 'Tổng tiền'));
                $cashbook_export = $wpdb->get_results($wpdb->prepare(
                    "SELECT method, type, SUM(amount) as total FROM {$wpdb->prefix}mkv_cashbook
                     WHERE created_at BETWEEN %s AND %s GROUP BY method, type",
                    $start_dt, $end_dt
                ));
                foreach ($cashbook_export as $cb) {
                    fputcsv($output, array($cb->method, $cb->type, $cb->total));
                }
            }
            fclose($output);
            exit;
        }

        // --- Thống kê tổng ---
        $gross_revenue = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(GREATEST(0, total_amount - shipping_fee)) FROM {$wpdb->prefix}mkv_orders WHERE created_at BETWEEN %s AND %s AND status IN ('paid', 'completed')",
            $start_dt, $end_dt
        ));
        $returns_amount = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(GREATEST(0, total_amount - shipping_fee)) FROM {$wpdb->prefix}mkv_orders WHERE created_at BETWEEN %s AND %s AND status = 'returned'",
            $start_dt, $end_dt
        ));
        $total_revenue = max(0.0, $gross_revenue - $returns_amount);

        // Dòng tiền thực thu và nợ phát sinh
        $total_collected = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM {$wpdb->prefix}mkv_cashbook WHERE type = 'thu' AND created_at BETWEEN %s AND %s",
            $start_dt, $end_dt
        ));
        $total_debt_pending = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(COALESCE(customer_debt_amount, debt_amount, 0) + CASE WHEN payment_status = 'cod_pending' THEN COALESCE(cod_amount, total_amount, 0) ELSE 0 END)
             FROM {$wpdb->prefix}mkv_orders
             WHERE created_at BETWEEN %s AND %s AND status NOT IN ('cancelled', 'draft', 'returned')",
            $start_dt, $end_dt
        ));

        // Giá vốn và Lợi nhuận gộp
        $gross_cost = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(cost_total) FROM {$wpdb->prefix}mkv_orders WHERE created_at BETWEEN %s AND %s AND status IN ('paid', 'completed')",
            $start_dt, $end_dt
        ));
        $returns_cost = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(cost_total) FROM {$wpdb->prefix}mkv_orders WHERE created_at BETWEEN %s AND %s AND status = 'returned'",
            $start_dt, $end_dt
        ));
        $total_cost    = max(0.0, $gross_cost - $returns_cost);
        $gross_profit  = $total_revenue - $total_cost;

        $total_orders  = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$wpdb->prefix}mkv_orders WHERE created_at BETWEEN %s AND %s AND status IN ('paid', 'completed')",
            $start_dt, $end_dt
        ));
        $cancelled_orders = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$wpdb->prefix}mkv_orders WHERE created_at BETWEEN %s AND %s AND status='cancelled'",
            $start_dt, $end_dt
        ));
        $returned_orders = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$wpdb->prefix}mkv_orders WHERE created_at BETWEEN %s AND %s AND status='returned'",
            $start_dt, $end_dt
        ));
        $aov = $total_orders > 0 ? $total_revenue / $total_orders : 0;
        $new_customers = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$wpdb->prefix}mkv_customers WHERE created_at BETWEEN %s AND %s",
            $start_dt, $end_dt
        ));

                $channel_breakdown = $wpdb->get_results($wpdb->prepare(
                        "SELECT sales_channel, COUNT(id) AS order_count, SUM(GREATEST(0, total_amount - shipping_fee)) AS revenue
                         FROM {$wpdb->prefix}mkv_orders
                         WHERE created_at BETWEEN %s AND %s
                             AND status IN ('paid', 'completed')
                         GROUP BY sales_channel
                         ORDER BY revenue DESC",
                        $start_dt, $end_dt
                ));

        // --- Top sản phẩm ---
        $top_products = $wpdb->get_results($wpdb->prepare(
            "SELECT p.post_title as name, SUM(oi.qty) as total_sold, SUM(oi.subtotal) as total_revenue,
                    SUM(oi.qty * oi.cost_price) as total_cost
             FROM {$wpdb->prefix}mkv_order_items oi
             JOIN {$wpdb->prefix}posts p ON oi.product_id = p.ID
             JOIN {$wpdb->prefix}mkv_orders o ON oi.order_id = o.id
             WHERE o.created_at BETWEEN %s AND %s AND o.status IN ('paid', 'completed')
             GROUP BY oi.product_id
             ORDER BY total_sold DESC
             LIMIT 10",
            $start_dt, $end_dt
        ));

        // --- Dữ liệu biểu đồ theo ngày ---
        $chart_raw = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as day, SUM(GREATEST(0, total_amount - shipping_fee)) as revenue, SUM(cost_total) as cost, COUNT(id) as orders
             FROM {$wpdb->prefix}mkv_orders
             WHERE created_at BETWEEN %s AND %s AND status IN ('paid', 'completed')
             GROUP BY DATE(created_at)
             ORDER BY day ASC",
            $start_dt, $end_dt
        ));
        $chart_labels  = array_map(function($r) { return date('d/m', strtotime($r->day)); }, $chart_raw);
        $chart_revenue = array_map(function($r) { return (float)$r->revenue; }, $chart_raw);
        $chart_profit  = array_map(function($r) { return (float)$r->revenue - (float)$r->cost; }, $chart_raw);

        // --- Danh sách đơn ---
        $orders = array();
        if ($active_tab === 'sales') {
            $orders = $wpdb->get_results($wpdb->prepare(
                "SELECT o.*, c.name as customer_name
                 FROM {$wpdb->prefix}mkv_orders o
                 LEFT JOIN {$wpdb->prefix}mkv_customers c ON o.customer_id = c.id
                 WHERE o.created_at BETWEEN %s AND %s
                 ORDER BY o.created_at DESC",
                $start_dt, $end_dt
            ));
        }

        // --- Dữ liệu Cuối Ngày (Sổ Quỹ) ---
        $cashbook_summary = array();
        if ($active_tab === 'end_of_day') {
            $cashbook_summary = $wpdb->get_results($wpdb->prepare(
                "SELECT method, type, SUM(amount) as total
                 FROM {$wpdb->prefix}mkv_cashbook
                 WHERE created_at BETWEEN %s AND %s
                 GROUP BY method, type",
                $start_dt, $end_dt
            ));
        }

        require_once MKV_DIR . 'includes/views/view-reports.php';
    }
}
