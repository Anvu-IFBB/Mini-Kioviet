let cart = {};
// vatRate and currentCustomerPoints will be initialized in the view via a small inline script
// since they depend on PHP variables and DOM state
// We'll define them globally here and let the view set them if needed.

function escapeHtml(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

jQuery(document).ready(function($) {
    if ($.fn.select2) {
        $('#pos-customer-select').select2({
            placeholder: "Tìm tên hoặc SĐT...",
            width: '100%',
            language: { noResults: () => "Không tìm thấy" }
        }).on('change', function() {
            // Trigger native handler
            if (typeof onCustomerChange === 'function') {
                onCustomerChange(this);
            }
        });
        
        // Add styling override for Select2 to match mkv-select
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .select2-container--default .select2-selection--single {
                    height: 42px;
                    border: 1px solid #cbd5e1;
                    border-radius: 8px;
                    display: flex;
                    align-items: center;
                    padding-left: 6px;
                }
                .select2-container--default .select2-selection--single .select2-selection__arrow {
                    height: 40px;
                    right: 8px;
                }
                .select2-container--default .select2-selection--single .select2-selection__rendered {
                    color: #1e293b;
                    font-size: 14px;
                    font-weight: 500;
                }
            `)
            .appendTo('head');
    }

    const customerSelect = document.getElementById('pos-customer-select');
    if (customerSelect) onCustomerChange(customerSelect);
    ensurePosOrderCode();
});

function ensurePosOrderCode() {
    const codeInput = document.getElementById('pos_order_code');
    if (codeInput && !codeInput.value) {
        codeInput.value = 'DH' + Math.floor(Date.now() / 1000) + Math.floor(10 + Math.random() * 90);
    }
}

function resetPosOrderCode() {
    const codeInput = document.getElementById('pos_order_code');
    if (codeInput) {
        codeInput.value = 'DH' + Math.floor(Date.now() / 1000) + Math.floor(10 + Math.random() * 90);
    }
}

function addToCart(el) {
    const id    = el.dataset.id;
    const name  = el.dataset.name;
    const price = parseFloat(el.dataset.price) || 0;
    const stock = parseInt(el.dataset.stock) || 0;

    if (cart[id]) {
        if (window.mkv_allow_negative_stock !== 1 && cart[id].qty >= stock) {
            mkvToast(mkv_pos_i18n.out_of_stock, 'warning');
            return;
        }
        cart[id].qty++;
    } else {
        if (window.mkv_allow_negative_stock !== 1 && stock <= 0) {
            mkvToast(mkv_pos_i18n.out_of_stock, 'warning');
            return;
        }
        cart[id] = { name, price, qty: 1, stock };
    }
    renderCart();
}

function onCustomerChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    window.currentCustomerPoints = parseInt(opt.dataset.points || 0);
    const addressInput = document.getElementById('pos-customer-address');
    const phoneInput = document.getElementById('pos-shipping-phone');
    if (addressInput && opt.value !== '0' && opt.dataset.address) {
        addressInput.value = opt.dataset.address;
        addressInput.dataset.customerAddress = opt.dataset.address;
    } else if (addressInput && opt.value === '0') {
        addressInput.value = '';
        delete addressInput.dataset.customerAddress;
    }
    if (phoneInput && opt.value !== '0' && opt.dataset.phone && !phoneInput.dataset.edited) {
        phoneInput.value = opt.dataset.phone;
    } else if (phoneInput && opt.value === '0' && !phoneInput.dataset.edited) {
        phoneInput.value = '';
    }
    const box = document.getElementById('pos-points-box');
    const custPointsEl = document.getElementById('pos-cust-points');
    const maxPointsEl = document.getElementById('pos-max-points-val');

    if (window.currentCustomerPoints > 0) {
        box.style.display = 'block';
        custPointsEl.textContent = window.currentCustomerPoints.toLocaleString('vi-VN');
        const pointValue = window.mkv_point_value || 1;
        maxPointsEl.textContent = (window.currentCustomerPoints * pointValue).toLocaleString('vi-VN');
    } else {
        box.style.display = 'none';
        document.getElementById('pos-points-used').value = 0;
    }
    renderCart();
}

function useAllPoints() {
    const subtotal = parseInt(document.getElementById('cart-subtotal').textContent.replace(/\D/g, '')) || 0;
    const pointValue = window.mkv_point_value || 1;
    const maxPointsForSubtotal = Math.ceil(subtotal / pointValue);
    
    document.getElementById('pos-points-used').value = Math.min(window.currentCustomerPoints, maxPointsForSubtotal);
    renderCart();
}

function renderCart() {
    const itemsEl    = document.getElementById('cart-items');
    const emptyEl    = document.getElementById('cart-empty');
    const subtotalEl = document.getElementById('cart-subtotal');
    const totalEl    = document.getElementById('cart-total');
    const submitBtn  = document.getElementById('pos-submit-btn');
    const draftBtn   = document.getElementById('pos-draft-btn');
    const printBtn   = document.getElementById('pos-print-btn');
    const discRow    = document.getElementById('cart-discount-row');
    const discEl     = document.getElementById('cart-discount');
    const vatEl      = document.getElementById('cart-vat');

    let html = '', subtotal = 0;

    for (const id in cart) {
        const item = cart[id];
        subtotal += item.price * item.qty;
        html += `<div class="cart-item">
            <div style="flex:1; padding-right:12px;">
                <div style="font-weight:600; line-height:1.3; margin-bottom:4px; color:#1e293b; font-size:14px;">${escapeHtml(item.name)}</div>
                <div style="color:#64748b; font-size:12px;">${item.price.toLocaleString('vi-VN')} ₫</div>
                <input type="hidden" name="products[${id}][id]" value="${id}">
                <input type="hidden" name="products[${id}][price]" value="${item.price}">
            </div>
            <div style="display:flex; align-items:center; gap:2px; background:#f1f5f9; border-radius:8px; padding:4px;">
                <button type="button" class="cart-qty-btn" aria-label="Giảm số lượng" onclick="changeQty('${id}', -1)">−</button>
                <input type="number" class="pos-qty-input" aria-label="Số lượng" name="products[${id}][qty]" value="${item.qty}" min="1" max="${item.stock}"
                    onchange="updateQty('${id}', this.value)">
                <button type="button" class="cart-qty-btn" aria-label="Tăng số lượng" onclick="changeQty('${id}', 1)">+</button>
            </div>
            <div style="width:40px; text-align:right;">
                <button type="button" style="color:#ef4444; width:36px; height:36px; background:#fee2e2; border:none; border-radius:8px; margin-left:auto; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:0.2s;" aria-label="Xóa sản phẩm" title="Xóa sản phẩm" onclick="removeItem('${id}')" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                    <i class="hgi-stroke hgi-delete-02"></i>
                </button>
            </div>
        </div>`;
    }

    const hasItems = Object.keys(cart).length > 0;
    emptyEl.style.display  = hasItems ? 'none' : 'block';
    itemsEl.innerHTML      = html;
    subtotalEl.textContent = subtotal.toLocaleString('vi-VN') + ' ₫';

    // Tính điểm trừ
    const pointsInput = parseInt(document.getElementById('pos-points-used').value || 0);
    const pointValue  = window.mkv_point_value || 1;
    const vatRate = window.mkv_pos_vat_rate || 0;
    const isVatIncluded = parseInt(window.mkv_vat_included || 0) === 1;

    let vatAmount = 0;
    let baseTotalBeforeDiscount = subtotal;

    if (isVatIncluded) {
        vatAmount = vatRate > 0 ? Math.round(subtotal - (subtotal / (1 + vatRate))) : 0;
        baseTotalBeforeDiscount = subtotal;
        if (vatEl) vatEl.textContent = `(Đã gồm: ${vatAmount.toLocaleString('vi-VN')} ₫)`;
    } else {
        vatAmount = Math.round(subtotal * vatRate);
        baseTotalBeforeDiscount = subtotal + vatAmount;
        if (vatEl) vatEl.textContent = vatAmount.toLocaleString('vi-VN') + ' ₫';
    }

    // Số điểm dùng không được vượt quá số điểm khách có, 
    // và (số điểm * giá trị) không được vượt quá tổng tiền hàng + thuế (nếu thuế chưa gồm).
    const maxPointsForSubtotal = Math.ceil(baseTotalBeforeDiscount / pointValue);
    const pointsUsed  = Math.min(pointsInput, window.currentCustomerPoints || 0, maxPointsForSubtotal);
    document.getElementById('pos-points-used').value = pointsUsed;

    const discountAmount = pointsUsed * pointValue;

    if (pointsUsed > 0) {
        discRow.style.display = 'flex';
        discEl.textContent    = '-' + discountAmount.toLocaleString('vi-VN') + ' ₫';
    } else {
        discRow.style.display = 'none';
    }

    const afterDiscount = Math.max(0, baseTotalBeforeDiscount - discountAmount);

    // Shipping fee
    let shippingFee = 0;
    const enableShippingEl = document.getElementById('pos-enable-shipping');
    if (enableShippingEl && enableShippingEl.checked) {
        const feeInput = document.getElementById('pos-shipping-fee');
        shippingFee = parseInt(feeInput.value) || 0;
    }

    const finalTotal = afterDiscount + shippingFee;
    window.mkv_final_total = finalTotal;
    totalEl.textContent = finalTotal.toLocaleString('vi-VN') + ' ₫';
    
    // Auto-fill paid amount if empty or if we want to reset it
    const paidInput = document.getElementById('pos-paid-amount');
    const salesChannel = document.getElementById('pos-sales-channel')?.value || 'pos';
    if (paidInput && (!paidInput.dataset.edited || paidInput.value === '')) {
        const isDeliveryOrder = enableShippingEl && enableShippingEl.checked && salesChannel !== 'pos';
        paidInput.value = (isDeliveryOrder ? 0 : finalTotal).toLocaleString('vi-VN');
    }
    
    // Cập nhật lại số tiền thừa/nợ
    if (typeof calculateDebtChange === 'function') calculateDebtChange();

    const canSubmit = hasItems;
    submitBtn.disabled = !canSubmit;
    if (draftBtn) draftBtn.disabled = !hasItems;
    printBtn.disabled  = !hasItems;
}

function changeQty(id, delta) {
    if (!cart[id]) return;
    const newQty = cart[id].qty + delta;
    if (newQty <= 0) { removeItem(id); return; }
    if (newQty > cart[id].stock) { mkvToast(mkv_pos_i18n.out_of_stock, 'warning'); return; }
    cart[id].qty = newQty;
    renderCart();
}

function updateQty(id, val) {
    const qty = parseInt(val);
    if (!cart[id] || qty < 1) return;
    
    if (window.mkv_allow_negative_stock !== 1) {
        cart[id].qty = Math.min(qty, cart[id].stock);
    } else {
        cart[id].qty = qty;
    }
    renderCart();
}

function removeItem(id) {
    delete cart[id];
    renderCart();
}

function clearCart() {
    cart = {};
    resetPosOrderCode();
    renderCart();
}

function saveDraft() {
    document.getElementById('is_draft_input').value = '1';
    document.getElementById('pos-form').submit();
}

function filterProducts(query) {
    const q     = query.toLowerCase();
    const items = document.querySelectorAll('.pos-product-item');
    items.forEach(item => {
        item.style.display = item.dataset.search.includes(q) ? '' : 'none';
    });
}

// Theo dõi người dùng tự sửa ô khách đưa
document.addEventListener('DOMContentLoaded', () => {
    const paidInput = document.getElementById('pos-paid-amount');
    if (paidInput) {
        paidInput.addEventListener('input', () => {
            paidInput.dataset.edited = '1';
        });
    }
});

function calculateDebtChange() {
    const finalTotal = window.mkv_final_total || 0;
    const paidInput = document.getElementById('pos-paid-amount');
    if (!paidInput) return;
    
    const paidStr = paidInput.value;
    const paidAmount = parseInt(paidStr.replace(/\D/g, '')) || 0;
    
    const diff = paidAmount - finalTotal;
    const label = document.getElementById('debt-change-label');
    const amountEl = document.getElementById('debt-change-amount');
    
    if (diff >= 0) {
        label.textContent = 'Tiền thừa:';
        amountEl.textContent = diff.toLocaleString('vi-VN') + ' ₫';
        amountEl.style.color = '#0f172a';
    } else {
        label.textContent = 'Ghi nợ:';
        amountEl.textContent = Math.abs(diff).toLocaleString('vi-VN') + ' ₫';
        amountEl.style.color = '#ef4444'; // Red for debt
    }
    
    if (typeof updateQR === 'function') updateQR();
}

function updateQR() {
    const pmInput = document.querySelector('input[name="payment_method"]:checked');
    const pm = pmInput ? pmInput.value : '';
    const qrContainer = document.getElementById('pos-qr-container');
    const qrImg = document.getElementById('pos-qr-img');
    const amtEl = document.getElementById('pos-qr-amount');
    const descEl = document.getElementById('pos-qr-desc-text');
    const accEl = document.getElementById('pos-qr-acc-text');
    
    if (window.mkv_enable_pos_qr !== 0 && pm === 'transfer' && window.mkv_bank_info && window.mkv_bank_info.id && window.mkv_bank_info.account && qrContainer) {
        const finalTotal = window.mkv_final_total || 0;
        if (finalTotal > 0) {
            if (!window.mkv_pos_memo) {
                window.mkv_pos_memo = 'MKV' + Math.floor(100000 + Math.random() * 900000);
            }
            const memo = window.mkv_pos_memo;
            const bankId = encodeURIComponent(window.mkv_bank_info.id);
            const bankAcc = encodeURIComponent(window.mkv_bank_info.account);
            const bankName = encodeURIComponent(window.mkv_bank_info.name);
            const qrUrl = `https://img.vietqr.io/image/${bankId}-${bankAcc}-compact2.png?amount=${finalTotal}&addInfo=${encodeURIComponent(memo)}&accountName=${bankName}`;
            
            if (qrImg && qrImg.src !== qrUrl) {
                qrImg.src = qrUrl;
            }
            if (amtEl) amtEl.textContent = finalTotal.toLocaleString('vi-VN') + ' ₫';
            if (descEl) descEl.textContent = memo;
            if (accEl) accEl.textContent = window.mkv_bank_info.account;

            qrContainer.style.display = 'block';
        } else {
            qrContainer.style.display = 'none';
        }
    } else if (qrContainer) {
        qrContainer.style.display = 'none';
    }
}

// In Bill Modal
function previewReceipt() {
    const modal = document.getElementById('mkv-receipt-modal');
    const itemsList = document.getElementById('receipt-items-list');
    const totalEl = document.getElementById('receipt-total-amount');
    const custEl = document.getElementById('receipt-customer-name');
    const custSel = document.getElementById('pos-customer-select');

    custEl.textContent = mkv_pos_i18n.customer + ': ' + custSel.options[custSel.selectedIndex].text.split('(')[0].trim();

    let itemsHtml = '<table style="width:100%; border-collapse:collapse;">';
    let total = 0;
    for (const id in cart) {
        const item = cart[id];
        const lineTotal = item.price * item.qty;
        total += lineTotal;
        itemsHtml += `<tr>
            <td style="padding:2px 0;">${escapeHtml(item.name)}</td>
            <td style="text-align:center; padding:2px 0;">x${item.qty}</td>
            <td style="text-align:right; padding:2px 0;">${lineTotal.toLocaleString('vi-VN')}</td>
        </tr>`;
    }
    itemsHtml += '</table>';
    itemsList.innerHTML = itemsHtml;
    
    // Áp dụng giảm giá bằng điểm
    const pointsUsed = parseInt(document.getElementById('pos-points-used')?.value) || 0;
    const pointValue = window.mkv_point_value || 1;
    const discountAmount = pointsUsed * pointValue;
    
    // Phí vận chuyển
    let shippingFee = 0;
    const enableShippingEl = document.getElementById('pos-enable-shipping');
    if (enableShippingEl && enableShippingEl.checked) {
        shippingFee = parseInt(document.getElementById('pos-shipping-fee')?.value) || 0;
    }

    const isVatIncluded = parseInt(window.mkv_vat_included || 0) === 1;
    let vatAmount = 0;
    let baseBeforeDiscount = total;

    if (isVatIncluded) {
        vatAmount = (window.mkv_pos_vat_rate || 0) > 0 ? Math.round(total - (total / (1 + (window.mkv_pos_vat_rate || 0)))) : 0;
        baseBeforeDiscount = total;
    } else {
        vatAmount = Math.round(total * (window.mkv_pos_vat_rate || 0));
        baseBeforeDiscount = total + vatAmount;
    }

    let finalTotal = Math.max(0, baseBeforeDiscount - discountAmount) + shippingFee;
    
    let totalHtml = '';
    if (discountAmount > 0) {
        totalHtml += `<span style="font-size:11px; font-weight:normal; display:block; color:#059669;">Giảm trừ điểm: -${discountAmount.toLocaleString('vi-VN')} ₫</span>`;
    }
    if (vatAmount > 0) {
        if (isVatIncluded) {
            totalHtml += `<span style="font-size:11px; font-weight:normal; display:block; color:#6b7280;">(Đã gồm VAT ${window.mkv_pos_vat_rate * 100}%: ${vatAmount.toLocaleString('vi-VN')} ₫)</span>`;
        } else {
            totalHtml += `<span style="font-size:11px; font-weight:normal; display:block;">VAT (${window.mkv_pos_vat_rate * 100}%): +${vatAmount.toLocaleString('vi-VN')} ₫</span>`;
        }
    }
    if (shippingFee > 0) {
        totalHtml += `<span style="font-size:11px; font-weight:normal; display:block; color:#2563eb;">Phí vận chuyển: +${shippingFee.toLocaleString('vi-VN')} ₫</span>`;
    }
    totalHtml += `<span style="font-size:16px; font-weight:700;">${finalTotal.toLocaleString('vi-VN')} ₫</span>`;
    
    totalEl.innerHTML = totalHtml;
    totalEl.dataset.finalTotal = finalTotal;

    // Cập nhật QR trên phiếu in nếu có cấu hình và thanh toán chuyển khoản
    const pmInput = document.querySelector('input[name="payment_method"]:checked');
    const pm = pmInput ? pmInput.value : '';
    const receiptQrBox = document.getElementById('receipt-qr-container');
    if (window.mkv_receipt_enable_qr !== 0 && pm === 'transfer' && window.mkv_bank_info && window.mkv_bank_info.id && window.mkv_bank_info.account && receiptQrBox) {
        const memo = window.mkv_pos_memo || ('MKV' + Math.floor(100000 + Math.random() * 900000));
        const bankId = encodeURIComponent(window.mkv_bank_info.id);
        const bankAcc = encodeURIComponent(window.mkv_bank_info.account);
        const bankName = encodeURIComponent(window.mkv_bank_info.name);
        const receiptQrUrl = `https://img.vietqr.io/image/${bankId}-${bankAcc}-compact2.png?amount=${finalTotal}&addInfo=${encodeURIComponent(memo)}&accountName=${bankName}`;
        
        const receiptQrImg = document.getElementById('receipt-qr-img');
        if (receiptQrImg) receiptQrImg.src = receiptQrUrl;
        
        const receiptQrText = document.getElementById('receipt-qr-text');
        if (receiptQrText) {
            receiptQrText.replaceChildren();
            const bankLine = document.createElement('div');
            bankLine.textContent = 'STK: ' + window.mkv_bank_info.account + ' - ' + window.mkv_bank_info.id;
            const memoLine = document.createElement('div');
            memoLine.textContent = 'Nội dung: ' + memo;
            receiptQrText.append(bankLine, memoLine);
        }
        
        receiptQrBox.style.display = 'block';
    } else if (receiptQrBox) {
        receiptQrBox.style.display = 'none';
    }

    modal.style.display = 'flex';
}

function closeReceiptModal() {
    document.getElementById('mkv-receipt-modal').style.display = 'none';
    if (window.mkv_receipt_is_completed) {
        clearCart();
        document.getElementById('pos-customer-select').value = "0";
        onCustomerChange(document.getElementById('pos-customer-select'));
        window.mkv_receipt_is_completed = false;
        renderCart();
    }
}

function printReceipt() {
    window.print();
}

// Hotkey F9 để thanh toán nhanh
document.addEventListener('keydown', function(e) {
    if (e.key === 'F9') {
        e.preventDefault();
        const submitBtn = document.getElementById('pos-submit-btn');
        if (!submitBtn.disabled) {
            document.getElementById('pos-form').dispatchEvent(new Event('submit'));
        }
    }
});

// AJAX form submission
document.getElementById('pos-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('pos-submit-btn');
    if (btn.disabled) return;
    
    // Show preview modal to update DOM with current cart details
    // But hide it immediately so we can show it after success
    previewReceipt(); 
    document.getElementById('mkv-receipt-modal').style.display = 'none';

    btn.disabled = true;
    const oldText = btn.innerHTML;
    btn.innerHTML = '<i class="hgi-stroke hgi-loading-02" style="animation: spin 1s linear infinite;"></i> Đang xử lý...';

    const formData = new FormData(this);
    formData.append('is_ajax', '1');
    const fetchUrl = this.getAttribute('action');

    fetch(fetchUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async response => {
        let text = await response.text();
        // Lọc bỏ các HTML rác do Query Monitor hoặc plugin khác chèn vào
        // Tìm chuỗi JSON thực sự để loại bỏ HTML/CSS rác
        const match = text.match(/\{\s*"success"\s*:/);
        if (match) {
            const jsonStart = match.index;
            const lastBrace = text.lastIndexOf('}');
            if (lastBrace > jsonStart) {
                text = text.substring(jsonStart, lastBrace + 1);
            }
        }
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error("Raw response:", text);
            throw new Error("RAW_RESPONSE_ERROR:" + text);
        }
    })
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = oldText;
        if (res.success) {
            window.mkv_receipt_is_completed = true;
            resetPosOrderCode();
            // Hiển thị QR Code nếu là thanh toán chuyển khoản
            const pmInput = document.querySelector('input[name="payment_method"]:checked');
            const pm = pmInput ? pmInput.value : '';
            const qrContainer = document.getElementById('receipt-qr-container');
            if (window.mkv_receipt_enable_qr !== 0 && pm === 'transfer' && window.mkv_bank_info && window.mkv_bank_info.id && window.mkv_bank_info.account && qrContainer) {
                // Lấy tổng tiền từ receipt-total-amount dataset
                const totalEl = document.getElementById('receipt-total-amount');
                const finalTotal = totalEl ? parseInt(totalEl.dataset.finalTotal) : 0;
                
                const bankId = encodeURIComponent(window.mkv_bank_info.id);
                const bankAcc = encodeURIComponent(window.mkv_bank_info.account);
                const bankName = encodeURIComponent(window.mkv_bank_info.name);
                const orderCode = res.data && res.data.order_code ? res.data.order_code : (window.mkv_pos_memo || 'Thanh toan');
                
                const qrUrl = `https://img.vietqr.io/image/${bankId}-${bankAcc}-compact2.png?amount=${finalTotal}&addInfo=${encodeURIComponent(orderCode)}&accountName=${bankName}`;
                
                const qrImg = document.getElementById('receipt-qr-img');
                if (qrImg) qrImg.src = qrUrl;
                
                const qrText = document.getElementById('receipt-qr-text');
                if (qrText) {
                    qrText.replaceChildren();
                    const bankLine = document.createElement('div');
                    bankLine.textContent = 'STK: ' + window.mkv_bank_info.account + ' - ' + window.mkv_bank_info.id;
                    const orderLine = document.createElement('div');
                    orderLine.textContent = 'Mã đơn: ' + orderCode;
                    qrText.append(bankLine, orderLine);
                }
                
                qrContainer.style.display = 'block';
            } else if (qrContainer) {
                qrContainer.style.display = 'none';
            }

            // Show receipt modal
            document.getElementById('mkv-receipt-modal').style.display = 'flex';
            mkvToast('Tạo đơn hàng thành công!');
        } else {
            mkvToast(res.data || 'Đã xảy ra lỗi khi tạo đơn hàng.', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = oldText;
        if (err.message && err.message.startsWith('RAW_RESPONSE_ERROR:')) {
            let rawText = err.message.replace('RAW_RESPONSE_ERROR:', '').trim();
            // Cắt bớt nếu quá dài
            if (rawText.length > 200) rawText = rawText.substring(0, 200) + '...';
            mkvToast('Lỗi JSON: ' + rawText, 'error');
        } else {
            mkvToast('Lỗi hệ thống: Vui lòng kiểm tra Console (F12).', 'error');
        }
    });
});

// Keyboard accessibility for product selection in POS catalog
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        const item = e.target.closest('.pos-product-item');
        if (item && document.activeElement === item) {
            e.preventDefault();
            item.click();
        }
    }
});
