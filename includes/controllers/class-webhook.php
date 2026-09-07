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
        register_rest_route('mkv/v1', '/webhook/ghtk', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_ghtk_webhook'),
            'permission_callback' => array($this, 'authorize_webhook')
        ));

        register_rest_route('mkv/v1', '/webhook/ghn', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_ghn_webhook'),
            'permission_callback' => array($this, 'authorize_webhook')
        ));
    }

    public function authorize_webhook(WP_REST_Request $request)
    {
        $secret = (string) get_option('mkv_webhook_secret', '');
        $provided = (string) $request->get_header('x-mkv-webhook-secret');
        if ($secret === '' || $provided === '' || !hash_equals($secret, $provided)) {
            return new WP_Error('mkv_invalid_webhook', 'Invalid webhook signature.', array('status' => 401));
        }
        return true;
    }

    public function handle_ghtk_webhook(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_body_params();
        }

        $tracking_code = isset($params['label_id']) ? sanitize_text_field($params['label_id']) : '';
        $status_id = isset($params['status_id']) ? intval($params['status_id']) : 0;

        if (empty($tracking_code)) {
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
            $this->update_order_status_by_tracking($tracking_code, $new_status);
        }

        return new WP_REST_Response(array('success' => true), 200);
    }

    public function handle_ghn_webhook(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_body_params();
        }

        $tracking_code = isset($params['OrderCode']) ? sanitize_text_field($params['OrderCode']) : '';
        $status = isset($params['Status']) ? sanitize_text_field($params['Status']) : '';

        if (empty($tracking_code)) {
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
            $this->update_order_status_by_tracking($tracking_code, $new_status);
        }

        return new WP_REST_Response(array('success' => true), 200);
    }

    private function update_order_status_by_tracking($tracking_code, $new_status)
    {
        global $wpdb;
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mkv_orders WHERE tracking_code = %s",
            $tracking_code
        ));

        if (!$order) {
            $order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mkv_orders WHERE order_code = %s",
                $tracking_code
            ));
            if (!$order) {
                return false;
            }
        }

        if ($order->status === $new_status) {
            return true;
        }

        if (in_array($order->status, array('cancelled', 'returned'))) {
            return false;
        }

        $order_id = $order->id;

        if ($new_status === 'returned') {
            MKV_Orders::process_return_order($order_id, 0);
        } elseif ($new_status === 'cancelled') {
            MKV_Orders::process_cancel_order($order_id, 0);
        } else {
            $fulfillment_status = $new_status === 'completed' ? 'delivered' : 'in_transit';
            $is_previously_paid = in_array($order->status, array('paid', 'shipping', 'completed'));
            $is_now_paid = in_array($new_status, array('paid', 'shipping', 'completed'));
            $has_cod = !empty($order->cod_amount);
            
            // Giao thành công không đồng nghĩa tiền COD đã được đối soát.
            if ($is_now_paid && !$is_previously_paid && !$has_cod) {
                if ($order->total_amount > 0) {
                    $wpdb->insert("{$wpdb->prefix}mkv_cashbook", array(
                        'type'         => 'thu',
                        'method'       => $order->payment_method,
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
        }
        return true;
    }
}
