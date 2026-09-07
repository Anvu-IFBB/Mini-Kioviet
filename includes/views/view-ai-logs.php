<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_logs = $wpdb->prefix . 'mkv_ai_logs';

// Pagination
$per_page = 20;
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($paged - 1) * $per_page;

$total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_logs");
$total_pages = ceil($total_items / $per_page);

$logs = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_logs ORDER BY created_at DESC LIMIT %d OFFSET %d",
    $per_page, $offset
));

require_once MKV_DIR . 'includes/views/header-kiotviet.php';
?>

<div class="wrap mkv-wrap mkv-ai-logs-page">
    <div class="mkv-header">
        <h1 class="wp-heading-inline"><?php echo mkv__('Lịch sử Trợ lý AI (Audit Logs)'); ?></h1>
        <p><?php echo mkv__('Bảng ghi chú toàn bộ hoạt động tương tác giữa nhân viên và Trợ lý AI.'); ?></p>
    </div>

    <div class="mkv-card mkv-ai-logs-card">
        <div class="mkv-ai-logs-table-wrap">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 15%;"><?php echo mkv__('Thời gian'); ?></th>
                    <th style="width: 15%;"><?php echo mkv__('Nhân viên'); ?></th>
                    <th style="width: 10%;"><?php echo mkv__('Loại'); ?></th>
                    <th style="width: 60%;"><?php echo mkv__('Nội dung'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)) : ?>
                    <tr>
                        <td colspan="4"><?php echo mkv__('Chưa có lịch sử trò chuyện nào.'); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($logs as $log) : 
                        $user_info = get_userdata($log->user_id);
                        $user_name = $user_info ? $user_info->display_name : 'Unknown';
                        
                        $type_label = '';
                        $type_color = '';
                        if ($log->message_type === 'user') {
                            $type_label = 'User Hỏi';
                            $type_color = 'color: #0073aa; font-weight: 500;';
                        } elseif ($log->message_type === 'ai') {
                            $type_label = 'AI Trả lời';
                            $type_color = 'color: #28a745;';
                        } elseif ($log->message_type === 'function') {
                            $type_label = 'Tool / Hàm';
                            $type_color = 'color: #dc3235; font-style: italic;';
                        }
                    ?>
                        <tr>
                            <td><?php echo date_i18n('d/m/Y H:i', strtotime($log->created_at)); ?></td>
                            <td><strong><?php echo esc_html($user_name); ?></strong></td>
                            <td style="<?php echo $type_color; ?>"><?php echo esc_html($type_label); ?></td>
                            <td>
                                <div style="max-height: 100px; overflow-y: auto; font-family: monospace; white-space: pre-wrap; background: #f9f9f9; padding: 10px; border-radius: 4px; border: 1px solid #eee;">
                                    <?php echo esc_html($log->message); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

        <?php if ($total_pages > 1) : ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <span class="pagination-links">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $total_pages,
                            'current' => $paged
                        ));
                        ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
