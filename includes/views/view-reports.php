<?php if (!defined('ABSPATH')) exit; ?>
<?php require_once MKV_DIR . 'includes/views/header-kiotviet.php'; ?>
<div style="padding: 24px;">

    <!-- Page Header -->
    <div class="mkv-page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
        <h1 class="mkv-page-title" style="margin:0;"><i class="hgi-stroke hgi-analytics-01"></i> <?php echo esc_html(mkv__('Báo Cáo & Phân Tích Doanh Thu')); ?></h1>
        
        <div style="display:flex; gap:10px; align-items:center;">
            <?php
            $export_url = add_query_arg(array(
                'page'       => 'mkv-reports',
                'tab'        => $active_tab,
                'quick'      => $quick,
                'start_date' => $start_date,
                'end_date'   => $end_date,
                'export_csv' => 1
            ), admin_url('admin.php'));
            $export_url = wp_nonce_url($export_url, 'mkv_export_reports_csv');
            ?>
            <a href="<?php echo esc_url($export_url); ?>" class="mkv-btn mkv-btn-secondary" style="height:34px; padding:0 14px; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
                <i class="hgi-stroke hgi-download-02"></i> <?php echo esc_html(mkv__('Xuất CSV')); ?>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs with Persistent Date Parameters -->
    <?php
    $tab_query_params = array();
    if (!empty($quick)) {
        $tab_query_params['quick'] = $quick;
    } else {
        $tab_query_params['start_date'] = $start_date;
        $tab_query_params['end_date']   = $end_date;
    }
    $sales_tab_url = add_query_arg(array_merge(array('page' => 'mkv-reports', 'tab' => 'sales'), $tab_query_params), admin_url('admin.php'));
    $profit_tab_url = add_query_arg(array_merge(array('page' => 'mkv-reports', 'tab' => 'profit'), $tab_query_params), admin_url('admin.php'));
    $eod_tab_url = add_query_arg(array_merge(array('page' => 'mkv-reports', 'tab' => 'end_of_day'), $tab_query_params), admin_url('admin.php'));
    ?>
    <div class="mkv-tabs" style="margin-bottom:20px;">
        <a href="<?php echo esc_url($sales_tab_url); ?>" class="mkv-tab-link <?php echo $active_tab === 'sales' ? 'active' : ''; ?>">
            <i class="hgi-stroke hgi-analytics-01"></i> <?php echo esc_html(mkv__('Bán hàng')); ?>
        </a>
        <?php if (current_user_can('mkv_view_cost_price')): ?>
        <a href="<?php echo esc_url($profit_tab_url); ?>" class="mkv-tab-link <?php echo $active_tab === 'profit' ? 'active' : ''; ?>">
            <i class="hgi-stroke hgi-analytics-up"></i> <?php echo esc_html(mkv__('Lợi nhuận')); ?>
        </a>
        <?php endif; ?>
        <a href="<?php echo esc_url($eod_tab_url); ?>" class="mkv-tab-link <?php echo $active_tab === 'end_of_day' ? 'active' : ''; ?>">
            <i class="hgi-stroke hgi-time-02"></i> <?php echo esc_html(mkv__('Cuối ngày')); ?>
        </a>
    </div>

    <!-- Quick Filter Bar + Custom Date Picker -->
    <div class="mkv-filter-bar" style="margin-bottom:20px; display:block;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <!-- Quick Pills (Preserves Tab Context) -->
            <div class="mkv-filter-pills" style="margin-bottom:0; padding-bottom:0;">
                <a href="<?php echo esc_url(add_query_arg(array('page' => 'mkv-reports', 'tab' => $active_tab, 'quick' => 'today'), admin_url('admin.php'))); ?>" 
                   class="mkv-filter-pill <?php echo ($quick === 'today') ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Hôm nay')); ?></a>
                
                <a href="<?php echo esc_url(add_query_arg(array('page' => 'mkv-reports', 'tab' => $active_tab, 'quick' => 'yesterday'), admin_url('admin.php'))); ?>" 
                   class="mkv-filter-pill <?php echo ($quick === 'yesterday') ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Hôm qua')); ?></a>
                
                <a href="<?php echo esc_url(add_query_arg(array('page' => 'mkv-reports', 'tab' => $active_tab, 'quick' => 'week'), admin_url('admin.php'))); ?>" 
                   class="mkv-filter-pill <?php echo ($quick === 'week') ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Tuần này')); ?></a>
                
                <a href="<?php echo esc_url(add_query_arg(array('page' => 'mkv-reports', 'tab' => $active_tab, 'quick' => 'month'), admin_url('admin.php'))); ?>" 
                   class="mkv-filter-pill <?php echo ($quick === 'month') ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Tháng này')); ?></a>
                
                <a href="<?php echo esc_url(add_query_arg(array('page' => 'mkv-reports', 'tab' => $active_tab, 'quick' => 'year'), admin_url('admin.php'))); ?>" 
                   class="mkv-filter-pill <?php echo ($quick === 'year') ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Năm nay')); ?></a>
            </div>

            <!-- Custom Date Range Form (Preserves Tab Context) -->
            <form method="get" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin:0;">
                <input type="hidden" name="page" value="mkv-reports">
                <input type="hidden" name="tab" value="<?php echo esc_attr($active_tab); ?>">
                
                <span style="font-size:13px; color:var(--mkv-text-muted); font-weight:500;"><?php echo esc_html(mkv__('Khoảng ngày:')); ?></span>
                <input type="date" name="start_date" class="mkv-input" aria-label="<?php echo esc_attr(mkv__('Từ ngày')); ?>" value="<?php echo esc_attr($start_date); ?>" style="height:34px; font-size:13px; width:140px;">
                <span style="font-size:13px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('đến')); ?></span>
                <input type="date" name="end_date" class="mkv-input" aria-label="<?php echo esc_attr(mkv__('Đến ngày')); ?>" value="<?php echo esc_attr($end_date); ?>" style="height:34px; font-size:13px; width:140px;">
                <button type="submit" class="mkv-btn mkv-btn-primary" style="height:34px; padding:0 14px; font-size:13px;">
                    <i class="hgi-stroke hgi-filter"></i> <?php echo esc_html(mkv__('Lọc')); ?>
                </button>
            </form>
        </div>
    </div>

    <?php /* ==========================================================================
       TAB 1: BÁN HÀNG (SALES)
       ========================================================================== */ ?>
    <?php if ($active_tab === 'sales'): ?>

        <!-- KPI Cards: Sales Focus -->
        <div class="mkv-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom:24px;">
            <div class="mkv-stat-box mkv-border-blue">
                <h3><?php echo esc_html(mkv__('Doanh thu bán hàng')); ?></h3>
                <p class="mkv-text-blue"><?php echo number_format($total_revenue, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-money-bag-02 icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-green">
                <h3><?php echo esc_html(mkv__('Thực thu (Đã nhận)')); ?></h3>
                <p class="mkv-text-green"><?php echo number_format($total_collected, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-money-receive-01 icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-yellow">
                <h3><?php echo esc_html(mkv__('Chưa thu (Khách nợ + COD)')); ?></h3>
                <p class="mkv-text-yellow"><?php echo number_format($total_debt_pending, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-time-02 icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-purple">
                <h3><?php echo esc_html(mkv__('Đơn hoàn thành')); ?></h3>
                <p class="mkv-text-purple"><?php echo $total_orders; ?></p>
                <i class="hgi-stroke hgi-invoice-01 icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-red">
                <h3><?php echo esc_html(mkv__('Khách trả / Hủy đơn')); ?></h3>
                <p class="mkv-text-red">
                    <?php echo $returned_orders; ?> <small style="font-size:12px; font-weight:normal; color:var(--mkv-text-muted);">(Trả: <?php echo $return_rate; ?>% | Hủy: <?php echo $cancelled_orders; ?>)</small>
                </p>
                <i class="hgi-stroke hgi-arrow-left-right icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-yellow">
                <h3><?php echo esc_html(mkv__('Giá trị TB / Đơn (AOV)')); ?></h3>
                <p class="mkv-text-yellow"><?php echo number_format($aov, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-wallet-02 icon-bg"></i>
            </div>

            <div class="mkv-stat-box">
                <h3><?php echo esc_html(mkv__('Khách hàng mới')); ?></h3>
                <p><?php echo $new_customers; ?></p>
                <i class="hgi-stroke hgi-user-add-01 icon-bg"></i>
            </div>
        </div>

        <!-- Biểu đồ Doanh thu -->
        <?php if (!empty($chart_labels)): ?>
        <div class="mkv-card" style="margin-bottom:24px;">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title">
                    <i class="hgi-stroke hgi-analytics-01"></i> 
                    <?php echo esc_html(mkv__('Diễn biến doanh thu bán hàng')); ?> 
                    <small style="font-weight:400; font-size:12px; color:var(--mkv-text-muted);">
                        (<?php echo esc_html(date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date))); ?>)
                    </small>
                </h3>
            </div>
            <div class="mkv-card-body">
                <div style="position:relative; height:260px;">
                    <canvas id="mkvReportChartSales"></canvas>
                </div>
            </div>
        </div>
        <script>
        (function() {
            var chartLabels  = <?php echo json_encode($chart_labels); ?>;
            var chartRevenue = <?php echo json_encode($chart_revenue); ?>;
            function initSalesChart() {
                var ctx = document.getElementById('mkvReportChartSales');
                if (!ctx || typeof Chart === 'undefined') return;
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{ 
                            label: '<?php echo esc_js(mkv__('Doanh thu')); ?>', 
                            data: chartRevenue, 
                            backgroundColor: '#3b82f6', 
                            borderRadius: 6, 
                            maxBarThickness: 36 
                        }]
                    },
                    options: {
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false }, 
                            tooltip: { 
                                callbacks: { 
                                    label: function(c) { 
                                        return ' ' + c.dataset.label + ': ' + parseFloat(c.parsed.y).toLocaleString('vi-VN') + ' ₫'; 
                                    } 
                                } 
                            } 
                        },
                        scales: { 
                            y: { 
                                beginAtZero: true, 
                                grid: { color: '#f1f5f9' }, 
                                ticks: { 
                                    callback: function(v) { 
                                        if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M'; 
                                        if (v >= 1000) return (v / 1000).toFixed(0) + 'K'; 
                                        return v; 
                                    } 
                                } 
                            }, 
                            x: { grid: { display: false } } 
                        },
                        layout: { padding: 10 }
                    }
                });
            }
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initSalesChart);
            else initSalesChart();
        })();
        </script>
        <?php endif; ?>

        <!-- Kênh bán hàng -->
        <div class="mkv-card" style="margin-bottom:24px;">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title"><i class="hgi-stroke hgi-store-01"></i> <?php echo esc_html(mkv__('Doanh thu theo kênh bán')); ?></h3>
            </div>
            <div class="mkv-card-body" style="padding:0;">
                <table class="mkv-table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html(mkv__('Kênh bán')); ?></th>
                            <th style="width:100px; text-align:center;"><?php echo esc_html(mkv__('Số đơn')); ?></th>
                            <th style="width:180px; text-align:right;"><?php echo esc_html(mkv__('Doanh thu')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $channel_names = array(
                            'pos'         => mkv__('Tại quầy'),
                            'online'      => mkv__('Online'),
                            'social'      => mkv__('Facebook / Zalo'),
                            'marketplace' => mkv__('Sàn TMĐT'),
                        );
                        if (empty($channel_breakdown)): ?>
                            <tr><td colspan="3" style="text-align:center; padding:24px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Chưa có dữ liệu kênh bán trong kỳ này.')); ?></td></tr>
                        <?php else: foreach ($channel_breakdown as $ch): ?>
                            <tr>
                                <td><strong><?php echo esc_html($channel_names[$ch->sales_channel] ?? $ch->sales_channel); ?></strong></td>
                                <td style="text-align:center;"><span class="mkv-badge mkv-badge-blue"><?php echo (int) $ch->order_count; ?></span></td>
                                <td style="text-align:right;"><strong style="color:var(--mkv-primary);"><?php echo number_format((float) $ch->revenue, 0, ',', '.'); ?> ₫</strong></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2 Columns Grid: Top Sản Phẩm & Danh Sách Đơn Hàng Phân Trang -->
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap:24px; align-items:start;">
            
            <!-- Top sản phẩm bán chạy -->
            <div class="mkv-card" style="padding:0;">
                <div style="padding:16px 20px; border-bottom:1px solid var(--mkv-border);">
                    <h3 style="margin:0; padding:0; border:none;"><i class="hgi-stroke hgi-fire"></i> <?php echo esc_html(mkv__('Top 10 Sản Phẩm Bán Chạy Kỳ Này')); ?></h3>
                </div>
                <div class="mkv-table-wrap" style="border:none; box-shadow:none; border-radius:0; margin-bottom:0; max-height:480px; overflow-y:auto;">
                    <table class="mkv-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html(mkv__('Sản phẩm')); ?></th>
                                <th style="width:80px; text-align:center;"><?php echo esc_html(mkv__('Đã bán')); ?></th>
                                <th style="width:130px; text-align:right;"><?php echo esc_html(mkv__('Doanh thu')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($top_products)): ?>
                                <tr><td colspan="3" style="text-align:center; padding:30px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Không có dữ liệu trong kỳ này.')); ?></td></tr>
                            <?php else: foreach ($top_products as $tp): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($tp->name); ?></strong></td>
                                    <td style="text-align:center;"><span class="mkv-badge mkv-badge-blue"><?php echo $tp->total_sold; ?></span></td>
                                    <td style="text-align:right;"><strong style="color:var(--mkv-primary);"><?php echo number_format($tp->total_revenue, 0, ',', '.'); ?> ₫</strong></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Danh sách đơn hàng trong kỳ có phân trang -->
            <div class="mkv-card" style="padding:0;">
                <div style="padding:16px 20px; border-bottom:1px solid var(--mkv-border); display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="margin:0; padding:0; border:none;"><i class="hgi-stroke hgi-invoice-01"></i> <?php echo esc_html(mkv__('Đơn Hàng Trong Kỳ')); ?></h3>
                    <span style="font-size:12px; color:var(--mkv-text-muted);"><?php echo sprintf(mkv__('Tổng %d đơn'), $orders_total_count); ?></span>
                </div>
                <div class="mkv-table-wrap" style="border:none; box-shadow:none; border-radius:0; margin-bottom:0; max-height:480px; overflow-y:auto;">
                    <table class="mkv-table" style="min-width:600px;">
                        <thead>
                            <tr>
                                <th><?php echo esc_html(mkv__('Mã đơn')); ?></th>
                                <th><?php echo esc_html(mkv__('Khách hàng')); ?></th>
                                <th><?php echo esc_html(mkv__('Trạng thái')); ?></th>
                                <th style="text-align:right;"><?php echo esc_html(mkv__('Tổng tiền')); ?></th>
                                <th style="text-align:right;"><?php echo esc_html(mkv__('Đã thu')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Không có dữ liệu trong khoảng thời gian này.')); ?></td></tr>
                            <?php else: foreach ($orders as $o):
                                $st_color = ($o->status === 'completed' || $o->status === 'paid') ? 'mkv-badge-green' : (($o->status === 'cancelled') ? 'mkv-badge-red' : (($o->status === 'returned') ? 'mkv-badge-gray' : 'mkv-badge-yellow'));
                                $status_map = array(
                                    'draft'     => 'Nháp',
                                    'pending'   => 'Chờ duyệt',
                                    'paid'      => 'Đã TT',
                                    'shipping'  => 'Đang giao',
                                    'completed' => 'Hoàn thành',
                                    'cancelled' => 'Đã hủy',
                                    'returned'  => 'Đã trả hàng',
                                );
                            ?>
                            <tr>
                                <td>
                                    <strong><a href="<?php echo esc_url(admin_url('admin.php?page=mkv-orders&id=' . $o->id)); ?>"><?php echo esc_html($o->order_code); ?></a></strong><br>
                                    <small style="color:var(--mkv-text-muted);"><?php echo esc_html(date('d/m H:i', strtotime($o->created_at))); ?></small>
                                </td>
                                <td><?php echo esc_html($o->customer_name ?: mkv__('Khách lẻ')); ?></td>
                                <td><span class="mkv-badge <?php echo $st_color; ?>"><?php echo esc_html(mkv__($status_map[$o->status] ?? $o->status)); ?></span></td>
                                <td style="text-align:right;"><strong style="color:var(--mkv-primary);"><?php echo number_format($o->total_amount, 0, ',', '.'); ?> ₫</strong></td>
                                <td style="text-align:right;"><span style="color:#059669; font-weight:600;"><?php echo number_format($o->paid_amount, 0, ',', '.'); ?> ₫</span></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination for Orders -->
                <?php if ($orders_total_pages > 1): ?>
                <div style="display:flex; justify-content:center; align-items:center; gap:8px; padding:12px; border-top:1px solid var(--mkv-border);">
                    <?php for ($p = 1; $p <= $orders_total_pages; $p++):
                        $p_url = add_query_arg(array_merge(array(
                            'page'  => 'mkv-reports',
                            'tab'   => 'sales',
                            'paged' => $p
                        ), $tab_query_params), admin_url('admin.php'));
                    ?>
                        <a href="<?php echo esc_url($p_url); ?>" 
                           class="mkv-btn <?php echo ($orders_page == $p) ? 'mkv-btn-primary' : 'mkv-btn-secondary'; ?>" 
                           style="padding:3px 10px; font-size:12px; height:28px; line-height:22px;">
                           <?php echo $p; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>

    <?php endif; /* END TAB SALES */ ?>

    <?php /* ==========================================================================
       TAB 2: LỢI NHUẬN (PROFIT & LOSS)
       ========================================================================== */ ?>
    <?php if ($active_tab === 'profit' && current_user_can('mkv_view_cost_price')): ?>

        <!-- KPI Cards: Profit Focus -->
        <div class="mkv-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom:24px;">
            <div class="mkv-stat-box mkv-border-blue">
                <h3><?php echo esc_html(mkv__('Doanh thu thuần')); ?></h3>
                <p class="mkv-text-blue"><?php echo number_format($total_revenue, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-money-bag-02 icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-yellow">
                <h3><?php echo esc_html(mkv__('Tổng giá vốn (COGS)')); ?></h3>
                <p class="mkv-text-yellow"><?php echo number_format($total_cost, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-package-box icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-green">
                <h3><?php echo esc_html(mkv__('Lợi nhuận gộp')); ?></h3>
                <p class="mkv-text-green"><?php echo number_format($gross_profit, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-analytics-up icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-green">
                <h3><?php echo esc_html(mkv__('Tỷ suất LN gộp')); ?></h3>
                <p class="mkv-text-green"><?php echo $gross_margin; ?>%</p>
                <i class="hgi-stroke hgi-percentage-square icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-red">
                <h3><?php echo esc_html(mkv__('Chi phí hoạt động khác')); ?></h3>
                <p class="mkv-text-red"><?php echo number_format($operating_expenses, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-money-send-square icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-purple">
                <h3><?php echo esc_html(mkv__('Lợi nhuận ròng (Net)')); ?></h3>
                <p class="mkv-text-purple"><?php echo number_format($net_profit, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-wallet-01 icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-purple">
                <h3><?php echo esc_html(mkv__('Tỷ suất LN ròng')); ?></h3>
                <p class="mkv-text-purple"><?php echo $net_margin; ?>%</p>
                <i class="hgi-stroke hgi-chart-bubble icon-bg"></i>
            </div>

            <div class="mkv-stat-box">
                <h3><?php echo esc_html(mkv__('Lãi TB / Đơn hàng')); ?></h3>
                <p><?php echo number_format($avg_profit_order, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-invoice-02 icon-bg"></i>
            </div>
        </div>

        <!-- Biểu đồ So Sánh Doanh Thu & Lợi Nhuận Gộp -->
        <?php if (!empty($chart_labels)): ?>
        <div class="mkv-card" style="margin-bottom:24px;">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title">
                    <i class="hgi-stroke hgi-analytics-up"></i> 
                    <?php echo esc_html(mkv__('Tương quan Doanh thu & Lợi nhuận gộp')); ?> 
                    <small style="font-weight:400; font-size:12px; color:var(--mkv-text-muted);">
                        (<?php echo esc_html(date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date))); ?>)
                    </small>
                </h3>
            </div>
            <div class="mkv-card-body">
                <div style="position:relative; height:260px;">
                    <canvas id="mkvReportChartProfit"></canvas>
                </div>
            </div>
        </div>
        <script>
        (function() {
            var chartLabels  = <?php echo json_encode($chart_labels); ?>;
            var chartRevenue = <?php echo json_encode($chart_revenue); ?>;
            var chartProfit  = <?php echo json_encode($chart_profit); ?>;
            function initProfitChart() {
                var ctx = document.getElementById('mkvReportChartProfit');
                if (!ctx || typeof Chart === 'undefined') return;
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            { 
                                label: '<?php echo esc_js(mkv__('Doanh thu')); ?>', 
                                data: chartRevenue, 
                                backgroundColor: '#3b82f6', 
                                borderRadius: 6, 
                                maxBarThickness: 28 
                            },
                            { 
                                label: '<?php echo esc_js(mkv__('Lợi nhuận gộp')); ?>', 
                                data: chartProfit, 
                                backgroundColor: '#10b981', 
                                borderRadius: 6, 
                                maxBarThickness: 28 
                            }
                        ]
                    },
                    options: {
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: true, position: 'top' }, 
                            tooltip: { 
                                callbacks: { 
                                    label: function(c) { 
                                        return ' ' + c.dataset.label + ': ' + parseFloat(c.parsed.y).toLocaleString('vi-VN') + ' ₫'; 
                                    } 
                                } 
                            } 
                        },
                        scales: { 
                            y: { 
                                beginAtZero: true, 
                                grid: { color: '#f1f5f9' }, 
                                ticks: { 
                                    callback: function(v) { 
                                        if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M'; 
                                        if (v >= 1000) return (v / 1000).toFixed(0) + 'K'; 
                                        return v; 
                                    } 
                                } 
                            }, 
                            x: { grid: { display: false } } 
                        },
                        layout: { padding: 10 }
                    }
                });
            }
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initProfitChart);
            else initProfitChart();
        })();
        </script>
        <?php endif; ?>

        <!-- 2 Columns Grid: Top Sản Phẩm Lợi Nhuận & Lợi Nhuận Theo Kênh Bán -->
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap:24px; align-items:start;">
            
            <!-- Top sản phẩm sinh lời cao nhất -->
            <div class="mkv-card" style="padding:0;">
                <div style="padding:16px 20px; border-bottom:1px solid var(--mkv-border);">
                    <h3 style="margin:0; padding:0; border:none;"><i class="hgi-stroke hgi-medal-first"></i> <?php echo esc_html(mkv__('Top 10 Sản Phẩm Sinh Lời Cao Nhất')); ?></h3>
                </div>
                <div class="mkv-table-wrap" style="border:none; box-shadow:none; border-radius:0; margin-bottom:0; max-height:500px; overflow-y:auto;">
                    <table class="mkv-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html(mkv__('Sản phẩm')); ?></th>
                                <th style="width:70px; text-align:center;"><?php echo esc_html(mkv__('Đã bán')); ?></th>
                                <th style="width:110px; text-align:right;"><?php echo esc_html(mkv__('Doanh thu')); ?></th>
                                <th style="width:110px; text-align:right;"><?php echo esc_html(mkv__('Lợi nhuận')); ?></th>
                                <th style="width:80px; text-align:right;"><?php echo esc_html(mkv__('Biên lãi')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($top_profitable_products)): ?>
                                <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Chưa có dữ liệu trong kỳ này.')); ?></td></tr>
                            <?php else: foreach ($top_profitable_products as $p):
                                $margin_p = ($p->total_revenue > 0) ? round(($p->profit / $p->total_revenue) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td><strong><?php echo esc_html($p->name); ?></strong></td>
                                    <td style="text-align:center;"><span class="mkv-badge mkv-badge-blue"><?php echo $p->total_sold; ?></span></td>
                                    <td style="text-align:right;"><?php echo number_format($p->total_revenue, 0, ',', '.'); ?> ₫</td>
                                    <td style="text-align:right;"><strong style="color:var(--mkv-green);"><?php echo number_format($p->profit, 0, ',', '.'); ?> ₫</strong></td>
                                    <td style="text-align:right;"><span class="mkv-badge mkv-badge-green"><?php echo $margin_p; ?>%</span></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cơ cấu lợi nhuận theo kênh bán hàng -->
            <div class="mkv-card" style="padding:0;">
                <div style="padding:16px 20px; border-bottom:1px solid var(--mkv-border);">
                    <h3 style="margin:0; padding:0; border:none;"><i class="hgi-stroke hgi-store-01"></i> <?php echo esc_html(mkv__('Hiệu Quả Lợi Nhuận Theo Kênh Bán')); ?></h3>
                </div>
                <div class="mkv-table-wrap" style="border:none; box-shadow:none; border-radius:0; margin-bottom:0; max-height:500px; overflow-y:auto;">
                    <table class="mkv-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html(mkv__('Kênh bán')); ?></th>
                                <th style="width:70px; text-align:center;"><?php echo esc_html(mkv__('Số đơn')); ?></th>
                                <th style="text-align:right;"><?php echo esc_html(mkv__('Doanh thu')); ?></th>
                                <th style="text-align:right;"><?php echo esc_html(mkv__('Lợi nhuận')); ?></th>
                                <th style="width:80px; text-align:right;"><?php echo esc_html(mkv__('Biên lãi')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $channel_names = array(
                                'pos'         => mkv__('Tại quầy'),
                                'online'      => mkv__('Online'),
                                'social'      => mkv__('Facebook / Zalo'),
                                'marketplace' => mkv__('Sàn TMĐT'),
                            );
                            if (empty($channel_breakdown)): ?>
                                <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Chưa có dữ liệu trong kỳ này.')); ?></td></tr>
                            <?php else: foreach ($channel_breakdown as $ch):
                                $ch_margin = ($ch->revenue > 0) ? round(($ch->profit / $ch->revenue) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td><strong><?php echo esc_html($channel_names[$ch->sales_channel] ?? $ch->sales_channel); ?></strong></td>
                                    <td style="text-align:center;"><span class="mkv-badge mkv-badge-blue"><?php echo (int) $ch->order_count; ?></span></td>
                                    <td style="text-align:right;"><?php echo number_format((float) $ch->revenue, 0, ',', '.'); ?> ₫</td>
                                    <td style="text-align:right;"><strong style="color:var(--mkv-green);"><?php echo number_format((float) $ch->profit, 0, ',', '.'); ?> ₫</strong></td>
                                    <td style="text-align:right;"><span class="mkv-badge mkv-badge-green"><?php echo $ch_margin; ?>%</span></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    <?php endif; /* END TAB PROFIT */ ?>

    <?php /* ==========================================================================
       TAB 3: CUỐI NGÀY (END OF DAY / CASH RECONCILIATION)
       ========================================================================== */ ?>
    <?php if ($active_tab === 'end_of_day'): ?>

        <?php
        $closing_total = $eod_opening['total'] + $eod_period_thu['total'] - $eod_period_chi['total'];
        $closing_cash  = $eod_opening['cash']  + $eod_period_thu['cash']  - $eod_period_chi['cash'];
        $closing_trans = $eod_opening['transfer'] + $eod_period_thu['transfer'] - $eod_period_chi['transfer'];
        ?>

        <!-- KPI Cards: Cash & Treasury Reconciliation Focus -->
        <div class="mkv-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom:24px;">
            <div class="mkv-stat-box mkv-border-yellow">
                <h3><?php echo esc_html(mkv__('Tồn quỹ đầu kỳ')); ?></h3>
                <p class="mkv-text-yellow"><?php echo number_format($eod_opening['total'], 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-wallet-01 icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-green">
                <h3><?php echo esc_html(mkv__('Tổng thực thu trong kỳ')); ?></h3>
                <p class="mkv-text-green">+<?php echo number_format($eod_period_thu['total'], 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-money-receive-01 icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-red">
                <h3><?php echo esc_html(mkv__('Tổng thực chi trong kỳ')); ?></h3>
                <p class="mkv-text-red">-<?php echo number_format($eod_period_chi['total'], 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-money-send-square icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-blue">
                <h3><?php echo esc_html(mkv__('Tồn quỹ cuối kỳ')); ?></h3>
                <p class="mkv-text-blue"><?php echo number_format($closing_total, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-safe-box icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-purple">
                <h3><?php echo esc_html(mkv__('Tồn két tiền mặt')); ?></h3>
                <p class="mkv-text-purple"><?php echo number_format($closing_cash, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-money-bag-01 icon-bg"></i>
            </div>

            <div class="mkv-stat-box mkv-border-blue">
                <h3><?php echo esc_html(mkv__('Tồn tài khoản ngân hàng')); ?></h3>
                <p class="mkv-text-blue"><?php echo number_format($closing_trans, 0, ',', '.'); ?> ₫</p>
                <i class="hgi-stroke hgi-credit-card icon-bg"></i>
            </div>
        </div>

        <!-- 2 Columns Grid: Bảng Đối Soát Phương Thức & Cơ Cấu Thu Chi -->
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap:24px; align-items:start; margin-bottom:24px;">
            
            <!-- Bảng 1: Đối soát chi tiết theo phương thức -->
            <div class="mkv-card" style="padding:0;">
                <div style="padding:16px 20px; border-bottom:1px solid var(--mkv-border);">
                    <h3 style="margin:0; padding:0; border:none;"><i class="hgi-stroke hgi-safe-box"></i> <?php echo esc_html(mkv__('Bảng Đối Soát Quỹ Theo Phương Thức')); ?></h3>
                    <p style="margin:4px 0 0 0; font-size:12px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Đối chiếu số dư đầu kỳ, dòng tiền phát sinh và số dư cuối kỳ.')); ?></p>
                </div>
                <div class="mkv-table-wrap" style="border:none; box-shadow:none; border-radius:0; margin-bottom:0;">
                    <table class="mkv-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html(mkv__('Phương thức')); ?></th>
                                <th style="text-align:right;"><?php echo esc_html(mkv__('Đầu kỳ')); ?></th>
                                <th style="text-align:right; color:var(--mkv-green);"><?php echo esc_html(mkv__('Thu (+)')); ?></th>
                                <th style="text-align:right; color:var(--mkv-red);"><?php echo esc_html(mkv__('Chi (-)')); ?></th>
                                <th style="text-align:right;"><?php echo esc_html(mkv__('Cuối kỳ')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $methods = array(
                                'cash'     => mkv__('Tiền mặt'),
                                'transfer' => mkv__('Chuyển khoản'),
                                'card'     => mkv__('Thẻ ngân hàng')
                            );
                            foreach ($methods as $code => $label):
                                $op  = $eod_opening[$code] ?? 0.0;
                                $th  = $eod_period_thu[$code] ?? 0.0;
                                $ch  = $eod_period_chi[$code] ?? 0.0;
                                $cl  = $op + $th - $ch;
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($label); ?></strong></td>
                                <td style="text-align:right;"><?php echo number_format($op, 0, ',', '.'); ?> ₫</td>
                                <td style="text-align:right; color:var(--mkv-green);">+<?php echo number_format($th, 0, ',', '.'); ?> ₫</td>
                                <td style="text-align:right; color:var(--mkv-red);">-<?php echo number_format($ch, 0, ',', '.'); ?> ₫</td>
                                <td style="text-align:right;"><strong style="color:var(--mkv-primary);"><?php echo number_format($cl, 0, ',', '.'); ?> ₫</strong></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr style="background:#f8fafc; font-weight:bold;">
                                <td><strong><?php echo esc_html(mkv__('TỔNG CỘNG:')); ?></strong></td>
                                <td style="text-align:right;"><?php echo number_format($eod_opening['total'], 0, ',', '.'); ?> ₫</td>
                                <td style="text-align:right; color:var(--mkv-green);">+<?php echo number_format($eod_period_thu['total'], 0, ',', '.'); ?> ₫</td>
                                <td style="text-align:right; color:var(--mkv-red);">-<?php echo number_format($eod_period_chi['total'], 0, ',', '.'); ?> ₫</td>
                                <td style="text-align:right; font-size:15px; color:var(--mkv-primary);"><?php echo number_format($closing_total, 0, ',', '.'); ?> ₫</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bảng 2: Cơ cấu dòng tiền theo nghiệp vụ -->
            <div class="mkv-card" style="padding:0;">
                <div style="padding:16px 20px; border-bottom:1px solid var(--mkv-border);">
                    <h3 style="margin:0; padding:0; border:none;"><i class="hgi-stroke hgi-flow"></i> <?php echo esc_html(mkv__('Phân Loại Dòng Tiền Theo Nghiệp Vụ')); ?></h3>
                    <p style="margin:4px 0 0 0; font-size:12px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Bóc tách nguồn thu và nguồn chi thực tế trong kỳ.')); ?></p>
                </div>
                <div class="mkv-table-wrap" style="border:none; box-shadow:none; border-radius:0; margin-bottom:0;">
                    <table class="mkv-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html(mkv__('Khoản mục')); ?></th>
                                <th style="text-align:right; width:150px;"><?php echo esc_html(mkv__('Số tiền')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Thu -->
                            <tr style="background:#f0fdf4;">
                                <td colspan="2" style="font-weight:600; color:var(--mkv-green);"><i class="hgi-stroke hgi-arrow-down-left-01"></i> <?php echo esc_html(mkv__('CÁC NGUỒN THU VÀO (+)')); ?></td>
                            </tr>
                            <tr>
                                <td style="padding-left:24px;"><?php echo esc_html(mkv__('1. Thu tiền bán hàng trực tiếp')); ?></td>
                                <td style="text-align:right; font-weight:600; color:var(--mkv-green);">+<?php echo number_format($eod_cashflow_breakdown['thu_order'], 0, ',', '.'); ?> ₫</td>
                            </tr>
                            <tr>
                                <td style="padding-left:24px;"><?php echo esc_html(mkv__('2. Thu công nợ khách hàng')); ?></td>
                                <td style="text-align:right; font-weight:600; color:var(--mkv-green);">+<?php echo number_format($eod_cashflow_breakdown['thu_debt'], 0, ',', '.'); ?> ₫</td>
                            </tr>
                            <tr>
                                <td style="padding-left:24px;"><?php echo esc_html(mkv__('3. Thu khác (góp vốn, nộp tiền...)')); ?></td>
                                <td style="text-align:right; font-weight:600; color:var(--mkv-green);">+<?php echo number_format($eod_cashflow_breakdown['thu_other'], 0, ',', '.'); ?> ₫</td>
                            </tr>

                            <!-- Chi -->
                            <tr style="background:#fef2f2;">
                                <td colspan="2" style="font-weight:600; color:var(--mkv-red);"><i class="hgi-stroke hgi-arrow-up-right-01"></i> <?php echo esc_html(mkv__('CÁC NGUỒN CHI RA (-)')); ?></td>
                            </tr>
                            <tr>
                                <td style="padding-left:24px;"><?php echo esc_html(mkv__('1. Chi thanh toán tiền hàng NCC')); ?></td>
                                <td style="text-align:right; font-weight:600; color:var(--mkv-red);">-<?php echo number_format($eod_cashflow_breakdown['chi_supplier'], 0, ',', '.'); ?> ₫</td>
                            </tr>
                            <tr>
                                <td style="padding-left:24px;"><?php echo esc_html(mkv__('2. Chi hoàn tiền khách trả hàng')); ?></td>
                                <td style="text-align:right; font-weight:600; color:var(--mkv-red);">-<?php echo number_format($eod_cashflow_breakdown['chi_refund'], 0, ',', '.'); ?> ₫</td>
                            </tr>
                            <tr>
                                <td style="padding-left:24px;"><?php echo esc_html(mkv__('3. Chi phí vận hành khác (mặt bằng, điện nước...)')); ?></td>
                                <td style="text-align:right; font-weight:600; color:var(--mkv-red);">-<?php echo number_format($eod_cashflow_breakdown['chi_expense'], 0, ',', '.'); ?> ₫</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Bảng 3: Bảng Kê Chi Tiết Giao Dịch Trong Ca / Ngày (Transaction Journal) -->
        <div class="mkv-card" style="padding:0;">
            <div style="padding:16px 20px; border-bottom:1px solid var(--mkv-border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div>
                    <h3 style="margin:0; padding:0; border:none;"><i class="hgi-stroke hgi-receipt-02"></i> <?php echo esc_html(mkv__('Bảng Kê Chi Tiết Giao Dịch Thu / Chi Trong Ca')); ?></h3>
                    <p style="margin:4px 0 0 0; font-size:12px; color:var(--mkv-text-muted);"><?php echo sprintf(mkv__('Tổng cộng %d giao dịch phát sinh'), $eod_journal_count); ?></p>
                </div>
                <?php if (current_user_can('mkv_manage_cashbook')): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mkv-cashbook')); ?>" class="mkv-btn mkv-btn-secondary" style="height:32px; font-size:12px; padding:0 12px; display:inline-flex; align-items:center; gap:6px;">
                    <i class="hgi-stroke hgi-arrow-right-01"></i> <?php echo esc_html(mkv__('Quản lý Sổ Quỹ')); ?>
                </a>
                <?php endif; ?>
            </div>
            
            <div class="mkv-table-wrap" style="border:none; box-shadow:none; border-radius:0; margin-bottom:0; max-height:500px; overflow-y:auto;">
                <table class="mkv-table" style="min-width:750px;">
                    <thead>
                        <tr>
                            <th style="width:110px;"><?php echo esc_html(mkv__('Thời gian')); ?></th>
                            <th style="width:90px;"><?php echo esc_html(mkv__('Mã phiếu')); ?></th>
                            <th style="width:80px; text-align:center;"><?php echo esc_html(mkv__('Loại')); ?></th>
                            <th style="width:110px;"><?php echo esc_html(mkv__('Phương thức')); ?></th>
                            <th><?php echo esc_html(mkv__('Đối tác')); ?></th>
                            <th><?php echo esc_html(mkv__('Lý do / Ghi chú')); ?></th>
                            <th style="width:100px;"><?php echo esc_html(mkv__('Người lập')); ?></th>
                            <th style="width:130px; text-align:right;"><?php echo esc_html(mkv__('Số tiền')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($eod_journal)): ?>
                            <tr><td colspan="8" style="text-align:center; padding:36px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Không có giao dịch thu chi nào trong khoảng thời gian này.')); ?></td></tr>
                        <?php else: foreach ($eod_journal as $tx):
                            $is_thu = ($tx->type === 'thu');
                            $tx_color = $is_thu ? 'mkv-badge-green' : 'mkv-badge-red';
                            $method_lbl = ($tx->method === 'transfer') ? mkv__('Chuyển khoản') : (($tx->method === 'card') ? mkv__('Thẻ') : mkv__('Tiền mặt'));
                            $partner = $tx->customer_name ?: ($tx->supplier_name ?: ($tx->order_code ? mkv__('Đơn: ') . $tx->order_code : '-'));
                        ?>
                            <tr>
                                <td><small style="color:var(--mkv-text-muted); font-weight:500;"><?php echo esc_html(date('H:i d/m/Y', strtotime($tx->created_at))); ?></small></td>
                                <td><strong>#CB-<?php echo $tx->id; ?></strong></td>
                                <td style="text-align:center;"><span class="mkv-badge <?php echo $tx_color; ?>"><?php echo esc_html(strtoupper($tx->type)); ?></span></td>
                                <td><span class="mkv-badge mkv-badge-blue"><?php echo esc_html($method_lbl); ?></span></td>
                                <td><?php echo esc_html($partner); ?></td>
                                <td><?php echo esc_html($tx->note ?: '-'); ?></td>
                                <td><small><?php echo esc_html($tx->staff_name ?: '-'); ?></small></td>
                                <td style="text-align:right;">
                                    <strong style="color:<?php echo $is_thu ? 'var(--mkv-green)' : 'var(--mkv-red)'; ?>;">
                                        <?php echo $is_thu ? '+' : '-'; ?><?php echo number_format($tx->amount, 0, ',', '.'); ?> ₫
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination for EOD Journal -->
            <?php if ($eod_journal_pages > 1): ?>
            <div style="display:flex; justify-content:center; align-items:center; gap:8px; padding:12px; border-top:1px solid var(--mkv-border);">
                <?php for ($cp = 1; $cp <= $eod_journal_pages; $cp++):
                    $cp_url = add_query_arg(array_merge(array(
                        'page'     => 'mkv-reports',
                        'tab'      => 'end_of_day',
                        'cb_paged' => $cp
                    ), $tab_query_params), admin_url('admin.php'));
                ?>
                    <a href="<?php echo esc_url($cp_url); ?>" 
                       class="mkv-btn <?php echo ($eod_page == $cp) ? 'mkv-btn-primary' : 'mkv-btn-secondary'; ?>" 
                       style="padding:3px 10px; font-size:12px; height:28px; line-height:22px;">
                       <?php echo $cp; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>

    <?php endif; /* END TAB END OF DAY */ ?>

</div>

<script>
document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Báo cáo')); ?>');
</script>

<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>