<?php
if (!defined('ABSPATH')) exit;

class MKV_Analytics_Service
{
    public static function get_dashboard_stats($period = 'today')
    {
        global $wpdb;

        // Xác định khoảng thời gian hiện tại
        $dates = self::get_period_dates($period);
        $start_date = $dates['start_date'];
        $end_date   = $dates['end_date'];
        $prev_start = $dates['prev_start'];
        $prev_end   = $dates['prev_end'];

        // --- 1. Hóa đơn (Hoàn thành / Đã thanh toán) ---
        $current_orders = self::get_orders_count($start_date, $end_date, array('paid', 'completed'));
        $prev_orders    = self::get_orders_count($prev_start, $prev_end, array('paid', 'completed'));
        
        // --- 2. Trả hàng (Khách trả lại hàng) ---
        $current_returns = self::get_orders_count($start_date, $end_date, array('returned'));

        // --- 3. Doanh thu bán hàng thuần ---
        $current_revenue = self::get_revenue($start_date, $end_date, array('paid', 'completed'));
        $prev_revenue    = self::get_revenue($prev_start, $prev_end, array('paid', 'completed'));

        // --- 4. Dòng tiền Thực thu (Cash Inflow vào Quỹ) ---
        $current_collected = self::get_actual_collected($start_date, $end_date);
        $prev_collected    = self::get_actual_collected($prev_start, $prev_end);

        // --- 5. Chưa thu (Công nợ mới + COD chờ đối soát) ---
        $current_pending_debt = self::get_pending_receivables($start_date, $end_date);

        // --- 6. Đang giao hàng ---
        $shipping_stats = self::get_shipping_stats($start_date, $end_date);

        // --- 7. Khách hàng mới ---
        $current_new_customers = self::get_new_customers_count($start_date, $end_date);
        $prev_new_customers    = self::get_new_customers_count($prev_start, $prev_end);
        
        // Tính % thay đổi
        $orders_change     = $prev_orders > 0 ? round((($current_orders - $prev_orders) / $prev_orders) * 100, 2) : ($current_orders > 0 ? 100 : 0);
        $revenue_change    = $prev_revenue > 0 ? round((($current_revenue - $prev_revenue) / $prev_revenue) * 100, 2) : ($current_revenue > 0 ? 100 : 0);
        $collected_change  = $prev_collected > 0 ? round((($current_collected - $prev_collected) / $prev_collected) * 100, 2) : ($current_collected > 0 ? 100 : 0);
        $customers_change  = $prev_new_customers > 0 ? round((($current_new_customers - $prev_new_customers) / $prev_new_customers) * 100, 2) : ($current_new_customers > 0 ? 100 : 0);

        // Biểu đồ doanh thu
        $chart_data = self::get_revenue_chart_data($period, $start_date, $end_date);

        // Top 10 sản phẩm bán chạy nhất theo thời gian
        $top_products = self::get_top_products($start_date, $end_date, 10);

        // Top 10 khách hàng nhiều nhất theo thời gian
        $top_customers = self::get_top_customers($start_date, $end_date, 10);

        // Hoạt động gần đây (nhật ký giao dịch đơn hàng mới nhất - Lấy 10 để hiển thị)
        $recent_activities = self::get_recent_activities(10);

        // Sản phẩm sắp hết hàng (tồn kho <= min_stock hoặc threshold cấu hình)
        $low_stock = self::get_low_stock_products(5);

        // Tỷ trọng đơn hàng theo trạng thái (cho biểu đồ tròn Doughnut)
        $order_status_map = self::get_order_status_distribution();

        $total_products      = (int) wp_count_posts('mkv_product')->publish;
        $total_customers     = (int) $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}mkv_customers");
        $all_time_rev = $wpdb->get_row("SELECT 
            SUM(CASE WHEN status IN ('paid', 'completed') THEN GREATEST(0, total_amount - shipping_fee) ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = 'returned' THEN GREATEST(0, total_amount - shipping_fee) ELSE 0 END) as total_returns_rev
        FROM {$wpdb->prefix}mkv_orders
        WHERE status IN ('paid', 'completed', 'returned')");
        $total_revenue     = (float) ($all_time_rev->total_revenue ?? 0);
        $total_returns_rev = (float) ($all_time_rev->total_returns_rev ?? 0);
        $net_total_revenue = max(0.0, $total_revenue - $total_returns_rev);
        $total_cash_balance  = (float) $wpdb->get_var("SELECT SUM(CASE WHEN type='thu' THEN amount ELSE -amount END) FROM {$wpdb->prefix}mkv_cashbook");
        $total_customer_debt = (float) $wpdb->get_var("SELECT SUM(total_debt) FROM {$wpdb->prefix}mkv_customers WHERE total_debt > 0");

        return array(
            'today_orders'            => (int) $current_orders,
            'today_returns'           => (int) $current_returns,
            'today_revenue'           => (float) $current_revenue,
            'today_actual_collected'  => (float) $current_collected,
            'today_pending_debt'      => (float) $current_pending_debt,
            'today_shipping_count'    => (int) $shipping_stats['count'],
            'today_shipping_total'    => (float) $shipping_stats['total'],
            'today_customers'         => (int) $current_new_customers,
            'orders_change'           => $orders_change,
            'revenue_change'          => $revenue_change,
            'collected_change'        => $collected_change,
            'customers_change'        => $customers_change,
            'total_products'          => $total_products,
            'total_customers'         => $total_customers,
            'total_revenue'           => $net_total_revenue,
            'total_cash_balance'      => $total_cash_balance,
            'total_customer_debt'     => $total_customer_debt,
            'chart_labels'            => $chart_data['labels'],
            'chart_data'              => $chart_data['data'],
            'order_status'            => $order_status_map,
            'top_products'            => $top_products,
            'top_customers'           => $top_customers,
            'low_stock'               => $low_stock,
            'recent_activities'       => $recent_activities,
        );
    }

    private static function get_period_dates($period)
    {
        // P4-005: Use time() — current_time('timestamp') was deprecated in WP 5.3
        $now = time();


        if ($period === 'yesterday') {
            $start_date = gmdate('Y-m-d 00:00:00', strtotime('-1 day', $now));
            $end_date   = gmdate('Y-m-d 23:59:59', strtotime('-1 day', $now));
            $prev_start = gmdate('Y-m-d 00:00:00', strtotime('-2 days', $now));
            $prev_end   = gmdate('Y-m-d 23:59:59', strtotime('-2 days', $now));
        } elseif ($period === '7days') {
            $start_date = gmdate('Y-m-d 00:00:00', strtotime('-6 days', $now));
            $end_date   = gmdate('Y-m-d 23:59:59', $now);
            $prev_start = gmdate('Y-m-d 00:00:00', strtotime('-13 days', $now));
            $prev_end   = gmdate('Y-m-d 23:59:59', strtotime('-7 days', $now));
        } elseif ($period === '30days') {
            $start_date = gmdate('Y-m-d 00:00:00', strtotime('-29 days', $now));
            $end_date   = gmdate('Y-m-d 23:59:59', $now);
            $prev_start = gmdate('Y-m-d 00:00:00', strtotime('-59 days', $now));
            $prev_end   = gmdate('Y-m-d 23:59:59', strtotime('-30 days', $now));
        } elseif ($period === 'week') {
            $start_date = gmdate('Y-m-d 00:00:00', strtotime('monday this week', $now));
            $end_date   = gmdate('Y-m-d 23:59:59', strtotime('sunday this week', $now));
            $prev_start = gmdate('Y-m-d 00:00:00', strtotime('-1 week', strtotime($start_date)));
            $prev_end   = gmdate('Y-m-d 23:59:59', strtotime('-1 week', strtotime($end_date)));
        } elseif ($period === 'month') {
            $start_date = gmdate('Y-m-01 00:00:00', $now);
            $end_date   = gmdate('Y-m-t 23:59:59', $now);
            $prev_start = gmdate('Y-m-01 00:00:00', strtotime('first day of -1 month', $now));
            $prev_end   = gmdate('Y-m-t 23:59:59', strtotime('last day of -1 month', $now));
        } elseif ($period === 'last_month') {
            $start_date = gmdate('Y-m-01 00:00:00', strtotime('first day of -1 month', $now));
            $end_date   = gmdate('Y-m-t 23:59:59', strtotime('last day of -1 month', $now));
            $prev_start = gmdate('Y-m-01 00:00:00', strtotime('first day of -2 month', $now));
            $prev_end   = gmdate('Y-m-t 23:59:59', strtotime('last day of -2 month', $now));
        } elseif ($period === 'year') {
            $start_date = gmdate('Y-01-01 00:00:00', $now);
            $end_date   = gmdate('Y-12-31 23:59:59', $now);
            $prev_start = gmdate('Y-01-01 00:00:00', strtotime('-1 year', $now));
            $prev_end   = gmdate('Y-12-31 23:59:59', strtotime('-1 year', $now));
        } elseif ($period === 'all') {
            $start_date = '2020-01-01 00:00:00';
            $end_date   = gmdate('Y-m-d 23:59:59', $now);
            $prev_start = '2020-01-01 00:00:00';
            $prev_end   = '2020-01-01 00:00:00';
        } else {
            // today
            $start_date = gmdate('Y-m-d 00:00:00', $now);
            $end_date   = gmdate('Y-m-d 23:59:59', $now);
            $prev_start = gmdate('Y-m-d 00:00:00', strtotime('-1 day', $now));
            $prev_end   = gmdate('Y-m-d 23:59:59', strtotime('-1 day', $now));
        }

        return array(
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'prev_start' => $prev_start,
            'prev_end'   => $prev_end,
        );
    }

    public static function get_orders_count($start_date, $end_date, $statuses = array())
    {
        global $wpdb;
        $status_in = "'" . implode("','", array_map('esc_sql', $statuses)) . "'";
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$wpdb->prefix}mkv_orders WHERE created_at >= %s AND created_at <= %s AND status IN ($status_in)",
            $start_date, $end_date
        ));
    }

    public static function get_revenue($start_date, $end_date, $statuses = array('paid', 'completed'))
    {
        global $wpdb;
        $status_in = "'" . implode("','", array_map('esc_sql', $statuses)) . "'";
        $gross_sales = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(GREATEST(0, total_amount - shipping_fee)) FROM {$wpdb->prefix}mkv_orders WHERE created_at >= %s AND created_at <= %s AND status IN ($status_in)",
            $start_date, $end_date
        ));
        $returns = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(GREATEST(0, total_amount - shipping_fee)) FROM {$wpdb->prefix}mkv_orders WHERE created_at >= %s AND created_at <= %s AND status = 'returned'",
            $start_date, $end_date
        ));
        return max(0.0, $gross_sales - $returns);
    }

    public static function get_actual_collected($start_date, $end_date)
    {
        global $wpdb;
        $total_thu = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM {$wpdb->prefix}mkv_cashbook WHERE type = 'thu' AND created_at >= %s AND created_at <= %s",
            $start_date, $end_date
        ));
        $order_refunds = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM {$wpdb->prefix}mkv_cashbook WHERE type = 'chi' AND (note LIKE '%%trả hàng%%' OR note LIKE '%%Hoàn tiền%%') AND created_at >= %s AND created_at <= %s",
            $start_date, $end_date
        ));
        return max(0.0, $total_thu - $order_refunds);
    }

    public static function get_pending_receivables($start_date, $end_date)
    {
        global $wpdb;
        return (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(COALESCE(customer_debt_amount, debt_amount, 0) + CASE WHEN payment_status = 'cod_pending' THEN COALESCE(cod_amount, total_amount, 0) ELSE 0 END)
             FROM {$wpdb->prefix}mkv_orders
             WHERE created_at >= %s AND created_at <= %s
               AND status NOT IN ('cancelled', 'draft', 'returned')",
            $start_date, $end_date
        ));
    }

    public static function get_shipping_stats($start_date, $end_date)
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(id) as count, SUM(total_amount) as total
             FROM {$wpdb->prefix}mkv_orders
             WHERE status = 'shipping' AND created_at >= %s AND created_at <= %s",
            $start_date, $end_date
        ));
        return array(
            'count' => (int) ($row->count ?? 0),
            'total' => (float) ($row->total ?? 0.0),
        );
    }

    public static function get_new_customers_count($start_date, $end_date)
    {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$wpdb->prefix}mkv_customers WHERE created_at >= %s AND created_at <= %s",
            $start_date, $end_date
        ));
    }

    private static function get_revenue_chart_data($period, $start_date, $end_date)
    {
        global $wpdb;
        $chart_labels = array();
        $chart_data   = array();
        $now = current_time('timestamp');

        $base_sql = "SELECT DATE(created_at) as bucket_date, SUM(GREATEST(0, total_amount - shipping_fee)) as rev
            FROM {$wpdb->prefix}mkv_orders
            WHERE status IN ('paid', 'completed')";
        $hourly_sql = "SELECT HOUR(created_at) as bucket_hour, SUM(GREATEST(0, total_amount - shipping_fee)) as rev
            FROM {$wpdb->prefix}mkv_orders
            WHERE status IN ('paid', 'completed')";

        if ($period === 'yesterday') {
            $yest = gmdate('Y-m-d', strtotime('-1 day', $now));
            $rows = $wpdb->get_results($wpdb->prepare(
                $hourly_sql . " AND DATE(created_at) = %s GROUP BY HOUR(created_at)",
                $yest
            ), ARRAY_A);

            $by_hour = array();
            foreach ($rows as $row) {
                $by_hour[(int) $row['bucket_hour']] = (float) $row['rev'];
            }

            for ($i = 0; $i <= 23; $i++) {
                $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                $chart_labels[] = $hour . ':00';
                $chart_data[] = isset($by_hour[$i]) ? $by_hour[$i] : 0.0;
            }
        } elseif ($period === '7days' || $period === '30days') {
            $num_days = ($period === '7days') ? 7 : 30;
            $rows = $wpdb->get_results($wpdb->prepare(
                $base_sql . " AND created_at >= %s AND created_at <= %s GROUP BY DATE(created_at)",
                $start_date,
                $end_date
            ), ARRAY_A);
            $by_day = array();
            foreach ($rows as $row) {
                $by_day[$row['bucket_date']] = (float) $row['rev'];
            }

            for ($i = $num_days - 1; $i >= 0; $i--) {
                $d = gmdate('Y-m-d', strtotime("-{$i} days", $now));
                $chart_labels[] = gmdate('d/m', strtotime($d));
                $chart_data[] = isset($by_day[$d]) ? $by_day[$d] : 0.0;
            }
        } elseif ($period === 'week') {
            $days = array(mkv__('Thứ 2'), mkv__('Thứ 3'), mkv__('Thứ 4'), mkv__('Thứ 5'), mkv__('Thứ 6'), mkv__('Thứ 7'), mkv__('CN'));
            $rows = $wpdb->get_results($wpdb->prepare(
                $base_sql . " AND created_at >= %s AND created_at <= %s GROUP BY DATE(created_at)",
                $start_date,
                $end_date
            ), ARRAY_A);
            $by_day = array();
            foreach ($rows as $row) {
                $by_day[$row['bucket_date']] = (float) $row['rev'];
            }
            $week_start = gmdate('Y-m-d', strtotime('monday this week', $now));
            for ($i = 0; $i < 7; $i++) {
                $chart_labels[] = $days[$i];
                $date = gmdate('Y-m-d', strtotime($week_start . " +{$i} days"));
                $chart_data[] = isset($by_day[$date]) ? $by_day[$date] : 0.0;
            }
        } elseif ($period === 'month') {
            $rows = $wpdb->get_results($wpdb->prepare(
                $base_sql . " AND created_at >= %s AND created_at <= %s GROUP BY DATE(created_at)",
                $start_date,
                $end_date
            ), ARRAY_A);
            $by_day = array();
            foreach ($rows as $row) {
                $by_day[$row['bucket_date']] = (float) $row['rev'];
            }
            $day_count = (int) gmdate('t', $now);
            $month = gmdate('Y-m', $now);
            for ($i = 1; $i <= $day_count; $i++) {
                $chart_labels[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                $date = $month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $chart_data[] = isset($by_day[$date]) ? $by_day[$date] : 0.0;
            }
        } elseif ($period === 'last_month') {
            $last_month_ts = strtotime('first day of -1 month', $now);
            $rows = $wpdb->get_results($wpdb->prepare(
                $base_sql . " AND created_at >= %s AND created_at <= %s GROUP BY DATE(created_at)",
                $start_date,
                $end_date
            ), ARRAY_A);
            $by_day = array();
            foreach ($rows as $row) {
                $by_day[$row['bucket_date']] = (float) $row['rev'];
            }
            $day_count = (int) gmdate('t', $last_month_ts);
            $month = gmdate('Y-m', $last_month_ts);
            for ($i = 1; $i <= $day_count; $i++) {
                $chart_labels[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                $date = $month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $chart_data[] = isset($by_day[$date]) ? $by_day[$date] : 0.0;
            }
        } elseif ($period === 'year') {
            $rows = $wpdb->get_results($wpdb->prepare(
                $base_sql . " AND created_at >= %s AND created_at <= %s GROUP BY YEAR(created_at), MONTH(created_at)",
                $start_date,
                $end_date
            ), ARRAY_A);
            $by_month = array();
            foreach ($rows as $row) {
                $month_key = gmdate('Y-m', strtotime($row['bucket_date']));
                $by_month[$month_key] = (float) $row['rev'];
            }
            $year = gmdate('Y', $now);
            for ($i = 1; $i <= 12; $i++) {
                $chart_labels[] = mkv__('Th') . $i;
                $month = $year . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $chart_data[] = isset($by_month[$month]) ? $by_month[$month] : 0.0;
            }
        } elseif ($period === 'all') {
            $months_data = $wpdb->get_results(
                "SELECT DATE_FORMAT(created_at, '%m/%Y') as label, SUM(GREATEST(0, total_amount - shipping_fee)) as rev 
                 FROM {$wpdb->prefix}mkv_orders 
                 WHERE status IN ('paid', 'completed')
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY created_at ASC
                 LIMIT 12"
            );
            if ($months_data) {
                foreach ($months_data as $row) {
                    $chart_labels[] = $row->label;
                    $chart_data[] = (float) $row->rev;
                }
            } else {
                $chart_labels = array(date('m/Y', $now));
                $chart_data = array(0);
            }
        } else {
            $today = gmdate('Y-m-d', $now);
            $rows = $wpdb->get_results($wpdb->prepare(
                $hourly_sql . " AND DATE(created_at) = %s GROUP BY HOUR(created_at)",
                $today
            ), ARRAY_A);
            $by_hour = array();
            foreach ($rows as $row) {
                $by_hour[(int) $row['bucket_hour']] = (float) $row['rev'];
            }
            for ($i = 0; $i <= 23; $i++) {
                $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                $chart_labels[] = $hour . ':00';
                $chart_data[] = isset($by_hour[$i]) ? $by_hour[$i] : 0.0;
            }
        }

        return array('labels' => $chart_labels, 'data' => $chart_data);
    }

    public static function get_top_products($start_date, $end_date, $limit = 10)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT p.post_title as name, SUM(i.qty) as total_sold
             FROM {$wpdb->prefix}mkv_order_items i
             JOIN {$wpdb->prefix}posts p ON i.product_id = p.ID
             JOIN {$wpdb->prefix}mkv_orders o ON i.order_id = o.id
             WHERE p.post_type = 'mkv_product' AND o.status IN ('paid', 'completed')
             AND o.created_at >= %s AND o.created_at <= %s
             GROUP BY i.product_id
             ORDER BY total_sold DESC
             LIMIT %d",
             $start_date, $end_date, $limit
        ));
    }

    public static function get_top_customers($start_date, $end_date, $limit = 10)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT c.name, SUM(o.total_amount - o.shipping_fee) as spent
             FROM {$wpdb->prefix}mkv_customers c
             JOIN {$wpdb->prefix}mkv_orders o ON c.id = o.customer_id
             WHERE o.status IN ('paid', 'completed')
             AND o.created_at >= %s AND o.created_at <= %s
             GROUP BY c.id
             ORDER BY spent DESC
             LIMIT %d",
             $start_date, $end_date, $limit
        ));
    }

    public static function get_recent_activities($limit = 15)
    {
        global $wpdb;
        $activities = $wpdb->get_results($wpdb->prepare(
            "SELECT o.order_code, o.total_amount, o.status, COALESCE(o.updated_at, o.created_at) as time, c.name as customer_name
             FROM {$wpdb->prefix}mkv_orders o
             LEFT JOIN {$wpdb->prefix}mkv_customers c ON o.customer_id = c.id
             ORDER BY COALESCE(o.updated_at, o.created_at) DESC
             LIMIT %d", $limit
        ));
        if (!empty($activities)) {
            foreach ($activities as &$item) {
                $item->total_amount = round((float) $item->total_amount);
            }
        }
        return $activities;
    }


    public static function get_low_stock_products($limit = 5)
    {
        global $wpdb;
        $threshold = (int) get_option('mkv_min_stock_threshold', 5);
        $low_stock_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT product_id, SUM(stock) as total_stock
             FROM {$wpdb->prefix}mkv_inventory_stock
             GROUP BY product_id
             HAVING total_stock <= %d
             ORDER BY total_stock ASC
             LIMIT %d",
            $threshold, $limit
        ));
        $low_stock = array();
        foreach ($low_stock_rows as $row) {
            $title = get_the_title($row->product_id);
            if ($title) {
                $low_stock[] = array(
                    'name'  => $title,
                    'stock' => (int) $row->total_stock
                );
            }
        }
        return $low_stock;
    }

    public static function get_order_status_distribution()
    {
        global $wpdb;
        $status_counts = $wpdb->get_results(
            "SELECT status, COUNT(id) as count FROM {$wpdb->prefix}mkv_orders GROUP BY status"
        );
        $order_status_map = array('completed' => 0, 'paid' => 0, 'pending' => 0, 'shipping' => 0, 'cancelled' => 0, 'draft' => 0);
        foreach ($status_counts as $sc) {
            if (isset($order_status_map[$sc->status])) {
                $order_status_map[$sc->status] = (int)$sc->count;
            }
        }
        return $order_status_map;
    }
}
