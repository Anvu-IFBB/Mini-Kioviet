jQuery(document).ready(function ($) {
    let revenueChartInstance = null;
    let topProductsChartInstance = null;
    let topCustomersChartInstance = null;

    function loadDashboardData(period = 'today') {
        const apiURL = mkv_api.root + 'stats?period=' + period;

        $.ajax({
            url: apiURL,
            method: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', mkv_api.nonce);
            },
            success: function (data) {
                function fmtMoney(v) {
                    return parseFloat(v || 0).toLocaleString('vi-VN') + ' ₫';
                }

                // --- Stat Cards ---
                $('#mkv-today-orders-count').text(data.today_orders || 0);
                $('#mkv-today-returns-count').text(data.today_returns || 0);
                $('#mkv-today-customers-count').text(data.today_customers || 0);
                $('#mkv-today-net-revenue').text(fmtMoney(data.today_revenue));

                // Global Stats
                $('#mkv-global-total-products').text(data.total_products || 0);
                $('#mkv-global-total-customers').text(data.total_customers || 0);
                $('#mkv-global-total-revenue').text(fmtMoney(data.total_revenue));

                // Change indicator
                function getChangeHtml(percent, curPeriod) {
                    var compareTexts = {
                        'today': 'So với hôm qua',
                        'yesterday': 'So với hôm kia',
                        '7days': 'So với 7 ngày trước',
                        'week': 'So với tuần trước',
                        'month': 'So với tháng trước',
                        'last_month': 'So với tháng trước nữa',
                        '30days': 'So với 30 ngày trước',
                        'year': 'So với năm trước',
                        'all': 'Toàn bộ thời gian'
                    };
                    var compareStr = compareTexts[curPeriod] || 'So với kỳ trước';

                    if (curPeriod === 'all') {
                        return '<span style="color: var(--mkv-text-muted);"><i class="hgi-stroke hgi-tick-02"></i> ' + compareStr + '</span>';
                    }

                    if (percent > 0) {
                        return '<span style="color: var(--mkv-green);"><i class="hgi-stroke hgi-arrow-up-02"></i> ' + percent + '% ' + compareStr + '</span>';
                    } else if (percent < 0) {
                        return '<span style="color: var(--mkv-red);"><i class="hgi-stroke hgi-arrow-down-02"></i> ' + Math.abs(percent) + '% ' + compareStr + '</span>';
                    } else {
                        return '<span style="color: var(--mkv-text-muted);">Bằng kỳ trước</span>';
                    }
                }
                $('#mkv-orders-change').html(getChangeHtml(data.orders_change, period));
                $('#mkv-customers-change').html(getChangeHtml(data.customers_change, period));
                $('#mkv-revenue-change').html(getChangeHtml(data.revenue_change, period));

                // --- 1. Line Chart: Doanh thu ---
                var canvas = document.getElementById('mkvRevenueChart');
                if (canvas) {
                    var ctx = canvas.getContext('2d');
                    if (revenueChartInstance) {
                        revenueChartInstance.destroy();
                    }

                    var gradient = ctx.createLinearGradient(0, 0, 0, 240);
                    gradient.addColorStop(0, 'rgba(0, 112, 243, 0.25)');
                    gradient.addColorStop(1, 'rgba(0, 112, 243, 0.0)');

                    revenueChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.chart_labels || [],
                            datasets: [{
                                label: 'Doanh thu (VNĐ)',
                                data: data.chart_data || [],
                                backgroundColor: gradient,
                                borderColor: '#0070f3',
                                borderWidth: 2.5,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#0070f3',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                fill: true,
                                tension: 0.35
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function (ctx) {
                                            return ' ' + ((typeof mkv_i18n !== 'undefined' && mkv_i18n.revenue_label) || 'Doanh thu:') + ' ' + parseFloat(ctx.parsed.y).toLocaleString('vi-VN') + ' ₫';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: {
                                        callback: function (v) {
                                            if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M';
                                            if (v >= 1000) return (v / 1000).toFixed(0) + 'K';
                                            return v;
                                        }
                                    }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }

                // --- 2. Horizontal Bar Chart: Top Sản phẩm ---
                var prodCanvas = document.getElementById('mkvTopProductsChart');
                if (prodCanvas && data.top_products) {
                    var prodCtx = prodCanvas.getContext('2d');
                    if (topProductsChartInstance) {
                        topProductsChartInstance.destroy();
                    }

                    let pLabels = data.top_products.map(p => p.name);
                    let pData = data.top_products.map(p => p.total_sold);

                    topProductsChartInstance = new Chart(prodCtx, {
                        type: 'bar',
                        data: {
                            labels: pLabels,
                            datasets: [{
                                data: pData,
                                backgroundColor: '#0ea5e9',
                                borderRadius: 4,
                                barThickness: 12
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function (ctx) {
                                            return ' ' + ctx.parsed.x + ' ' + ((typeof mkv_i18n !== 'undefined' && mkv_i18n.product_unit) || 'sản phẩm');
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { display: false },
                                y: {
                                    grid: { display: false },
                                    ticks: {
                                        font: { family: 'Inter', size: 12 },
                                        color: '#475569'
                                    }
                                }
                            }
                        }
                    });
                }

                // --- 3. Horizontal Bar Chart: Top Khách hàng ---
                var custCanvas = document.getElementById('mkvTopCustomersChart');
                if (custCanvas && data.top_customers) {
                    var custCtx = custCanvas.getContext('2d');
                    if (topCustomersChartInstance) {
                        topCustomersChartInstance.destroy();
                    }

                    let cLabels = data.top_customers.map(c => c.name);
                    let cData = data.top_customers.map(c => c.spent);

                    topCustomersChartInstance = new Chart(custCtx, {
                        type: 'bar',
                        data: {
                            labels: cLabels,
                            datasets: [{
                                data: cData,
                                backgroundColor: '#10b981',
                                borderRadius: 4,
                                barThickness: 12
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function (ctx) {
                                            return ' ' + parseFloat(ctx.parsed.x).toLocaleString('vi-VN') + ' ₫';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { display: false },
                                y: {
                                    grid: { display: false },
                                    ticks: {
                                        font: { family: 'Inter', size: 12 },
                                        color: '#475569'
                                    }
                                }
                            }
                        }
                    });
                }

                // --- Recent Activities Timeline ---
                if (data.recent_activities && data.recent_activities.length > 0) {
                    let html = '';
                    data.recent_activities.forEach(function (o) {
                        const fallbackGuest = (typeof mkv_i18n !== 'undefined' && mkv_i18n.guest_customer) ? mkv_i18n.guest_customer : 'Khách lẻ';
                        const customer = o.customer_name ? o.customer_name : fallbackGuest;
                        const amount = parseFloat(o.total_amount || 0).toLocaleString('vi-VN') + ' ₫';
                        
                        let timeStr = '';
                        if (o.time) {
                            const orderDate = new Date(o.time.replace(/-/g, "/"));
                            const now = new Date();
                            const isToday = orderDate.toDateString() === now.toDateString();
                            const timeDigits = orderDate.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
                            timeStr = isToday
                                ? timeDigits
                                : (orderDate.getDate().toString().padStart(2, '0') + '/' + (orderDate.getMonth() + 1).toString().padStart(2, '0') + ' ' + timeDigits);
                        }

                        const activityTemplate = ((typeof mkv_i18n !== 'undefined' && mkv_i18n.activity_template) || 'vừa mua đơn hàng <strong>{order_code}</strong> với giá trị <strong style="color:var(--mkv-primary);">{amount}</strong>');
                        const activityText = activityTemplate
                            .replace('{order_code}', o.order_code)
                            .replace('{amount}', amount);

                        html += `
                        <div class="mkv-timeline-item">
                            <div class="mkv-timeline-time">${timeStr}</div>
                            <div class="mkv-timeline-content">
                                <strong>${customer}</strong> ${activityText}
                            </div>
                        </div>
                        `;
                    });
                    $('#mkv-recent-activities').html(html);
                } else {
                    $('#mkv-recent-activities').html('<div style="color:var(--mkv-text-muted); font-style:italic; padding: 10px 0;">' + ((typeof mkv_i18n !== 'undefined' && mkv_i18n.no_activities) || 'Chưa có hoạt động nào.') + '</div>');
                }

                // --- Low Stock ---
                if (data.low_stock && data.low_stock.length > 0) {
                    let html = '';
                    data.low_stock.forEach(function (item) {
                        const badge = item.stock <= 0
                            ? '<span class="mkv-badge mkv-badge-red">' + ((typeof mkv_i18n !== 'undefined' && mkv_i18n.out_of_stock) || 'Hết hàng') + '</span>'
                            : '<span class="mkv-badge mkv-badge-yellow">' + ((typeof mkv_i18n !== 'undefined' && mkv_i18n.in_stock_prefix) || 'Còn') + ' ' + item.stock + '</span>';
                        html += '<li><span>' + item.name + '</span>' + badge + '</li>';
                    });
                    $('#mkv-low-stock').html(html);
                } else {
                    $('#mkv-low-stock').html('<li style="color:var(--mkv-text-muted); font-style:italic; padding:14px 20px;">' + ((typeof mkv_i18n !== 'undefined' && mkv_i18n.all_in_stock) || 'Tất cả sản phẩm đều đủ tồn kho.') + '</li>');
                }

            },
            error: function (err) {
                console.error('MKV API Error:', err);
                $('#mkv-today-orders-count, #mkv-today-returns-count, #mkv-today-net-revenue').text('Lỗi').css('color', 'var(--mkv-red)');
            }
        });
    }

    // Initial load
    loadDashboardData('today');

    // Filter change handler
    $('#mkv-dashboard-period').on('change', function () {
        const period = $(this).val();
        loadDashboardData(period);

        var i18n = (typeof mkv_i18n !== 'undefined') ? mkv_i18n : {};
        var titleMap = {
            'today': 'hôm nay',
            'yesterday': 'hôm qua',
            '7days': '7 ngày qua',
            'week': 'tuần này',
            'month': 'tháng này',
            'last_month': 'tháng trước',
            '30days': '30 ngày qua',
            'year': 'năm nay',
            'all': 'toàn thời gian'
        };
        var prefix = 'Kết quả bán hàng';
        $('#mkv-main-stats-title').html('<i class="hgi-stroke hgi-calendar-01"></i> ' + prefix + ' ' + (titleMap[period] || ''));
    });
});