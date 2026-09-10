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
        add_submenu_page(
            'mini-kiotviet',
            'Báo Cáo',
            'Báo Cáo',
            'mkv_manage_reports',
            'mkv-reports',
            array($this, 'render_reports_page')
        );
    }

    public function render_reports_page()
    {
        global $wpdb;

        // 1. Active Tab & Permission Check
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'sales';
        if (!in_array($active_tab, array('sales', 'profit', 'end_of_day'), true)) {
            $active_tab = 'sales';
        }
        if ($active_tab === 'profit' && !current_user_can('mkv_view_cost_price')) {
            $active_tab = 'sales';
        }

        // 2. Quick filter presets & Smart Tab Default
        $quick = isset($_GET['quick']) ? sanitize_text_field($_GET['quick']) : '';
        if (empty($quick) && !isset($_GET['start_date'])) {
            // End of day & profit default to today for real-time daily operational relevance
            $quick = 'today';
        }

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
                $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : current_time('Y-m-d');
                $end_date   = isset($_GET['end_date'])   ? sanitize_text_field($_GET['end_date'])   : current_time('Y-m-d');
        }

        $start_dt = $start_date . ' 00:00:00';
        $end_dt   = $end_date   . ' 23:59:59';

        // 3. Export CSV handler
        if (!empty($_GET['export_csv'])) {
            check_admin_referer('mkv_export_reports_csv');
            if (!current_user_can('mkv_view_reports')) {
                wp_die(mkv__('Bạn không có quyền xem báo cáo.'));
            }
            $this->handle_export_csv($active_tab, $start_dt, $end_dt);
            exit;
        }

        // 4. Common Revenue & Sales Metrics (Consolidated Single Query)
        $order_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                SUM(CASE WHEN status IN ('paid', 'completed') THEN GREATEST(0, total_amount - shipping_fee) ELSE 0 END) as gross_revenue,
                SUM(CASE WHEN status = 'returned' THEN GREATEST(0, total_amount - shipping_fee) ELSE 0 END) as returns_amount,
                SUM(CASE WHEN status NOT IN ('cancelled', 'draft', 'returned') THEN COALESCE(customer_debt_amount, debt_amount, 0) + CASE WHEN payment_status = 'cod_pending' THEN COALESCE(cod_amount, total_amount, 0) ELSE 0 END ELSE 0 END) as total_debt_pending,
                SUM(CASE WHEN status IN ('paid', 'completed') THEN cost_total ELSE 0 END) as gross_cost,
                SUM(CASE WHEN status = 'returned' THEN cost_total ELSE 0 END) as returns_cost,
                COUNT(CASE WHEN status IN ('paid', 'completed') THEN 1 END) as total_orders,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders,
                COUNT(CASE WHEN status = 'returned' THEN 1 END) as returned_orders
             FROM {$wpdb->prefix}mkv_orders
             WHERE created_at BETWEEN %s AND %s",
            $start_dt, $end_dt
        ));

        $gross_revenue      = (float) ($order_stats->gross_revenue ?? 0);
        $returns_amount     = (float) ($order_stats->returns_amount ?? 0);
        $total_debt_pending = (float) ($order_stats->total_debt_pending ?? 0);
        $gross_cost         = (float) ($order_stats->gross_cost ?? 0);
        $returns_cost       = (float) ($order_stats->returns_cost ?? 0);
        $total_orders       = (int) ($order_stats->total_orders ?? 0);
        $cancelled_orders   = (int) ($order_stats->cancelled_orders ?? 0);
        $returned_orders    = (int) ($order_stats->returned_orders ?? 0);

        $total_revenue = max(0.0, $gross_revenue - $returns_amount);

        // Cash collected and pending debt
        $total_collected = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM {$wpdb->prefix}mkv_cashbook WHERE type = 'thu' AND created_at BETWEEN %s AND %s",
            $start_dt, $end_dt
        ));

        // COGS and Gross profit
        $total_cost    = max(0.0, $gross_cost - $returns_cost);
        $gross_profit  = $total_revenue - $total_cost;
        $gross_margin  = ($total_revenue > 0) ? round(($gross_profit / $total_revenue) * 100, 1) : 0.0;

        // Operating expenses from cashbook (other expenses, excluding supplier PO payments)
        $operating_expenses = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM {$wpdb->prefix}mkv_cashbook 
             WHERE type = 'chi' AND (supplier_id IS NULL OR supplier_id = 0) AND created_at BETWEEN %s AND %s",
            $start_dt, $end_dt
        ));
        $net_profit  = $gross_profit - $operating_expenses;
        $net_margin  = ($total_revenue > 0) ? round(($net_profit / $total_revenue) * 100, 1) : 0.0;
        $aov = $total_orders > 0 ? ($total_revenue / $total_orders) : 0;
        $avg_profit_order = $total_orders > 0 ? ($gross_profit / $total_orders) : 0;

        $return_rate = ($total_orders + $returned_orders > 0)
            ? round(($returned_orders / ($total_orders + $returned_orders)) * 100, 1)
            : 0;

        $new_customers = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$wpdb->prefix}mkv_customers WHERE created_at BETWEEN %s AND %s",
            $start_dt, $end_dt
        ));

        // Sales by channel
        $channel_breakdown = $wpdb->get_results($wpdb->prepare(
            "SELECT sales_channel, 
                    COUNT(id) AS order_count, 
                    SUM(GREATEST(0, total_amount - shipping_fee)) AS revenue,
                    SUM(cost_total) AS cost,
                    (SUM(GREATEST(0, total_amount - shipping_fee)) - SUM(cost_total)) AS profit
             FROM {$wpdb->prefix}mkv_orders
             WHERE created_at BETWEEN %s AND %s AND status IN ('paid', 'completed')
             GROUP BY sales_channel
             ORDER BY revenue DESC",
            $start_dt, $end_dt
        ));

        // Chart Data (Hourly if 1 day, Daily if date range > 1 day)
        $is_single_day = ($start_date === $end_date);
        if ($is_single_day) {
            $chart_raw = $wpdb->get_results($wpdb->prepare(
                "SELECT DATE_FORMAT(created_at, '%H:00') as time_slot, 
                        SUM(GREATEST(0, total_amount - shipping_fee)) as revenue, 
                        SUM(cost_total) as cost, 
                        COUNT(id) as orders
                 FROM {$wpdb->prefix}mkv_orders
                 WHERE created_at BETWEEN %s AND %s AND status IN ('paid', 'completed')
                 GROUP BY DATE_FORMAT(created_at, '%H:00')
                 ORDER BY time_slot ASC",
                $start_dt, $end_dt
            ));
            $chart_labels  = array_map(function($r) { return $r->time_slot; }, $chart_raw);
        } else {
            $chart_raw = $wpdb->get_results($wpdb->prepare(
                "SELECT DATE(created_at) as time_slot, 
                        SUM(GREATEST(0, total_amount - shipping_fee)) as revenue, 
                        SUM(cost_total) as cost, 
                        COUNT(id) as orders
                 FROM {$wpdb->prefix}mkv_orders
                 WHERE created_at BETWEEN %s AND %s AND status IN ('paid', 'completed')
                 GROUP BY DATE(created_at)
                 ORDER BY time_slot ASC",
                $start_dt, $end_dt
            ));
            $chart_labels  = array_map(function($r) { return date('d/m', strtotime($r->time_slot)); }, $chart_raw);
        }

        $chart_revenue = array_map(function($r) { return (float)$r->revenue; }, $chart_raw);
        $chart_profit  = array_map(function($r) { return max(0.0, (float)$r->revenue - (float)$r->cost); }, $chart_raw);

        // --- TAB 1: SALES SPECIFIC (Top sold products & Paginated Orders) ---
        $top_products = array();
        $orders = array();
        $orders_total_count = 0;
        $orders_total_pages = 1;
        $orders_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $orders_per_page = 15;

        if ($active_tab === 'sales') {
            $top_products = $wpdb->get_results($wpdb->prepare(
                "SELECT p.post_title as name, 
                        SUM(oi.qty) as total_sold, 
                        SUM(oi.subtotal) as total_revenue,
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

            $orders_total_count = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(id) FROM {$wpdb->prefix}mkv_orders WHERE created_at BETWEEN %s AND %s",
                $start_dt, $end_dt
            ));
            $orders_total_pages = max(1, (int) ceil($orders_total_count / $orders_per_page));
            $offset = ($orders_page - 1) * $orders_per_page;

            $orders = $wpdb->get_results($wpdb->prepare(
                "SELECT o.*, c.name as customer_name
                 FROM {$wpdb->prefix}mkv_orders o
                 LEFT JOIN {$wpdb->prefix}mkv_customers c ON o.customer_id = c.id
                 WHERE o.created_at BETWEEN %s AND %s
                 ORDER BY o.created_at DESC
                 LIMIT %d OFFSET %d",
                $start_dt, $end_dt, $orders_per_page, $offset
            ));
        }

        // --- TAB 2: PROFIT SPECIFIC (Top Profitable Products) ---
        $top_profitable_products = array();
        if ($active_tab === 'profit') {
            $top_profitable_products = $wpdb->get_results($wpdb->prepare(
                "SELECT p.post_title as name, 
                        SUM(oi.qty) as total_sold, 
                        SUM(oi.subtotal) as total_revenue,
                        SUM(oi.qty * oi.cost_price) as total_cost,
                        (SUM(oi.subtotal) - SUM(oi.qty * oi.cost_price)) as profit
                 FROM {$wpdb->prefix}mkv_order_items oi
                 JOIN {$wpdb->prefix}posts p ON oi.product_id = p.ID
                 JOIN {$wpdb->prefix}mkv_orders o ON oi.order_id = o.id
                 WHERE o.created_at BETWEEN %s AND %s AND o.status IN ('paid', 'completed')
                 GROUP BY oi.product_id
                 ORDER BY profit DESC
                 LIMIT 10",
                $start_dt, $end_dt
            ));
        }

        // --- TAB 3: END OF DAY SPECIFIC (Cash Reconciliation & Journal) ---
        $eod_opening = array('cash' => 0.0, 'transfer' => 0.0, 'card' => 0.0, 'total' => 0.0);
        $eod_period_thu = array('cash' => 0.0, 'transfer' => 0.0, 'card' => 0.0, 'total' => 0.0);
        $eod_period_chi = array('cash' => 0.0, 'transfer' => 0.0, 'card' => 0.0, 'total' => 0.0);
        $eod_cashflow_breakdown = array(
            'thu_order'     => 0.0,
            'thu_debt'      => 0.0,
            'thu_other'     => 0.0,
            'chi_supplier'  => 0.0,
            'chi_refund'    => 0.0,
            'chi_expense'   => 0.0,
        );
        $eod_journal = array();
        $eod_journal_count = 0;
        $eod_journal_pages = 1;
        $eod_page = isset($_GET['cb_paged']) ? max(1, intval($_GET['cb_paged'])) : 1;
        $eod_per_page = 20;

        if ($active_tab === 'end_of_day') {
            // Opening Balance before $start_dt
            $opening_raw = $wpdb->get_results($wpdb->prepare(
                "SELECT method, type, SUM(amount) as total
                 FROM {$wpdb->prefix}mkv_cashbook
                 WHERE created_at < %s
                 GROUP BY method, type",
                $start_dt
            ));
            foreach ($opening_raw as $r) {
                $m = $r->method;
                if (!isset($eod_opening[$m])) $eod_opening[$m] = 0.0;
                if ($r->type === 'thu') {
                    $eod_opening[$m] += (float) $r->total;
                    $eod_opening['total'] += (float) $r->total;
                } else {
                    $eod_opening[$m] -= (float) $r->total;
                    $eod_opening['total'] -= (float) $r->total;
                }
            }

            // Current Period Thu / Chi by Method
            $period_raw = $wpdb->get_results($wpdb->prepare(
                "SELECT method, type, SUM(amount) as total
                 FROM {$wpdb->prefix}mkv_cashbook
                 WHERE created_at BETWEEN %s AND %s
                 GROUP BY method, type",
                $start_dt, $end_dt
            ));
            foreach ($period_raw as $r) {
                $m = $r->method;
                if ($r->type === 'thu') {
                    if (!isset($eod_period_thu[$m])) $eod_period_thu[$m] = 0.0;
                    $eod_period_thu[$m] += (float) $r->total;
                    $eod_period_thu['total'] += (float) $r->total;
                } else {
                    if (!isset($eod_period_chi[$m])) $eod_period_chi[$m] = 0.0;
                    $eod_period_chi[$m] += (float) $r->total;
                    $eod_period_chi['total'] += (float) $r->total;
                }
            }

            // Cashflow breakdown by business context
            $tx_list_all = $wpdb->get_results($wpdb->prepare(
                "SELECT type, amount, reference_id, customer_id, supplier_id, note
                 FROM {$wpdb->prefix}mkv_cashbook
                 WHERE created_at BETWEEN %s AND %s",
                $start_dt, $end_dt
            ));
            foreach ($tx_list_all as $tx) {
                $amt = (float) $tx->amount;
                if ($tx->type === 'thu') {
                    if (!empty($tx->reference_id)) {
                        $eod_cashflow_breakdown['thu_order'] += $amt;
                    } elseif (!empty($tx->customer_id)) {
                        $eod_cashflow_breakdown['thu_debt'] += $amt;
                    } else {
                        $eod_cashflow_breakdown['thu_other'] += $amt;
                    }
                } else {
                    if (!empty($tx->supplier_id)) {
                        $eod_cashflow_breakdown['chi_supplier'] += $amt;
                    } elseif (stripos($tx->note, 'trả hàng') !== false || stripos($tx->note, 'hoàn tiền') !== false) {
                        $eod_cashflow_breakdown['chi_refund'] += $amt;
                    } else {
                        $eod_cashflow_breakdown['chi_expense'] += $amt;
                    }
                }
            }

            // Paginated Transaction Journal
            $eod_journal_count = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(id) FROM {$wpdb->prefix}mkv_cashbook WHERE created_at BETWEEN %s AND %s",
                $start_dt, $end_dt
            ));
            $eod_journal_pages = max(1, (int) ceil($eod_journal_count / $eod_per_page));
            $cb_offset = ($eod_page - 1) * $eod_per_page;

            $eod_journal = $wpdb->get_results($wpdb->prepare(
                "SELECT cb.*, u.display_name as staff_name, 
                        cust.name as customer_name, sup.name as supplier_name,
                        ord.order_code
                 FROM {$wpdb->prefix}mkv_cashbook cb
                 LEFT JOIN {$wpdb->prefix}users u ON cb.created_by = u.ID
                 LEFT JOIN {$wpdb->prefix}mkv_customers cust ON cb.customer_id = cust.id
                 LEFT JOIN {$wpdb->prefix}mkv_suppliers sup ON cb.supplier_id = sup.id
                 LEFT JOIN {$wpdb->prefix}mkv_orders ord ON cb.reference_id = ord.id
                 WHERE cb.created_at BETWEEN %s AND %s
                 ORDER BY cb.created_at DESC
                 LIMIT %d OFFSET %d",
                $start_dt, $end_dt, $eod_per_page, $cb_offset
            ));
        }

        require_once MKV_DIR . 'includes/views/view-reports.php';
    }

    private function handle_export_csv($active_tab, $start_dt, $end_dt)
    {
        global $wpdb;

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="bao-cao-' . $active_tab . '-' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

        if ($active_tab === 'sales') {
            fputcsv($output, array('Mã đơn', 'Ngày tạo', 'Khách hàng', 'Kênh bán', 'Tổng tiền', 'Đã thu', 'Nợ / COD', 'Trạng thái'));
            $orders = $wpdb->get_results($wpdb->prepare(
                "SELECT o.*, c.name as customer_name 
                 FROM {$wpdb->prefix}mkv_orders o
                 LEFT JOIN {$wpdb->prefix}mkv_customers c ON o.customer_id = c.id
                 WHERE o.created_at BETWEEN %s AND %s 
                 ORDER BY o.created_at DESC",
                $start_dt, $end_dt
            ));
            foreach ($orders as $o) {
                $debt = (float)($o->customer_debt_amount ?? $o->debt_amount ?? 0);
                if (($o->payment_status ?? '') === 'cod_pending') {
                    $debt = (float)($o->cod_amount ?? $o->total_amount ?? 0);
                }
                fputcsv($output, array_map('mkv_sanitize_csv_cell', array(
                    $o->order_code,
                    $o->created_at,
                    $o->customer_name ?: 'Khách lẻ',
                    $o->sales_channel,
                    $o->total_amount,
                    $o->paid_amount,
                    $debt,
                    $o->status
                )));
            }
        } elseif ($active_tab === 'profit') {
            fputcsv($output, array('Sản phẩm', 'Số lượng bán', 'Doanh thu', 'Tiền vốn', 'Lợi nhuận gộp', 'Tỷ suất LN (%)'));
            $top_products = $wpdb->get_results($wpdb->prepare(
                "SELECT p.post_title as name, 
                        SUM(oi.qty) as total_sold, 
                        SUM(oi.subtotal) as total_revenue,
                        SUM(oi.qty * oi.cost_price) as total_cost,
                        (SUM(oi.subtotal) - SUM(oi.qty * oi.cost_price)) as profit
                 FROM {$wpdb->prefix}mkv_order_items oi
                 JOIN {$wpdb->prefix}posts p ON oi.product_id = p.ID
                 JOIN {$wpdb->prefix}mkv_orders o ON oi.order_id = o.id
                 WHERE o.created_at BETWEEN %s AND %s AND o.status IN ('paid', 'completed')
                 GROUP BY oi.product_id 
                 ORDER BY profit DESC",
                $start_dt, $end_dt
            ));
            foreach ($top_products as $tp) {
                $margin = ($tp->total_revenue > 0) ? round(($tp->profit / $tp->total_revenue) * 100, 2) : 0;
                fputcsv($output, array_map('mkv_sanitize_csv_cell', array(
                    $tp->name,
                    $tp->total_sold,
                    $tp->total_revenue,
                    $tp->total_cost,
                    $tp->profit,
                    $margin . '%'
                )));
            }
        } elseif ($active_tab === 'end_of_day') {
            fputcsv($output, array('Thời gian', 'Mã phiếu', 'Loại', 'Phương thức', 'Số tiền', 'Đối tác', 'Nhân viên', 'Ghi chú'));
            $cashbook = $wpdb->get_results($wpdb->prepare(
                "SELECT cb.*, u.display_name as staff_name, 
                        cust.name as customer_name, sup.name as supplier_name
                 FROM {$wpdb->prefix}mkv_cashbook cb
                 LEFT JOIN {$wpdb->prefix}users u ON cb.created_by = u.ID
                 LEFT JOIN {$wpdb->prefix}mkv_customers cust ON cb.customer_id = cust.id
                 LEFT JOIN {$wpdb->prefix}mkv_suppliers sup ON cb.supplier_id = sup.id
                 WHERE cb.created_at BETWEEN %s AND %s 
                 ORDER BY cb.created_at DESC",
                $start_dt, $end_dt
            ));
            foreach ($cashbook as $cb) {
                $partner = $cb->customer_name ?: ($cb->supplier_name ?: 'Khác');
                fputcsv($output, array_map('mkv_sanitize_csv_cell', array(
                    $cb->created_at,
                    '#CB-' . $cb->id,
                    strtoupper($cb->type),
                    $cb->method,
                    $cb->amount,
                    $partner,
                    $cb->staff_name,
                    $cb->note
                )));
            }
        }
        fclose($output);
    }
}
