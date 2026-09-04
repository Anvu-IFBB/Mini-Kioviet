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

        // --- 1. Hóa đơn (Thành công) ---
        $current_orders = self::get_orders_count($start_date, $end_date, array('paid', 'shipping', 'completed'));
        $prev_orders = self::get_orders_count($prev_start, $prev_end, array('paid', 'shipping', 'completed'));
        
        // --- 2. Trả hàng (Đã hủy) ---
        $current_returns = self::get_orders_count($start_date, $end_date, array('cancelled'));

        // --- 3. Doanh thu thuần ---
        $current_revenue = self::get_revenue($start_date, $end_date, array('paid', 'shipping', 'completed'));
        $prev_revenue = self::get_revenue($prev_start, $prev_end, array('paid', 'shipping', 'completed'));

        // --- 4. Khách hàng mới ---
        $current_new_customers = self::get_new_customers_count($start_date, $end_date);
        $prev_new_customers = self::get_new_customers_count($prev_start, $prev_end);
        
        // Tính % thay đổi
        $orders_change = $prev_orders > 0 ? round((($current_orders - $prev_orders) / $prev_orders) * 100, 2) : ($current_orders > 0 ? 100 : 0);
        $revenue_change = $prev_revenue > 0 ? round((($current_revenue - $prev_revenue) / $prev_revenue) * 100, 2) : ($current_revenue > 0 ? 100 : 0);
        $customers_change = $prev_new_customers > 0 ? round((($current_new_customers - $prev_new_customers) / $prev_new_customers) * 100, 2) : ($current_new_customers > 0 ? 100 : 0);

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

        $total_products  = (int) wp_count_posts('mkv_product')->publish;
        $total_customers = (int) $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}mkv_customers");
        $total_revenue   = (float) $wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}mkv_orders WHERE status IN ('paid', 'shipping', 'completed')");

        return array(
            'today_orders'      => (int) $current_orders,
            'today_returns'     => (int) $current_returns,
            'today_revenue'     => (float) $current_revenue,
            'today_customers'   => (int) $current_new_customers,
            'orders_change'     => $orders_change,
            'revenue_change'    => $revenue_change,
            'customers_change'  => $customers_change,
            'total_products'    => $total_products,
            'total_customers'   => $total_customers,
            'total_revenue'     => $total_revenue,
            'chart_labels'      => $chart_data['labels'],
            'chart_data'        => $chart_data['data'],
            'order_status'      => $order_status_map,
            'top_products'      => $top_products,
            'top_customers'     => $top_customers,
            'low_stock'         => $low_stock,
            'recent_activities' => $recent_activities,
        );
    }

    private static function get_period_dates($period)
    {
        $now = current_time('timestamp');

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

    public static function get_revenue($start_date, $end_date, $statuses = array())
    {
        global $wpdb;
        $status_in = "'" . implode("','", array_map('esc_sql', $statuses)) . "'";
        return (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total_amount) FROM {$wpdb->prefix}mkv_orders WHERE created_at >= %s AND created_at <= %s AND status IN ($status_in)",
            $start_date, $end_date
        ));
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
        
        if ($period === 'yesterday') {
            $yest = gmdate('Y-m-d', strtotime('-1 day', $now));
            for ($i = 0; $i <= 23; $i++) {
                $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                $chart_labels[] = $hour . ':00';
                $rev = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}mkv_orders WHERE DATE(created_at) = %s AND HOUR(created_at) = %d AND status IN ('paid', 'shipping', 'completed')",
                    $yest, $i
                ));
                $chart_data[] = $rev ? (float) $rev : 0;
            }
        } elseif ($period === '7days' || $period === '30days') {
            $num_days = ($period === '7days') ? 7 : 30;
            for ($i = $num_days - 1; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-{$i} days", $now));
                $chart_labels[] = date('d/m', strtotime($d));
                $rev = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}mkv_orders WHERE DATE(created_at) = %s AND status IN ('paid', 'shipping', 'completed')",
                    $d
                ));
                $chart_data[] = $rev ? (float) $rev : 0;
            }
        } elseif ($period === 'week') {
            $days = array(mkv__('Thứ 2'), mkv__('Thứ 3'), mkv__('Thứ 4'), mkv__('Thứ 5'), mkv__('Thứ 6'), mkv__('Thứ 7'), mkv__('CN'));
            $week_start = date('Y-m-d', strtotime('monday this week', $now));
            for ($i = 0; $i < 7; $i++) {
                $chart_labels[] = $days[$i];
                $date = date('Y-m-d', strtotime($week_start . " +{$i} days"));
                $rev = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}mkv_orders WHERE DATE(created_at) = %s AND status IN ('paid', 'shipping', 'completed')",
                    $date
                ));
                $chart_data[] = $rev ? (float) $rev : 0;
            }
        } elseif ($period === 'month') {
            $days_in_month = date('t', $now);
            $month = date('Y-m', $now);
            for ($i = 1; $i <= $days_in_month; $i++) {
                $chart_labels[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                $date = $month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $rev = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}mkv_orders WHERE DATE(created_at) = %s AND status IN ('paid', 'shipping', 'completed')",
                    $date
                ));
                $chart_data[] = $rev ? (float) $rev : 0;
            }
        } elseif ($period === 'last_month') {
            $last_month_ts = strtotime('first day of -1 month', $now);
            $days_in_month = date('t', $last_month_ts);
            $month = date('Y-m', $last_month_ts);
            for ($i = 1; $i <= $days_in_month; $i++) {
                $chart_labels[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                $date = $month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $rev = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}mkv_orders WHERE DATE(created_at) = %s AND status IN ('paid', 'shipping', 'completed')",
                    $date
                ));
                $chart_data[] = $rev ? (float) $rev : 0;
            }
        } elseif ($period === 'year') {
            $year = date('Y', $now);
            for ($i = 1; $i <= 12; $i++) {
                $chart_labels[] = mkv__('Th') . $i;
                $month = str_pad($i, 2, '0', STR_PAD_LEFT);
                $rev = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}mkv_orders WHERE YEAR(created_at) = %s AND MONTH(created_at) = %s AND status IN ('paid', 'shipping', 'completed')",
                    $year, $month
                ));
                $chart_data[] = $rev ? (float) $rev : 0;
            }
        } elseif ($period === 'all') {
            $months_data = $wpdb->get_results(
                "SELECT DATE_FORMAT(created_at, '%m/%Y') as label, SUM(total_amount) as rev 
                 FROM {$wpdb->prefix}mkv_orders 
                 WHERE status IN ('paid', 'shipping', 'completed')
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY created_at ASC
                 LIMIT 12"
            );
            if ($months_data) {
                foreach ($months_data as $row) {
                    $chart_labels[] = $row->label;
                    $chart_data[] = (float)$row->rev;
                }
            } else {
                $chart_labels = array(date('m/Y', $now));
                $chart_data = array(0);
            }
        } else {
            $today = date('Y-m-d', $now);
            for ($i = 0; $i <= 23; $i++) {
                $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                $chart_labels[] = $hour . ':00';
                $rev = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(total_amount) FROM {$wpdb->prefix}mkv_orders WHERE DATE(created_at) = %s AND HOUR(created_at) = %d AND status IN ('paid', 'shipping', 'completed')",
                    $today, $i
                ));
                $chart_data[] = $rev ? (float) $rev : 0;
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
             WHERE p.post_type = 'mkv_product' AND o.status IN ('paid', 'shipping', 'completed')
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
            "SELECT c.name, SUM(o.total_amount) as spent
             FROM {$wpdb->prefix}mkv_customers c
             JOIN {$wpdb->prefix}mkv_orders o ON c.id = o.customer_id
             WHERE o.status IN ('paid', 'shipping', 'completed')
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
        return $wpdb->get_results($wpdb->prepare(
            "SELECT o.order_code, o.total_amount, o.status, o.created_at as time, c.name as customer_name
             FROM {$wpdb->prefix}mkv_orders o
             LEFT JOIN {$wpdb->prefix}mkv_customers c ON o.customer_id = c.id
             ORDER BY o.created_at DESC
             LIMIT %d", $limit
        ));
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
