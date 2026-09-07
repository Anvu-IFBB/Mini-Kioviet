<?php if (!defined('ABSPATH')) exit; ?>
<?php require_once MKV_DIR . 'includes/views/header-kiotviet.php'; ?>
<div style="padding: 24px;">
<div class="mkv-page-header">
    <h1 class="mkv-page-title"><i class="hgi-stroke hgi-analytics-01"></i> <?php echo esc_html(mkv__('Báo Cáo & Phân Tích Doanh Thu')); ?></h1>
</div>
    <div class="mkv-tabs">
        <a href="?page=mkv-reports&tab=sales"      class="mkv-tab-link <?php echo $active_tab==='sales'?'active':'';?>"><i class="hgi-stroke hgi-analytics-01"></i> <?php echo esc_html(mkv__('Bán hàng')); ?></a>
        <?php if (current_user_can('mkv_view_cost_price')): ?>
        <a href="?page=mkv-reports&tab=profit"     class="mkv-tab-link <?php echo $active_tab==='profit'?'active':'';?>"><i class="hgi-stroke hgi-analytics-up"></i> <?php echo esc_html(mkv__('Lợi nhuận')); ?></a>
        <?php endif; ?>
        <a href="?page=mkv-reports&tab=end_of_day" class="mkv-tab-link <?php echo $active_tab==='end_of_day'?'active':'';?>"><i class="hgi-stroke hgi-time-02"></i> <?php echo esc_html(mkv__('Cuối ngày')); ?></a>
    </div>
    <!-- Quick filter + Date picker -->
    <div class="mkv-filter-bar" style="margin-bottom:16px; display:block;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <!-- Quick buttons -->
            <div class="mkv-filter-pills" style="margin-bottom:0; padding-bottom:0;">
                <a href="?page=mkv-reports&quick=today"     class="mkv-filter-pill <?php echo ($quick === 'today') ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Hôm nay')); ?></a>
                <a href="?page=mkv-reports&quick=yesterday" class="mkv-filter-pill <?php echo ($quick === 'yesterday') ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Hôm qua')); ?></a>
                <a href="?page=mkv-reports&quick=week"      class="mkv-filter-pill <?php echo ($quick === 'week') ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Tuần này')); ?></a>
                <a href="?page=mkv-reports&quick=month"     class="mkv-filter-pill <?php echo ($quick === 'month' || (empty($quick) && !isset($_GET['start_date']))) ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Tháng này')); ?></a>
                <a href="?page=mkv-reports&quick=year"      class="mkv-filter-pill <?php echo ($quick === 'year') ? 'active' : ''; ?>"><?php echo esc_html(mkv__('Năm nay')); ?></a>
            </div>

            <!-- Custom date range -->
            <form method="get" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <input type="hidden" name="page" value="mkv-reports">
                <input type="date" name="start_date" class="mkv-input" value="<?php echo esc_attr($start_date); ?>" style="height:34px; font-size:13px; width:140px;">
                <span style="font-size:13px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('đến')); ?></span>
                <input type="date" name="end_date" class="mkv-input" value="<?php echo esc_attr($end_date); ?>" style="height:34px; font-size:13px; width:140px;">
                <button type="submit" class="mkv-btn mkv-btn-primary" style="height:34px; padding:0 14px; font-size:13px;"><i class="hgi-stroke hgi-filter"></i> <?php echo esc_html(mkv__('Lọc')); ?></button>
            </form>
        </div>
    </div>

    <!-- 8 KPI Stat Cards -->
    <div class="mkv-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="mkv-stat-box mkv-border-blue">
            <h3><?php echo esc_html(mkv__('Doanh thu bán hàng')); ?></h3>
            <p class="mkv-text-blue"><?php echo number_format($total_revenue, 0, ',', '.'); ?> ₫</p>
            <i class="hgi-stroke hgi-money-bag-02 icon-bg"></i>
        </div>

        <div class="mkv-stat-box mkv-border-green">
            <h3><?php echo esc_html(mkv__('Thực thu (Đã nhận tiền)')); ?></h3>
            <p class="mkv-text-green"><?php echo number_format($total_collected, 0, ',', '.'); ?> ₫</p>
            <i class="hgi-stroke hgi-money-receive-01 icon-bg"></i>
        </div>

        <div class="mkv-stat-box mkv-border-yellow">
            <h3><?php echo esc_html(mkv__('Chưa thu (Khách nợ + COD)')); ?></h3>
            <p class="mkv-text-yellow"><?php echo number_format($total_debt_pending, 0, ',', '.'); ?> ₫</p>
            <i class="hgi-stroke hgi-time-02 icon-bg"></i>
        </div>

        <?php if (current_user_can('mkv_view_cost_price')): ?>
        <div class="mkv-stat-box mkv-border-green">
            <h3><?php echo esc_html(mkv__('Lợi nhuận gộp')); ?></h3>
            <p class="mkv-text-green"><?php echo number_format($gross_profit, 0, ',', '.'); ?> ₫</p>
            <i class="hgi-stroke hgi-analytics-up icon-bg"></i>
        </div>
        <?php endif; ?>

        <div class="mkv-stat-box mkv-border-purple">
            <h3><?php echo esc_html(mkv__('Số đơn hoàn thành')); ?></h3>
            <p class="mkv-text-purple"><?php echo $total_orders; ?></p>
            <i class="hgi-stroke hgi-invoice-01 icon-bg"></i>
        </div>

        <div class="mkv-stat-box mkv-border-red">
            <h3><?php echo esc_html(mkv__('Khách trả hàng')); ?></h3>
            <p class="mkv-text-red"><?php echo $returned_orders; ?></p>
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

    <div class="mkv-card" style="margin-top:20px;">
        <div class="mkv-card-header">
            <h3 class="mkv-card-title"><i class="hgi-stroke hgi-store-01"></i> <?php echo esc_html(mkv__('Doanh thu theo kênh bán')); ?></h3>
        </div>
        <div class="mkv-card-body" style="padding:0; overflow:auto;">
            <table class="mkv-table">
                <thead>
                    <tr>
                        <th><?php echo esc_html(mkv__('Kênh bán')); ?></th>
                        <th><?php echo esc_html(mkv__('Số đơn')); ?></th>
                        <th><?php echo esc_html(mkv__('Doanh thu')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $channel_names = array(
                        'pos' => mkv__('Tại quầy'),
                        'online' => mkv__('Online'),
                        'social' => mkv__('Facebook / Zalo'),
                        'marketplace' => mkv__('Sàn thương mại điện tử'),
                    );
                    ?>
                    <?php if (empty($channel_breakdown)): ?>
                        <tr><td colspan="3" style="text-align:center; padding:24px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Chưa có dữ liệu trong kỳ này.')); ?></td></tr>
                    <?php else: foreach ($channel_breakdown as $channel_row): ?>
                        <tr>
                            <td><strong><?php echo esc_html($channel_names[$channel_row->sales_channel] ?? $channel_row->sales_channel); ?></strong></td>
                            <td><?php echo (int) $channel_row->order_count; ?></td>
                            <td><strong style="color:var(--mkv-primary);"><?php echo number_format((float) $channel_row->revenue, 0, ',', '.'); ?> ₫</strong></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php /* ===================== TAB BÁN HÀNG & LỢI NHUẬN ===================== */ ?>
    <?php if ($active_tab === 'sales' || $active_tab === 'profit'): ?>

    <!-- Biểu đồ -->
    <?php if (!empty($chart_labels)): ?>
    <div class="mkv-card">
        <div class="mkv-card-header">
            <h3 class="mkv-card-title"><i class="hgi-stroke hgi-analytics-01"></i> <?php echo esc_html(mkv__($active_tab === 'profit' ? 'Diễn biến lợi nhuận theo ngày' : 'Diễn biến doanh thu theo ngày')); ?> <small style="font-weight:400; font-size:12px; color:var(--mkv-text-muted);">(<?php echo esc_html(date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date))); ?>)</small></h3>
        </div>
        <div class="mkv-card-body">
            <div style="position:relative; height:260px;">
                <canvas id="mkvReportChart"></canvas>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var chartLabels  = <?php echo json_encode($chart_labels); ?>;
        var chartRevenue = <?php echo json_encode($active_tab === 'profit' ? $chart_profit : $chart_revenue); ?>;
        var barColor     = '<?php echo $active_tab === 'profit' ? '#10b981' : '#0070f3'; ?>';
        var tooltipLabel = '<?php echo esc_js(mkv__('Doanh thu:')); ?>';
        function initReportChart() {
            var ctx = document.getElementById('mkvReportChart');
            if (!ctx || typeof Chart === 'undefined') return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{ label: tooltipLabel, data: chartRevenue, backgroundColor: barColor, borderRadius: 6, maxBarThickness: 40 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(c) { return ' ' + tooltipLabel + ' ' + parseFloat(c.parsed.y).toLocaleString('vi-VN') + ' ₫'; } } } },
                    scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: function(v) { if(v>=1000000) return (v/1000000).toFixed(1)+'M'; if(v>=1000) return (v/1000).toFixed(0)+'K'; return v; } } }, x: { grid: { display: false } } },
                    layout: { padding: 10 }
                }
            });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initReportChart);
        else initReportChart();
    })();
    </script>
    <?php endif; ?>

    <!-- Grid: Top sản phẩm bán chạy + Danh sách đơn hàng trong kỳ -->
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; align-items:start;">

        <!-- Top sản phẩm & Lợi nhuận -->
        <div class="mkv-card" style="padding:0;">
            <div style="padding:16px 20px; border-bottom:1px solid var(--mkv-border);">
                <h3 style="margin:0; padding:0; border:none;"><i class="hgi-stroke hgi-fire"></i> <?php echo esc_html(mkv__('Top Sản Phẩm Bán Chạy Kỳ Này')); ?></h3>
            </div>
            <div class="mkv-table-wrap" style="border:none; box-shadow:none; border-radius:0; margin-bottom:0; max-height:450px;">
                <table class="mkv-table" style="min-width:500px;">
                <thead>
                    <tr>
                        <th><?php echo esc_html(mkv__('Sản phẩm')); ?></th>
                        <th style="width:70px;"><?php echo esc_html(mkv__('Đã bán')); ?></th>
                        <th style="width:110px;"><?php echo esc_html(mkv__('Doanh thu')); ?></th>
                        <?php if (current_user_can('mkv_view_cost_price')): ?>
                            <th style="width:100px;"><?php echo esc_html(mkv__('Lợi nhuận')); ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($top_products)): ?>
                        <tr><td colspan="4" style="text-align:center; padding:30px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Không có dữ liệu trong kỳ này.')); ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($top_products as $p): 
                            $profit = (float)$p->total_revenue - (float)$p->total_cost;
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($p->name); ?></strong></td>
                            <td><span class="mkv-badge mkv-badge-blue"><?php echo $p->total_sold; ?></span></td>
                            <td><strong style="color:var(--mkv-primary);"><?php echo number_format($p->total_revenue, 0, ',', '.'); ?> ₫</strong></td>
                            <?php if (current_user_can('mkv_view_cost_price')): ?>
                                <td><span style="color:var(--mkv-green); font-weight:600;"><?php echo number_format($profit, 0, ',', '.'); ?> ₫</span></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- Danh sách đơn hàng trong kỳ -->
        <?php if ($active_tab === 'sales'): ?>
        <div class="mkv-card" style="padding:0;">
            <div style="padding:16px 20px; border-bottom:1px solid var(--mkv-border);">
                <h3 style="margin:0; padding:0; border:none;"><i class="hgi-stroke hgi-invoice-01"></i> <?php echo esc_html(mkv__('Đơn Hàng Trong Kỳ')); ?></h3>
            </div>
            <div class="mkv-table-wrap" style="border:none; box-shadow:none; border-radius:0; margin-bottom:0; max-height:450px;">
                <table class="mkv-table" style="min-width:720px;">
                <thead>
                    <tr>
                        <th><?php echo esc_html(mkv__('Mã đơn')); ?></th>
                        <th><?php echo esc_html(mkv__('Khách hàng')); ?></th>
                        <th><?php echo esc_html(mkv__('Trạng thái')); ?></th>
                        <th style="width:110px;"><?php echo esc_html(mkv__('Tổng tiền')); ?></th>
                        <th style="width:110px;"><?php echo esc_html(mkv__('Đã thu')); ?></th>
                        <th style="width:130px;"><?php echo esc_html(mkv__('Còn nợ / COD')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Không có dữ liệu trong khoảng thời gian này.')); ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): 
                            $st_color = ($o->status === 'completed' || $o->status === 'paid') ? 'mkv-badge-green' : (($o->status === 'cancelled') ? 'mkv-badge-red' : (($o->status === 'returned') ? 'mkv-badge-gray' : 'mkv-badge-yellow'));
                            $status_map = array(
                                'draft'     => 'Nháp',
                                'pending'   => 'Chờ duyệt',
                                'paid'      => 'Đã thanh toán',
                                'shipping'  => 'Đang giao',
                                'completed' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy',
                                'returned'  => 'Đã trả hàng',
                            );
                            $paid_val = (float) ($o->paid_amount ?? 0);
                            $debt_val = (float) ($o->customer_debt_amount ?? $o->debt_amount ?? 0);
                            $is_cod   = ($o->payment_status ?? '') === 'cod_pending';
                            $cod_val  = (float) ($o->cod_amount ?? $o->total_amount ?? 0);
                        ?>
                        <tr>
                            <td><strong><a href="?page=mkv-orders&id=<?php echo $o->id; ?>"><?php echo esc_html($o->order_code); ?></a></strong><br><small style="color:var(--mkv-text-muted);"><?php echo esc_html(date('d/m H:i', strtotime($o->created_at))); ?></small></td>
                            <td><?php echo esc_html($o->customer_name ?: mkv__('Khách lẻ')); ?></td>
                            <td><span class="mkv-badge <?php echo $st_color; ?>"><?php echo esc_html(mkv__($status_map[$o->status] ?? $o->status)); ?></span></td>
                            <td><strong style="color:var(--mkv-primary);"><?php echo number_format($o->total_amount, 0, ',', '.'); ?> ₫</strong></td>
                            <td><span style="color:#059669; font-weight:600;"><?php echo number_format($paid_val, 0, ',', '.'); ?> ₫</span></td>
                            <td>
                                <?php if ($is_cod): ?>
                                    <span class="mkv-badge mkv-badge-purple" title="COD chờ đối soát"><?php echo number_format($cod_val, 0, ',', '.'); ?> ₫</span>
                                <?php elseif ($debt_val > 0): ?>
                                    <span class="mkv-badge mkv-badge-red" title="Khách nợ"><?php echo number_format($debt_val, 0, ',', '.'); ?> ₫</span>
                                <?php else: ?>
                                    <span class="mkv-badge mkv-badge-green"><?php echo esc_html(mkv__('Đủ')); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; /* END TAB SALES/PROFIT */ ?>

    <?php /* ===================== TAB CUỐI NGÀY ===================== */ ?>
    <?php if ($active_tab === 'end_of_day'): ?>
    <div style="display:grid; grid-template-columns: 1fr; gap:24px; align-items:start;">
        <div class="mkv-card" style="padding:0;">
            <div style="padding:16px 20px; border-bottom:1px solid var(--mkv-border);">
                <h3 style="margin:0; padding:0; border:none;"><i class="hgi-stroke hgi-time-02"></i> <?php echo esc_html(mkv__('Báo Cáo Chốt Ca (Cuối Ngày)')); ?></h3>
                <p style="margin:5px 0 0 0; font-size:13px; color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Tổng hợp dòng tiền thực tế nhập vào / xuất ra khỏi Sổ Quỹ.')); ?></p>
            </div>
            
            <div class="mkv-table-wrap" style="border:none; box-shadow:none; border-radius:0; margin-bottom:0; padding:0;">
                <table class="mkv-table" style="min-width:640px;">
                    <thead>
                        <tr>
                            <th><?php echo esc_html(mkv__('Phương thức thanh toán')); ?></th>
                            <th style="text-align:right; color:var(--mkv-green);"><?php echo esc_html(mkv__('Tổng Thu')); ?></th>
                            <th style="text-align:right; color:var(--mkv-red);"><?php echo esc_html(mkv__('Tổng Chi')); ?></th>
                            <th style="text-align:right;"><?php echo esc_html(mkv__('Tồn Quỹ Đầu Cuối')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $methods = array(
                            'cash' => mkv__('Tiền mặt'),
                            'transfer' => mkv__('Chuyển khoản'),
                            'card' => mkv__('Thẻ ngân hàng')
                        );
                        $total_all_thu = 0;
                        $total_all_chi = 0;

                        foreach ($methods as $code => $label):
                            $thu = 0; $chi = 0;
                            foreach ($cashbook_summary as $cb) {
                                if ($cb->method === $code) {
                                    if ($cb->type === 'thu') $thu += (float)$cb->total;
                                    if ($cb->type === 'chi') $chi += (float)$cb->total;
                                }
                            }
                            $total_all_thu += $thu;
                            $total_all_chi += $chi;
                            $net = $thu - $chi;
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($label); ?></strong></td>
                            <td style="text-align:right; color:var(--mkv-green);">+<?php echo number_format($thu, 0, ',', '.'); ?> ₫</td>
                            <td style="text-align:right; color:var(--mkv-red);">-<?php echo number_format($chi, 0, ',', '.'); ?> ₫</td>
                            <td style="text-align:right; font-weight:bold;"><?php echo number_format($net, 0, ',', '.'); ?> ₫</td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <!-- Tổng hợp -->
                        <tr style="background:#f8fafc;">
                            <td style="text-align:right;"><strong><?php echo esc_html(mkv__('TỔNG CỘNG:')); ?></strong></td>
                            <td style="text-align:right; color:var(--mkv-green); font-weight:bold;">+<?php echo number_format($total_all_thu, 0, ',', '.'); ?> ₫</td>
                            <td style="text-align:right; color:var(--mkv-red); font-weight:bold;">-<?php echo number_format($total_all_chi, 0, ',', '.'); ?> ₫</td>
                            <td style="text-align:right; font-weight:bold; font-size:16px; color:var(--mkv-primary);"><?php echo number_format($total_all_thu - $total_all_chi, 0, ',', '.'); ?> ₫</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<script>document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Báo cáo')); ?>');</script>
<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>