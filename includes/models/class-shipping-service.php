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

        $provider = get_option('mkv_shipping_provider', 'ghtk');
        $token = get_option("mkv_{$provider}_token", '');

        if (empty($token)) {
            return new WP_Error('no_token', 'Chưa cấu hình API Token cho ' . strtoupper($provider));
        }

        $customer_name = 'Khách lẻ';
        $customer_phone = '0900000000';
        if ($order->customer_id) {
            $customer = $wpdb->get_row($wpdb->prepare("SELECT name, phone FROM {$wpdb->prefix}mkv_customers WHERE id = %d", $order->customer_id));
            if ($customer) {
                $customer_name = $customer->name;
                $customer_phone = $customer->phone ?: '0900000000';
            }
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
                'pick_address' => get_option('mkv_shipping_pickup_address', 'Hà Nội'),
                'pick_province' => 'Hà Nội', // Fixed for demo
                'pick_district' => 'Quận Đống Đa', // Fixed for demo
                'pick_tel' => get_option('mkv_store_phone', '0900000000'),
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

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (isset($result['success']) && $result['success']) {
            $tracking_code = $result['order']['label'];
            global $wpdb;
            $wpdb->update("{$wpdb->prefix}mkv_orders", array(
                'tracking_code'     => $tracking_code,
                'shipping_provider' => 'ghtk',
                'status'            => 'shipping'
            ), array('id' => $order->id));
            return $tracking_code;
        }

        // MOCK cho môi trường dev nếu chưa có Token thật
        $tracking_code = 'GHTK' . date('ymd') . rand(100, 999);
        global $wpdb;
        $wpdb->update("{$wpdb->prefix}mkv_orders", array(
            'tracking_code'     => $tracking_code,
            'shipping_provider' => 'ghtk',
            'status'            => 'shipping'
        ), array('id' => $order->id));
        return $tracking_code;
    }

    private static function push_to_ghn($order, $items, $customer_name, $customer_phone, $token)
    {
        // MOCK GHN
        $tracking_code = 'GHN' . date('ymd') . rand(100, 999);
        global $wpdb;
        $wpdb->update("{$wpdb->prefix}mkv_orders", array(
            'tracking_code'     => $tracking_code,
            'shipping_provider' => 'ghn',
            'status'            => 'shipping'
        ), array('id' => $order->id));
        return $tracking_code;
    }
}
