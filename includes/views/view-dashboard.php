<?php
if (!defined('ABSPATH')) exit;
require_once MKV_DIR . 'includes/views/header-kiotviet.php';
?>

<div class="mkv-page-header">
    <h1 class="mkv-page-title">
        <i class="hgi-stroke hgi-dashboard-square-02"></i> <?php echo esc_html(mkv__('Tổng Quan')); ?>
    </h1>
    <div class="mkv-page-actions">
        <?php if (current_user_can('mkv_manage_orders')): ?>
        <a href="<?php echo admin_url('admin.php?page=mkv-pos'); ?>" class="mkv-btn mkv-btn-primary"><i class="hgi-stroke hgi-shopping-cart-01"></i> <?php echo esc_html(mkv__('Bán hàng POS')); ?></a>
        <a href="<?php echo admin_url('admin.php?page=mkv-pos&channel=online'); ?>" class="mkv-btn mkv-btn-info"><i class="hgi-stroke hgi-truck-delivery"></i> <?php echo esc_html(mkv__('Tạo đơn giao hàng')); ?></a>
        <?php endif; ?>
        <?php if (current_user_can('mkv_manage_purchases')): ?>
        <a href="<?php echo admin_url('admin.php?page=mkv-purchases&tab=create'); ?>" class="mkv-btn mkv-btn-secondary"><i class="hgi-stroke hgi-package-add"></i> <?php echo esc_html(mkv__('Nhập hàng')); ?></a>
        <?php endif; ?>
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
                <div class="mkv-today-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="mkv-dashboard-stat-col" style="border-right: 1px solid #ebecf0;">
                        <div class="mkv-dashboard-stat-label"><?php echo esc_html(mkv__('Tổng sản phẩm')); ?></div>
                        <div id="mkv-global-total-products" class="mkv-dashboard-stat-value mkv-text-blue" style="font-size: 20px;">0</div>
                    </div>
                    <div class="mkv-dashboard-stat-col" style="border-right: 1px solid #ebecf0;">
                        <div class="mkv-dashboard-stat-label"><?php echo esc_html(mkv__('Tổng khách hàng')); ?></div>
                        <div id="mkv-global-total-customers" class="mkv-dashboard-stat-value mkv-text-purple" style="font-size: 20px;">0</div>
                    </div>
                    <div class="mkv-dashboard-stat-col" style="border-right: 1px solid #ebecf0;">
                        <div class="mkv-dashboard-stat-label"><?php echo esc_html(mkv__('Tổng doanh số (Toàn TG)')); ?></div>
                        <div id="mkv-global-total-revenue" class="mkv-dashboard-stat-value mkv-text-green" style="font-size: 20px;">0 ₫</div>
                    </div>
                    <div class="mkv-dashboard-stat-col">
                        <div class="mkv-dashboard-stat-label"><?php echo esc_html(mkv__('Tồn quỹ hiện tại')); ?></div>
                        <div id="mkv-global-total-balance" class="mkv-dashboard-stat-value mkv-text-emerald" style="font-size: 20px;">0 ₫</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kết quả bán hàng & Dòng tiền -->
        <div class="mkv-card">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title" id="mkv-main-stats-title">
                    <i class="hgi-stroke hgi-calendar-01"></i> <?php echo esc_html(mkv__('Kết quả bán hàng hôm nay')); ?>
                </h3>
                <select id="mkv-dashboard-period" aria-label="<?php echo esc_attr(mkv__('Khoảng thời gian')); ?>">
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
                <!-- Hàng 1: Hiệu suất bán hàng -->
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
                        <div class="mkv-dashboard-stat-label" title="<?php echo esc_attr(mkv__('Tổng giá trị hàng đã xuất bán (đã trừ trả hàng)')); ?>">
                            <?php echo esc_html(mkv__('Doanh thu bán hàng')); ?> <i class="hgi-stroke hgi-information-circle" style="font-size:12px; vertical-align:middle;"></i>
                        </div>
                        <div id="mkv-today-net-revenue" class="mkv-dashboard-stat-value mkv-text-green">0 ₫</div>
                        <div id="mkv-revenue-change" class="mkv-dashboard-stat-change">—</div>
                    </div>
                </div>

                <!-- Hàng 2: Dòng tiền thực tế & Công nợ (Chuẩn KiotViet) -->
                <div class="mkv-today-stats-grid mkv-today-stats-row-divider">
                    <div class="mkv-dashboard-stat-col">
                        <div class="mkv-dashboard-stat-label">
                            <i class="hgi-stroke hgi-money-receive-01" style="color:#059669;"></i> <?php echo esc_html(mkv__('Thực thu (Đã nhận tiền)')); ?>
                        </div>
                        <div id="mkv-today-actual-collected" class="mkv-dashboard-stat-value mkv-text-emerald">0 ₫</div>
                        <div id="mkv-collected-change" class="mkv-dashboard-stat-change">—</div>
                        <span class="mkv-stat-sublabel"><?php echo esc_html(mkv__('Tiền thực tế đã vào Quỹ')); ?></span>
                    </div>
                    <div class="mkv-dashboard-stat-col">
                        <div class="mkv-dashboard-stat-label">
                            <i class="hgi-stroke hgi-time-02" style="color:#d97706;"></i> <?php echo esc_html(mkv__('Chưa thu (Khách nợ + COD)')); ?>
                        </div>
                        <div id="mkv-today-pending-debt" class="mkv-dashboard-stat-value mkv-text-amber">0 ₫</div>
                        <div class="mkv-dashboard-stat-change" style="visibility:hidden;">—</div>
                        <span class="mkv-stat-sublabel"><?php echo esc_html(mkv__('Nợ đơn mới & COD shipper')); ?></span>
                    </div>
                    <div class="mkv-dashboard-stat-col">
                        <div class="mkv-dashboard-stat-label">
                            <i class="hgi-stroke hgi-truck-delivery" style="color:#6366f1;"></i> <?php echo esc_html(mkv__('Đang giao hàng (Online)')); ?>
                        </div>
                        <div id="mkv-today-shipping-info" class="mkv-dashboard-stat-value mkv-text-indigo" style="font-size:22px; padding-top:4px;">0 đơn</div>
                        <div class="mkv-dashboard-stat-change" id="mkv-today-shipping-total-sub">—</div>
                        <span class="mkv-stat-sublabel"><?php echo esc_html(mkv__('Đơn gửi qua bưu điện/hãng')); ?></span>
                    </div>
                    <div class="mkv-dashboard-stat-col">
                        <div class="mkv-dashboard-stat-label">
                            <i class="hgi-stroke hgi-user-account" style="color:#64748b;"></i> <?php echo esc_html(mkv__('Tổng nợ khách hàng')); ?>
                        </div>
                        <div id="mkv-global-total-customer-debt" class="mkv-dashboard-stat-value" style="color:#475569; font-size:22px; padding-top:4px;">0 ₫</div>
                        <div class="mkv-dashboard-stat-change" style="visibility:hidden;">—</div>
                        <span class="mkv-stat-sublabel"><?php echo esc_html(mkv__('Toàn bộ công nợ cần thu')); ?></span>
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
                <div class="mkv-timeline-scroll" tabindex="0" role="region" aria-label="<?php echo esc_attr(mkv__('Hoạt động gần đây')); ?>">
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
                <div class="mkv-card-scroll" tabindex="0" role="region" aria-label="<?php echo esc_attr(mkv__('Hàng sắp hết')); ?>">
                    <ul id="mkv-low-stock" class="mkv-widget-list">
                        <li class="mkv-loading" style="padding:14px 20px;"><?php echo esc_html(mkv__('Đang tải...')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Tổng quan')); ?>');</script>
<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>
