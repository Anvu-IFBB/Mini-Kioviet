<?php
if (!defined('ABSPATH')) exit;

/**
 * MKV_Audit_Logger
 * 
 * Centralized audit logging for security-sensitive events in Mini KiotViet.
 * Enforces privacy, zero secret leakage, safe database insertion, and access-controlled retrieval.
 */
class MKV_Audit_Logger
{
    /**
     * Allowed event categories
     */
    const ALLOWED_EVENT_TYPES = array(
        'SECURITY',
        'SETTINGS',
        'AUTHORIZATION',
        'WEBHOOK',
        'CSV',
        'EMPLOYEE',
        'ORDER',
        'AUTH',
        'GENERAL'
    );

    /**
     * Log an audit event securely.
     *
     * @param string $event_type High-level category (e.g., SETTINGS, WEBHOOK, CSV)
     * @param string $action Specific action performed (e.g., CHANGE_WEBHOOK_SECRET)
     * @param string $object_type Target object category (e.g., option, shipping_webhook)
     * @param string $object_id Identifier of target object
     * @param string $description Safe human-readable summary
     * @param int|null $user_id User who performed action (null for current user)
     * @return int|false Inserted log ID or false on failure
     */
    public static function log($event_type, $action, $object_type = '', $object_id = '', $description = '', $user_id = null)
    {
        global $wpdb;

        // Sanitize and validate event type
        $event_type = strtoupper(sanitize_text_field($event_type));
        if (!in_array($event_type, self::ALLOWED_EVENT_TYPES, true)) {
            $event_type = 'GENERAL';
        }

        $action = strtoupper(sanitize_text_field($action));
        $object_type = sanitize_text_field($object_type);
        $object_id = sanitize_text_field((string)$object_id);

        // Resolve user ID
        if ($user_id === null) {
            $user_id = get_current_user_id();
        } else {
            $user_id = absint($user_id);
        }

        // Resolve client IP and User Agent safely
        $ip_address = '';
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $raw_ip = wp_unslash($_SERVER['REMOTE_ADDR']);
            $ip_address = filter_var($raw_ip, FILTER_VALIDATE_IP) ? $raw_ip : '';
        }

        $user_agent = '';
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $user_agent = sanitize_text_field(substr(wp_unslash($_SERVER['HTTP_USER_AGENT']), 0, 255));
        }

        // Scrub any accidental secrets from description
        $scrubbed_desc = self::scrub_sensitive_data($description);
        if (mb_strlen($scrubbed_desc, 'UTF-8') > 65000) {
            $scrubbed_desc = mb_substr($scrubbed_desc, 0, 65000, 'UTF-8') . '...[TRUNCATED]';
        }

        $table_name = "{$wpdb->prefix}mkv_audit_logs";

        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id'     => $user_id,
                'event_type'  => $event_type,
                'action'      => $action,
                'object_type' => $object_type,
                'object_id'   => $object_id,
                'description' => $scrubbed_desc,
                'ip_address'  => $ip_address,
                'user_agent'  => $user_agent,
                'created_at'  => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        return $result ? (int) $wpdb->insert_id : false;
    }

    /**
     * Remove or mask any sensitive tokens, secrets, or passwords from strings.
     *
     * @param string $text
     * @return string
     */
    public static function scrub_sensitive_data($text)
    {
        if (!is_string($text) || empty($text)) {
            return '';
        }

        // Scrub active configured secrets if present
        $secrets_to_mask = array_filter(array(
            get_option('mkv_webhook_secret', ''),
            get_option('mkv_shipping_token', ''),
            get_option('mkv_ghtk_token', ''),
            get_option('mkv_ghn_token', ''),
            get_option('mkv_gemini_api_key', '')
        ));

        foreach ($secrets_to_mask as $secret) {
            $secret = trim((string)$secret);
            if (strlen($secret) >= 4) {
                $text = str_replace($secret, '[REDACTED_SECRET]', $text);
            }
        }

        // Scrub standard patterns: Bearer tokens, passwords, authorization headers
        $patterns = array(
            '/Bearer\s+[A-Za-z0-9_\-\.\$\/]+/i'           => 'Bearer [REDACTED_TOKEN]',
            '/Basic\s+[A-Za-z0-9+\/]+=*/i'                => 'Basic [REDACTED_AUTH]',
            '/(password|passwd|pwd)\s*[:=]\s*[^\s,]+/i'    => '$1=[REDACTED_PASSWORD]',
            '/(token|secret|api_key)\s*[:=]\s*[^\s,]+/i'   => '$1=[REDACTED_TOKEN]',
            '/AIzaSy[A-Za-z0-9_-]{20,50}/'                 => '[REDACTED_API_KEY]',
            '/(cookie|sessionid)\s*[:=]\s*[^\s,]+/i'       => '$1=[REDACTED_COOKIE]',
            '/nonce\s*[:=]\s*[a-f0-9]{10}/i'               => 'nonce=[REDACTED_NONCE]',
        );

        return preg_replace(array_keys($patterns), array_values($patterns), $text);
    }

    /**
     * Query audit logs with parameterized filtering.
     *
     * @param array $args
     * @return array
     */
    public static function get_logs($args = array())
    {
        global $wpdb;

        $table_name = "{$wpdb->prefix}mkv_audit_logs";
        $where_clauses = array('1=1');
        $params = array();

        if (!empty($args['event_type']) && in_array(strtoupper($args['event_type']), self::ALLOWED_EVENT_TYPES, true)) {
            $where_clauses[] = 'event_type = %s';
            $params[] = strtoupper(sanitize_text_field($args['event_type']));
        }

        if (!empty($args['action'])) {
            $where_clauses[] = 'action = %s';
            $params[] = strtoupper(sanitize_text_field($args['action']));
        }

        if (isset($args['user_id']) && $args['user_id'] !== '' && $args['user_id'] !== 'all') {
            $where_clauses[] = 'user_id = %d';
            $params[] = absint($args['user_id']);
        }

        if (!empty($args['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $args['date_from'])) {
            $where_clauses[] = 'created_at >= %s';
            $params[] = sanitize_text_field($args['date_from']) . ' 00:00:00';
        }

        if (!empty($args['date_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $args['date_to'])) {
            $where_clauses[] = 'created_at <= %s';
            $params[] = sanitize_text_field($args['date_to']) . ' 23:59:59';
        }

        if (!empty($args['search'])) {
            $like = '%' . $wpdb->esc_like(sanitize_text_field($args['search'])) . '%';
            $where_clauses[] = '(description LIKE %s OR object_id LIKE %s OR ip_address LIKE %s)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $limit = isset($args['limit']) ? max(1, absint($args['limit'])) : 20;
        $offset = isset($args['offset']) ? max(0, absint($args['offset'])) : 0;

        $allowed_orderbys = array('id', 'created_at', 'event_type', 'action', 'user_id');
        $orderby = isset($args['orderby']) && in_array($args['orderby'], $allowed_orderbys, true) ? $args['orderby'] : 'id';
        $order = isset($args['order']) && strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $where_sql = implode(' AND ', $where_clauses);
        $sql = "SELECT * FROM {$table_name} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        $prepared = $wpdb->prepare($sql, $params);
        return $wpdb->get_results($prepared);
    }

    /**
     * Count total audit logs matching criteria.
     *
     * @param array $args
     * @return int
     */
    public static function count_logs($args = array())
    {
        global $wpdb;

        $table_name = "{$wpdb->prefix}mkv_audit_logs";
        $where_clauses = array('1=1');
        $params = array();

        if (!empty($args['event_type']) && in_array(strtoupper($args['event_type']), self::ALLOWED_EVENT_TYPES, true)) {
            $where_clauses[] = 'event_type = %s';
            $params[] = strtoupper(sanitize_text_field($args['event_type']));
        }

        if (!empty($args['action'])) {
            $where_clauses[] = 'action = %s';
            $params[] = strtoupper(sanitize_text_field($args['action']));
        }

        if (isset($args['user_id']) && $args['user_id'] !== '' && $args['user_id'] !== 'all') {
            $where_clauses[] = 'user_id = %d';
            $params[] = absint($args['user_id']);
        }

        if (!empty($args['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $args['date_from'])) {
            $where_clauses[] = 'created_at >= %s';
            $params[] = sanitize_text_field($args['date_from']) . ' 00:00:00';
        }

        if (!empty($args['date_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $args['date_to'])) {
            $where_clauses[] = 'created_at <= %s';
            $params[] = sanitize_text_field($args['date_to']) . ' 23:59:59';
        }

        if (!empty($args['search'])) {
            $like = '%' . $wpdb->esc_like(sanitize_text_field($args['search'])) . '%';
            $where_clauses[] = '(description LIKE %s OR object_id LIKE %s OR ip_address LIKE %s)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $where_sql = implode(' AND ', $where_clauses);
        $sql = "SELECT COUNT(1) FROM {$table_name} WHERE {$where_sql}";

        if (!empty($params)) {
            return (int) $wpdb->get_var($wpdb->prepare($sql, $params));
        }

        return (int) $wpdb->get_var($sql);
    }

    /**
     * Clear audit logs. Requires administrator manage_options capability.
     *
     * @return bool
     */
    public static function clear_logs()
    {
        if (!current_user_can('manage_options')) {
            return false;
        }

        global $wpdb;
        $table_name = "{$wpdb->prefix}mkv_audit_logs";
        $wpdb->query("TRUNCATE TABLE `{$table_name}`");

        self::log('SECURITY', 'AUDIT_LOG_PURGE', 'audit_table', $table_name, 'Tất cả nhật ký kiểm toán đã được dọn sạch bởi quản trị viên.');
        return true;
    }

    /**
     * Purge expired audit logs based on configured retention days.
     * Uses batched deletion (DELETE ... LIMIT 500) to prevent table-locking.
     *
     * @return int Number of deleted records
     */
    public static function purge_expired_logs()
    {
        global $wpdb;
        $days = max(30, min(3650, (int) get_option('mkv_audit_retention_days', 365)));
        $cutoff = gmdate('Y-m-d H:i:s', time() - ($days * DAY_IN_SECONDS));
        $table_name = "{$wpdb->prefix}mkv_audit_logs";

        $total_deleted = 0;
        $batch_size = 500;

        do {
            $deleted = $wpdb->query($wpdb->prepare(
                "DELETE FROM `{$table_name}` WHERE created_at < %s LIMIT %d",
                $cutoff,
                $batch_size
            ));
            if ($deleted === false || $deleted === 0) {
                break;
            }
            $total_deleted += (int) $deleted;
        } while ($deleted >= $batch_size);

        if ($total_deleted > 0) {
            self::log(
                'SECURITY',
                'AUDIT_LOG_RETENTION_CLEANUP',
                'audit_table',
                $table_name,
                sprintf('Audit log retention cleanup completed. Deleted %d records.', $total_deleted)
            );
        }

        return $total_deleted;
    }

    /**
     * Get aggregated security monitoring summary for the last N days.
     * Fast, single query using indexed created_at.
     *
     * @param int $days
     * @return array
     */
    public static function get_security_summary($days = 30)
    {
        global $wpdb;
        $days = max(1, min(365, absint($days)));
        $cutoff = gmdate('Y-m-d H:i:s', time() - ($days * DAY_IN_SECONDS));
        $table_name = "{$wpdb->prefix}mkv_audit_logs";

        $sql = "SELECT 
            SUM(CASE WHEN event_type = 'WEBHOOK' AND action IN ('INVALID_SIGNATURE', 'INVALID_METHOD', 'INVALID_CONTENT_TYPE', 'PAYLOAD_TOO_LARGE', 'INVALID_JSON', 'INVALID_PAYLOAD') THEN 1 ELSE 0 END) as invalid_webhooks,
            SUM(CASE WHEN action = 'IP_REJECTED' THEN 1 ELSE 0 END) as blocked_ips,
            SUM(CASE WHEN action = 'RATE_LIMITED' THEN 1 ELSE 0 END) as rate_limited,
            SUM(CASE WHEN event_type = 'SETTINGS' THEN 1 ELSE 0 END) as settings_changes,
            SUM(CASE WHEN action IN ('AUDIT_LOG_PURGE', 'CLEAR_AUDIT_LOGS', 'AUDIT_LOG_RETENTION_CLEANUP') OR event_type = 'SECURITY' THEN 1 ELSE 0 END) as security_events
        FROM `{$table_name}`
        WHERE created_at >= %s";

        $row = $wpdb->get_row($wpdb->prepare($sql, $cutoff), ARRAY_A);

        return array(
            'invalid_webhooks' => (int) ($row['invalid_webhooks'] ?? 0),
            'blocked_ips'      => (int) ($row['blocked_ips'] ?? 0),
            'rate_limited'     => (int) ($row['rate_limited'] ?? 0),
            'settings_changes' => (int) ($row['settings_changes'] ?? 0),
            'security_events'  => (int) ($row['security_events'] ?? 0),
        );
    }
}

/**
 * Procedural helper for logging audit events.
 */
function mkv_audit_log($event_type, $action, $object_type = '', $object_id = '', $description = '', $user_id = null)
{
    return MKV_Audit_Logger::log($event_type, $action, $object_type, $object_id, $description, $user_id);
}
