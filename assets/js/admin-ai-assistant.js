/* ==========================================================================
   Mini KiotViet - AI Assistant JS
   ========================================================================== */

// Expose global drawer toggle functions immediately so buttons work even before DOM ready
window.mkvOpenAIChat = function() {
    var d = document.getElementById('mkv-ai-drawer');
    var o = document.getElementById('mkv-ai-overlay');
    document.body.classList.add('mkv-ai-drawer-open');
    if (d) {
        d.classList.add('open');
        d.style.setProperty('right', '0', 'important');
        d.style.setProperty('display', 'flex', 'important');
        d.style.setProperty('visibility', 'visible', 'important');
    }
    if (o) {
        o.classList.add('show');
        o.style.setProperty('display', 'block', 'important');
        o.style.setProperty('opacity', '1', 'important');
        o.style.setProperty('visibility', 'visible', 'important');
    }
    var inp = document.getElementById('mkv-ai-input');
    if (inp) {
        setTimeout(function() { inp.focus(); }, 300);
    }
    if (typeof window.mkvOnOpenAIChatHook === 'function') {
        window.mkvOnOpenAIChatHook();
    }
};

window.mkvCloseAIChat = function() {
    var d = document.getElementById('mkv-ai-drawer');
    var o = document.getElementById('mkv-ai-overlay');
    document.body.classList.remove('mkv-ai-drawer-open');
    if (d) {
        d.classList.remove('open');
        d.style.setProperty('right', '-450px', 'important');
    }
    if (o) {
        o.classList.remove('show');
        o.style.setProperty('display', 'none', 'important');
        o.style.setProperty('opacity', '0', 'important');
    }
};

(function($) {
    $(document).ready(function() {
        var chatHistory = [];
        var isWaiting = false;
        var hasRenderedHistory = false;
        var currentInteractionId = '';

        // Load memory
        try {
            var saved = localStorage.getItem('mkv_ai_chat_history');
            if (saved) {
                chatHistory = JSON.parse(saved);
                if (!Array.isArray(chatHistory)) chatHistory = [];
                chatHistory = chatHistory.slice(-10);
            }
            currentInteractionId = localStorage.getItem('mkv_ai_interaction_id') || '';
        } catch(e) {}

        var $drawer = $('#mkv-ai-drawer');
        var $overlay = $('#mkv-ai-overlay');
        var $input = $('#mkv-ai-input');
        var $btnSend = $('#mkv-ai-send');
        var $btnMic = $('#mkv-ai-mic');
        var $btnClear = $('#mkv-ai-clear-history');
        var $body = $('#mkv-ai-body');
        var $typing = $('#mkv-ai-typing-indicator');

        // Speech Recognition Setup with safe origin checks (prevents crash on HTTP)
        var recognition = null;
        try {
            var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (SpeechRecognition && window.isSecureContext !== false) {
                recognition = new SpeechRecognition();
                recognition.continuous = false;
                recognition.lang = 'vi-VN';
                recognition.interimResults = false;
                
                recognition.onstart = function() {
                    $btnMic.addClass('recording');
                    $input.attr('placeholder', 'Đang nghe...');
                };
                
                recognition.onresult = function(event) {
                    var transcript = event.results[0][0].transcript;
                    $input.val($input.val() + ' ' + transcript);
                    $input.trigger('input');
                };
                
                recognition.onerror = function(event) {
                    console.warn('Speech recognition warning:', event.error);
                    $input.attr('placeholder', 'Hỏi tôi bất cứ điều gì...');
                    $btnMic.removeClass('recording');
                };
                
                recognition.onend = function() {
                    $btnMic.removeClass('recording');
                    $input.attr('placeholder', 'Hỏi tôi bất cứ điều gì...');
                    $input.focus();
                };
            } else {
                $btnMic.hide();
            }
        } catch(err) {
            console.warn('Speech recognition disabled on this origin:', err);
            $btnMic.hide();
        }

        // On open hook to render history & auto-scroll
        window.mkvOnOpenAIChatHook = function() {
            if (!hasRenderedHistory && chatHistory.length > 0) {
                hasRenderedHistory = true;
                chatHistory.forEach(function(msg) {
                    if (msg.parts && msg.parts[0]) {
                        if (msg.parts[0].text) {
                            appendMessage(msg.role === 'user' ? 'user' : 'ai', msg.parts[0].text, msg.function_data || null, msg.function_name || null, true);
                        }
                    }
                });
            }
            scrollToBottom();
        };

        $(document).on('click', '#mkv-ai-close, #mkv-ai-overlay', function() {
            mkvCloseAIChat();
        });

        // Clear History
        $btnClear.on('click', function() {
            if (confirm('Bạn có chắc chắn muốn xóa lịch sử trò chuyện?')) {
                chatHistory = [];
                currentInteractionId = '';
                try {
                    localStorage.removeItem('mkv_ai_chat_history');
                    localStorage.removeItem('mkv_ai_interaction_id');
                } catch(e) {}
                $('.mkv-ai-msg:not(:first)').remove(); // Keep the first greeting message
                hasRenderedHistory = false;
            }
        });

        // Mic Button Click
        $btnMic.on('click', function() {
            if (recognition) {
                if ($btnMic.hasClass('recording')) {
                    recognition.stop();
                } else {
                    recognition.start();
                }
            }
        });

        // Prompt Chips (Dynamic)
        $('#mkv-ai-chips-container').on('click', '.mkv-ai-chip', function() {
            if (isWaiting) return;
            var text = $(this).text();
            $input.val(text);
            sendMessage();
        });

        function initSmartChips() {
            const pageTitle = document.title.toLowerCase();
            const urlParams = new URLSearchParams(window.location.search);
            const pageParam = urlParams.get('page') || '';
            const postType = urlParams.get('post_type') || '';

            let chips = [];

            if (pageParam === 'mkv-pos') {
                chips = [
                    "Tạo đơn áo thun size L",
                    "Tìm sản phẩm 'Áo khoác'",
                    "Đơn hàng gần nhất là đơn nào?"
                ];
            } else if (postType === 'mkv_product' || pageParam === 'mkv-categories' || pageTitle.includes('sản phẩm') || pageTitle.includes('hàng hóa')) {
                chips = [
                    "Sản phẩm nào sắp hết hàng?",
                    "Tìm sản phẩm theo từ khóa",
                    "Doanh thu hôm nay bao nhiêu?"
                ];
            } else if (pageParam === 'mkv-cashbook' || pageTitle.includes('sổ quỹ')) {
                chips = [
                    "Doanh thu hôm nay bao nhiêu?",
                    "Hôm qua thu được bao nhiêu tiền?",
                    "Chi phí tháng này là bao nhiêu?"
                ];
            } else if (pageParam === 'mini-kiotviet' || pageTitle.includes('dashboard')) {
                chips = [
                    "Doanh thu hôm nay bao nhiêu?",
                    "Sản phẩm nào sắp hết hàng?",
                    "Có bao nhiêu đơn hàng chờ xử lý?"
                ];
            } else {
                chips = [
                    "Sản phẩm nào sắp hết hàng?",
                    "Tra cứu đơn hàng MKV-...",
                    "Doanh thu hôm nay?"
                ];
            }

            const $container = $('#mkv-ai-chips-container');
            $container.empty();
            chips.forEach(function(text) {
                $container.append($('<div>').addClass('mkv-ai-chip').text(text));
            });
        }
        
        // Initialize chips on load
        initSmartChips();

        // Send Message
        $btnSend.on('click', function(e) {
            e.preventDefault();
            sendMessage();
        });

        $input.on('keypress', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Auto-resize textarea
        $input.on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight < 120 ? this.scrollHeight : 120) + 'px';
        });

        function sendMessage() {
            var text = $input.val().trim();
            if (!text || isWaiting) return;

            // 1. Add user message to UI
            appendMessage('user', text);
            $input.val('').css('height', 'auto');
            
            // 2. Show typing indicator
            isWaiting = true;
            $typing.css('display', 'flex');
            scrollToBottom();

            // 3. Send AJAX with security nonce
            var ajaxEndpoint = (typeof mkv_ai_data !== 'undefined' && mkv_ai_data.ajax_url) ? mkv_ai_data.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
            var nonceToken = (typeof mkv_ai_data !== 'undefined' && mkv_ai_data.nonce) ? mkv_ai_data.nonce : '';

            $.ajax({
                url: ajaxEndpoint,
                type: 'POST',
                timeout: 60000,
                data: {
                    action: 'mkv_ai_chat',
                    nonce: nonceToken,
                    message: text,
                    history: JSON.stringify(chatHistory),
                    current_page: document.title || window.location.href,
                    interaction_id: currentInteractionId
                },
                success: function(response) {
                    $typing.hide();
                    isWaiting = false;
                    
                    if (response.success) {
                        chatHistory = response.data.history || [];
                        if (response.data.function_executed && chatHistory.length > 0) {
                            chatHistory[chatHistory.length - 1].function_name = response.data.function_executed;
                            chatHistory[chatHistory.length - 1].function_data = response.data.function_data;
                        }
                        if (response.data.interaction_id) {
                            currentInteractionId = response.data.interaction_id;
                        }
                        try {
                            localStorage.setItem('mkv_ai_chat_history', JSON.stringify(chatHistory));
                            localStorage.setItem('mkv_ai_interaction_id', currentInteractionId);
                        } catch(e) {}
                        appendMessage('ai', response.data.text, response.data.function_data, response.data.function_executed);
                    } else {
                        var errorMessage = typeof response.data === 'string'
                            ? response.data
                            : (response.data && response.data.message) || 'Không thể kết nối với hệ thống.';
                        appendMessage('ai', errorMessage);
                    }
                },
                error: function(xhr, status) {
                    $typing.hide();
                    isWaiting = false;
                    if (status === 'timeout') {
                        appendMessage('ai', 'AI phản hồi chậm hơn dự kiến. Bạn vui lòng thử lại sau giây lát.');
                    } else {
                        appendMessage('ai', 'Lỗi kết nối máy chủ. Vui lòng kiểm tra lại kết nối!');
                    }
                }
            });
        }

        function appendMessage(role, text, functionData, functionName, isHistory) {
            functionData = functionData || null;
            functionName = functionName || null;
            isHistory = isHistory || false;
           
               function escapeHtml(value) {
                   return $('<div>').text(value == null ? '' : String(value)).html();
               }

            var parsedText = text;
            if (role === 'ai' && typeof marked !== 'undefined') {
                // Parse markdown
                parsedText = marked.parse(text);
                if (typeof DOMPurify !== 'undefined') {
                    parsedText = DOMPurify.sanitize(parsedText);
                } else {
                    parsedText = escapeHtml(text).replace(/\n/g, '<br>');
                }
            } else if (role === 'user') {
                // simple escape and replace newlines
                parsedText = $('<div>').text(text).html().replace(/\n/g, '<br>');
            }

            var cardHtml = '';
            if (functionData && functionName === 'check_order_status' && !functionData.error && functionData.status !== 'not_found') {
                cardHtml = `
                    <div style="margin-top: 10px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; font-size: 13px;">
                        <div style="font-weight: 600; margin-bottom: 8px;">Đơn hàng: ${escapeHtml(functionData.order_code)}</div>
                        <div><span style="color:#64748b">Trạng thái:</span> <strong>${escapeHtml(functionData.status)}</strong></div>
                        <div><span style="color:#64748b">Tổng tiền:</span> <strong style="color:var(--mkv-primary)">${escapeHtml(functionData.total_amount)}</strong></div>
                        <div><span style="color:#64748b">Khách hàng:</span> ${escapeHtml(functionData.customer)}</div>
                    </div>
                `;
            } else if (functionData && functionName === 'search_product_info' && !functionData.error && Array.isArray(functionData)) {
                cardHtml = '<div style="margin-top: 10px; display:flex; flex-direction:column; gap:8px;">';
                functionData.forEach(function(item) {
                    cardHtml += `
                        <div style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; font-size: 13px;">
                            <div style="font-weight: 600; margin-bottom: 4px;">${escapeHtml(item.name)}</div>
                            <div><span style="color:#64748b">Giá:</span> <strong style="color:var(--mkv-primary)">${escapeHtml(item.price)}</strong> | <span style="color:#64748b">Tồn kho:</span> <strong>${escapeHtml(item.stock)}</strong></div>
                        </div>
                    `;
                });
                cardHtml += '</div>';
            } else if (functionData && functionName === 'create_order_draft' && !functionData.error) {
                cardHtml = `
                    <div class="mkv-func-card" style="margin-top: 10px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff4e6; font-size: 13px;">
                        <div style="font-weight: 600; margin-bottom: 8px; color: #ea580c;">Đơn Nháp: ${escapeHtml(functionData.order_code)}</div>
                        <div><span class="label" style="color:#64748b">Sản phẩm:</span> <strong class="val">${escapeHtml(functionData.product)} (x${escapeHtml(functionData.quantity)})</strong></div>
                        <div><span class="label" style="color:#64748b">Tổng cộng:</span> <strong class="val" style="color:var(--mkv-primary)">${Number(functionData.total_amount || 0).toLocaleString()} VNĐ</strong></div>
                    </div>
                `;
            } else if (functionData && (functionName === 'get_revenue_report' || functionName === 'get_today_revenue') && !functionData.error) {
                var lbl = functionData.date_label || ('ngày ' + (functionData.date || ''));
                var rev = functionData.revenue_formatted || functionData.formatted_revenue || '0 VNĐ';
                cardHtml = `
                    <div class="mkv-func-card" style="margin-top: 10px; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f0fdf4; font-size: 13px; text-align: center;">
                        <div class="label" style="font-weight: 600; margin-bottom: 4px; color: #166534;">Doanh thu ${escapeHtml(lbl)}</div>
                        <div class="val" style="font-size: 20px; font-weight: 700; color: #15803d;">${escapeHtml(rev)}</div>
                    </div>
                `;
            } else if (functionData && functionName === 'get_low_stock_alert' && !functionData.error && functionData.items) {
                cardHtml = '<div style="margin-top: 10px; display:flex; flex-direction:column; gap:8px;">';
                if (functionData.items.length === 0) {
                    cardHtml += '<div style="padding:10px; text-align:center; color:#64748b; font-size:13px; font-style:italic;">Không có sản phẩm nào sắp hết hàng.</div>';
                } else {
                    functionData.items.forEach(function(item) {
                        cardHtml += `
                            <div class="mkv-func-card" style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fef2f2; font-size: 13px;">
                                <div class="val" style="font-weight: 600; margin-bottom: 4px; color: #b91c1c;">${escapeHtml(item.name)}</div>
                                <div><span class="label" style="color:#64748b">SKU:</span> <strong class="val">${escapeHtml(item.sku)}</strong> | <span class="label" style="color:#64748b">Tồn:</span> <strong class="val" style="color:#ef4444">${escapeHtml(item.stock)}</strong></div>
                            </div>
                        `;
                    });
                }
                cardHtml += '</div>';
            } else if (functionData && functionName === 'search_customer_info' && !functionData.error) {
                cardHtml = `
                    <div class="mkv-func-card" style="margin-top: 10px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; font-size: 13px;">
                        <div class="val" style="font-weight: 600; margin-bottom: 8px; font-size: 14px;"><i class="hgi-stroke hgi-user"></i> ${escapeHtml(functionData.name)}</div>
                        <div><span class="label" style="color:#64748b">SĐT:</span> <strong class="val">${escapeHtml(functionData.phone)}</strong></div>
                        <div><span class="label" style="color:#64748b">Địa chỉ:</span> <span class="val">${escapeHtml(functionData.address)}</span></div>
                        <hr style="margin: 8px 0; border: none; border-top: 1px dashed #cbd5e1;">
                        <div style="display:flex; justify-content:space-between;">
                            <div><span class="label" style="color:#64748b">Đã mua:</span> <strong class="val">${functionData.total_orders} đơn</strong></div>
                            <div><span class="label" style="color:#64748b">Chi tiêu:</span> <strong class="val" style="color:var(--mkv-primary)">${functionData.total_spent}</strong></div>
                        </div>
                    </div>
                `;
            } else if (functionData && functionName === 'get_order_summary_stats' && !functionData.error) {
                cardHtml = `
                    <div class="mkv-func-card" style="margin-top: 10px; display:grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div style="padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #eff6ff; text-align:center;">
                            <div class="label" style="font-size:12px; color:#64748b; margin-bottom:4px;">Chờ xử lý</div>
                            <div class="val" style="font-size:18px; font-weight:700; color:#2563eb;">${functionData.pending}</div>
                        </div>
                        <div style="padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f0fdf4; text-align:center;">
                            <div class="label" style="font-size:12px; color:#64748b; margin-bottom:4px;">Hoàn thành</div>
                            <div class="val" style="font-size:18px; font-weight:700; color:#16a34a;">${functionData.completed}</div>
                        </div>
                        <div style="padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fef2f2; text-align:center;">
                            <div class="label" style="font-size:12px; color:#64748b; margin-bottom:4px;">Đã hủy</div>
                            <div class="val" style="font-size:18px; font-weight:700; color:#dc2626;">${functionData.cancelled}</div>
                        </div>
                        <div style="padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; text-align:center;">
                            <div class="label" style="font-size:12px; color:#64748b; margin-bottom:4px;">Tổng cộng</div>
                            <div class="val" style="font-size:18px; font-weight:700; color:#475569;">${functionData.total}</div>
                        </div>
                    </div>
                `;
            }

            var icon = role === 'ai' ? '<i class="hgi-stroke hgi-ai-chat-02"></i>' : '<i class="hgi-stroke hgi-user"></i>';
            var opacityStyle = isHistory ? 'opacity: 0.8;' : '';
            var msgHtml = `
                <div class="mkv-ai-msg ${role}" style="${opacityStyle}">
                    <div class="mkv-ai-avatar">${icon}</div>
                    <div class="mkv-ai-content">${parsedText}${cardHtml}</div>
                </div>
            `;
            
            // Insert before typing indicator
            $typing.before(msgHtml);
            scrollToBottom();
        }

        function scrollToBottom() {
            $body.scrollTop($body[0].scrollHeight);
        }
    });
})(jQuery);
