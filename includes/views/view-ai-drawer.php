<?php
/**
 * View: Mini KiotViet AI Assistant Drawer & Overlay
 * Rendered at root level in admin_footer to avoid any parent clipping or stacking issues.
 */
if (!defined('ABSPATH')) exit;

$user = wp_get_current_user();
$can_see_all = current_user_can('manage_options');
?>
<!-- AI Floating Action Button (FAB) -->
<button type="button" class="mkv-ai-fab" id="mkv-ai-fab" title="<?php echo esc_attr(mkv__('Trợ lý AI Copilot')); ?>" aria-label="<?php echo esc_attr(mkv__('Mở trợ lý AI Copilot')); ?>" onclick="window.mkvOpenAIChat && window.mkvOpenAIChat()">
    <div class="mkv-ai-fab-icon">
        <i class="hgi-stroke hgi-ai-chat-02"></i>
    </div>
    <div class="mkv-ai-fab-text">Copilot</div>
</button>

<!-- AI Assistant Drawer Overlay -->
<div class="mkv-ai-overlay" id="mkv-ai-overlay"></div>

<!-- AI Assistant Drawer -->
<div class="mkv-ai-drawer" id="mkv-ai-drawer" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="mkv-ai-drawer-title" tabindex="-1">

    <!-- History Sidebar Panel (Gemini / ChatGPT Style) -->
    <div class="mkv-ai-history-panel" id="mkv-ai-history-panel">
        <div class="mkv-ai-history-header">
            <div class="mkv-ai-history-header-title">
                <i class="hgi-stroke hgi-clock-01"></i>
                <span><?php echo esc_html(mkv__('Lịch sử trò chuyện')); ?></span>
            </div>
            <button type="button" class="mkv-ai-history-close" id="mkv-ai-history-close" title="<?php echo esc_attr(mkv__('Đóng lịch sử')); ?>">
                <i class="hgi-stroke hgi-arrow-left-01"></i>
            </button>
        </div>

        <div class="mkv-ai-history-actions">
            <button type="button" class="mkv-ai-sidebar-new-chat-btn" id="mkv-ai-sidebar-new-chat">
                <i class="hgi-stroke hgi-plus-sign"></i>
                <span><?php echo esc_html(mkv__('Đoạn chat mới')); ?></span>
            </button>
        </div>

        <div class="mkv-ai-history-search">
            <i class="hgi-stroke hgi-search-01"></i>
            <input type="text" id="mkv-ai-history-search" placeholder="<?php echo esc_attr(mkv__('Tìm kiếm đoạn chat...')); ?>">
        </div>

        <div class="mkv-ai-history-list" id="mkv-ai-history-list">
            <div class="mkv-ai-history-loading">
                <div class="mkv-ai-history-spinner"></div>
                <span><?php echo esc_html(mkv__('Đang tải lịch sử...')); ?></span>
            </div>
        </div>

        <div class="mkv-ai-history-footer">
            <?php if ($can_see_all): ?>
            <label class="mkv-ai-history-filter" title="<?php echo esc_attr(mkv__('Xem lịch sử hội thoại của tất cả nhân viên')); ?>">
                <input type="checkbox" id="mkv-ai-history-all-users">
                <span><?php echo esc_html(mkv__('Tất cả nhân sự')); ?></span>
            </label>
            <?php endif; ?>
            <span class="mkv-ai-history-count" id="mkv-ai-history-count"></span>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="mkv-ai-main" id="mkv-ai-main">
        <div class="mkv-ai-header">
            <div class="mkv-ai-header-title">
                <button type="button" class="mkv-ai-history-toggle-btn" id="mkv-ai-history-toggle" title="<?php echo esc_attr(mkv__('Lịch sử truy cập AI')); ?>">
                    <i class="hgi-stroke hgi-sidebar-left"></i>
                </button>
                <div class="mkv-ai-header-avatar">
                    <i class="hgi-stroke hgi-ai-chat-02"></i>
                </div>
                <div>
                    <h3 id="mkv-ai-drawer-title"><?php echo esc_html(get_option('mkv_ai_assistant_name', 'KiotViet Copilot')); ?></h3>
                    <p><?php echo esc_html(mkv__('Luôn sẵn sàng hỗ trợ bạn')); ?></p>
                </div>
            </div>
            <div style="display:flex; gap:6px; align-items:center;">
                <button type="button" class="mkv-ai-close" id="mkv-ai-new-chat" title="<?php echo esc_attr(mkv__('Đoạn chat mới')); ?>">
                    <i class="hgi-stroke hgi-edit-02"></i>
                </button>
                <button type="button" class="mkv-ai-close" id="mkv-ai-clear-history" title="<?php echo esc_attr(mkv__('Xóa tin nhắn hiện tại')); ?>">
                    <i class="hgi-stroke hgi-delete-02"></i>
                </button>
                <button type="button" class="mkv-ai-close" id="mkv-ai-close" title="<?php echo esc_attr(mkv__('Đóng')); ?>">
                    <i class="hgi-stroke hgi-cancel-01"></i>
                </button>
            </div>
        </div>

        <div class="mkv-ai-body" id="mkv-ai-body">
            <div class="mkv-ai-msg ai">
                <div class="mkv-ai-avatar"><i class="hgi-stroke hgi-ai-chat-02"></i></div>
                <div class="mkv-ai-content">
                    <p><?php echo esc_html(mkv__('Xin chào')); ?> <strong><?php echo esc_html($user->display_name); ?></strong>!</p>
                    <p><?php echo sprintf(esc_html(mkv__('Tôi là %s. Hôm nay bạn cần hỗ trợ gì về số liệu kinh doanh hay thao tác bán hàng không?')), esc_html(get_option('mkv_ai_assistant_name', 'KiotViet Copilot'))); ?></p>
                </div>
            </div>
            <div class="mkv-ai-typing" id="mkv-ai-typing-indicator">
                <div class="mkv-ai-avatar" style="width:24px;height:24px;font-size:12px;margin-right:4px;"><i class="hgi-stroke hgi-ai-chat-02"></i></div>
                <div class="mkv-ai-content" style="padding: 8px 12px; display:flex; gap:4px; align-items:center;">
                    <div class="mkv-ai-dot"></div>
                    <div class="mkv-ai-dot"></div>
                    <div class="mkv-ai-dot"></div>
                </div>
            </div>
        </div>

        <div class="mkv-ai-chips" id="mkv-ai-chips-container"></div>

        <div class="mkv-ai-footer">
            <button type="button" class="mkv-ai-mic" id="mkv-ai-mic" title="<?php echo esc_attr(mkv__('Nói để nhập')); ?>"><i class="hgi-stroke hgi-mic-01"></i></button>
            <div class="mkv-ai-input-wrap">
                <textarea class="mkv-ai-input" id="mkv-ai-input" placeholder="<?php echo esc_attr(mkv__('Hỏi tôi bất cứ điều gì...')); ?>" rows="1"></textarea>
            </div>
            <button type="button" class="mkv-ai-send" id="mkv-ai-send" title="<?php echo esc_attr(mkv__('Gửi tin nhắn')); ?>"><i class="hgi-stroke hgi-sent"></i></button>
        </div>
    </div>
</div>

<script>
(function() {
    window.mkvOpenAIChat = window.mkvOpenAIChat || function() {
        var d = document.getElementById('mkv-ai-drawer');
        var o = document.getElementById('mkv-ai-overlay');
        document.body.classList.add('mkv-ai-drawer-open');
        if (d) {
            d.classList.add('open');
            d.style.setProperty('right', '0', 'important');
            d.style.setProperty('display', 'flex', 'important');
            d.style.setProperty('visibility', 'visible', 'important');
            d.style.setProperty('opacity', '1', 'important');
        }
        if (o) {
            o.classList.add('show');
            o.style.setProperty('display', 'block', 'important');
            o.style.setProperty('opacity', '1', 'important');
            o.style.setProperty('visibility', 'visible', 'important');
        }
        var inp = document.getElementById('mkv-ai-input');
        if (inp) setTimeout(function() { inp.focus(); }, 300);
        if (typeof window.mkvOnOpenAIChatHook === 'function') window.mkvOnOpenAIChatHook();
    };

    window.mkvCloseAIChat = window.mkvCloseAIChat || function() {
        var d = document.getElementById('mkv-ai-drawer');
        var o = document.getElementById('mkv-ai-overlay');
        document.body.classList.remove('mkv-ai-drawer-open');
        if (d) { 
            d.classList.remove('open'); 
            d.style.setProperty('right', '-850px', 'important'); 
        }
        if (o) { 
            o.classList.remove('show'); 
            o.style.setProperty('display', 'none', 'important'); 
            o.style.setProperty('opacity', '0', 'important'); 
        }
        mkvCloseHistoryPanel();
    };

    var historyOpen = false;
    var historyLoaded = false;
    var allHistoryData = [];

    window.mkvRefreshAIHistory = function() {
        historyLoaded = false;
        if (historyOpen) {
            var chk = document.getElementById('mkv-ai-history-all-users');
            loadAIHistory(chk && chk.checked);
        }
    };

    function mkvOpenHistoryPanel() {
        var panel = document.getElementById('mkv-ai-history-panel');
        var drawer = document.getElementById('mkv-ai-drawer');
        if (!panel) return;
        panel.classList.add('open');
        if (drawer) drawer.classList.add('history-open');
        historyOpen = true;
        if (!historyLoaded) {
            var chk = document.getElementById('mkv-ai-history-all-users');
            loadAIHistory(chk && chk.checked);
        }
    }

    function mkvCloseHistoryPanel() {
        var panel = document.getElementById('mkv-ai-history-panel');
        var drawer = document.getElementById('mkv-ai-drawer');
        if (!panel) return;
        panel.classList.remove('open');
        if (drawer) drawer.classList.remove('history-open');
        historyOpen = false;
    }

    function loadAIHistory(allUsers) {
        var list = document.getElementById('mkv-ai-history-list');
        if (!list) return;
        list.innerHTML = '<div class="mkv-ai-history-loading"><div class="mkv-ai-history-spinner"></div><span><?php echo esc_js(mkv__('Đang tải lịch sử...')); ?></span></div>';
        
        var data = new FormData();
        data.append('action', 'mkv_get_ai_history');
        data.append('nonce', (window.mkv_ai_data && window.mkv_ai_data.nonce) ? window.mkv_ai_data.nonce : '');
        data.append('all_users', allUsers ? '1' : '0');

        fetch((window.mkv_ai_data && window.mkv_ai_data.ajax_url) ? window.mkv_ai_data.ajax_url : '/wp-admin/admin-ajax.php', {
            method: 'POST', body: data, credentials: 'same-origin'
        })
        .then(function(r){ return r.json(); })
        .then(function(resp){
            historyLoaded = true;
            if (resp.success && resp.data) {
                allHistoryData = resp.data;
                renderHistoryList(resp.data);
                updateHistoryCount(resp.data.length);
            } else {
                list.innerHTML = '<div class="mkv-ai-history-empty"><i class="hgi-stroke hgi-message-01"></i><p><?php echo esc_js(mkv__('Chưa có lịch sử trò chuyện nào.')); ?></p></div>';
                updateHistoryCount(0);
            }
        })
        .catch(function(){
            list.innerHTML = '<div class="mkv-ai-history-empty"><i class="hgi-stroke hgi-wifi-disconnected-01"></i><p><?php echo esc_js(mkv__('Không thể tải lịch sử. Vui lòng thử lại.')); ?></p></div>';
            updateHistoryCount(0);
        });
    }

    function updateHistoryCount(count) {
        var countEl = document.getElementById('mkv-ai-history-count');
        if (countEl) {
            countEl.textContent = count > 0 ? (count + ' <?php echo esc_js(mkv__('hội thoại')); ?>') : '';
        }
    }

    function renderHistoryList(data) {
        var list = document.getElementById('mkv-ai-history-list');
        if (!list) return;
        if (!data || data.length === 0) {
            list.innerHTML = '<div class="mkv-ai-history-empty"><i class="hgi-stroke hgi-message-01"></i><p><?php echo esc_js(mkv__('Chưa có lịch sử trò chuyện nào.')); ?></p></div>';
            return;
        }

        var groups = {};
        var today = new Date();
        var yesterday = new Date(today); yesterday.setDate(today.getDate() - 1);
        var sevenDays = new Date(today); sevenDays.setDate(today.getDate() - 7);
        var thirtyDays = new Date(today); thirtyDays.setDate(today.getDate() - 30);

        data.forEach(function(item) {
            var d = new Date(item.created_at);
            var label;
            if (isSameDay(d, today)) label = '<?php echo esc_js(mkv__('Hôm nay')); ?>';
            else if (isSameDay(d, yesterday)) label = '<?php echo esc_js(mkv__('Hôm qua')); ?>';
            else if (d > sevenDays) label = '<?php echo esc_js(mkv__('7 ngày qua')); ?>';
            else if (d > thirtyDays) label = '<?php echo esc_js(mkv__('30 ngày qua')); ?>';
            else label = '<?php echo esc_js(mkv__('Cũ hơn')); ?>';

            if (!groups[label]) groups[label] = [];
            groups[label].push(item);
        });

        var order = [
            '<?php echo esc_js(mkv__('Hôm nay')); ?>',
            '<?php echo esc_js(mkv__('Hôm qua')); ?>',
            '<?php echo esc_js(mkv__('7 ngày qua')); ?>',
            '<?php echo esc_js(mkv__('30 ngày qua')); ?>',
            '<?php echo esc_js(mkv__('Cũ hơn')); ?>'
        ];

        var html = '';
        order.forEach(function(label) {
            if (!groups[label]) return;
            html += '<div class="mkv-ai-history-group-label">' + label + '</div>';
            groups[label].forEach(function(item) {
                var preview = item.message ? item.message.substring(0, 65) + (item.message.length > 65 ? '…' : '') : '';
                var time = formatTime(new Date(item.created_at));
                var userBadge = item.user_name ? '<span class="mkv-ai-history-user-badge"><i class="hgi-stroke hgi-user" style="font-size:10px;"></i> ' + escHtml(item.user_name) + '</span>' : '';

                html += '<div class="mkv-ai-history-item" data-id="' + escHtml(item.id) + '">'
                    + '<div class="mkv-ai-history-item-icon"><i class="hgi-stroke hgi-message-01"></i></div>'
                    + '<div class="mkv-ai-history-item-body">'
                    + '<div class="mkv-ai-history-item-preview">' + escHtml(preview) + '</div>'
                    + '<div class="mkv-ai-history-item-meta">' + userBadge + '<span>' + time + '</span></div>'
                    + '</div>'
                    + '<button class="mkv-ai-history-del-btn" data-del-id="' + escHtml(item.id) + '" title="<?php echo esc_attr(mkv__('Xóa đoạn chat này')); ?>">'
                    + '<i class="hgi-stroke hgi-delete-02"></i>'
                    + '</button>'
                    + '</div>';
            });
        });
        list.innerHTML = html;
    }

    function isSameDay(d1, d2) {
        return d1.getFullYear() === d2.getFullYear() && d1.getMonth() === d2.getMonth() && d1.getDate() === d2.getDate();
    }

    function formatTime(d) {
        var now = new Date();
        var diff = Math.floor((now - d) / 1000);
        if (diff < 60) return '<?php echo esc_js(mkv__('Vừa xong')); ?>';
        if (diff < 3600) return Math.floor(diff/60) + ' <?php echo esc_js(mkv__('phút trước')); ?>';
        if (diff < 86400) return Math.floor(diff/3600) + ' <?php echo esc_js(mkv__('giờ trước')); ?>';
        return d.getDate().toString().padStart(2,'0') + '/' + (d.getMonth()+1).toString().padStart(2,'0') + '/' + d.getFullYear();
    }

    function escHtml(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function filterHistory(query) {
        if (!query) { 
            renderHistoryList(allHistoryData); 
            return; 
        }
        var q = query.toLowerCase();
        renderHistoryList(allHistoryData.filter(function(item) {
            return (item.message && item.message.toLowerCase().indexOf(q) >= 0) ||
                   (item.user_name && item.user_name.toLowerCase().indexOf(q) >= 0);
        }));
    }

    function loadInteractionIntoChat(item) {
        if (!item) return;
        var body = document.getElementById('mkv-ai-body');
        if (!body) return;

        // Preserve first welcome message
        var greeting = body.querySelector('.mkv-ai-msg.ai:first-child');
        var greetingHtml = greeting ? greeting.outerHTML : '';

        var userMsgHtml = '<div class="mkv-ai-msg user">'
            + '<div class="mkv-ai-content">' + escHtml(item.message).replace(/\n/g, '<br>') + '</div>'
            + '</div>';

        var aiText = item.ai_reply || '<?php echo esc_js(mkv__('Không có nội dung phản hồi lưu trữ cho câu hỏi này.')); ?>';
        var parsedAiText = aiText;
        if (typeof marked !== 'undefined') {
            try {
                parsedAiText = marked.parse(aiText);
                if (typeof DOMPurify !== 'undefined') parsedAiText = DOMPurify.sanitize(parsedAiText);
            } catch(e) {
                parsedAiText = escHtml(aiText).replace(/\n/g, '<br>');
            }
        } else {
            parsedAiText = escHtml(aiText).replace(/\n/g, '<br>');
        }

        var aiMsgHtml = '<div class="mkv-ai-msg ai">'
            + '<div class="mkv-ai-avatar"><i class="hgi-stroke hgi-ai-chat-02"></i></div>'
            + '<div class="mkv-ai-content">' + parsedAiText + '</div>'
            + '</div>';

        var typing = document.getElementById('mkv-ai-typing-indicator');
        var typingHtml = typing ? typing.outerHTML : '';

        body.innerHTML = greetingHtml + userMsgHtml + aiMsgHtml + typingHtml;
        body.scrollTop = body.scrollHeight;

        if (item.interaction_id) {
            try {
                localStorage.setItem('mkv_ai_interaction_id', item.interaction_id);
            } catch(e){}
        }

        document.querySelectorAll('.mkv-ai-history-item').forEach(function(el) {
            el.classList.remove('active');
        });
        var activeEl = document.querySelector('.mkv-ai-history-item[data-id="' + item.id + '"]');
        if (activeEl) activeEl.classList.add('active');

        // On mobile / tablet screens, slide history panel back so chat is visible
        if (window.innerWidth <= 768) {
            mkvCloseHistoryPanel();
        }
    }

    function deleteHistoryItem(id, e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        if (!confirm('<?php echo esc_js(mkv__('Bạn có chắc chắn muốn xóa bản ghi hội thoại này?')); ?>')) return;

        var data = new FormData();
        data.append('action', 'mkv_delete_ai_history');
        data.append('nonce', (window.mkv_ai_data && window.mkv_ai_data.nonce) ? window.mkv_ai_data.nonce : '');
        data.append('log_id', id);

        fetch((window.mkv_ai_data && window.mkv_ai_data.ajax_url) ? window.mkv_ai_data.ajax_url : '/wp-admin/admin-ajax.php', {
            method: 'POST', body: data, credentials: 'same-origin'
        })
        .then(function(r){ return r.json(); })
        .then(function(resp){
            if (resp.success) {
                allHistoryData = allHistoryData.filter(function(x){ return String(x.id) !== String(id); });
                renderHistoryList(allHistoryData);
                updateHistoryCount(allHistoryData.length);
            } else {
                alert('<?php echo esc_js(mkv__('Không thể xóa bản ghi. Vui lòng thử lại.')); ?>');
            }
        })
        .catch(function(){
            alert('<?php echo esc_js(mkv__('Lỗi kết nối máy chủ.')); ?>');
        });
    }

    function startNewChat() {
        var body = document.getElementById('mkv-ai-body');
        if (body) {
            var msgs = body.querySelectorAll('.mkv-ai-msg');
            for (var i = 1; i < msgs.length; i++) {
                msgs[i].remove();
            }
        }
        try {
            localStorage.removeItem('mkv_ai_interaction_id');
            localStorage.removeItem('mkv_ai_chat_history');
        } catch(e){}

        document.querySelectorAll('.mkv-ai-history-item').forEach(function(el){ 
            el.classList.remove('active'); 
        });

        var input = document.getElementById('mkv-ai-input');
        if (input) {
            input.value = '';
            input.style.height = 'auto';
            input.focus();
        }

        if (window.innerWidth <= 768) {
            mkvCloseHistoryPanel();
        }
    }

    function initAIEvents() {
        var fab            = document.getElementById('mkv-ai-fab');
        var closeBtn       = document.getElementById('mkv-ai-close');
        var overlay        = document.getElementById('mkv-ai-overlay');
        var histToggle     = document.getElementById('mkv-ai-history-toggle');
        var histClose      = document.getElementById('mkv-ai-history-close');
        var newChat        = document.getElementById('mkv-ai-new-chat');
        var sidebarNewChat = document.getElementById('mkv-ai-sidebar-new-chat');
        var searchInput    = document.getElementById('mkv-ai-history-search');
        var allUsersChk    = document.getElementById('mkv-ai-history-all-users');

        if (fab) fab.onclick = function(e){ e.preventDefault(); e.stopPropagation(); window.mkvOpenAIChat(); };
        if (closeBtn) closeBtn.onclick = function(e){ e.preventDefault(); window.mkvCloseAIChat(); };
        if (overlay) overlay.onclick = function(e){ e.preventDefault(); window.mkvCloseAIChat(); };
        
        if (histToggle) {
            histToggle.onclick = function(e) {
                e.preventDefault();
                historyOpen ? mkvCloseHistoryPanel() : mkvOpenHistoryPanel();
            };
        }
        if (histClose) {
            histClose.onclick = function(e) {
                e.preventDefault();
                mkvCloseHistoryPanel();
            };
        }

        if (newChat) newChat.onclick = function(e){ e.preventDefault(); startNewChat(); };
        if (sidebarNewChat) sidebarNewChat.onclick = function(e){ e.preventDefault(); startNewChat(); };

        if (searchInput) {
            searchInput.addEventListener('input', function(){
                filterHistory(this.value.trim());
            });
        }
        if (allUsersChk) {
            allUsersChk.addEventListener('change', function(){
                historyLoaded = false;
                loadAIHistory(this.checked);
            });
        }

        // Delegate item click and delete click
        document.addEventListener('click', function(e) {
            var delBtn = e.target.closest('.mkv-ai-history-del-btn');
            if (delBtn) {
                var delId = delBtn.getAttribute('data-del-id');
                if (delId) deleteHistoryItem(delId, e);
                return;
            }

            var itemEl = e.target.closest('.mkv-ai-history-item');
            if (itemEl) {
                var id = itemEl.getAttribute('data-id');
                var found = allHistoryData.find(function(x){ return String(x.id) === String(id); });
                if (found) {
                    loadInteractionIntoChat(found);
                }
            }
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initAIEvents);
    else initAIEvents();
})();
</script>
