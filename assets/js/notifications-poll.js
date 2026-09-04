/* Notifications polling JS - Mini KiotViet v2.1 */
;(function ($) {
    var lastOrdersCount = -1;
    var lastAlertsCount = -1;

    function playNotificationChime() {
        try {
            var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            var osc = audioCtx.createOscillator();
            var gainNode = audioCtx.createGain();
            
            osc.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880.0, audioCtx.currentTime); // A5
            gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
            gainNode.gain.linearRampToValueAtTime(0.5, audioCtx.currentTime + 0.05);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
            
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + 0.5);
            
            setTimeout(function() {
                var osc2 = audioCtx.createOscillator();
                var gain2 = audioCtx.createGain();
                osc2.connect(gain2);
                gain2.connect(audioCtx.destination);
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(1046.50, audioCtx.currentTime); // C6
                gain2.gain.setValueAtTime(0, audioCtx.currentTime);
                gain2.gain.linearRampToValueAtTime(0.5, audioCtx.currentTime + 0.05);
                gain2.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
                osc2.start(audioCtx.currentTime);
                osc2.stop(audioCtx.currentTime + 0.5);
            }, 150);
        } catch (e) {
            // AudioContext not supported or user interaction required
        }
    }

    function checkNotifications() {
        $.ajax({
            url: mkv_notif.root + 'notifications/unread-count',
            method: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', mkv_notif.nonce);
            },
            success: function (data) {
                var total = data.total || 0;

                // Cập nhật badge menu
                var $badge = $('#toplevel_page_mini-kiotviet .awaiting-mod .pending-count');
                if (total > 0) {
                    if ($badge.length) {
                        $badge.text(total);
                    }
                }

                // Hiển thị toast nếu có đơn mới (tracking orders count)
                if (lastOrdersCount >= 0 && data.orders > lastOrdersCount) {
                    showToast('🛒 Có đơn hàng mới!', 'Hệ thống vừa ghi nhận đơn hàng mới.', 'order');
                    playNotificationChime();
                }
                
                // Hiển thị toast nếu có cảnh báo hệ thống mới (tracking alerts count)
                if (lastAlertsCount >= 0 && data.alerts > lastAlertsCount) {
                    showToast('⚠️ Cảnh báo hệ thống', 'Bạn có thông báo mới (hết hàng, dưới định mức...) trong mục Thông Báo.', 'warning');
                }

                lastOrdersCount = data.orders;
                lastAlertsCount = data.alerts;
            }
        });
    }

    function showToast(title, message, type) {
        var colors = { order: '#0070f3', warning: '#f59e0b', info: '#10b981' };
        var color  = colors[type] || '#1e293b';

        var $toast = $('<div id="mkv-toast" style="' +
            'position:fixed; bottom:24px; right:24px; z-index:99999;' +
            'background:#fff; border-left:4px solid ' + color + ';' +
            'border-radius:12px; padding:16px 20px; min-width:280px;' +
            'box-shadow:0 8px 30px rgba(0,0,0,.15);' +
            'font-family:Inter,sans-serif; animation:mkvSlideIn .3s ease;' +
            '">');

        $toast.html('<div style="display:flex;justify-content:space-between;align-items:flex-start;">' +
            '<div><strong style="color:#1e293b;font-size:14px;">' + title + '</strong>' +
            '<p style="margin:4px 0 0;font-size:13px;color:#64748b;">' + message + '</p></div>' +
            '<button onclick="$(\'#mkv-toast\').remove()" style="background:none;border:none;cursor:pointer;color:#aaa;font-size:18px;line-height:1;margin-left:12px;">×</button>' +
            '</div>');

        $('#mkv-toast').remove();
        $('body').append($toast);

        setTimeout(function () { $toast.fadeOut(400, function () { $(this).remove(); }); }, 6000);
    }

    // CSS animation
    $('<style>')
        .text('@keyframes mkvSlideIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:translateX(0)}}')
        .appendTo('head');

    // Chạy ngay sau 2s và sau đó mỗi 45s
    setTimeout(checkNotifications, 2000);
    setInterval(checkNotifications, mkv_notif.interval || 45000);

}(jQuery));
