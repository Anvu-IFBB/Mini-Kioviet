<?php
if (!defined('ABSPATH')) exit;

class MKV_Orders
{
    const STATUSES = array(
        'draft'     => array('label' => 'Nháp',         'badge' => 'mkv-badge-gray',   'next' => 'pending'),
        'pending'   => array('label' => 'Chờ duyệt',    'badge' => 'mkv-badge-yellow', 'next' => 'paid'),
        'paid'      => array('label' => 'Đã thanh toán','badge' => 'mkv-badge-blue',   'next' => 'shipping'),
        'shipping'  => array('label' => 'Đang giao',    'badge' => 'mkv-badge-purple', 'next' => 'completed'),
        'completed' => array('label' => 'Hoàn thành',   'badge' => 'mkv-badge-green',  'next' => null),
        'cancelled' => array('label' => 'Đã hủy',       'badge' => 'mkv-badge-red',    'next' => null),
        'returned'  => array('label' => 'Đã trả hàng',  'badge' => 'mkv-badge-gray',   'next' => null),
    );

    public function __construct()
    {
        add_action('admin_menu',                          array($this, 'register_admin_menu'));
        add_action('admin_post_mkv_create_order',         array($this, 'handle_create_order'));
        add_action('admin_post_nopriv_mkv_create_order',  array($this, 'handle_nopriv_create_order'));
        add_action('admin_post_mkv_update_order_status',  array($this, 'handle_update_order_status'));
        add_action('admin_post_mkv_cancel_order',         array($this, 'handle_cancel_order'));
        add_action('admin_post_mkv_return_order',         array($this, 'handle_return_order'));
        add_action('admin_post_mkv_push_shipping',        array($this, 'handle_push_shipping'));
        add_action('admin_enqueue_scripts',               array($this, 'enqueue_assets'));
    }

    public function handle_nopriv_create_order()
    {
        $is_ajax = isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1';
        if ($is_ajax) {
            wp_send_json_error('Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang.');
        }
        wp_die('Phiên đăng nhập đã hết hạn.');
    }

    public function enqueue_assets($hook)
    {
        if ($hook === 'mini-kiotviet_page_mkv-pos') {
            wp_enqueue_style('mkv-pos-css', MKV_URL . 'assets/css/admin-pos.css', array(), MKV_VERSION);
            wp_enqueue_script('mkv-pos-js', MKV_URL . 'assets/js/admin-pos.js', array('jquery'), time(), true);
        }
    }

    public function register_admin_menu()
    {
        add_submenu_page('mini-kiotviet', 'Bán Hàng (POS)', 'Bán Hàng',
            'mkv_manage_orders', 'mkv-pos', array($this, 'render_pos_page'));

        add_submenu_page('mini-kiotviet', 'Danh sách Đơn hàng', 'Đơn hàng',
            'mkv_manage_orders', 'mkv-orders', array($this, 'render_orders_list'));
    }

    public function render_pos_page()
    {
        global $wpdb;
        $customers = $wpdb->get_results("SELECT id, name, phone, points FROM {$wpdb->prefix}mkv_customers ORDER BY name ASC");
        $products  = get_posts(array('post_type' => 'mkv_product', 'numberposts' => -1, 'post_status' => 'publish'));
        $locations = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mkv_locations WHERE status='active' ORDER BY id ASC");
        require_once MKV_DIR . 'includes/views/view-pos.php';
    }

    public function render_orders_list()
    {
        global $wpdb;

        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $where = '';
        if ($status_filter) {
            $where = $wpdb->prepare("WHERE o.status = %s", $status_filter);
        }

        $orders = $wpdb->get_results(
            "SELECT o.*, c.name as customer_name
             FROM {$wpdb->prefix}mkv_orders o
             LEFT JOIN {$wpdb->prefix}mkv_customers c ON o.customer_id = c.id
             $where
             ORDER BY o.id DESC LIMIT 100"
        );
        require_once MKV_DIR . 'includes/views/view-orders.php';
    }

    public function handle_create_order()
    {
        try {
            $this->_handle_create_order();
        } catch (\Throwable $e) {
            error_log('Mini KiotViet order error: ' . $e->getMessage());
            $is_ajax = isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1';
            if ($is_ajax) {
                if (ob_get_level() > 0) ob_clean();
                wp_send_json_error('Lỗi hệ thống nội bộ. Vui lòng thử lại sau.');
            }
            wp_die('Lỗi hệ thống nội bộ');
        }
    }

    private function _handle_create_order()
    {
        $is_ajax = isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1';

        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mkv_create_order_nonce') || !current_user_can('mkv_manage_orders')) {
            if ($is_ajax) wp_send_json_error('Không có quyền.');
            wp_die('Không có quyền.');
        }
        if (empty($_POST['products'])) {
            if ($is_ajax) wp_send_json_error('Giỏ hàng trống!');
            wp_die('Giỏ hàng trống!');
        }

        global $wpdb;

        $customer_id    = intval($_POST['customer_id'] ?? 0);
        $location_id    = intval($_POST['location_id'] ?? 1);
        $payment_method = sanitize_text_field($_POST['payment_method'] ?? 'cash');
        $note           = sanitize_textarea_field($_POST['order_note'] ?? '');
        $points_used    = intval($_POST['points_used'] ?? 0);
        $allow_negative = (int) get_option('mkv_allow_negative_stock', 0);
        $order_code     = 'DH' . date('ymdHis') . rand(10, 99);
        $is_draft       = isset($_POST['is_draft']) && $_POST['is_draft'] == '1';
        
        $paid_str       = sanitize_text_field($_POST['paid_amount'] ?? '');
        $paid_input     = (float) preg_replace('/\D/', '', $paid_str);

        // Validate tồn kho từng sản phẩm (không bắt lỗi tồn kho nếu là lưu nháp)
        if (!$is_draft) {
            foreach ($_POST['products'] as $item) {
            $pid  = intval($item['id']);
            $qty  = intval($item['qty']);
            $stock_row = $wpdb->get_row($wpdb->prepare(
                "SELECT SUM(stock) as s FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d AND location_id=%d",
                $pid, $location_id
            ));
            $avail = ($stock_row && $stock_row->s !== null) ? (int)$stock_row->s : 0;
            if (!$allow_negative && $avail < $qty) {
                $err_msg = 'Sản phẩm "' . get_the_title($pid) . '" không đủ tồn kho (còn ' . $avail . ', cần ' . $qty . ').';
                if ($is_ajax) {
                    if (ob_get_level() > 0) ob_clean();
                    wp_send_json_error($err_msg);
                }
                wp_die($err_msg);
            }
        }
        }

        $wpdb->query('START TRANSACTION');
        // Tạo đơn hàng
        $initial_status = $is_draft ? 'draft' : 'pending';
        $wpdb->insert("{$wpdb->prefix}mkv_orders", array(
            'order_code'     => $order_code,
            'customer_id'    => $customer_id ?: null,
            'warehouse_id'   => $location_id,
            'status'         => $initial_status,
            'payment_method' => $payment_method,
            'total_amount'   => 0,
            'points_used'    => $points_used,
            'note'           => $note,
            'created_by'     => get_current_user_id(),
            'created_at'     => current_time('mysql'),
        ));
        $order_id = $wpdb->insert_id;
        if (!$order_id) {
            $wpdb->query('ROLLBACK');
            if ($is_ajax) {
                if (ob_get_level() > 0) ob_clean();
                wp_send_json_error('Lỗi tạo đơn hàng.');
            }
            wp_die('Lỗi tạo đơn hàng.');
        }

        $subtotal    = 0;
        $cost_total  = 0;
        $vat_rate    = (float) get_option('mkv_store_vat', 0) / 100;

        try {

        foreach ($_POST['products'] as $item) {
            $pid      = intval($item['id']);
            $qty      = intval($item['qty']);
            // SECURITY FIX: Luôn lấy giá từ Database để tránh client-side manipulation (giá 0đ, âm)
            $price    = (float) get_post_meta($pid, '_mkv_price_out', true);
            $cost     = (float) get_post_meta($pid, '_mkv_price_in', true);
            $sub      = $qty * $price;
            $subtotal    += $sub;
            $cost_total  += $qty * $cost;

            $wpdb->insert("{$wpdb->prefix}mkv_order_items", array(
                'order_id'   => $order_id,
                'product_id' => $pid,
                'qty'        => $qty,
                'price'      => $price,
                'cost_price' => $cost,
                'subtotal'   => $sub,
            ));

            // Trừ kho (chỉ trừ nếu KHÔNG phải là nháp)
            if (!$is_draft) {
                // Check tồn kho sử dụng FOR UPDATE để tránh race condition
                if (!$allow_negative) {
                    $stock_row = $wpdb->get_row($wpdb->prepare(
                        "SELECT SUM(stock) as s FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d AND location_id=%d FOR UPDATE",
                        $pid, $location_id
                    ));
                    $avail = ($stock_row && $stock_row->s !== null) ? (int)$stock_row->s : 0;
                    if ($avail < $qty) {
                        $wpdb->query('ROLLBACK');
                        if ($is_ajax) {
                            if (ob_get_level() > 0) ob_clean();
                            wp_send_json_error('Sản phẩm "' . get_the_title($pid) . '" không đủ tồn kho (còn ' . $avail . '). Vui lòng kiểm tra lại.');
                        }
                        wp_die('Sản phẩm không đủ tồn kho.');
                    }
                }
                
                $this->deduct_stock($pid, $location_id, $qty, $order_code);
            }
        }
        
        $tax_amount = (int) get_option('mkv_vat_included', 0)
            ? $subtotal - ($subtotal / (1 + $vat_rate))
            : $subtotal * $vat_rate;

        // Tính discount điểm
        $points_discount = 0;
        $point_value = (float) get_option('mkv_point_value', 1); // 1 điểm = x VNĐ
        if ($point_value <= 0) $point_value = 1;
        
        if ($points_used > 0 && $customer_id > 0) {
            $customer = $wpdb->get_row($wpdb->prepare("SELECT points FROM {$wpdb->prefix}mkv_customers WHERE id=%d", $customer_id));
            if ($customer && $customer->points >= $points_used) {
                // Giới hạn điểm dùng không vượt quá tổng tiền
                $max_points_needed = ceil(($subtotal + $tax_amount) / $point_value);
                $points_used = (int) min($points_used, $max_points_needed);
                
                $points_discount = $points_used * $point_value;
                if (!$is_draft) {
                    $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->prefix}mkv_customers SET points = points - %d WHERE id=%d",
                        $points_used, $customer_id
                    ));
                }
            } else {
                $points_used = 0;
            }
        }

        // Xử lý phí giao hàng
        $shipping_fee = 0;
        $shipping_provider = null;
        $customer_address = null;
        if (!empty($_POST['is_enable_shipping']) || !empty($_POST['customer_address']) || (isset($_POST['shipping_fee']) && $_POST['shipping_fee'] > 0)) {
            $shipping_fee = max(0, (float) ($_POST['shipping_fee'] ?? 0));
            $shipping_provider = sanitize_text_field($_POST['shipping_provider'] ?? get_option('mkv_shipping_provider', 'ghtk'));
            $customer_address = sanitize_textarea_field($_POST['customer_address'] ?? '');
        }

        $total_amount = max(0, $subtotal + $tax_amount - $points_discount) + $shipping_fee;
        
        // Tính toán thanh toán và công nợ
        if ($is_draft) {
            $paid_amount = 0;
            $debt_amount = 0;
        } else {
            // Khách có thể đưa dư, nhưng hệ thống chỉ ghi nhận thu tối đa bằng tổng tiền.
            // Nếu đưa thiếu, phần còn lại là công nợ.
            $paid_amount = min($total_amount, $paid_input);
            $debt_amount = max(0, $total_amount - $paid_amount);
        }

        $wpdb->update("{$wpdb->prefix}mkv_orders", array(
            'subtotal'     => $subtotal,
            'discount'     => $points_discount,
            'tax'          => $tax_amount,
            'total_amount' => $total_amount,
            'paid_amount'  => $paid_amount,
            'debt_amount'  => $debt_amount,
            'cost_total'   => $cost_total,
            'points_used'  => $points_used,
            'status'       => $is_draft ? 'draft' : 'paid',
            'shipping_provider' => $shipping_provider,
            'shipping_fee' => $shipping_fee,
            'customer_address' => $customer_address,
        ), array('id' => $order_id));

        // Tích điểm (chỉ tích khi đơn hàng là paid, KHÔNG phải draft)
        $earned_points = 0;
        if (!$is_draft && $customer_id > 0) {
            $rate   = max(1, (int) get_option('mkv_points_rate', 100)); // Tránh division by zero
            $earned_points = (int) floor($total_amount / $rate);
            
            // Nếu có cộng điểm hoặc có nợ, cập nhật customer
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}mkv_customers SET points=points+%d, total_spent=total_spent+%f, total_debt=total_debt+%f WHERE id=%d",
                $earned_points, $total_amount, $debt_amount, $customer_id
            ));
        }

        // Tạo phiếu Thu trong Sổ Quỹ (chỉ thu số tiền thực trả)
        if (!$is_draft && $paid_amount > 0) {
            $wpdb->insert("{$wpdb->prefix}mkv_cashbook", array(
                'type'         => 'thu',
                'method'       => $payment_method,
                'amount'       => $paid_amount,
                'reference_id' => $order_id,
                'customer_id'  => $customer_id ?: null,
                'note'         => 'Thu tiền bán hàng (Đơn ' . $order_code . ')',
                'created_by'   => get_current_user_id()
            ));
        }

        // Thêm vào bảng notifications
        $wpdb->insert("{$wpdb->prefix}mkv_notifications", array(
            'type'    => 'order',
            'title'   => 'Đơn hàng mới: ' . $order_code,
            'message' => 'Giá trị: ' . number_format($total_amount, 0, ',', '.') . ' ₫',
            'is_read' => 0,
        ));

        $wpdb->query('COMMIT');
        
        if ($is_ajax) {
            if (ob_get_level() > 0) ob_clean();
            wp_send_json_success(array(
                'order_id'   => $order_id,
                'order_code' => $order_code,
                'points_earned' => $earned_points
            ));
        } else {
            wp_redirect(admin_url('admin.php?page=mkv-orders&created=1&oid=' . $order_id));
            exit;
        }
        
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            if ($is_ajax) {
                if (ob_get_level() > 0) ob_clean();
                wp_send_json_error('Lỗi hệ thống: ' . $e->getMessage());
            }
            wp_die('Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    private function deduct_stock(int $product_id, int $location_id, int $qty, string $order_code)
    {
        global $wpdb;
        
        // 1. Chèn dòng mới nếu chưa tồn tại (INSERT IGNORE)
        $wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO {$wpdb->prefix}mkv_inventory_stock (product_id, location_id, stock) VALUES (%d, %d, 0)",
            $product_id, $location_id
        ));
        
        // 2. Trừ kho nguyên tử (Atomic Update) để tránh Race Condition
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}mkv_inventory_stock SET stock = stock - %d WHERE product_id = %d AND location_id = %d",
            $qty, $product_id, $location_id
        ));

        // Đồng bộ meta
        $total = (int) $wpdb->get_var($wpdb->prepare("SELECT SUM(stock) FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d FOR UPDATE", $product_id));
        update_post_meta($product_id, '_mkv_stock', $total);

        // Bổ sung Notification Hết hàng / Dưới định mức
        $threshold = (int) get_option('mkv_min_stock_threshold', 5);
        $product_title = get_the_title($product_id);
        
        if ($total == 0) {
            $wpdb->insert("{$wpdb->prefix}mkv_notifications", array(
                'type'    => 'danger',
                'title'   => 'Sản phẩm hết hàng: ' . $product_title,
                'message' => 'Đã bán hết sản phẩm trong kho (Tồn = 0).',
                'is_read' => 0,
            ));
        } elseif ($total <= $threshold) {
            $wpdb->insert("{$wpdb->prefix}mkv_notifications", array(
                'type'    => 'warning',
                'title'   => 'Sản phẩm dưới định mức: ' . $product_title,
                'message' => 'Tồn kho chỉ còn ' . $total . ' sản phẩm. Vui lòng nhập thêm hàng.',
                'is_read' => 0,
            ));
        }

        // Log
        $wpdb->insert("{$wpdb->prefix}mkv_inventory_logs", array(
            'product_id'  => $product_id,
            'location_id' => $location_id,
            'type'        => 'out',
            'qty'         => $qty,
            'note'        => 'Xuất bán - ' . $order_code,
            'created_by'  => get_current_user_id(),
            'created_at'  => current_time('mysql'),
        ));
    }

    public function handle_update_order_status()
    {
        $order_id  = intval($_GET['id'] ?? 0);
        $new_status = sanitize_text_field($_GET['new_status'] ?? '');
        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'mkv_status_' . $order_id . '_' . $new_status) || !current_user_can('mkv_manage_orders'))
            wp_die('Không có quyền.');
        if (!isset(self::STATUSES[$new_status])) wp_die('Trạng thái không hợp lệ.');

        global $wpdb;
        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mkv_orders WHERE id=%d", $order_id));
        if (!$order) wp_die('Đơn hàng không tồn tại.');

        $is_previously_paid = in_array($order->status, array('paid', 'shipping', 'completed'));
        $is_now_paid        = in_array($new_status, array('paid', 'shipping', 'completed'));

        if ($is_previously_paid && !$is_now_paid) {
            wp_die('Lỗi logic: Không được phép chuyển ngược trạng thái từ Đã thanh toán về chưa thanh toán. Vui lòng sử dụng chức năng Trả Hàng hoặc Hủy Đơn.');
        }
        if ($order->status !== 'draft' && $new_status === 'draft') {
            wp_die('Lỗi logic: Không thể chuyển trạng thái về Nháp.');
        }

        // Nếu chuyển từ draft sang trạng thái khác -> trừ kho & trừ điểm
        if ($order->status === 'draft' && $new_status !== 'draft' && $new_status !== 'cancelled') {
            $allow_negative = (int) get_option('mkv_allow_negative_stock', 0);
            $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mkv_order_items WHERE order_id=%d", $order_id));
            
            $wpdb->query('START TRANSACTION');
            
            // Check tồn kho trước
            if (!$allow_negative) {
                foreach ($items as $item) {
                    $stock_row = $wpdb->get_row($wpdb->prepare(
                        "SELECT SUM(stock) as s FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d AND location_id=%d FOR UPDATE",
                        $item->product_id, $order->warehouse_id
                    ));
                    $avail = ($stock_row && $stock_row->s !== null) ? (int)$stock_row->s : 0;
                    if ($avail < $item->qty) {
                        $wpdb->query('ROLLBACK');
                        wp_die('Sản phẩm "' . get_the_title($item->product_id) . '" không đủ tồn kho (còn ' . $avail . ', cần ' . $item->qty . '). Không thể duyệt đơn.');
                    }
                }
            }
            
            foreach ($items as $item) {
                $this->deduct_stock($item->product_id, $order->warehouse_id, $item->qty, $order->order_code);
            }
            
            // Trừ điểm đã dùng
            if ($order->customer_id > 0 && $order->points_used > 0) {
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}mkv_customers SET points = points - %d WHERE id=%d",
                    $order->points_used, $order->customer_id
                ));
            }
            
            $wpdb->query('COMMIT');
        }

        $wpdb->update("{$wpdb->prefix}mkv_orders", array('status' => $new_status), array('id' => $order_id));

        // Tích điểm và cập nhật Sổ quỹ nếu đơn hàng chuyển sang nhóm đã thanh toán
        if ($is_now_paid && !$is_previously_paid) {
            // Sổ quỹ
            if ($order->total_amount > 0) {
                $wpdb->insert("{$wpdb->prefix}mkv_cashbook", array(
                    'type'         => 'thu',
                    'method'       => $order->payment_method,
                    'amount'       => $order->total_amount,
                    'reference_id' => $order_id,
                    'note'         => 'Thu tiền bán hàng (Đơn ' . $order->order_code . ')',
                    'created_by'   => get_current_user_id(),
                    'created_at'   => current_time('mysql')
                ));
            }

            // Cập nhật paid_amount trong order thành total_amount
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

        wp_redirect(admin_url('admin.php?page=mkv-orders&status_updated=1'));
        exit;
    }

    public function handle_cancel_order()
    {
        $order_id = intval($_GET['id'] ?? 0);
        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'mkv_cancel_' . $order_id) || !current_user_can('mkv_manage_orders'))
            wp_die('Không có quyền.');

        $result = self::process_cancel_order($order_id, get_current_user_id());
        if (is_wp_error($result)) {
            wp_redirect(admin_url('admin.php?page=mkv-orders&cancel_error=1'));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=mkv-orders&cancelled=1'));
        exit;
    }

    public static function process_cancel_order($order_id, $user_id = 0)
    {
        global $wpdb;
        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mkv_orders WHERE id=%d", $order_id));
        if (!$order || $order->status === 'cancelled') {
            return new WP_Error('invalid_order', 'Đơn hàng không hợp lệ.');
        }

        $is_paid_shipping = in_array($order->status, array('paid', 'shipping', 'completed'));

        // 1. Hoàn trả tiền vào sổ quỹ (chỉ khi đã thanh toán)
        if ($is_paid_shipping && $order->paid_amount > 0) {
            $wpdb->insert("{$wpdb->prefix}mkv_cashbook", array(
                'type'         => 'chi',
                'method'       => $order->payment_method,
                'amount'       => $order->paid_amount,
                'reference_id' => $order_id,
                'customer_id'  => $order->customer_id ?: null,
                'note'         => 'Hoàn tiền - Hủy đơn ' . $order->order_code,
                'created_by'   => $user_id
            ));
        }

        // 2. Hoàn trả / trừ điểm khách hàng
        if ($order->customer_id > 0) {
            $used_points   = $order->status !== 'draft' ? (int) $order->points_used : 0;
            $earned_points = 0;
            $total_spent   = 0;
            $debt_amount   = 0;
            
            // Chỉ thu hồi điểm tích lũy và total_spent nếu đơn này ĐÃ cộng
            if ($is_paid_shipping) {
                $rate = max(1, (int) get_option('mkv_points_rate', 100));
                $earned_points = (int) floor($order->total_amount / $rate);
                $total_spent = $order->total_amount;
                $debt_amount = isset($order->debt_amount) ? $order->debt_amount : 0;
            }
            
            $points_diff = (int) ($used_points - $earned_points); // Khôi phục điểm đã dùng, trừ điểm đã kiếm
            
            if ($points_diff !== 0 || $total_spent > 0 || $debt_amount > 0) {
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}mkv_customers SET points = points + %d, total_spent = total_spent - %f, total_debt = total_debt - %f WHERE id = %d",
                    $points_diff, $total_spent, $debt_amount, $order->customer_id
                ));
            }
        }

        // 3. Hoàn trả tồn kho (áp dụng cho tất cả các đơn KHÁC draft)
        // Vì pending cũng đã trừ kho lúc tạo rồi.
        if ($order->status !== 'draft') {
            $items = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mkv_order_items WHERE order_id=%d", $order_id
            ));
            foreach ($items as $item) {
                $wpdb->query($wpdb->prepare(
                    "INSERT IGNORE INTO {$wpdb->prefix}mkv_inventory_stock (product_id, location_id, stock) VALUES (%d, %d, 0)",
                    $item->product_id, $order->warehouse_id
                ));
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}mkv_inventory_stock SET stock = stock + %d WHERE product_id = %d AND location_id = %d",
                    $item->qty, $item->product_id, $order->warehouse_id
                ));
                $total = (int) $wpdb->get_var($wpdb->prepare("SELECT SUM(stock) FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d", $item->product_id));
                update_post_meta($item->product_id, '_mkv_stock', $total);

                $wpdb->insert("{$wpdb->prefix}mkv_inventory_logs", array(
                    'product_id'  => $item->product_id,
                    'location_id' => $order->warehouse_id,
                    'type'        => 'in',
                    'qty'         => $item->qty,
                    'note'        => 'Hoàn kho - Hủy đơn ' . $order->order_code,
                    'created_by'  => $user_id,
                    'created_at'  => current_time('mysql'),
                ));
            }
        }

        $wpdb->update("{$wpdb->prefix}mkv_orders", array('status' => 'cancelled'), array('id' => $order_id));
        return true;
    }

    public function handle_return_order()
    {
        $order_id = intval($_GET['id'] ?? 0);
        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'mkv_return_' . $order_id) || !current_user_can('mkv_manage_orders'))
            wp_die('Không có quyền.');

        $result = self::process_return_order($order_id, get_current_user_id());
        if (is_wp_error($result)) {
            wp_die($result->get_error_message());
        }

        wp_redirect(admin_url('admin.php?page=mkv-orders&returned=1'));
        exit;
    }

    public static function process_return_order($order_id, $user_id = 0)
    {
        global $wpdb;
        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mkv_orders WHERE id=%d", $order_id));
        if (!$order || !in_array($order->status, array('paid', 'completed', 'shipping'))) {
            return new WP_Error('invalid_order', 'Không thể trả hàng cho đơn này.');
        }

        // Tạo phiếu trả hàng
        $return_code = 'TH' . date('ymdHis');
        $wpdb->insert("{$wpdb->prefix}mkv_returns", array(
            'return_code'  => $return_code,
            'order_id'     => $order_id,
            'customer_id'  => $order->customer_id,
            'location_id'  => $order->warehouse_id,
            'total_refund' => $order->total_amount,
            'created_by'   => $user_id
        ));
        $return_id = $wpdb->insert_id;

        // Xử lý từng sản phẩm trong đơn (Hoàn kho toàn bộ)
        $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mkv_order_items WHERE order_id=%d", $order_id));
        foreach ($items as $item) {
            $wpdb->insert("{$wpdb->prefix}mkv_return_items", array(
                'return_id'  => $return_id,
                'product_id' => $item->product_id,
                'qty'        => $item->qty,
                'price'      => $item->price,
                'subtotal'   => $item->subtotal
            ));

            // Hoàn kho bằng Atomic Update
            $wpdb->query($wpdb->prepare(
                "INSERT IGNORE INTO {$wpdb->prefix}mkv_inventory_stock (product_id, location_id, stock) VALUES (%d, %d, 0)",
                $item->product_id, $order->warehouse_id
            ));
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}mkv_inventory_stock SET stock = stock + %d WHERE product_id = %d AND location_id = %d",
                $item->qty, $item->product_id, $order->warehouse_id
            ));
            $total = (int) $wpdb->get_var($wpdb->prepare("SELECT SUM(stock) FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d", $item->product_id));
            update_post_meta($item->product_id, '_mkv_stock', $total);

            // Ghi log tồn kho
            $wpdb->insert("{$wpdb->prefix}mkv_inventory_logs", array(
                'product_id'  => $item->product_id,
                'location_id' => $order->warehouse_id,
                'type'        => 'in',
                'qty'         => $item->qty,
                'note'        => 'Khách trả hàng (Đơn ' . $order->order_code . ')',
                'created_by'  => $user_id
            ));
        }

        // Tạo Phiếu Chi trong sổ quỹ để hoàn tiền
        if (isset($order->paid_amount) && $order->paid_amount > 0) {
            $wpdb->insert("{$wpdb->prefix}mkv_cashbook", array(
                'type'         => 'chi',
                'method'       => $order->payment_method,
                'amount'       => $order->paid_amount,
                'reference_id' => $return_id,
                'customer_id'  => $order->customer_id ?: null,
                'note'         => 'Hoàn tiền trả hàng cho đơn ' . $order->order_code,
                'created_by'   => $user_id
            ));
        }

        // Hoàn trả / trừ điểm khách hàng
        if ($order->customer_id > 0) {
            $rate   = max(1, (int) get_option('mkv_points_rate', 100));
            $earned_points = (int) floor($order->total_amount / $rate);
            $used_points   = (int) $order->points_used;
            $points_diff   = (int) ($used_points - $earned_points); // Khôi phục điểm đã dùng, trừ điểm đã kiếm
            $debt_amount   = isset($order->debt_amount) ? $order->debt_amount : 0;
            
            if ($points_diff !== 0 || $order->total_amount > 0 || $debt_amount > 0) {
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}mkv_customers SET points = points + %d, total_spent = total_spent - %f, total_debt = total_debt - %f WHERE id = %d",
                    $points_diff, $order->total_amount, $debt_amount, $order->customer_id
                ));
            }
        }

        // Cập nhật trạng thái đơn thành Trả Hàng
        $wpdb->update("{$wpdb->prefix}mkv_orders", array('status' => 'returned'), array('id' => $order_id));

        return true;
    }

    public function handle_push_shipping()
    {
        $order_id = intval($_GET['id'] ?? 0);
        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'mkv_shipping_' . $order_id) || !current_user_can('mkv_manage_orders')) {
            wp_die('Không có quyền.');
        }

        $result = MKV_Shipping_Service::push_order($order_id);

        if (is_wp_error($result)) {
            wp_die($result->get_error_message());
        }

        wp_redirect(admin_url('admin.php?page=mkv-orders&pushed=1'));
        exit;
    }
}
