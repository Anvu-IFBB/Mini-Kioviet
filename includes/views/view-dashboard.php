<?php
if (!defined('ABSPATH')) exit;
require_once MKV_DIR . 'includes/views/header-kiotviet.php';
?>

<div class="mkv-page-header">
    <h1 class="mkv-page-title">
        <i class="hgi-stroke hgi-dashboard-square-02"></i> <?php echo esc_html(mkv__('Tổng Quan')); ?>
    </h1>
    <div class="mkv-page-actions">
        <a href="<?php echo admin_url('admin.php?page=mkv-pos'); ?>" class="mkv-btn mkv-btn-primary">
            <i class="hgi-stroke hgi-shopping-cart-01"></i> <?php echo esc_html(mkv__('Bán Hàng (POS)')); ?>
        </a>
        <a href="<?php echo admin_url('post-new.php?post_type=mkv_product'); ?>" class="mkv-btn mkv-btn-secondary">
            <i class="hgi-stroke hgi-add-square"></i> <?php echo esc_html(mkv__('Thêm sản phẩm')); ?>
        </a>
    </div>
</div>

<div class="mkv-dashboard-layout">
    <!-- Cột trái (Main) -->
    <div class="mkv-dashboard-main">
        <!-- Thống kê tổng quan -->
        <div class="mkv-card" style="margin-bottom: 20px;">
            <div class="mkv-card-body" style="padding: 15px;">
                <div class="mkv-today-stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="mkv-dashboard-stat-col" style="border-right: 1px solid #ebecf0;">
                        <div class="mkv-dashboard-stat-label"><?php echo esc_html(mkv__('Tổng sản phẩm (Toàn thời gian)')); ?></div>
                        <div id="mkv-global-total-products" class="mkv-dashboard-stat-value mkv-text-blue" style="font-size: 20px;">0</div>
                    </div>
                    <div class="mkv-dashboard-stat-col" style="border-right: 1px solid #ebecf0;">
                        <div class="mkv-dashboard-stat-label"><?php echo esc_html(mkv__('Tổng khách hàng (Toàn thời gian)')); ?></div>
                        <div id="mkv-global-total-customers" class="mkv-dashboard-stat-value mkv-text-purple" style="font-size: 20px;">0</div>
                    </div>
                    <div class="mkv-dashboard-stat-col">
                        <div class="mkv-dashboard-stat-label"><?php echo esc_html(mkv__('Tổng doanh thu (Toàn thời gian)')); ?></div>
                        <div id="mkv-global-total-revenue" class="mkv-dashboard-stat-value mkv-text-green" style="font-size: 20px;">0 ₫</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kết quả bán hàng -->
        <div class="mkv-card">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title" id="mkv-main-stats-title">
                    <i class="hgi-stroke hgi-calendar-01"></i> <?php echo esc_html(mkv__('Kết quả bán hàng hôm nay')); ?>
                </h3>
                <select id="mkv-dashboard-period">
                    <option value="today"><?php echo esc_html(mkv__('Hôm nay')); ?></option>
                    <option value="yesterday"><?php echo esc_html(mkv__('Hôm qua')); ?></option>
                    <option value="7days"><?php echo esc_html(mkv__('7 ngày qua')); ?></option>
                    <option value="week"><?php echo esc_html(mkv__('Tuần này')); ?></option>
                    <option value="month"><?php echo esc_html(mkv__('Tháng này')); ?></option>
                    <option value="last_month"><?php echo esc_html(mkv__('Tháng trước')); ?></option>
                    <option value="30days"><?php echo esc_html(mkv__('30 ngày qua')); ?></option>
                    <option value="year"><?php echo esc_html(mkv__('Năm nay')); ?></option>
                    <option value="all"><?php echo esc_html(mkv__('Toàn thời gian')); ?></option>
                </select>
            </div>
            <div class="mkv-card-body" style="padding:0;">
                <div class="mkv-today-stats-grid">
                    <div class="mkv-dashboard-stat-col">
                        <div class="mkv-dashboard-stat-label"><?php echo esc_html(mkv__('Hóa đơn')); ?></div>
                        <div id="mkv-today-orders-count" class="mkv-dashboard-stat-value mkv-text-blue">0</div>
                        <div id="mkv-orders-change" class="mkv-dashboard-stat-change">—</div>
                    </div>
                    <div class="mkv-dashboard-stat-col">
                        <div class="mkv-dashboard-stat-label"><?php echo esc_html(mkv__('Trả Hàng')); ?></div>
                        <div id="mkv-today-returns-count" class="mkv-dashboard-stat-value mkv-text-yellow">0</div>
                        <div class="mkv-dashboard-stat-change" style="visibility:hidden;">—</div>
                    </div>
                    <div class="mkv-dashboard-stat-col">
                        <div class="mkv-dashboard-stat-label"><?php echo esc_html(mkv__('Khách hàng mới')); ?></div>
                        <div id="mkv-today-customers-count" class="mkv-dashboard-stat-value mkv-text-purple">0</div>
                        <div id="mkv-customers-change" class="mkv-dashboard-stat-change">—</div>
                    </div>
                    <div class="mkv-dashboard-stat-col">
                        <div class="mkv-dashboard-stat-label"><?php echo esc_html(mkv__('Doanh thu thuần')); ?></div>
                        <div id="mkv-today-net-revenue" class="mkv-dashboard-stat-value mkv-text-green">0 ₫</div>
                        <div id="mkv-revenue-change" class="mkv-dashboard-stat-change">—</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ doanh thu -->
        <div class="mkv-card">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title">
                    <i class="hgi-stroke hgi-analytics-01"></i> <?php echo esc_html(mkv__('Doanh thu')); ?>
                </h3>
            </div>
            <div class="mkv-card-body">
                <div style="position:relative; height:300px;">
                    <canvas id="mkvRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Lưới Top 10 -->
        <div class="mkv-grid-2" style="margin-bottom:20px;">
            <div class="mkv-card" style="margin-bottom:0;">
                <div class="mkv-card-header">
                    <h3 class="mkv-card-title">
                        <i class="hgi-stroke hgi-fire"></i> <?php echo esc_html(mkv__('Top 10 hàng hóa bán chạy')); ?>
                    </h3>
                </div>
                <div class="mkv-card-body">
                    <div style="position:relative; height:280px;">
                        <canvas id="mkvTopProductsChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="mkv-card" style="margin-bottom:0;">
                <div class="mkv-card-header">
                    <h3 class="mkv-card-title">
                        <i class="hgi-stroke hgi-crown"></i> <?php echo esc_html(mkv__('Top 10 khách hàng')); ?>
                    </h3>
                </div>
                <div class="mkv-card-body">
                    <div style="position:relative; height:280px;">
                        <canvas id="mkvTopCustomersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cột phải (Sidebar) -->
    <div class="mkv-dashboard-sidebar">
        <!-- Hoạt động gần đây -->
        <div class="mkv-card">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title">
                    <i class="hgi-stroke hgi-clock-01"></i> <?php echo esc_html(mkv__('Hoạt động gần đây')); ?>
                </h3>
            </div>
            <div class="mkv-card-body">
                <div class="mkv-timeline-scroll">
                    <div class="mkv-timeline" id="mkv-recent-activities">
                        <div class="mkv-loading"><?php echo esc_html(mkv__('Đang tải...')); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cảnh báo hết hàng -->
        <div class="mkv-card mkv-card-warning-accent">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title mkv-card-warning-title">
                    <i class="hgi-stroke hgi-alert-02"></i> <?php echo esc_html(mkv__('Hàng sắp hết')); ?>
                </h3>
            </div>
            <div class="mkv-card-body" style="padding:0;">
                <ul id="mkv-low-stock" class="mkv-widget-list">
                    <li class="mkv-loading" style="padding:14px 20px;"><?php echo esc_html(mkv__('Đang tải...')); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Tổng quan')); ?>');</script>
<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>
