/* Notifications polling JS - Mini KiotViet v3.0 (Optimized Lifecycle & Anti-Overlap) */
;(function ($) {
    'use strict';

    if (!window.mkv_notif || !window.mkv_notif.root) {
        return;
    }

    var lastOrdersCount = -1;
    var lastAlertsCount = -1;

    // Polling lifecycle & concurrency control
    var pollTimer       = null;
    var currentXhr      = null;
    var isPolling       = false;
    var latestRequestId = 0;
    var pollInterval    = parseInt(window.mkv_notif.interval, 10) || 45000;

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

    function updateBadges(total) {
        // 1. Cập nhật menu WP sidebar
        var $badge = $('#toplevel_page_mini-kiotviet .awaiting-mod .pending-count');
        if ($badge.length) {
            if (total > 0) {
                $badge.text(total);
                $badge.closest('.awaiting-mod').show();
            } else {
                $badge.closest('.awaiting-mod').hide();
            }
        }

        // 2. Cập nhật icon chuông trên KiotViet topbar
        var $topbarIcon = $('.mkv-header-icon[href*="page=mkv-notifications"]');
        if ($topbarIcon.length) {
            var $topbarBadge = $topbarIcon.find('.mkv-notif-badge');
            if (total > 0) {
                var label = total > 9 ? '9+' : total;
                if ($topbarBadge.length) {
                    $topbarBadge.text(label).show();
                } else {
                    $topbarIcon.append('<span class="mkv-notif-badge">' + label + '</span>');
                }
            } else if ($topbarBadge.length) {
                $topbarBadge.hide();
            }
        }
    }

    function stopPolling(abortInFlight) {
        if (pollTimer) {
            clearTimeout(pollTimer);
            pollTimer = null;
        }
        if (abortInFlight && currentXhr && typeof currentXhr.abort === 'function') {
            try {
                currentXhr.abort();
            } catch (e) {}
            currentXhr = null;
            isPolling = false;
        }
    }

    function scheduleNextPoll(delay) {
        stopPolling(false);
        if (document.visibilityState === 'hidden') {
            return; // Pause while tab is hidden
        }
        var nextDelay = (typeof delay === 'number' && delay >= 0) ? delay : pollInterval;
        pollTimer = setTimeout(poll, nextDelay);
    }

    function poll() {
        if (document.visibilityState === 'hidden') {
            return;
        }
        if (isPolling) {
            return; // Maximum 1 in-flight request guard
        }

        isPolling = true;
        var requestId = ++latestRequestId;

        currentXhr = $.ajax({
            url: mkv_notif.root + 'notifications/unread-count',
            method: 'GET',
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', mkv_notif.nonce);
            },
            success: function (data) {
                // Stale response protection
                if (requestId !== latestRequestId) {
                    return;
                }
                if (!data || typeof data !== 'object') {
                    return;
                }

                var total = data.total || 0;
                updateBadges(total);

                // Hiển thị toast nếu có đơn hàng mới
                var i18n = (window.mkv_notif && window.mkv_notif.i18n) || {};
                if (lastOrdersCount >= 0 && data.orders > lastOrdersCount) {
                    var orderTitle = i18n.new_order_title || 'Có đơn hàng mới!';
                    var orderBody = i18n.new_order_body || 'Hệ thống vừa ghi nhận đơn hàng mới.';
                    showToast('<i class="hgi-stroke hgi-shopping-bag-02" style="margin-right:6px; color:#0070f3;"></i> ' + orderTitle, orderBody, 'order');
                    playNotificationChime();
                }

                // Hiển thị toast nếu có cảnh báo hệ thống mới
                if (lastAlertsCount >= 0 && data.alerts > lastAlertsCount) {
                    var alertTitle = i18n.system_alert_title || 'Cảnh báo hệ thống';
                    var alertBody = i18n.system_alert_body || 'Bạn có thông báo mới (hết hàng, dưới định mức...) trong mục Thông Báo.';
                    showToast('<i class="hgi-stroke hgi-alert-02" style="margin-right:6px; color:#f59e0b;"></i> ' + alertTitle, alertBody, 'warning');
                }

                lastOrdersCount = data.orders;
                lastAlertsCount = data.alerts;
            },
            error: function (xhr, textStatus) {
                // Ignore intentional aborts when tab is switched
                if (textStatus === 'abort') {
                    return;
                }
            },
            complete: function () {
                currentXhr = null;
                isPolling = false;
                // Schedule next poll only after this request has completely finished
                scheduleNextPoll();
            }
        });
    }

    // Single document-level VisibilityChange listener
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            stopPolling(true);
        } else if (document.visibilityState === 'visible') {
            // Resume polling cleanly: run fresh check after 1s, then regular 45s interval
            scheduleNextPoll(1000);
        }
    });

    // Cleanup on page unload/hide
    window.addEventListener('pagehide', function () {
        stopPolling(true);
    });

    // CSS animation (added once)
    if (!$('#mkv-poll-style').length) {
        $('<style id="mkv-poll-style">')
            .text('@keyframes mkvSlideIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:translateX(0)}}')
            .appendTo('head');
    }

    // Initial boot: run first poll after 2s, then cycle automatically via complete()
    scheduleNextPoll(2000);

}(jQuery));
