<?php
/**
 * View: Mini KiotViet AI Assistant Drawer & Overlay
 * Rendered at root level in admin_footer to avoid any parent clipping or stacking issues.
 */
if (!defined('ABSPATH')) exit;

$user = wp_get_current_user();
?>
<!-- AI Floating Action Button (FAB) -->
<div class="mkv-ai-fab" id="mkv-ai-fab" title="Hỗ trợ AI" onclick="window.mkvOpenAIChat && window.mkvOpenAIChat()">
    <div class="mkv-ai-fab-icon">
        <i class="hgi-stroke hgi-ai-chat-02"></i>
    </div>
    <div class="mkv-ai-fab-text">Copilot</div>
</div>

<!-- AI Assistant Drawer Overlay -->
<div class="mkv-ai-overlay" id="mkv-ai-overlay"></div>

<!-- AI Assistant Drawer -->
<div class="mkv-ai-drawer" id="mkv-ai-drawer">
    <div class="mkv-ai-header">
        <div class="mkv-ai-header-title">
            <i class="hgi-stroke hgi-ai-chat-02"></i>
            <div>
                <h3><?php echo esc_html(get_option('mkv_ai_assistant_name', 'KiotViet Copilot')); ?></h3>
                <p>Luôn sẵn sàng hỗ trợ bạn</p>
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <button class="mkv-ai-close" id="mkv-ai-clear-history" title="Xóa lịch sử trò chuyện"><i class="hgi-stroke hgi-delete-02"></i></button>
            <button class="mkv-ai-close" id="mkv-ai-close" title="Đóng"><i class="hgi-stroke hgi-cancel-01"></i></button>
        </div>
    </div>
    
    <div class="mkv-ai-body" id="mkv-ai-body">
        <!-- Greet user -->
        <div class="mkv-ai-msg ai">
            <div class="mkv-ai-avatar"><i class="hgi-stroke hgi-ai-chat-02"></i></div>
            <div class="mkv-ai-content">
                <p>Xin chào <strong><?php echo esc_html($user->display_name); ?></strong>! 👋</p>
                <p>Tôi là <?php echo esc_html(get_option('mkv_ai_assistant_name', 'KiotViet Copilot')); ?>. Hôm nay bạn cần hỗ trợ gì về số liệu hay thao tác bán hàng không?</p>
            </div>
        </div>
        <!-- Typing Indicator -->
        <div class="mkv-ai-typing" id="mkv-ai-typing-indicator">
            <div class="mkv-ai-avatar" style="width:24px;height:24px;font-size:12px;margin-right:4px;"><i class="hgi-stroke hgi-ai-chat-02"></i></div>
            <div class="mkv-ai-content" style="padding: 8px 12px; display:flex; gap:4px; align-items:center;">
                <div class="mkv-ai-dot"></div>
                <div class="mkv-ai-dot"></div>
                <div class="mkv-ai-dot"></div>
            </div>
        </div>
    </div>

    <div class="mkv-ai-chips" id="mkv-ai-chips-container">
        <!-- Chips will be dynamically populated here -->
    </div>

    <div class="mkv-ai-footer">
        <button class="mkv-ai-mic" id="mkv-ai-mic" title="Nói để nhập"><i class="hgi-stroke hgi-mic-01"></i></button>
        <div class="mkv-ai-input-wrap">
            <textarea class="mkv-ai-input" id="mkv-ai-input" placeholder="Hỏi tôi bất cứ điều gì..." rows="1"></textarea>
        </div>
        <button class="mkv-ai-send" id="mkv-ai-send"><i class="hgi-stroke hgi-sent"></i></button>
    </div>
</div>

<script>
(function() {
    function initAICloseEvents() {
        var closeBtn = document.getElementById('mkv-ai-close');
        var overlay = document.getElementById('mkv-ai-overlay');
        if (closeBtn) closeBtn.onclick = function(e) { e.preventDefault(); if (typeof window.mkvCloseAIChat === 'function') window.mkvCloseAIChat(); };
        if (overlay) overlay.onclick = function(e) { e.preventDefault(); if (typeof window.mkvCloseAIChat === 'function') window.mkvCloseAIChat(); };
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAICloseEvents);
    } else {
        initAICloseEvents();
    }
})();
</script>
