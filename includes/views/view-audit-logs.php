<?php
if (!defined('ABSPATH')) exit;

if (!current_user_can('mkv_view_audit_logs') && !current_user_can('manage_options')) {
    wp_die('Bạn không có quyền xem nhật ký kiểm toán.', 'Lỗi phân quyền', array('response' => 403));
}

require_once MKV_DIR . 'includes/views/header-kiotviet.php';

$paged       = max(1, absint($_GET['paged'] ?? 1));
$per_page    = 25;
$offset      = ($paged - 1) * $per_page;

$event_type  = sanitize_text_field($_GET['event_type'] ?? '');
$action_flt  = sanitize_text_field($_GET['action_filter'] ?? '');
$user_id_flt = sanitize_text_field($_GET['user_id'] ?? '');
$date_from   = sanitize_text_field($_GET['date_from'] ?? '');
$date_to     = sanitize_text_field($_GET['date_to'] ?? '');
$search      = sanitize_text_field($_GET['s'] ?? '');

$filter_args = array(
    'event_type' => $event_type,
    'action'     => $action_flt,
    'user_id'    => $user_id_flt,
    'date_from'  => $date_from,
    'date_to'    => $date_to,
    'search'     => $search,
    'limit'      => $per_page,
    'offset'     => $offset
);

$logs = MKV_Audit_Logger::get_logs($filter_args);
$total_logs = MKV_Audit_Logger::count_logs($filter_args);
$total_pages = ceil($total_logs / $per_page);

// Base export URL
$export_params = array(
    'action'        => 'mkv_export_audit_logs',
    'nonce'         => wp_create_nonce('mkv_audit_nonce'),
    'event_type'    => $event_type,
    'action_filter' => $action_flt,
    'user_id'       => $user_id_flt,
    'date_from'     => $date_from,
    'date_to'       => $date_to,
    's'             => $search
);
$export_url = add_query_arg($export_params, admin_url('admin-ajax.php'));
?>

<div class="mkv-main-container" style="padding: 24px; max-width: 1400px; margin: 0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <div>
            <h2 style="margin:0; font-size:22px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
                <i class="hgi-stroke hgi-security-check" style="color:var(--mkv-primary);"></i>
                <?php echo esc_html(mkv__('Nhật Ký Kiểm Toán & Bảo Mật')); ?>
            </h2>
            <p style="margin:4px 0 0 0; color:#64748b; font-size:13px;">
                <?php echo esc_html(mkv__('Ghi nhận và giám sát toàn bộ các thay đổi cấu hình, phân quyền và sự kiện an ninh hệ thống.')); ?>
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="<?php echo esc_url($export_url); ?>" class="mkv-btn mkv-btn-outline" style="display:flex; align-items:center; gap:6px; font-size:13px; text-decoration:none;">
                <i class="hgi-stroke hgi-download-04"></i> <?php echo esc_html(mkv__('Xuất CSV')); ?>
            </a>
            <?php if (current_user_can('manage_options')): ?>
            <button type="button" id="btn-clear-audit-logs" class="mkv-btn" style="background:#fee2e2; color:#dc2626; border:1px solid #fca5a5; display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                <i class="hgi-stroke hgi-delete-02"></i> <?php echo esc_html(mkv__('Xóa toàn bộ nhật ký')); ?>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $sec_summary = MKV_Audit_Logger::get_security_summary(30);
    ?>
    <!-- Thống kê An ninh 30 ngày gần nhất -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom: 20px;">
        <div class="mkv-card" style="padding:16px; display:flex; align-items:center; gap:12px; background:#fff;">
            <div style="width:42px; height:42px; border-radius:8px; background:#fef2f2; color:#ef4444; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="hgi-stroke hgi-shield-cross"></i>
            </div>
            <div>
                <div style="font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase;"><?php echo esc_html(mkv__('Webhook lỗi')); ?></div>
                <div style="font-size:20px; font-weight:700; color:#1e293b;"><?php echo esc_html(number_format_i18n($sec_summary['invalid_webhooks'])); ?></div>
            </div>
        </div>

        <div class="mkv-card" style="padding:16px; display:flex; align-items:center; gap:12px; background:#fff;">
            <div style="width:42px; height:42px; border-radius:8px; background:#fff7ed; color:#f97316; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="hgi-stroke hgi-computer-programming-01"></i>
            </div>
            <div>
                <div style="font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase;"><?php echo esc_html(mkv__('IP bị chặn')); ?></div>
                <div style="font-size:20px; font-weight:700; color:#1e293b;"><?php echo esc_html(number_format_i18n($sec_summary['blocked_ips'])); ?></div>
            </div>
        </div>

        <div class="mkv-card" style="padding:16px; display:flex; align-items:center; gap:12px; background:#fff;">
            <div style="width:42px; height:42px; border-radius:8px; background:#fefce8; color:#eab308; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="hgi-stroke hgi-dashboard-speed-01"></i>
            </div>
            <div>
                <div style="font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase;"><?php echo esc_html(mkv__('Rate Limit')); ?></div>
                <div style="font-size:20px; font-weight:700; color:#1e293b;"><?php echo esc_html(number_format_i18n($sec_summary['rate_limited'])); ?></div>
            </div>
        </div>

        <div class="mkv-card" style="padding:16px; display:flex; align-items:center; gap:12px; background:#fff;">
            <div style="width:42px; height:42px; border-radius:8px; background:#eff6ff; color:#3b82f6; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="hgi-stroke hgi-settings-01"></i>
            </div>
            <div>
                <div style="font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase;"><?php echo esc_html(mkv__('Đổi cài đặt')); ?></div>
                <div style="font-size:20px; font-weight:700; color:#1e293b;"><?php echo esc_html(number_format_i18n($sec_summary['settings_changes'])); ?></div>
            </div>
        </div>

        <div class="mkv-card" style="padding:16px; display:flex; align-items:center; gap:12px; background:#fff;">
            <div style="width:42px; height:42px; border-radius:8px; background:#f0fdf4; color:#22c55e; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="hgi-stroke hgi-security-check"></i>
            </div>
            <div>
                <div style="font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase;"><?php echo esc_html(mkv__('Sự kiện An ninh')); ?></div>
                <div style="font-size:20px; font-weight:700; color:#1e293b;"><?php echo esc_html(number_format_i18n($sec_summary['security_events'])); ?></div>
            </div>
        </div>
    </div>

    <!-- Bộ lọc tìm kiếm -->
    <div class="mkv-card" style="padding: 16px; margin-bottom: 20px;">
        <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
            <input type="hidden" name="page" value="mkv-audit-logs">

            <div style="flex:1; min-width:180px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:4px;"><?php echo esc_html(mkv__('Tìm kiếm nội dung')); ?></label>
                <input type="text" name="s" class="mkv-input" style="width:100%;" placeholder="<?php echo esc_attr(mkv__('Mô tả, ID đối tượng, IP...')); ?>" value="<?php echo esc_attr($search); ?>">
            </div>

            <div style="width:150px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:4px;"><?php echo esc_html(mkv__('Loại sự kiện')); ?></label>
                <select name="event_type" class="mkv-select" style="width:100%;">
                    <option value=""><?php echo esc_html(mkv__('Tất cả')); ?></option>
                    <?php foreach (MKV_Audit_Logger::ALLOWED_EVENT_TYPES as $type): ?>
                        <option value="<?php echo esc_attr($type); ?>" <?php selected($event_type, $type); ?>><?php echo esc_html($type); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="width:180px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:4px;"><?php echo esc_html(mkv__('Hành động (Action)')); ?></label>
                <input type="text" name="action_filter" class="mkv-input" style="width:100%;" placeholder="<?php echo esc_attr(mkv__('VD: UPDATE_SETTINGS')); ?>" value="<?php echo esc_attr($action_flt); ?>">
            </div>

            <div style="width:140px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:4px;"><?php echo esc_html(mkv__('Từ ngày')); ?></label>
                <input type="date" name="date_from" class="mkv-input" style="width:100%;" value="<?php echo esc_attr($date_from); ?>">
            </div>

            <div style="width:140px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:4px;"><?php echo esc_html(mkv__('Đến ngày')); ?></label>
                <input type="date" name="date_to" class="mkv-input" style="width:100%;" value="<?php echo esc_attr($date_to); ?>">
            </div>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="mkv-btn mkv-btn-primary" style="height:38px; padding:0 16px; font-size:13px;">
                    <i class="hgi-stroke hgi-filter"></i> <?php echo esc_html(mkv__('Lọc')); ?>
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mkv-audit-logs')); ?>" class="mkv-btn mkv-btn-outline" style="height:38px; padding:0 12px; font-size:13px; text-decoration:none; display:flex; align-items:center;">
                    <?php echo esc_html(mkv__('Đặt lại')); ?>
                </a>
            </div>
        </form>
    </div>

    <!-- Bảng danh sách nhật ký -->
    <div class="mkv-card" style="padding:0; overflow:hidden;">
        <table class="mkv-table" style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; color:#475569; font-weight:600;">
                    <th style="padding:12px 16px; width:60px;">ID</th>
                    <th style="padding:12px 16px; width:150px;"><?php echo esc_html(mkv__('Thời gian')); ?></th>
                    <th style="padding:12px 16px; width:160px;"><?php echo esc_html(mkv__('Người dùng')); ?></th>
                    <th style="padding:12px 16px; width:110px;"><?php echo esc_html(mkv__('Sự kiện')); ?></th>
                    <th style="padding:12px 16px; width:180px;"><?php echo esc_html(mkv__('Hành động')); ?></th>
                    <th style="padding:12px 16px; width:140px;"><?php echo esc_html(mkv__('Đối tượng')); ?></th>
                    <th style="padding:12px 16px;"><?php echo esc_html(mkv__('Mô tả')); ?></th>
                    <th style="padding:12px 16px; width:120px;"><?php echo esc_html(mkv__('IP')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                        // Badge color mapping
                        $badge_bg = '#f1f5f9';
                        $badge_color = '#475569';
                        switch ($log->event_type) {
                            case 'SECURITY':
                                $badge_bg = '#fee2e2'; $badge_color = '#dc2626'; break;
                            case 'SETTINGS':
                                $badge_bg = '#dbeafe'; $badge_color = '#2563eb'; break;
                            case 'AUTHORIZATION':
                                $badge_bg = '#f3e8ff'; $badge_color = '#9333ea'; break;
                            case 'WEBHOOK':
                                $badge_bg = '#fef3c7'; $badge_color = '#d97706'; break;
                            case 'CSV':
                                $badge_bg = '#ccfbf1'; $badge_color = '#0d9488'; break;
                            case 'EMPLOYEE':
                                $badge_bg = '#dcfce7'; $badge_color = '#16a34a'; break;
                        }

                        $user_name = 'Hệ thống / Khách';
                        if ($log->user_id > 0) {
                            $u = get_userdata($log->user_id);
                            if ($u) {
                                $user_name = $u->display_name . ' (' . $u->user_login . ')';
                            } else {
                                $user_name = 'User #' . $log->user_id;
                            }
                        }
                        ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:12px 16px; color:#94a3b8;"><?php echo esc_html($log->id); ?></td>
                            <td style="padding:12px 16px; color:#64748b; white-space:nowrap;"><?php echo esc_html($log->created_at); ?></td>
                            <td style="padding:12px 16px; font-weight:500; color:#1e293b;"><?php echo esc_html($user_name); ?></td>
                            <td style="padding:12px 16px;">
                                <span style="background:<?php echo esc_attr($badge_bg); ?>; color:<?php echo esc_attr($badge_color); ?>; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600;">
                                    <?php echo esc_html($log->event_type); ?>
                                </span>
                            </td>
                            <td style="padding:12px 16px; font-family:monospace; font-size:12px; font-weight:600; color:#334155;">
                                <?php echo esc_html($log->action); ?>
                            </td>
                            <td style="padding:12px 16px; color:#64748b;">
                                <?php if (!empty($log->object_type)): ?>
                                    <span style="color:#475569; font-weight:500;"><?php echo esc_html($log->object_type); ?></span>
                                    <?php if (!empty($log->object_id)): ?>
                                        <span style="color:#94a3b8;">: <?php echo esc_html($log->object_id); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px 16px; color:#334155; line-height:1.4;">
                                <?php echo esc_html($log->description); ?>
                            </td>
                            <td style="padding:12px 16px; font-family:monospace; font-size:11px; color:#64748b; white-space:nowrap;">
                                <?php echo esc_html($log->ip_address ?: '-'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="padding:36px; text-align:center; color:#94a3b8;">
                            <i class="hgi-stroke hgi-note" style="font-size:28px; display:block; margin-bottom:8px;"></i>
                            <?php echo esc_html(mkv__('Không có bản ghi nhật ký kiểm toán nào phù hợp.')); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px; background:#f8fafc; border-top:1px solid #e2e8f0; font-size:13px; color:#64748b;">
            <div>
                <?php printf(esc_html(mkv__('Hiển thị %d - %d trong tổng số %d bản ghi')), min($total_logs, $offset + 1), min($total_logs, $offset + $per_page), $total_logs); ?>
            </div>
            <div style="display:flex; gap:6px;">
                <?php if ($paged > 1): ?>
                    <a href="<?php echo esc_url(add_query_arg('paged', $paged - 1)); ?>" class="mkv-btn mkv-btn-outline" style="padding:4px 10px; font-size:12px; text-decoration:none;">&laquo; <?php echo esc_html(mkv__('Trước')); ?></a>
                <?php endif; ?>
                <span style="padding:4px 8px; font-weight:600; color:#1e293b;"><?php echo esc_html($paged . ' / ' . $total_pages); ?></span>
                <?php if ($paged < $total_pages): ?>
                    <a href="<?php echo esc_url(add_query_arg('paged', $paged + 1)); ?>" class="mkv-btn mkv-btn-outline" style="padding:4px 10px; font-size:12px; text-decoration:none;"><?php echo esc_html(mkv__('Sau')); ?> &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (current_user_can('manage_options')): ?>
<script>
jQuery(document).ready(function($) {
    $('#btn-clear-audit-logs').on('click', function(e) {
        e.preventDefault();
        if (!confirm('<?php echo esc_js(mkv__('CẢNH BÁO BẢO MẬT: Bạn có chắc chắn muốn xóa sạch toàn bộ nhật ký kiểm toán không? Thao tác này không thể hoàn tác.')); ?>')) {
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).text('Đang xóa...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mkv_clear_audit_logs',
                nonce: '<?php echo wp_create_nonce("mkv_audit_nonce"); ?>'
            },
            success: function(res) {
                if (res && res.success) {
                    alert(res.data && res.data.message ? res.data.message : 'Đã xóa nhật ký.');
                    window.location.reload();
                } else {
                    alert(res && res.data && res.data.message ? res.data.message : 'Không thể xóa nhật ký.');
                    $btn.prop('disabled', false).text('Xóa toàn bộ nhật ký');
                }
            },
            error: function() {
                alert('Không thể kết nối máy chủ.');
                $btn.prop('disabled', false).text('Xóa toàn bộ nhật ký');
            }
        });
    });
});
</script>
<?php endif; ?>

<script>document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Nhật Ký Bảo Mật')); ?>');</script>
<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>
