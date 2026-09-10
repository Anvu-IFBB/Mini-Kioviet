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
        add_action('admin_post_mkv_collect_order_debt',   array($this, 'handle_collect_order_debt'));
        add_action('admin_post_mkv_push_shipping',        array($this, 'handle_push_shipping'));
        add_action('admin_post_mkv_update_tracking_code', array($this, 'handle_update_tracking_code'));
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
            // P4-007: Use filemtime() for proper browser cache invalidation instead of time() which disables caching
            $pos_js_ver = file_exists(MKV_DIR . 'assets/js/admin-pos.js') ? filemtime(MKV_DIR . 'assets/js/admin-pos.js') : MKV_VERSION;
            wp_enqueue_script('mkv-pos-js', MKV_URL . 'assets/js/admin-pos.js', array('jquery'), $pos_js_ver, true);
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
        $customers = $wpdb->get_results("SELECT id, name, phone, address, points FROM {$wpdb->prefix}mkv_customers ORDER BY name ASC");
        // P4-003: Safety cap of 2000 products to prevent memory exhaustion on large catalogs
        $products  = get_posts(array('post_type' => 'mkv_product', 'numberposts' => 2000, 'post_status' => 'publish'));
        $locations = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mkv_locations WHERE status='active' ORDER BY id ASC");
        require_once MKV_DIR . 'includes/views/view-pos.php';
    }

    public function render_orders_list()
    {
        global $wpdb;

        $order_id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0);
        if ($order_id > 0) {
            $this->render_order_detail($order_id);
            return;
        }

        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $channel_filter = sanitize_key($_GET['channel'] ?? '');
        $where_parts = array();
        $where_values = array();
        if ($status_filter) {
            $where_parts[] = 'o.status = %s';
            $where_values[] = $status_filter;
        }
        if (in_array($channel_filter, array('pos', 'online', 'social', 'marketplace'), true)) {
            $where_parts[] = 'o.sales_channel = %s';
            $where_values[] = $channel_filter;
        } else {
            $channel_filter = '';
        }
        $where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';
        if ($where_values) $where = $wpdb->prepare($where, $where_values);

        $per_page = 20;
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($paged - 1) * $per_page;

        $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mkv_orders o $where");
        $total_pages = ceil($total_items / $per_page);

        $orders = $wpdb->get_results(
            "SELECT o.*, c.name as customer_name
             FROM {$wpdb->prefix}mkv_orders o
             LEFT JOIN {$wpdb->prefix}mkv_customers c ON o.customer_id = c.id
             $where
             ORDER BY o.id DESC LIMIT $per_page OFFSET $offset"
        );
        require_once MKV_DIR . 'includes/views/view-orders.php';
    }

    /**
     * Render Single Order Detail View (KiotViet Pro UI/UX)
     */
    public function render_order_detail($order_id)
    {
        global $wpdb;

        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT o.*, 
                    c.name as customer_name, c.phone as customer_phone, c.address as customer_full_address, c.points as customer_points, c.total_debt as customer_total_debt, c.total_spent as customer_total_spent,
                    u.display_name as cashier_name,
                    l.name as warehouse_name
             FROM {$wpdb->prefix}mkv_orders o
             LEFT JOIN {$wpdb->prefix}mkv_customers c ON o.customer_id = c.id
             LEFT JOIN {$wpdb->users} u ON o.created_by = u.ID
             LEFT JOIN {$wpdb->prefix}mkv_locations l ON o.warehouse_id = l.id
             WHERE o.id = %d",
            $order_id
        ));

        if (!$order) {
            echo '<div class="notice notice-error"><p>' . esc_html(mkv__('Không tìm thấy đơn hàng yêu cầu.')) . '</p></div>';
            // P4-004: Reduced fallback limit to 20 (consistent with main list pagination)
            $orders = $wpdb->get_results(
                "SELECT o.*, c.name as customer_name
                 FROM {$wpdb->prefix}mkv_orders o
                 LEFT JOIN {$wpdb->prefix}mkv_customers c ON o.customer_id = c.id
                 ORDER BY o.id DESC LIMIT 20"
            );
            require_once MKV_DIR . 'includes/views/view-orders.php';
            return;
        }

        // Fetch Order Items
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT oi.*, p.post_title as product_name
             FROM {$wpdb->prefix}mkv_order_items oi
             LEFT JOIN {$wpdb->posts} p ON oi.product_id = p.ID
             WHERE oi.order_id = %d
             ORDER BY oi.id ASC",
            $order_id
        ));

        foreach ($items as &$it) {
            $it->sku = get_post_meta($it->product_id, '_mkv_sku', true) ?: ('SP' . str_pad($it->product_id, 4, '0', STR_PAD_LEFT));
            $it->barcode = get_post_meta($it->product_id, '_mkv_barcode', true) ?: '';
            $it->unit = get_post_meta($it->product_id, '_mkv_unit', true) ?: 'Cái';
            $thumb = get_the_post_thumbnail_url($it->product_id, 'thumbnail');
            $it->thumb = $thumb ?: '';
        }
        unset($it);

        // Fetch Cashbook Transactions related to this order
        $cashbook_logs = $wpdb->get_results($wpdb->prepare(
            "SELECT cb.*, u.display_name as staff_name
             FROM {$wpdb->prefix}mkv_cashbook cb
             LEFT JOIN {$wpdb->users} u ON cb.created_by = u.ID
             WHERE cb.reference_id = %d
             ORDER BY cb.id ASC",
            $order_id
        ));

        require_once MKV_DIR . 'includes/views/view-order-detail.php';
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
        $client_order_code = sanitize_text_field($_POST['order_code'] ?? '');
        if (!empty($client_order_code) && preg_match('/^[A-Za-z0-9_-]{6,50}$/', $client_order_code)) {
            $order_code = $client_order_code;
        } else {
            $order_code = 'DH' . date('ymdHis') . rand(10, 99);
        }
        $is_draft       = isset($_POST['is_draft']) && $_POST['is_draft'] == '1';
        $sales_channel  = sanitize_text_field($_POST['sales_channel'] ?? 'pos');
        $allowed_channels = array('pos', 'online', 'social', 'marketplace');
        if (!in_array($sales_channel, $allowed_channels, true)) {
            $sales_channel = 'pos';
        }
        
        $paid_str       = sanitize_text_field($_POST['paid_amount'] ?? '');
        $paid_input     = (float) preg_replace('/\D/', '', $paid_str);
        $shipping_requested = !empty($_POST['is_enable_shipping']);
        $shipping_address_input = sanitize_textarea_field($_POST['customer_address'] ?? '');
        $shipping_phone_input = sanitize_text_field($_POST['shipping_phone'] ?? '');

        if ($shipping_requested && $customer_id > 0) {
            $customer_shipping = $wpdb->get_row($wpdb->prepare(
                "SELECT address, phone FROM {$wpdb->prefix}mkv_customers WHERE id=%d",
                $customer_id
            ));
            if ($customer_shipping) {
                $shipping_address_input = $shipping_address_input ?: (string) $customer_shipping->address;
                $shipping_phone_input = $shipping_phone_input ?: (string) $customer_shipping->phone;
            }
        }

        if ($shipping_requested && ($shipping_address_input === '' || $shipping_phone_input === '')) {
            $err_msg = 'Đơn giao hàng cần có địa chỉ và số điện thoại người nhận.';
            if ($is_ajax) wp_send_json_error($err_msg);
            wp_die($err_msg);
        }

        // Validate tồn kho từng sản phẩm (không bắt lỗi tồn kho nếu là lưu nháp)
        foreach ($_POST['products'] as $item) {
            $pid = intval($item['id'] ?? 0);
            $qty = intval($item['qty'] ?? 0);
            if ($pid <= 0 || $qty <= 0 || !get_post($pid) || get_post_type($pid) !== 'mkv_product') {
                $err_msg = 'Sản phẩm hoặc số lượng không hợp lệ.';
                if ($is_ajax) {
                    if (ob_get_level() > 0) ob_clean();
                    wp_send_json_error($err_msg);
                }
                wp_die($err_msg);
            }
        }

        if (!$is_draft) {
            foreach ($_POST['products'] as $item) {
            $pid  = intval($item['id'] ?? 0);
            $qty  = intval($item['qty'] ?? 0);
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
                
                $this->deduct_stock($pid, $location_id, $qty, $order_code, (bool)$allow_negative);
            }
        }
        
        // Xử lý phí giao hàng trước khi tính tổng tiền và chiết khấu điểm
        $shipping_fee = 0;
        $shipping_provider = null;
        $customer_address = null;
        $shipping_phone = null;
        if ($shipping_requested || !empty($_POST['customer_address']) || (isset($_POST['shipping_fee']) && $_POST['shipping_fee'] > 0)) {
            $shipping_fee = max(0, round((float) ($_POST['shipping_fee'] ?? 0)));
            $shipping_provider = sanitize_text_field($_POST['shipping_provider'] ?? get_option('mkv_shipping_provider', 'ghtk'));
            $customer_address = $shipping_address_input;
            $shipping_phone = $shipping_phone_input;
        }

        $vat_included = (int) get_option('mkv_vat_included', 0);
        if ($vat_included) {
            $tax_amount = round($vat_rate > 0 ? ($subtotal - ($subtotal / (1 + $vat_rate))) : 0);
            $total_amount = round(max(0, $subtotal) + $shipping_fee);
        } else {
            $tax_amount = round($subtotal * $vat_rate);
            $total_amount = round(max(0, $subtotal + $tax_amount) + $shipping_fee);
        }

        // Tính discount điểm
        $points_discount = 0;
        $point_value = (float) get_option('mkv_point_value', 1); // 1 điểm = x VNĐ
        if ($point_value <= 0) $point_value = 1;
        
        if ($points_used > 0 && $customer_id > 0) {
            $customer = $wpdb->get_row($wpdb->prepare("SELECT points FROM {$wpdb->prefix}mkv_customers WHERE id=%d FOR UPDATE", $customer_id));
            if ($customer && $customer->points >= $points_used) {
                // Giới hạn điểm dùng không vượt quá tổng tiền
                $max_points_needed = ceil($total_amount / $point_value);
                $points_used = (int) min($points_used, $max_points_needed);
                
                $points_discount = $points_used * $point_value;
                if (!$is_draft) {
                    $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->prefix}mkv_customers SET points = GREATEST(0, points - %d) WHERE id=%d AND points >= %d",
                        $points_used, $customer_id, $points_used
                    ));
                }
            } else {
                $points_used = 0;
            }
        }

        // Tính lại tổng tiền sau khi trừ giảm giá và cộng phí ship
        if ($vat_included) {
            $total_amount = round(max(0, $subtotal - $points_discount) + $shipping_fee);
        } else {
            $total_amount = round(max(0, $subtotal + $tax_amount - $points_discount) + $shipping_fee);
        }
        
        // Tính toán thanh toán và công nợ (luôn làm tròn số nguyên tiền VNĐ)
        if ($is_draft) {
            $paid_amount = 0;
            $debt_amount = 0;
        } else {
            // Khách có thể đưa dư, nhưng hệ thống chỉ ghi nhận thu tối đa bằng tổng tiền.
            // Nếu đưa thiếu, phần còn lại là công nợ.
            $paid_amount = round(min($total_amount, $paid_input));
            $debt_amount = round(max(0, $total_amount - $paid_amount));
        }

        $cod_amount = !empty($_POST['is_enable_shipping'])
            ? max(0, min($total_amount, $total_amount - $paid_amount))
            : 0;
        $customer_debt_amount = $shipping_requested ? 0 : $debt_amount;
        if ($is_draft) {
            $payment_status = 'unpaid';
        } elseif ($cod_amount > 0) {
            $payment_status = 'cod_pending';
        } elseif ($paid_amount >= $total_amount) {
            $payment_status = 'paid';
        } elseif ($paid_amount > 0) {
            $payment_status = 'partially_paid';
        } else {
            $payment_status = 'unpaid';
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
            'status'       => $is_draft ? 'draft' : ($shipping_requested ? 'pending' : 'paid'),
            'payment_status' => $payment_status,
            'shipping_provider' => $shipping_provider,
            'shipping_fee' => $shipping_fee,
            'customer_address' => $customer_address,
            'sales_channel' => $sales_channel,
            'fulfillment_status' => $is_draft ? 'pending' : ($shipping_provider ? 'ready' : 'completed'),
            'cod_amount' => $cod_amount,
            'customer_debt_amount' => $customer_debt_amount,
            'shipping_phone' => $shipping_phone,
        ), array('id' => $order_id));

        // Tích điểm (chỉ tích khi đơn hàng là paid, KHÔNG phải draft)
        $earned_points = 0;
        if (!$is_draft && $customer_id > 0) {
            $rate   = max(1, (int) get_option('mkv_points_rate', 100)); // Tránh division by zero
            $earned_points = (int) floor($total_amount / $rate);
            
            // Nếu có cộng điểm hoặc có nợ, cập nhật customer
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}mkv_customers SET points=points+%d, total_spent=total_spent+%f, total_debt=total_debt+%f WHERE id=%d",
                $earned_points, $total_amount, $customer_debt_amount, $customer_id
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

    private function deduct_stock(int $product_id, int $location_id, int $qty, string $order_code, bool $allow_negative = false)
    {
        global $wpdb;
        
        // 1. Chèn dòng mới nếu chưa tồn tại (INSERT IGNORE)
        $wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO {$wpdb->prefix}mkv_inventory_stock (product_id, location_id, stock) VALUES (%d, %d, 0)",
            $product_id, $location_id
        ));
        
        // 2. Trừ kho nguyên tử (Atomic Update có điều kiện nếu không cho phép âm)
        if ($allow_negative) {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}mkv_inventory_stock SET stock = stock - %d WHERE product_id = %d AND location_id = %d",
                $qty, $product_id, $location_id
            ));
        } else {
            $update_res = $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}mkv_inventory_stock SET stock = stock - %d WHERE product_id = %d AND location_id = %d AND stock >= %d",
                $qty, $product_id, $location_id, $qty
            ));
            if ($update_res === false || $update_res === 0) {
                throw new Exception('Sản phẩm "' . get_the_title($product_id) . '" không đủ tồn kho để xuất bán.');
            }
        }

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
        $wpdb->query('START TRANSACTION');
        try {
            $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mkv_orders WHERE id=%d FOR UPDATE", $order_id));
            if (!$order) {
                $wpdb->query('ROLLBACK');
                wp_die('Đơn hàng không tồn tại.');
            }

            if ($order->status === $new_status) {
                $wpdb->query('COMMIT');
                wp_redirect(admin_url('admin.php?page=mkv-orders&status_updated=1'));
                exit;
            }

            if (in_array($order->status, array('cancelled', 'returned'), true)) {
                $wpdb->query('ROLLBACK');
                wp_die('Đơn hàng đã ở trạng thái kết thúc (Đã hủy hoặc Đã trả hàng), không thể cập nhật.');
            }

            $is_previously_paid = in_array($order->payment_status ?? '', array('paid', 'cod_settled'), true);
            $is_now_paid        = in_array($new_status, array('paid', 'shipping', 'completed'));

            if ($is_previously_paid && !$is_now_paid) {
                $wpdb->query('ROLLBACK');
                wp_die('Lỗi logic: Không được phép chuyển ngược trạng thái từ Đã thanh toán về chưa thanh toán. Vui lòng sử dụng chức năng Trả Hàng hoặc Hủy Đơn.');
            }
            if ($order->status !== 'draft' && $new_status === 'draft') {
                $wpdb->query('ROLLBACK');
                wp_die('Lỗi logic: Không thể chuyển trạng thái về Nháp.');
            }

            // Nếu chuyển từ draft sang trạng thái khác -> trừ kho & trừ điểm
            if ($order->status === 'draft' && $new_status !== 'draft' && $new_status !== 'cancelled') {
                $allow_negative = (int) get_option('mkv_allow_negative_stock', 0);
                $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mkv_order_items WHERE order_id=%d", $order_id));
                
                // Check tồn kho trước
                if (!$allow_negative) {
                    foreach ($items as $item) {
                        $stock_row = $wpdb->get_row($wpdb->prepare(
                            "SELECT SUM(stock) as s FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d AND location_id=%d FOR UPDATE",
                            $item->product_id, $order->warehouse_id
                        ));
                        $avail = ($stock_row && $stock_row->s !== null) ? (int)$stock_row->s : 0;
                        if ($avail < $item->qty) {
                            throw new Exception('Sản phẩm "' . get_the_title($item->product_id) . '" không đủ tồn kho (còn ' . $avail . ', cần ' . $item->qty . '). Không thể duyệt đơn.');
                        }
                    }
                }
                
                foreach ($items as $item) {
                    $this->deduct_stock($item->product_id, $order->warehouse_id, $item->qty, $order->order_code, (bool)$allow_negative);
                }
                
                // Trừ điểm đã dùng
                if ($order->customer_id > 0 && $order->points_used > 0) {
                    $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->prefix}mkv_customers SET points = points - %d WHERE id=%d",
                        $order->points_used, $order->customer_id
                    ));
                }
            }

            $fulfillment_status = 'pending';
            if ($new_status === 'shipping') $fulfillment_status = 'in_transit';
            if ($new_status === 'completed') $fulfillment_status = 'delivered';
            if ($new_status === 'cancelled') $fulfillment_status = 'cancelled';

            $wpdb->update("{$wpdb->prefix}mkv_orders", array(
                'status' => $new_status,
                'fulfillment_status' => $fulfillment_status,
            ), array('id' => $order_id));

            // Tích điểm và cập nhật Sổ quỹ nếu đơn hàng chuyển sang nhóm đã thanh toán
            if ($is_now_paid && !$is_previously_paid && ($order->payment_status ?? '') !== 'cod_pending') {
                $customer_debt_to_clear = (float) ($order->customer_debt_amount ?? $order->debt_amount);
                $amount_to_collect = max(0.0, (float) $order->total_amount - (float) $order->paid_amount);
                
                // Sổ quỹ (chỉ thu số tiền còn thiếu, không thu lại phần đã thanh toán trước đó)
                if ($amount_to_collect > 0) {
                    $wpdb->insert("{$wpdb->prefix}mkv_cashbook", array(
                        'type'         => 'thu',
                        'method'       => $order->payment_method ?: 'cash',
                        'amount'       => $amount_to_collect,
                        'reference_id' => $order_id,
                        'note'         => 'Thu tiền bán hàng (Đơn ' . $order->order_code . ')',
                        'created_by'   => get_current_user_id(),
                        'created_at'   => current_time('mysql')
                    ));
                }

                // Cập nhật paid_amount trong order thành total_amount
                $wpdb->update("{$wpdb->prefix}mkv_orders", array(
                    'paid_amount' => $order->total_amount,
                    'debt_amount' => 0,
                    'customer_debt_amount' => 0,
                    'payment_status' => 'paid',
                ), array('id' => $order_id));

                if ($order->customer_id && $customer_debt_to_clear > 0) {
                    $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->prefix}mkv_customers SET total_debt = GREATEST(0, total_debt - %f) WHERE id=%d",
                        $customer_debt_to_clear,
                        $order->customer_id
                    ));
                }

                if ($order->customer_id) {
                    $rate   = max(1, (int) get_option('mkv_points_rate', 100));
                    $points = (int) floor($order->total_amount / $rate);
                    
                    $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->prefix}mkv_customers SET points=points+%d, total_spent=total_spent+%f WHERE id=%d",
                        $points, $order->total_amount, $order->customer_id
                    ));
                }
            }

            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_die('Lỗi cập nhật trạng thái đơn: ' . $e->getMessage());
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

    /**
     * Parse monetary amount input robustly supporting Vietnamese and standard float formats
     */
    public static function sanitize_money($raw)
    {
        if (is_int($raw)) return (float) $raw;
        if (is_float($raw)) return $raw;
        if (!is_scalar($raw)) return 0.0;

        $raw_str = trim((string) $raw);
        if ($raw_str === '') return 0.0;

        $is_negative = (strpos($raw_str, '-') === 0);
        $sign = $is_negative ? -1.0 : 1.0;

        // Remove currency symbols and non-essential whitespace e.g. "500.000 đ" -> "500.000"
        $str = preg_replace('/[^\d.,]/u', '', $raw_str);
        if ($str === '') return 0.0;

        // Both dots and commas present e.g. 1.250.000,50 (VN) or 1,250,000.50 (US)
        if (strpos($str, ',') !== false && strpos($str, '.') !== false) {
            $last_dot = strrpos($str, '.');
            $last_comma = strrpos($str, ',');
            if ($last_comma > $last_dot) {
                // VN: 1.250.000,50
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                // US: 1,250,000.50
                $str = str_replace(',', '', $str);
            }
            return $sign * (float) $str;
        }

        // Multiple dots e.g. "16.514.227" -> all thousand separators
        if (substr_count($str, '.') > 1) {
            return $sign * (float) str_replace('.', '', $str);
        }

        // Single dot e.g. "272.727" vs "272727.27"
        if (strpos($str, '.') !== false) {
            $parts = explode('.', $str);
            // If exactly 3 digits after the dot (e.g. 272.727, 500.000, 10.000) -> thousand separator in VND!
            if (strlen($parts[1]) === 3) {
                return $sign * (float) ($parts[0] . $parts[1]);
            }
            // Standard float e.g. 272727.27
            return $sign * (float) $str;
        }

        // Commas without dots
        if (strpos($str, ',') !== false) {
            $parts = explode(',', $str);
            if (count($parts) > 2 || strlen($parts[1]) === 3) {
                return $sign * (float) implode('', $parts);
            }
            return $sign * (float) ($parts[0] . '.' . $parts[1]);
        }

        return $sign * (float) preg_replace('/\D/', '', $str);
    }

    public function handle_collect_order_debt()
    {
        $order_id = intval($_POST['order_id'] ?? 0);
        if (!current_user_can('mkv_manage_orders') || !wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mkv_collect_order_debt_' . $order_id)) {
            wp_die('Không có quyền.');
        }

        global $wpdb;
        $amount = self::sanitize_money($_POST['amount'] ?? '');
        $method = sanitize_key($_POST['method'] ?? 'cash');
        if (!in_array($method, array('cash', 'transfer', 'card'), true)) $method = 'cash';

        $wpdb->query('START TRANSACTION');
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mkv_orders WHERE id=%d FOR UPDATE",
            $order_id
        ));
        if (!$order) {
            $wpdb->query('ROLLBACK');
            wp_die('Đơn hàng không tồn tại.');
        }

        $is_cod = ($order->payment_status ?? '') === 'cod_pending';
        $unpaid = max(0.0, (float) $order->total_amount - (float) $order->paid_amount);

        if ($is_cod) {
            $due = (float) $order->cod_amount > 0 ? (float) $order->cod_amount : $unpaid;
        } else {
            if ((float) ($order->customer_debt_amount ?? 0) > 0) {
                $due = (float) $order->customer_debt_amount;
            } elseif ((float) ($order->debt_amount ?? 0) > 0) {
                $due = (float) $order->debt_amount;
            } else {
                $due = $unpaid;
            }
        }

        // Allow a 10 VND rounding tolerance for fractional VND taxes/discounts
        $tolerance = 10.0;
        if ($due <= 0 || $amount <= 0 || ($amount - $due) > $tolerance) {
            $wpdb->query('ROLLBACK');
            wp_die('Số tiền thu không hợp lệ.');
        }

        $is_full = ($amount >= $due - $tolerance);
        $new_paid = $is_full ? (float) $order->total_amount : min((float) $order->total_amount, (float) $order->paid_amount + $amount);
        $new_debt = $is_cod ? 0.0 : ($is_full ? 0.0 : max(0.0, $due - $amount));
        $new_payment_status = $is_cod
            ? ($is_full ? 'cod_settled' : 'cod_pending')
            : ($is_full ? 'paid' : 'partially_paid');

        $order_update = array(
            'paid_amount'    => $new_paid,
            'payment_status' => $new_payment_status,
        );
        if ($is_cod) {
            if ($is_full) {
                $order_update['cod_amount'] = 0;
                $order_update['cod_settled_at'] = current_time('mysql');
                $order_update['cod_settlement_ref'] = 'COD-' . $order->order_code . '-' . time();
            } else {
                $order_update['cod_amount'] = $new_debt;
            }
            $order_update['debt_amount'] = 0;
            $order_update['customer_debt_amount'] = 0;
        } else {
            $order_update['debt_amount'] = $new_debt;
            $order_update['customer_debt_amount'] = $new_debt;
        }

        if ($wpdb->update("{$wpdb->prefix}mkv_orders", $order_update, array('id' => $order_id)) === false) {
            $wpdb->query('ROLLBACK');
            wp_die('Không thể cập nhật thanh toán.');
        }

        if (!$is_cod && $order->customer_id) {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}mkv_customers
                 SET total_debt = GREATEST(0, total_debt - %f)
                 WHERE id=%d",
                $amount,
                $order->customer_id
            ));
        }

        $note = $is_cod
            ? 'Đối soát COD đơn ' . $order->order_code
            : 'Thu công nợ đơn ' . $order->order_code;
        if ($wpdb->insert("{$wpdb->prefix}mkv_cashbook", array(
            'type'         => 'thu',
            'method'       => $method,
            'amount'       => $amount,
            'reference_id' => $order_id,
            'customer_id'  => $order->customer_id ?: null,
            'note'         => $note,
            'created_by'   => get_current_user_id(),
            'created_at'   => current_time('mysql'),
        )) === false) {
            $wpdb->query('ROLLBACK');
            wp_die('Không thể ghi phiếu thu.');
        }

        // Tạo thông báo nội bộ
        $wpdb->insert("{$wpdb->prefix}mkv_notifications", array(
            'type'       => 'order',
            'title'      => ($is_cod ? 'Đối soát COD thành công: ' : 'Thu nợ thành công: ') . $order->order_code,
            'message'    => 'Số tiền: ' . number_format($amount, 0, ',', '.') . ' ₫ (' . ($method === 'cash' ? 'Tiền mặt' : ($method === 'transfer' ? 'Chuyển khoản' : 'Thẻ')) . ')',
            'is_read'    => 0,
            'created_at' => current_time('mysql'),
        ));

        $wpdb->query('COMMIT');
        $redirect_to = wp_get_referer() ?: admin_url('admin.php?page=mkv-orders&payment_collected=1');
        wp_safe_redirect(add_query_arg('payment_collected', '1', $redirect_to));
        exit;
    }

    public static function process_cancel_order($order_id, $user_id = 0)
    {
        global $wpdb;
        $wpdb->query('START TRANSACTION');
        try {
            $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mkv_orders WHERE id=%d FOR UPDATE", $order_id));
            if (!$order || in_array($order->status, array('cancelled', 'returned'), true)) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('invalid_order', 'Đơn hàng không hợp lệ hoặc đã bị hủy/trả trước đó.');
            }

            $is_paid_shipping = in_array($order->status, array('paid', 'shipping', 'completed'), true);

            // 1. Hoàn trả tiền vào sổ quỹ (chỉ khi đã thanh toán)
            if ($is_paid_shipping && $order->paid_amount > 0) {
                $cb_res = $wpdb->insert("{$wpdb->prefix}mkv_cashbook", array(
                    'type'         => 'chi',
                    'method'       => $order->payment_method ?: 'cash',
                    'amount'       => $order->paid_amount,
                    'reference_id' => $order_id,
                    'customer_id'  => $order->customer_id ?: null,
                    'note'         => 'Hoàn tiền - Hủy đơn ' . $order->order_code,
                    'created_by'   => $user_id,
                    'created_at'   => current_time('mysql')
                ));
                if ($cb_res === false) {
                    if (!empty($wpdb->last_error)) {
                        error_log('[Mini-KiotViet] Cancel order cashbook error: ' . $wpdb->last_error);
                    }
                    throw new Exception('Lỗi tạo phiếu chi hoàn tiền trong sổ quỹ.');
                }
            }

            // 2. Hoàn trả / trừ điểm khách hàng
            if ($order->customer_id > 0) {
                $used_points   = $order->status !== 'draft' ? (int) $order->points_used : 0;
                $earned_points = 0;
                $total_spent   = 0;
                $debt_amount   = (float) ($order->customer_debt_amount ?? $order->debt_amount ?? 0);
                
                // Chỉ thu hồi điểm tích lũy và total_spent nếu đơn này ĐÃ cộng
                if ($is_paid_shipping) {
                    $rate = max(1, (int) get_option('mkv_points_rate', 100));
                    $earned_points = (int) floor($order->total_amount / $rate);
                    $total_spent = $order->total_amount;
                }
                
                $points_diff = (int) ($used_points - $earned_points); // Khôi phục điểm đã dùng, trừ điểm đã kiếm
                
                if ($points_diff !== 0 || $total_spent > 0 || $debt_amount > 0) {
                    $cust_res = $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->prefix}mkv_customers SET points = points + %d, total_spent = GREATEST(0, total_spent - %f), total_debt = GREATEST(0, total_debt - %f) WHERE id = %d",
                        $points_diff, $total_spent, $debt_amount, $order->customer_id
                    ));
                    if ($cust_res === false) {
                        if (!empty($wpdb->last_error)) {
                            error_log('[Mini-KiotViet] Cancel order customer error: ' . $wpdb->last_error);
                        }
                        throw new Exception('Lỗi cập nhật điểm và công nợ khách hàng.');
                    }
                }
            }

            // 3. Hoàn trả tồn kho (chỉ áp dụng cho các đơn KHÁC draft vì draft chưa từng trừ kho)
            if ($order->status !== 'draft') {
                $items = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}mkv_order_items WHERE order_id=%d", $order_id
                ));
                foreach ($items as $item) {
                    $wpdb->query($wpdb->prepare(
                        "INSERT IGNORE INTO {$wpdb->prefix}mkv_inventory_stock (product_id, location_id, stock) VALUES (%d, %d, 0)",
                        $item->product_id, $order->warehouse_id
                    ));
                    $stock_res = $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->prefix}mkv_inventory_stock SET stock = stock + %d WHERE product_id = %d AND location_id = %d",
                        $item->qty, $item->product_id, $order->warehouse_id
                    ));
                    if ($stock_res === false) {
                        throw new Exception('Lỗi khôi phục tồn kho sản phẩm ID ' . $item->product_id);
                    }
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

            $order_res = $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}mkv_orders 
                 SET status = 'cancelled', fulfillment_status = 'cancelled', debt_amount = 0, customer_debt_amount = 0 
                 WHERE id = %d AND status NOT IN ('cancelled', 'returned')",
                $order_id
            ));
            if ($order_res === false || $order_res === 0) {
                if (!empty($wpdb->last_error)) {
                    error_log('[Mini-KiotViet] Cancel order status error: ' . $wpdb->last_error);
                }
                throw new Exception('Lỗi cập nhật trạng thái đơn hàng (đơn có thể đã bị thay đổi đồng thời).');
            }

            $wpdb->query('COMMIT');
            return true;
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('cancel_order_failed', $e->getMessage());
        }
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
        $wpdb->query('START TRANSACTION');
        try {
            $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mkv_orders WHERE id=%d FOR UPDATE", $order_id));
            if (!$order || !in_array($order->status, array('paid', 'completed', 'shipping'), true)) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('invalid_order', 'Không thể trả hàng cho đơn này.');
            }

            $existing_return = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}mkv_returns WHERE order_id = %d LIMIT 1",
                $order_id
            ));
            if ($existing_return) {
                $wpdb->query('ROLLBACK');
                return new WP_Error('already_returned', 'Đơn hàng này đã có phiếu trả hàng.');
            }

            // Tạo phiếu trả hàng (bổ sung số ngẫu nhiên tránh trùng return_code khi chạy đồng thời cùng giây)
            $return_code = 'TH' . date('ymdHis') . rand(10, 99);
            $actual_refund = min((float)$order->total_amount, (float)($order->paid_amount ?? 0));
            $ret_res = $wpdb->insert("{$wpdb->prefix}mkv_returns", array(
                'return_code'  => $return_code,
                'order_id'     => $order_id,
                'customer_id'  => $order->customer_id,
                'location_id'  => $order->warehouse_id,
                'total_refund' => $actual_refund,
                'created_by'   => $user_id,
                'created_at'   => current_time('mysql')
            ));
            if ($ret_res === false) {
                if (!empty($wpdb->last_error)) {
                    error_log('[Mini-KiotViet] Return order insert error: ' . $wpdb->last_error);
                }
                throw new Exception('Không thể tạo phiếu trả hàng.');
            }
            $return_id = $wpdb->insert_id;

            // Xử lý từng sản phẩm trong đơn (Hoàn kho toàn bộ)
            $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mkv_order_items WHERE order_id=%d", $order_id));
            foreach ($items as $item) {
                $item_res = $wpdb->insert("{$wpdb->prefix}mkv_return_items", array(
                    'return_id'  => $return_id,
                    'product_id' => $item->product_id,
                    'qty'        => $item->qty,
                    'price'      => $item->price,
                    'subtotal'   => $item->subtotal
                ));
                if ($item_res === false) {
                    if (!empty($wpdb->last_error)) {
                        error_log('[Mini-KiotViet] Return order item error: ' . $wpdb->last_error);
                    }
                    throw new Exception('Lỗi lưu chi tiết sản phẩm trả hàng.');
                }

                // Hoàn kho bằng Atomic Update
                $wpdb->query($wpdb->prepare(
                    "INSERT IGNORE INTO {$wpdb->prefix}mkv_inventory_stock (product_id, location_id, stock) VALUES (%d, %d, 0)",
                    $item->product_id, $order->warehouse_id
                ));
                $stock_res = $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}mkv_inventory_stock SET stock = stock + %d WHERE product_id = %d AND location_id = %d",
                    $item->qty, $item->product_id, $order->warehouse_id
                ));
                if ($stock_res === false) {
                    throw new Exception('Lỗi khôi phục kho sản phẩm ID ' . $item->product_id);
                }
                $total = (int) $wpdb->get_var($wpdb->prepare("SELECT SUM(stock) FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d", $item->product_id));
                update_post_meta($item->product_id, '_mkv_stock', $total);

                // Ghi log tồn kho
                $wpdb->insert("{$wpdb->prefix}mkv_inventory_logs", array(
                    'product_id'  => $item->product_id,
                    'location_id' => $order->warehouse_id,
                    'type'        => 'in',
                    'qty'         => $item->qty,
                    'note'        => 'Khách trả hàng (Đơn ' . $order->order_code . ')',
                    'created_by'  => $user_id,
                    'created_at'  => current_time('mysql')
                ));
            }

            // Tạo Phiếu Chi trong sổ quỹ để hoàn tiền (chỉ hoàn số tiền thực tế khách đã trả)
            if ($actual_refund > 0) {
                $cb_res = $wpdb->insert("{$wpdb->prefix}mkv_cashbook", array(
                    'type'         => 'chi',
                    'method'       => $order->payment_method ?: 'cash',
                    'amount'       => $actual_refund,
                    'reference_id' => $return_id,
                    'customer_id'  => $order->customer_id ?: null,
                    'note'         => 'Hoàn tiền trả hàng cho đơn ' . $order->order_code,
                    'created_by'   => $user_id,
                    'created_at'   => current_time('mysql')
                ));
                if ($cb_res === false) {
                    if (!empty($wpdb->last_error)) {
                        error_log('[Mini-KiotViet] Return order cashbook error: ' . $wpdb->last_error);
                    }
                    throw new Exception('Lỗi tạo phiếu chi hoàn tiền trong sổ quỹ.');
                }
            }

            // Hoàn trả / trừ điểm khách hàng
            if ($order->customer_id > 0) {
                $rate   = max(1, (int) get_option('mkv_points_rate', 100));
                $earned_points = (int) floor($order->total_amount / $rate);
                $used_points   = (int) $order->points_used;
                $points_diff   = (int) ($used_points - $earned_points); // Khôi phục điểm đã dùng, trừ điểm đã kiếm
                $debt_amount   = (float) ($order->customer_debt_amount ?? $order->debt_amount ?? 0);
                
                if ($points_diff !== 0 || $order->total_amount > 0 || $debt_amount > 0) {
                    $cust_res = $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->prefix}mkv_customers SET points = points + %d, total_spent = GREATEST(0, total_spent - %f), total_debt = GREATEST(0, total_debt - %f) WHERE id = %d",
                        $points_diff, $order->total_amount, $debt_amount, $order->customer_id
                    ));
                    if ($cust_res === false) {
                        if (!empty($wpdb->last_error)) {
                            error_log('[Mini-KiotViet] Return order customer error: ' . $wpdb->last_error);
                        }
                        throw new Exception('Lỗi cập nhật điểm và công nợ khách hàng.');
                    }
                }
            }

            // Cập nhật trạng thái đơn thành Trả Hàng (State guard: chỉ cập nhật nếu trạng thái vẫn thuộc nhóm được phép trả)
            $ord_res = $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}mkv_orders 
                 SET status = 'returned', fulfillment_status = 'returned', debt_amount = 0, customer_debt_amount = 0 
                 WHERE id = %d AND status IN ('paid', 'completed', 'shipping')",
                $order_id
            ));
            if ($ord_res === false || $ord_res === 0) {
                if (!empty($wpdb->last_error)) {
                    error_log('[Mini-KiotViet] Return order status error: ' . $wpdb->last_error);
                }
                throw new Exception('Lỗi cập nhật trạng thái đơn hàng (đơn có thể đã bị thay đổi đồng thời).');
            }

            $wpdb->query('COMMIT');
            return true;
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('return_order_failed', $e->getMessage());
        }
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
    public function handle_update_tracking_code()
    {
        $order_id = intval($_POST['order_id'] ?? 0);
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mkv_tracking_' . $order_id) || !current_user_can('mkv_manage_orders')) {
            wp_die('Không có quyền.');
        }

        $tracking_code = sanitize_text_field($_POST['tracking_code'] ?? '');
        if (empty($tracking_code)) {
            wp_die('Vui lòng nhập mã vận đơn.');
        }

        global $wpdb;
        $order = $wpdb->get_row($wpdb->prepare("SELECT status FROM {$wpdb->prefix}mkv_orders WHERE id = %d", $order_id));
        if (!$order) {
            wp_die('Đơn hàng không tồn tại.');
        }

        // P4-008: Use strict comparison (true) consistent with rest of codebase
        if (in_array($order->status, array('cancelled', 'returned'), true)) {
            wp_die('Không thể cập nhật mã vận đơn cho đơn hàng đã hủy hoặc hoàn trả.');
        }

        $wpdb->update("{$wpdb->prefix}mkv_orders", array(
            'tracking_code'      => $tracking_code,
            'status'             => 'shipping',
            'fulfillment_status' => 'in_transit'
        ), array('id' => $order_id));

        wp_redirect(admin_url('admin.php?page=mkv-orders&action=detail&id=' . $order_id . '&updated=tracking'));
        exit;
    }
}
