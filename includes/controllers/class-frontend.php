<?php
if (!defined('ABSPATH')) exit;

class MKV_Frontend
{
    public function __construct()
    {
        // Shortcode
        add_shortcode('mkv_tracking', array($this, 'render_tracking_page'));

        // AJAX for tracking
        add_action('wp_ajax_nopriv_mkv_track_order', array($this, 'handle_track_order'));
        add_action('wp_ajax_mkv_track_order', array($this, 'handle_track_order'));
    }

    public function render_tracking_page($atts)
    {
        ob_start();
        include MKV_DIR . 'includes/views/view-tracking.php';
        return ob_get_clean();
    }

    public function handle_track_order()
    {
        check_ajax_referer('mkv_tracking_nonce', 'nonce');

        $order_code = isset($_POST['order_code']) ? sanitize_text_field(trim($_POST['order_code'])) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field(trim($_POST['phone'])) : '';

        if (empty($order_code) || empty($phone)) {
            wp_send_json_error(array('message' => 'Vui lòng nhập Mã đơn hàng và Số điện thoại.'));
        }

        global $wpdb;

        // 1. Tìm đơn hàng (Không phân biệt hoa thường)
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mkv_orders WHERE UPPER(order_code) = UPPER(%s)",
            $order_code
        ));

        if (!$order) {
            wp_send_json_error(array('message' => 'Không tìm thấy đơn hàng. Vui lòng kiểm tra lại mã đơn.'));
        }

        // 2. Xác thực Số điện thoại
        $customer_phone = '';
        if ($order->customer_id > 0) {
            $customer_phone = $wpdb->get_var($wpdb->prepare(
                "SELECT phone FROM {$wpdb->prefix}mkv_customers WHERE id = %d",
                $order->customer_id
            ));
        }

        // Chuẩn hóa số điện thoại: bỏ ký tự đặc biệt, chuẩn hóa đầu số 0/84/+84
        $normalize_phone = function($p) {
            $digits = preg_replace('/[^0-9]/', '', (string)$p);
            if (strpos($digits, '84') === 0 && strlen($digits) >= 10) {
                $digits = '0' . substr($digits, 2);
            }
            return ltrim($digits, '0');
        };

        $db_phone_norm = $normalize_phone($customer_phone);
        $input_phone_norm = $normalize_phone($phone);

        // Kiểm tra khớp số điện thoại
        if (!empty($db_phone_norm)) {
            if ($db_phone_norm !== $input_phone_norm) {
                wp_send_json_error(array('message' => 'Số điện thoại không khớp với thông tin đơn hàng.'));
            }
        } else {
            // Nếu đơn không lưu customer_id, kiểm tra xem SĐT có nằm trong customer_address hoặc note không
            $found_in_text = false;
            if (!empty($input_phone_norm)) {
                $raw_text = ($order->customer_address ?? '') . ' ' . ($order->note ?? '');
                $clean_raw = preg_replace('/[^0-9]/', '', $raw_text);
                if (strpos($clean_raw, $input_phone_norm) !== false) {
                    $found_in_text = true;
                }
            }
            
            if (!$found_in_text) {
                wp_send_json_error(array('message' => 'Không thể xác thực số điện thoại cho đơn hàng này.'));
            }
        }

        // 3. Lấy chi tiết sản phẩm
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT product_id, qty, price, subtotal FROM {$wpdb->prefix}mkv_order_items WHERE order_id = %d",
            $order->id
        ));

        $item_details = array();
        foreach ($items as $item) {
            $title = get_the_title($item->product_id);
            if (empty($title)) $title = 'Sản phẩm #' . $item->product_id;
            
            $img_url = get_the_post_thumbnail_url($item->product_id, 'thumbnail');
            if (!$img_url) {
                $img_url = 'https://via.placeholder.com/150?text=No+Image';
            }

            $item_details[] = array(
                'name' => $title,
                'qty' => $item->qty,
                'price' => (float)$item->price,
                'subtotal' => (float)$item->subtotal,
                'image' => $img_url
            );
        }

        // 4. Trả về kết quả
        wp_send_json_success(array(
            'order_code'        => $order->order_code,
            'created_at'        => date('d/m/Y H:i', strtotime($order->created_at)),
            'status'            => $order->status,
            'total_amount'      => (float)$order->total_amount,
            'shipping_fee'      => (float)$order->shipping_fee,
            'discount_amount'   => (float)($order->discount ?? 0),
            'tracking_code'     => $order->tracking_code,
            'shipping_provider' => $order->shipping_provider,
            'customer_address'  => $order->customer_address,
            'items'             => $item_details
        ));
    }
}
