<?php
if (!defined('ABSPATH')) exit;

class MKV_Webhook
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {
        // GHTK Webhook Route
        register_rest_route('mkv/v1', '/webhook/ghtk', array(
            array(
                'methods'             => 'POST',
                'callback'            => array($this, 'handle_ghtk_webhook'),
                'permission_callback' => array($this, 'authorize_webhook')
            ),
            array(
                'methods'             => array('GET', 'PUT', 'DELETE', 'PATCH'),
                'callback'            => array($this, 'reject_invalid_method'),
                'permission_callback' => '__return_true'
            )
        ));

        // GHN Webhook Route
        register_rest_route('mkv/v1', '/webhook/ghn', array(
            array(
                'methods'             => 'POST',
                'callback'            => array($this, 'handle_ghn_webhook'),
                'permission_callback' => array($this, 'authorize_webhook')
            ),
            array(
                'methods'             => array('GET', 'PUT', 'DELETE', 'PATCH'),
                'callback'            => array($this, 'reject_invalid_method'),
                'permission_callback' => '__return_true'
            )
        ));
    }

    /**
     * Reject unsupported HTTP methods with 405 Method Not Allowed.
     */
    public function reject_invalid_method(WP_REST_Request $request)
    {
        if (class_exists('MKV_Audit_Logger')) {
            MKV_Audit_Logger::log(
                'WEBHOOK',
                'INVALID_METHOD',
                'shipping_webhook',
                $request->get_route(),
                'HTTP method ' . $request->get_method() . ' is not allowed on webhook endpoints.'
            );
        }
        return new WP_Error('mkv_method_not_allowed', 'Method Not Allowed', array('status' => 405));
    }

    /**
     * Authorize incoming webhook: HTTP method, Content-Type, Request Size, Rate Limit, IP Allowlist, Secret.
     */
    public function authorize_webhook(WP_REST_Request $request)
    {
        // 1. HTTP Method Enforcement
        if ($request->get_method() !== 'POST') {
            if (class_exists('MKV_Audit_Logger')) {
                MKV_Audit_Logger::log('WEBHOOK', 'INVALID_METHOD', 'shipping_webhook', $request->get_route(), 'Method ' . $request->get_method() . ' not allowed');
            }
            return new WP_Error('mkv_method_not_allowed', 'Method Not Allowed', array('status' => 405));
        }

        // 2. Content-Type Validation
        $content_type = (string) $request->get_header('content-type');
        $is_json = stripos($content_type, 'application/json') !== false;
        $is_form = stripos($content_type, 'application/x-www-form-urlencoded') !== false || stripos($content_type, 'multipart/form-data') !== false;
        if (!empty($content_type) && !$is_json && !$is_form) {
            if (class_exists('MKV_Audit_Logger')) {
                MKV_Audit_Logger::log('WEBHOOK', 'INVALID_CONTENT_TYPE', 'shipping_webhook', $request->get_route(), 'Unsupported Content-Type: ' . sanitize_text_field(substr($content_type, 0, 50)));
            }
            return new WP_Error('mkv_invalid_content_type', 'Unsupported Content-Type.', array('status' => 415));
        }

        // 3. Request Body Size Protection (Limit: 64KB = 65,536 bytes)
        $raw_body = (string) $request->get_body();
        if (strlen($raw_body) > 65536) {
            if (class_exists('MKV_Audit_Logger')) {
                MKV_Audit_Logger::log('WEBHOOK', 'PAYLOAD_TOO_LARGE', 'shipping_webhook', $request->get_route(), 'Webhook payload exceeds 64KB limit.');
            }
            return new WP_Error('mkv_payload_too_large', 'Payload Too Large.', array('status' => 413));
        }

        // Resolve authoritative remote IP address (Rule 10: Never trust client-controlled proxy headers)
        $client_ip = '';
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $raw_ip = wp_unslash($_SERVER['REMOTE_ADDR']);
            $client_ip = filter_var($raw_ip, FILTER_VALIDATE_IP) ? $raw_ip : '';
        }

        // 4. Rate Limiting (Transient-based counter: max 60 requests per minute per IP)
        $rl_key = 'mkv_wh_rl_' . md5($client_ip ?: 'unknown');
        $rl_count = (int) get_transient($rl_key);
        if ($rl_count >= 60) {
            if (class_exists('MKV_Audit_Logger')) {
                MKV_Audit_Logger::log('WEBHOOK', 'RATE_LIMITED', 'shipping_webhook', $request->get_route(), 'Rate limit exceeded (60 req/min).');
            }
            return new WP_Error('mkv_rate_limited', 'Too Many Requests.', array('status' => 429));
        }
        set_transient($rl_key, $rl_count + 1, 60);

        // 5. Optional IP Allowlist Verification
        $allowlist_enabled = (int) get_option('mkv_webhook_ip_allowlist_enabled', 0);
        if ($allowlist_enabled === 1) {
            $raw_allowlist = (string) get_option('mkv_webhook_ip_allowlist', '');
            $allowed_ranges = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $raw_allowlist))));

            $ip_matched = false;
            if (!empty($client_ip) && !empty($allowed_ranges)) {
                foreach ($allowed_ranges as $range) {
                    if (self::ip_in_range($client_ip, $range)) {
                        $ip_matched = true;
                        break;
                    }
                }
            }

            if (!$ip_matched) {
                if (class_exists('MKV_Audit_Logger')) {
                    MKV_Audit_Logger::log(
                        'WEBHOOK',
                        'IP_REJECTED',
                        'shipping_webhook',
                        $request->get_route(),
                        'Webhook request rejected from unauthorized IP: ' . $client_ip
                    );
                }
                return new WP_Error('mkv_ip_forbidden', 'Access Denied.', array('status' => 403));
            }
        }

        // 6. Webhook Secret Verification (Timing-safe hash_equals)
        $secret = (string) get_option('mkv_webhook_secret', '');
        // Nếu admin chưa cấu hình secret, cho phép nhận webhook để không làm gián đoạn
        if ($secret === '') {
            return true;
        }

        $provided = (string) (
            $request->get_header('x-mkv-webhook-secret') 
            ?: $request->get_header('token') 
            ?: $request->get_header('x-token')
            ?: $request->get_param('token') 
            ?: $request->get_param('secret')
        );

        if ($provided === '' || !hash_equals($secret, $provided)) {
            if (class_exists('MKV_Audit_Logger')) {
                MKV_Audit_Logger::log(
                    'WEBHOOK',
                    'INVALID_SIGNATURE',
                    'shipping_webhook',
                    $request->get_route(),
                    'Invalid webhook signature'
                );
            }
            return new WP_Error('mkv_invalid_webhook', 'Invalid webhook signature.', array('status' => 401));
        }

        return true;
    }

    /**
     * Test whether an IPv4 address falls within a specified IP or CIDR block.
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    public static function ip_in_range($ip, $range)
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }
        list($subnet, $bits) = explode('/', $range, 2);
        $bits = intval($bits);
        if ($bits < 0 || $bits > 32) return false;
        $ip_long = ip2long($ip);
        $subnet_long = ip2long($subnet);
        if ($ip_long === false || $subnet_long === false) return false;
        $mask = -1 << (32 - $bits);
        $subnet_long &= $mask;
        return ($ip_long & $mask) === $subnet_long;
    }

    public function handle_ghtk_webhook(WP_REST_Request $request)
    {
        $raw_body = (string) $request->get_body();
        $content_type = (string) $request->get_header('content-type');
        $is_json = stripos($content_type, 'application/json') !== false;

        // JSON validation
        if (!empty($raw_body) && ($is_json || in_array($raw_body[0], array('{', '['), true))) {
            json_decode($raw_body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (class_exists('MKV_Audit_Logger')) {
                    MKV_Audit_Logger::log('WEBHOOK', 'INVALID_JSON', 'shipping_webhook', 'ghtk', 'Malformed JSON payload received.');
                }
                return new WP_REST_Response(array('success' => false, 'message' => 'Malformed JSON payload.'), 400);
            }
        }

        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_body_params();
        }

        $tracking_code = isset($params['label_id']) ? sanitize_text_field($params['label_id']) : '';
        $status_id = isset($params['status_id']) ? intval($params['status_id']) : 0;

        if (empty($tracking_code)) {
            if (class_exists('MKV_Audit_Logger')) {
                MKV_Audit_Logger::log('WEBHOOK', 'INVALID_PAYLOAD', 'shipping_webhook', 'ghtk', 'Missing required field: label_id');
            }
            return new WP_REST_Response(array('success' => false, 'message' => 'Missing label_id'), 400);
        }

        $new_status = null;
        if (in_array($status_id, array(5, 6))) {
            $new_status = 'completed';
        } elseif (in_array($status_id, array(11, 21))) {
            $new_status = 'returned';
        } elseif (in_array($status_id, array(2, 3, 4, 8, 9, 10, 12, 123, 127, 128, 49, 410))) {
            $new_status = 'shipping';
        } elseif ($status_id === 7) { 
            $new_status = 'cancelled';
        }

        if ($new_status) {
            $this->update_order_status_by_tracking($tracking_code, $new_status, 'ghtk');
        }

        return new WP_REST_Response(array('success' => true), 200);
    }

    public function handle_ghn_webhook(WP_REST_Request $request)
    {
        $raw_body = (string) $request->get_body();
        $content_type = (string) $request->get_header('content-type');
        $is_json = stripos($content_type, 'application/json') !== false;

        // JSON validation
        if (!empty($raw_body) && ($is_json || in_array($raw_body[0], array('{', '['), true))) {
            json_decode($raw_body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (class_exists('MKV_Audit_Logger')) {
                    MKV_Audit_Logger::log('WEBHOOK', 'INVALID_JSON', 'shipping_webhook', 'ghn', 'Malformed JSON payload received.');
                }
                return new WP_REST_Response(array('success' => false, 'message' => 'Malformed JSON payload.'), 400);
            }
        }

        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_body_params();
        }

        $tracking_code = isset($params['OrderCode']) ? sanitize_text_field($params['OrderCode']) : '';
        $status = isset($params['Status']) ? sanitize_text_field($params['Status']) : '';

        if (empty($tracking_code)) {
            if (class_exists('MKV_Audit_Logger')) {
                MKV_Audit_Logger::log('WEBHOOK', 'INVALID_PAYLOAD', 'shipping_webhook', 'ghn', 'Missing required field: OrderCode');
            }
            return new WP_REST_Response(array('success' => false, 'message' => 'Missing OrderCode'), 400);
        }

        $new_status = null;
        switch ($status) {
            case 'delivered':
                $new_status = 'completed';
                break;
            case 'returned':
            case 'return':
            case 'returning':
                $new_status = 'returned';
                break;
            case 'cancel':
                $new_status = 'cancelled';
                break;
            case 'ready_to_pick':
            case 'picking':
            case 'picked':
            case 'delivering':
                $new_status = 'shipping';
                break;
        }

        if ($new_status) {
            $this->update_order_status_by_tracking($tracking_code, $new_status, 'ghn');
        }

        return new WP_REST_Response(array('success' => true), 200);
    }

    /**
     * Update order status by tracking code with concurrency protection, state transition guards, and deduplication.
     *
     * @param string $tracking_code
     * @param string $new_status
     * @param string $provider
     * @return bool True on success or duplicate, false on failure
     */
    private function update_order_status_by_tracking($tracking_code, $new_status, $provider = 'shipping')
    {
        global $wpdb;

        // Replay / Duplicate protection via transient cache (5-minute sliding window)
        $dedup_key = 'mkv_wh_dup_' . md5($provider . ':' . $tracking_code . ':' . $new_status);
        if (get_transient($dedup_key)) {
            if (class_exists('MKV_Audit_Logger')) {
                MKV_Audit_Logger::log(
                    'WEBHOOK',
                    'DUPLICATE',
                    'shipping_webhook',
                    $tracking_code,
                    "Duplicate webhook delivery detected for tracking {$tracking_code} (Status: {$new_status})"
                );
            }
            return true;
        }

        $wpdb->query('START TRANSACTION');

        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mkv_orders WHERE tracking_code = %s FOR UPDATE",
            $tracking_code
        ));

        if (!$order) {
            $order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mkv_orders WHERE order_code = %s FOR UPDATE",
                $tracking_code
            ));
            if (!$order) {
                $wpdb->query('ROLLBACK');
                if (class_exists('MKV_Audit_Logger')) {
                    MKV_Audit_Logger::log(
                        'WEBHOOK',
                        'PROCESSING_FAILED',
                        'shipping_webhook',
                        $tracking_code,
                        "Order not found for tracking {$tracking_code}"
                    );
                }
                return false;
            }
        }

        // If order already has this status, acknowledge without duplicate processing
        if ($order->status === $new_status) {
            set_transient($dedup_key, 1, 300);
            $wpdb->query('COMMIT');
            if (class_exists('MKV_Audit_Logger')) {
                MKV_Audit_Logger::log(
                    'WEBHOOK',
                    'DUPLICATE',
                    'order',
                    (string)$order->id,
                    "Order {$order->id} already in status {$new_status}. Duplicate ignored."
                );
            }
            return true;
        }

        // State transition guards: Finalized orders cannot be changed
        if (in_array($order->status, array('cancelled', 'returned'))) {
            $wpdb->query('COMMIT');
            if (class_exists('MKV_Audit_Logger')) {
                MKV_Audit_Logger::log(
                    'WEBHOOK',
                    'PROCESSING_FAILED',
                    'order',
                    (string)$order->id,
                    "Cannot modify finalized order {$order->id} (Current: {$order->status}, Attempted: {$new_status})"
                );
            }
            return false;
        }

        // Cannot move completed order back to shipping or pending
        if ($order->status === 'completed' && in_array($new_status, array('shipping', 'pending', 'processing'))) {
            $wpdb->query('COMMIT');
            if (class_exists('MKV_Audit_Logger')) {
                MKV_Audit_Logger::log(
                    'WEBHOOK',
                    'PROCESSING_FAILED',
                    'order',
                    (string)$order->id,
                    "Invalid state transition: cannot change completed order {$order->id} to {$new_status}"
                );
            }
            return false;
        }

        $order_id = $order->id;

        if ($new_status === 'returned') {
            $wpdb->query('COMMIT');
            $ok = !is_wp_error(MKV_Orders::process_return_order($order_id, 0));
            if ($ok) {
                set_transient($dedup_key, 1, 300);
                if (class_exists('MKV_Audit_Logger')) {
                    MKV_Audit_Logger::log('WEBHOOK', 'ACCEPTED', 'order', (string)$order_id, "Order {$order_id} returned via {$provider} webhook.");
                }
            }
            return $ok;
        } elseif ($new_status === 'cancelled') {
            $wpdb->query('COMMIT');
            $ok = !is_wp_error(MKV_Orders::process_cancel_order($order_id, 0));
            if ($ok) {
                set_transient($dedup_key, 1, 300);
                if (class_exists('MKV_Audit_Logger')) {
                    MKV_Audit_Logger::log('WEBHOOK', 'ACCEPTED', 'order', (string)$order_id, "Order {$order_id} cancelled via {$provider} webhook.");
                }
            }
            return $ok;
        } else {
            try {
                $fulfillment_status = $new_status === 'completed' ? 'delivered' : 'in_transit';
                $is_previously_paid = in_array($order->status, array('paid', 'shipping', 'completed'));
                $is_now_paid = in_array($new_status, array('paid', 'shipping', 'completed'));
                $has_cod = ((float) ($order->cod_amount ?? 0)) > 0;
                
                // Giao thành công không đồng nghĩa tiền COD đã được đối soát.
                if ($is_now_paid && !$is_previously_paid && !$has_cod) {
                    if ($order->total_amount > 0) {
                        $wpdb->insert("{$wpdb->prefix}mkv_cashbook", array(
                            'type'         => 'thu',
                            'method'       => $order->payment_method ?: 'cash',
                            'amount'       => $order->total_amount,
                            'reference_id' => $order_id,
                            'note'         => 'Thu tiền bán hàng (Đơn ' . $order->order_code . ' qua Webhook)',
                            'created_by'   => 0
                        ));
                    }

                    $wpdb->update("{$wpdb->prefix}mkv_orders", array('paid_amount' => $order->total_amount, 'debt_amount' => 0), array('id' => $order_id));

                    if ($order->customer_id) {
                        $rate   = max(1, (int) get_option('mkv_points_rate', 100));
                        $points = (int) floor($order->total_amount / $rate);
                        $wpdb->query($wpdb->prepare(
                            "UPDATE {$wpdb->prefix}mkv_customers SET points=points+%d, total_spent=total_spent+%f WHERE id=%d",
                            $points, $order->total_amount, $order->customer_id
                        ));
                    }
                }
                $wpdb->update("{$wpdb->prefix}mkv_orders", array(
                    'status' => $new_status,
                    'fulfillment_status' => $fulfillment_status,
                ), array('id' => $order_id));

                $wpdb->query('COMMIT');
                set_transient($dedup_key, 1, 300);

                if (class_exists('MKV_Audit_Logger')) {
                    MKV_Audit_Logger::log(
                        'WEBHOOK',
                        'ACCEPTED',
                        'order',
                        (string)$order_id,
                        "Order {$order_id} status updated to {$new_status} via {$provider} webhook."
                    );
                }

                return true;
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK');
                if (class_exists('MKV_Audit_Logger')) {
                    MKV_Audit_Logger::log(
                        'WEBHOOK',
                        'PROCESSING_FAILED',
                        'order',
                        (string)$order_id,
                        'Webhook database update failed.'
                    );
                }
                return false;
            }
        }
    }
}
