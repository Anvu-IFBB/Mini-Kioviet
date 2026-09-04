<?php
if (!defined('ABSPATH')) exit;
// Front-end UI template for shortcode [mkv_tracking]
?>
<style>
/* Premium Modern Tracking Design */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.mkv-tracking-wrapper {
    font-family: 'Inter', sans-serif;
    max-width: 700px;
    margin: 0 auto;
    color: #1f2937;
}

.mkv-tracking-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    padding: 32px;
    transition: all 0.4s ease;
}

.mkv-tracking-title {
    text-align: center;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #111827;
}

.mkv-tracking-subtitle {
    text-align: center;
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 32px;
}

.mkv-tracking-form-group {
    margin-bottom: 20px;
}

.mkv-tracking-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #374151;
}

.mkv-tracking-input {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    font-size: 15px;
    background: #f9fafb;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.mkv-tracking-input:focus {
    outline: none;
    border-color: #3b82f6;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.mkv-tracking-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
}

.mkv-tracking-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
}

.mkv-tracking-btn:active {
    transform: translateY(0);
}

/* Loader */
.mkv-tracking-loader {
    display: none;
    text-align: center;
    margin-top: 20px;
}
.mkv-spinner {
    display: inline-block;
    width: 30px;
    height: 30px;
    border: 3px solid rgba(59, 130, 246, 0.2);
    border-radius: 50%;
    border-top-color: #3b82f6;
    animation: mkv-spin 1s ease-in-out infinite;
}
@keyframes mkv-spin { to { transform: rotate(360deg); } }

/* Results Area */
.mkv-tracking-result {
    display: none;
    margin-top: 32px;
    animation: mkv-fade-in 0.5s ease;
}
@keyframes mkv-fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.mkv-tracking-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 20px;
    border-bottom: 1px dashed #e5e7eb;
    margin-bottom: 24px;
}

.mkv-tracking-order-id {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}

.mkv-tracking-date {
    font-size: 13px;
    color: #6b7280;
}

.mkv-status-badge {
    padding: 6px 12px;
    border-radius: 99px;
    font-size: 12px;
    font-weight: 600;
}
.mkv-status-pending { background: #fef3c7; color: #d97706; }
.mkv-status-shipping { background: #dbeafe; color: #2563eb; }
.mkv-status-completed { background: #d1fae5; color: #059669; }
.mkv-status-cancelled { background: #fee2e2; color: #dc2626; }
.mkv-status-returned { background: #f3f4f6; color: #4b5563; }

/* Timeline */
.mkv-timeline {
    position: relative;
    padding: 0;
    list-style: none;
    margin-bottom: 32px;
}

.mkv-timeline::before {
    content: '';
    position: absolute;
    top: 15px;
    left: 0;
    width: 100%;
    height: 3px;
    background: #e5e7eb;
    z-index: 1;
}

.mkv-timeline-progress {
    position: absolute;
    top: 15px;
    left: 0;
    height: 3px;
    background: #3b82f6;
    z-index: 2;
    transition: width 0.6s ease;
}

.mkv-timeline-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 3;
}

.mkv-timeline-step {
    text-align: center;
    width: 25%;
}

.mkv-timeline-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #e5e7eb;
    margin: 0 auto 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    transition: all 0.3s ease;
}

.mkv-timeline-step.active .mkv-timeline-icon {
    border-color: #3b82f6;
    background: #3b82f6;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}

.mkv-timeline-step.done .mkv-timeline-icon {
    border-color: #3b82f6;
    background: #3b82f6;
    color: #fff;
}

.mkv-timeline-label {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
}

.mkv-timeline-step.active .mkv-timeline-label,
.mkv-timeline-step.done .mkv-timeline-label {
    color: #111827;
}

/* Shipping Info */
.mkv-shipping-info {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.mkv-shipping-icon {
    width: 48px;
    height: 48px;
    background: #e0e7ff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4f46e5;
}
.mkv-shipping-details h4 {
    margin: 0 0 4px;
    font-size: 14px;
    color: #6b7280;
}
.mkv-shipping-details p {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}

/* Item List */
.mkv-tracking-items {
    margin-bottom: 24px;
}
.mkv-item-row {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f3f4f6;
}
.mkv-item-row:last-child {
    border-bottom: none;
}
.mkv-item-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 16px;
    border: 1px solid #e5e7eb;
}
.mkv-item-info {
    flex-grow: 1;
}
.mkv-item-name {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 4px;
}
.mkv-item-meta {
    font-size: 13px;
    color: #6b7280;
}
.mkv-item-price {
    font-weight: 600;
    color: #111827;
}

/* Summary */
.mkv-tracking-summary {
    background: #f9fafb;
    border-radius: 12px;
    padding: 20px;
}
.mkv-summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
    color: #4b5563;
}
.mkv-summary-total {
    display: flex;
    justify-content: space-between;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px dashed #d1d5db;
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}
</style>

<div class="mkv-tracking-wrapper">
    <div class="mkv-tracking-card">
        <h2 class="mkv-tracking-title">Tra cứu đơn hàng</h2>
        <p class="mkv-tracking-subtitle">Theo dõi trạng thái đơn hàng của bạn theo thời gian thực</p>
        
        <form id="mkv-tracking-form">
            <div class="mkv-tracking-form-group">
                <label class="mkv-tracking-label">Mã đơn hàng</label>
                <input type="text" id="mkv_order_code" class="mkv-tracking-input" placeholder="VD: DH123456" required>
            </div>
            <div class="mkv-tracking-form-group">
                <label class="mkv-tracking-label">Số điện thoại đặt hàng</label>
                <input type="tel" id="mkv_order_phone" class="mkv-tracking-input" placeholder="Nhập số điện thoại của bạn" required>
            </div>
            <button type="submit" class="mkv-tracking-btn">Tra cứu ngay</button>
        </form>

        <div class="mkv-tracking-loader" id="mkv-tracking-loader">
            <div class="mkv-spinner"></div>
            <p style="margin-top:12px; font-size:14px; color:#6b7280;">Đang tìm kiếm thông tin...</p>
        </div>

        <div id="mkv-tracking-error" style="display:none; margin-top:20px; padding:16px; background:#fee2e2; color:#dc2626; border-radius:12px; font-size:14px; text-align:center;"></div>

        <div class="mkv-tracking-result" id="mkv-tracking-result">
            <div class="mkv-tracking-header">
                <div>
                    <div class="mkv-tracking-order-id" id="res-order-code">DH123</div>
                    <div class="mkv-tracking-date" id="res-order-date">01/01/2026</div>
                </div>
                <div id="res-order-status" class="mkv-status-badge mkv-status-pending">Chờ xử lý</div>
            </div>

            <!-- Timeline -->
            <div class="mkv-timeline" id="res-timeline">
                <div class="mkv-timeline-progress" id="res-timeline-progress" style="width: 0%;"></div>
                <div class="mkv-timeline-steps">
                    <div class="mkv-timeline-step" id="step-1">
                        <div class="mkv-timeline-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
                        <div class="mkv-timeline-label">Chờ duyệt</div>
                    </div>
                    <div class="mkv-timeline-step" id="step-2">
                        <div class="mkv-timeline-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
                        <div class="mkv-timeline-label">Đóng gói</div>
                    </div>
                    <div class="mkv-timeline-step" id="step-3">
                        <div class="mkv-timeline-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
                        <div class="mkv-timeline-label">Đang giao</div>
                    </div>
                    <div class="mkv-timeline-step" id="step-4">
                        <div class="mkv-timeline-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
                        <div class="mkv-timeline-label">Thành công</div>
                    </div>
                </div>
            </div>

            <div class="mkv-shipping-info" id="res-shipping-wrap" style="display:none;">
                <div class="mkv-shipping-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                </div>
                <div class="mkv-shipping-details">
                    <h4>Mã vận đơn (<span id="res-provider"></span>)</h4>
                    <p id="res-tracking-code"></p>
                </div>
            </div>

            <h3 style="font-size:16px; margin: 0 0 16px;">Sản phẩm đã mua</h3>
            <div class="mkv-tracking-items" id="res-items">
                <!-- Injected via JS -->
            </div>

            <div class="mkv-tracking-summary">
                <div class="mkv-summary-row">
                    <span>Tạm tính</span>
                    <span id="res-subtotal"></span>
                </div>
                <div class="mkv-summary-row">
                    <span>Phí vận chuyển</span>
                    <span id="res-shipping-fee"></span>
                </div>
                <div class="mkv-summary-row" style="color: #059669;">
                    <span>Giảm giá</span>
                    <span id="res-discount">-0</span>
                </div>
                <div class="mkv-summary-total">
                    <span>Tổng thanh toán</span>
                    <span id="res-total"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('mkv-tracking-form');
    const loader = document.getElementById('mkv-tracking-loader');
    const errorDiv = document.getElementById('mkv-tracking-error');
    const resultDiv = document.getElementById('mkv-tracking-result');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        loader.style.display = 'block';
        errorDiv.style.display = 'none';
        resultDiv.style.display = 'none';
        form.style.opacity = '0.5';

        const data = new FormData();
        data.append('action', 'mkv_track_order');
        data.append('nonce', '<?php echo wp_create_nonce("mkv_tracking_nonce"); ?>');
        data.append('order_code', document.getElementById('mkv_order_code').value.trim());
        data.append('phone', document.getElementById('mkv_order_phone').value.trim());

        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(res => {
            loader.style.display = 'none';
            form.style.opacity = '1';

            if(res.success) {
                renderResult(res.data);
            } else {
                errorDiv.innerHTML = res.data.message || 'Có lỗi xảy ra, vui lòng thử lại.';
                errorDiv.style.display = 'block';
            }
        })
        .catch(err => {
            loader.style.display = 'none';
            form.style.opacity = '1';
            errorDiv.innerHTML = 'Lỗi kết nối mạng, vui lòng thử lại.';
            errorDiv.style.display = 'block';
        });
    });

    function formatMoney(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    function renderResult(data) {
        document.getElementById('res-order-code').innerText = data.order_code;
        document.getElementById('res-order-date').innerText = 'Đặt lúc: ' + data.created_at;

        // Status Badge
        const statusEl = document.getElementById('res-order-status');
        let statusText = 'Chờ xử lý';
        let statusClass = 'mkv-status-pending';
        let progress = 0;
        let activeStep = 1;

        switch(data.status) {
            case 'pending': 
            case 'draft': 
                statusText = 'Chờ xử lý'; progress = 0; activeStep = 1; break;
            case 'paid': 
                statusText = 'Đã thanh toán (Chờ giao)'; progress = 33; activeStep = 2; break;
            case 'shipping': 
                statusText = 'Đang giao hàng'; statusClass = 'mkv-status-shipping'; progress = 66; activeStep = 3; break;
            case 'completed': 
                statusText = 'Hoàn thành'; statusClass = 'mkv-status-completed'; progress = 100; activeStep = 4; break;
            case 'cancelled': 
                statusText = 'Đã Hủy'; statusClass = 'mkv-status-cancelled'; progress = 0; activeStep = 0; break;
            case 'returned': 
                statusText = 'Hoàn / Trả hàng'; statusClass = 'mkv-status-returned'; progress = 0; activeStep = 0; break;
        }

        statusEl.innerText = statusText;
        statusEl.className = 'mkv-status-badge ' + statusClass;

        // Timeline
        const tlSteps = document.querySelectorAll('.mkv-timeline-step');
        tlSteps.forEach(el => { el.classList.remove('active', 'done'); });
        
        if (activeStep > 0) {
            for(let i = 1; i < activeStep; i++) {
                document.getElementById('step-'+i).classList.add('done');
            }
            document.getElementById('step-'+activeStep).classList.add('active');
            document.getElementById('res-timeline-progress').style.width = progress + '%';
            document.getElementById('res-timeline').style.display = 'block';
        } else {
            document.getElementById('res-timeline').style.display = 'none'; // hide timeline if cancelled/returned
        }

        // Shipping Info
        if (data.tracking_code || data.customer_address) {
            document.getElementById('res-shipping-wrap').style.display = 'flex';
            const providerName = data.shipping_provider ? data.shipping_provider.toUpperCase() : 'Giao hàng';
            document.getElementById('res-provider').innerText = providerName;
            
            let shipText = '';
            if (data.tracking_code) {
                shipText += `<span style="color:#2563eb; font-weight:700;">${data.tracking_code}</span>`;
            } else {
                shipText += `<span style="color:#64748b; font-size:13px; font-weight:500;">(Đang chuẩn bị hàng, chưa có mã vận đơn)</span>`;
            }
            if (data.customer_address) {
                shipText += `<div style="font-size:13px; font-weight:normal; color:#475569; margin-top:4px;"><i style="font-style:normal;">📍 Địa chỉ:</i> ${data.customer_address}</div>`;
            }
            document.getElementById('res-tracking-code').innerHTML = shipText;
        } else {
            document.getElementById('res-shipping-wrap').style.display = 'none';
        }

        // Items
        const itemsWrap = document.getElementById('res-items');
        itemsWrap.innerHTML = '';
        let subTotalRaw = 0;
        data.items.forEach(item => {
            subTotalRaw += item.subtotal;
            itemsWrap.innerHTML += `
                <div class="mkv-item-row">
                    <img src="${item.image}" class="mkv-item-image" alt="">
                    <div class="mkv-item-info">
                        <h4 class="mkv-item-name">${item.name}</h4>
                        <div class="mkv-item-meta">SL: ${item.qty} x ${formatMoney(item.price)}</div>
                    </div>
                    <div class="mkv-item-price">${formatMoney(item.subtotal)}</div>
                </div>
            `;
        });

        // Summary
        document.getElementById('res-subtotal').innerText = formatMoney(subTotalRaw);
        document.getElementById('res-shipping-fee').innerText = formatMoney(data.shipping_fee);
        
        const discRow = document.getElementById('res-discount').parentElement;
        if (data.discount_amount > 0) {
            discRow.style.display = 'flex';
            document.getElementById('res-discount').innerText = '-' + formatMoney(data.discount_amount);
        } else {
            discRow.style.display = 'none';
        }

        document.getElementById('res-total').innerText = formatMoney(data.total_amount);

        resultDiv.style.display = 'block';
    }
});
</script>
