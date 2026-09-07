<?php
if (!defined('ABSPATH')) exit;

class MKV_Shipping_Service
{
    public static function push_order($order_id)
    {
        global $wpdb;
        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mkv_orders WHERE id = %d", $order_id));
        if (!$order) {
            return new WP_Error('not_found', 'Đơn hàng không tồn tại.');
        }

        if (empty($order->customer_address)) {
            return new WP_Error('no_address', 'Đơn hàng chưa có địa chỉ giao hàng.');
        }

        $provider = sanitize_key($order->shipping_provider ?: get_option('mkv_shipping_provider', 'ghtk'));
        $token = get_option("mkv_{$provider}_token", '');
        if ($token === '') {
            $token = get_option('mkv_shipping_token', '');
        }

        if (empty($token)) {
            return new WP_Error('no_token', 'Chưa cấu hình API Token cho ' . strtoupper($provider));
        }

        $customer_name = 'Khách lẻ';
        $customer_phone = sanitize_text_field($order->shipping_phone ?? '');
        if ($order->customer_id) {
            $customer = $wpdb->get_row($wpdb->prepare("SELECT name, phone FROM {$wpdb->prefix}mkv_customers WHERE id = %d", $order->customer_id));
            if ($customer) {
                $customer_name = $customer->name;
                $customer_phone = $customer_phone ?: ($customer->phone ?: '');
            }
        }
        if ($customer_phone === '') {
            return new WP_Error('no_phone', 'Đơn hàng chưa có số điện thoại người nhận.');
        }

        $items = $wpdb->get_results($wpdb->prepare("SELECT product_id, qty, price FROM {$wpdb->prefix}mkv_order_items WHERE order_id = %d", $order_id));
        
        if ($provider === 'ghtk') {
            return self::push_to_ghtk($order, $items, $customer_name, $customer_phone, $token);
        } elseif ($provider === 'ghn') {
            return self::push_to_ghn($order, $items, $customer_name, $customer_phone, $token);
        }

        return new WP_Error('invalid_provider', 'Đơn vị vận chuyển không hợp lệ.');
    }

    private static function push_to_ghtk($order, $items, $customer_name, $customer_phone, $token)
    {
        $url = 'https://services.giaohangtietkiem.vn/services/shipment/order';
        
        $products = [];
        foreach ($items as $item) {
            $products[] = array(
                'name' => get_the_title($item->product_id),
                'weight' => 0.5,
                'quantity' => $item->qty,
                'product_code' => $item->product_id
            );
        }

        // Tách địa chỉ (rất cơ bản)
        $parts = explode(',', $order->customer_address);
        $province = trim(end($parts)); 
        $district = count($parts) > 1 ? trim($parts[count($parts)-2]) : '';
        $address = count($parts) > 2 ? trim($parts[0]) : $order->customer_address;

        // COD chỉ thu số tiền khách chưa trả (debt_amount)
        $pick_money = max(0, (float) ($order->debt_amount ?? ($order->total_amount - $order->paid_amount)));

        $data = array(
            'products' => $products,
            'order' => array(
                'id' => $order->order_code,
                'pick_name' => get_option('mkv_store_name', 'Cửa hàng'),
                'pick_address' => get_option('mkv_shipping_address', get_option('mkv_store_address', '')),
                'pick_province' => get_option('mkv_shipping_province', ''),
                'pick_district' => get_option('mkv_shipping_district', ''),
                'pick_ward' => get_option('mkv_shipping_ward', ''),
                'pick_tel' => get_option('mkv_shipping_phone', get_option('mkv_store_phone', '')),
                'tel' => $customer_phone,
                'name' => $customer_name,
                'address' => $address,
                'province' => $province,
                'district' => $district,
                'is_freeship' => 1,
                'pick_money' => $pick_money, // Thu hộ
                'note' => $order->note
            )
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Token' => $token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        if ($status_code < 200 || $status_code >= 300) {
            return new WP_Error('provider_http_error', 'GHTK không phản hồi thành công (HTTP ' . $status_code . ').');
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        if (!is_array($result)) {
            return new WP_Error('invalid_provider_response', 'GHTK trả về dữ liệu không hợp lệ.');
        }

        if (isset($result['success']) && $result['success']) {
            $tracking_code = sanitize_text_field($result['order']['label'] ?? '');
            if ($tracking_code === '') {
                return new WP_Error('invalid_provider_response', 'GHTK không trả về mã vận đơn hợp lệ.');
            }
            global $wpdb;
            $wpdb->update("{$wpdb->prefix}mkv_orders", array(
                'tracking_code'     => $tracking_code,
                'shipping_provider' => 'ghtk',
                'status'            => 'shipping',
                'fulfillment_status' => 'in_transit'
            ), array('id' => $order->id));
            return $tracking_code;
        }

        $message = isset($result['message']) ? sanitize_text_field($result['message']) : 'GHTK từ chối tạo vận đơn.';
        return new WP_Error('provider_rejected', $message);
    }

    private static function push_to_ghn($order, $items, $customer_name, $customer_phone, $token)
    {
        return new WP_Error('provider_not_implemented', 'Tích hợp GHN chưa được triển khai đầy đủ; đơn chưa được đẩy đi.');
    }
}
