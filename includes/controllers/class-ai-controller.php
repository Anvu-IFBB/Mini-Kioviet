<?php
if (!defined('ABSPATH')) exit;

class MKV_AI_Controller
{
    public function __construct()
    {
        add_action('wp_ajax_mkv_ai_chat', array($this, 'handle_chat'));
    }

    public function handle_chat()
    {
        // Phân quyền: Yêu cầu đăng nhập và có quyền quản lý của MKV
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => mkv__('Vui lòng đăng nhập để sử dụng Trợ lý AI.')));
        }

        if (!current_user_can('mkv_manage_dashboard') && !current_user_can('mkv_manage_orders') && !current_user_can('mkv_manage_products') && !current_user_can('mkv_manage_cashbook') && !current_user_can('mkv_manage_inventory') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => mkv__('Không có quyền truy cập.')));
        }

        check_ajax_referer('mkv_ai_chat_nonce', 'nonce');

        $ai_enable = get_option('mkv_ai_enable', 1);
        if (!$ai_enable) {
            wp_send_json_error(array('message' => mkv__('Tính năng Trợ lý AI đang bị tắt.')));
        }

        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
        $history_json = isset($_POST['history']) ? wp_unslash($_POST['history']) : '[]';
        $history = json_decode($history_json, true);
        $current_page = isset($_POST['current_page']) ? sanitize_text_field($_POST['current_page']) : '';
        // Bỏ interaction_id cũ vì giờ ta chạy Stateless Agents
        $interaction_id = uniqid('mkv_chat_');

        if (empty($message)) {
            wp_send_json_error(array('message' => mkv__('Tin nhắn không được để trống.')));
        }

        if (!is_array($history)) {
            $history = array();
        }

        // Xây dựng Context từ History
        $context_text = "";
        if (!empty($history)) {
            $context_text = "[Lịch sử trò chuyện gần đây]\n";
            $recent = array_slice($history, -4);
            foreach ($recent as $turn) {
                if (!is_array($turn)) {
                    continue;
                }
                $role = isset($turn['role']) && $turn['role'] === 'user' ? 'User' : 'AI';
                $text = isset($turn['parts'][0]['text']) ? $turn['parts'][0]['text'] : '';
                if (!is_string($text)) {
                    continue;
                }
                $context_text .= "$role: $text\n";
            }
            $context_text .= "\n[Yêu cầu mới nhất]\n$message";
        } else {
            $context_text = $message;
        }

        // 1. Unified Agent Config (Single-Pass Function Calling)
        $store_name = get_option('mkv_store_name', 'Cửa hàng');
        $today_stats = MKV_Analytics_Service::get_dashboard_stats('today');
        
        $base_context = "Ngữ cảnh trang hiện tại: '$current_page'.\nBạn là KiotViet Copilot - Trợ lý AI toàn năng, thông minh và nhạy bén của $store_name.\nTính cách & Khả năng:\n1. Bạn có kiến thức sâu rộng về mọi lĩnh vực: Quản trị bán hàng, kinh doanh, tài chính, công nghệ, đời sống và các câu hỏi tổng quát. Luôn sẵn sàng giải đáp bất kỳ chủ đề nào người dùng hỏi, kể cả các vấn đề bên ngoài hệ thống.\n2. Với các yêu cầu tra cứu hay thao tác trên hệ thống (tìm sản phẩm, kiểm tra đơn hàng, cảnh báo tồn kho, báo cáo doanh thu, thông tin khách hàng, tạo đơn nháp), bạn PHẢI tự động kích hoạt TOOL tương ứng phù hợp nhất.\n3. Khi người dùng chỉ chào hỏi hoặc hỏi kiến thức, hãy đối đáp tự nhiên, thông minh, lịch sự và súc tích.\n4. Luôn trả lời bằng tiếng Việt, dùng Markdown rõ ràng, dễ nhìn.\nDoanh thu hôm nay (lý thuyết): " . number_format($today_stats['today_revenue'], 0, ',', '.') . " VNĐ. Đơn hàng hôm nay: " . $today_stats['today_orders'] . ".";
        
        // Define all available tools based on user capability
        $tools = array($this->get_tool('search_product_info'), $this->get_tool('get_low_stock_alert'));
        if (current_user_can('mkv_manage_orders')) {
            $tools[] = $this->get_tool('check_order_status');
            $tools[] = $this->get_tool('create_order_draft');
            $tools[] = $this->get_tool('get_order_summary_stats');
        }
        if (current_user_can('mkv_manage_customers')) {
            $tools[] = $this->get_tool('search_customer_info');
        }

        // Only allow financial tools if user has report access
        if (current_user_can('mkv_manage_reports') || current_user_can('manage_options') || current_user_can('mkv_manage_dashboard')) {
            $tools[] = $this->get_tool('get_revenue_report');
        }

        $agent_responses = array();
        $func_name_executed = '';
        $func_data = null;
        
        // Chạy Single-Pass Agent
        $resp = MKV_AI_Service::generate_chat_response($context_text, $base_context, $tools, '');
        
        if (!is_wp_error($resp)) {
            if (isset($resp['is_function']) && $resp['is_function'] === true) {
                $func_call = $resp['function_call'];
                $fname = $func_call['name'];
                $fargs = isset($func_call['args']) ? $func_call['args'] : array();
                
                $allowed_tools = array_map(function ($tool) {
                    return isset($tool['name']) ? $tool['name'] : '';
                }, $tools);
                if (!in_array($fname, $allowed_tools, true)) {
                    wp_send_json_error(array('message' => mkv__('Bạn không có quyền thực hiện thao tác này.')));
                }

                $fresult = array();
                if ($fname === 'check_order_status') $fresult = $this->execute_check_order_status($fargs);
                elseif ($fname === 'search_product_info') $fresult = $this->execute_search_product_info($fargs);
                elseif ($fname === 'create_order_draft') $fresult = $this->execute_create_order_draft($fargs);
                elseif ($fname === 'get_revenue_report' || $fname === 'get_today_revenue') $fresult = $this->execute_get_revenue_report($fargs);
                elseif ($fname === 'get_low_stock_alert') $fresult = $this->execute_get_low_stock_alert($fargs);
                elseif ($fname === 'search_customer_info') $fresult = $this->execute_search_customer_info($fargs);
                elseif ($fname === 'get_order_summary_stats') $fresult = $this->execute_get_order_summary_stats($fargs);
                
                $func_name_executed = $fname;
                $func_data = $fresult;
                
                if (isset($fresult['error'])) {
                    $final_text = "Rất tiếc, đã có lỗi xảy ra: " . $fresult['error'];
                } elseif ($fname === 'check_order_status') {
                    $final_text = "Dưới đây là thông tin đơn hàng bạn cần tra cứu:";
                } elseif ($fname === 'search_product_info') {
                    $final_text = "Đây là thông tin sản phẩm tôi tìm được:";
                } elseif ($fname === 'create_order_draft') {
                    $final_text = "Tuyệt vời! Tôi đã tạo xong đơn hàng nháp cho bạn.";
                } elseif ($fname === 'get_revenue_report' || $fname === 'get_today_revenue') {
                    $rev = isset($fresult['revenue_formatted']) ? $fresult['revenue_formatted'] : (isset($fresult['revenue']) ? number_format((float)$fresult['revenue'], 0, ',', '.') . ' VNĐ' : '0 VNĐ');
                    $range = isset($fresult['date_label']) ? $fresult['date_label'] : '';
                    $final_text = "Doanh thu $range là **$rev**.";
                } elseif ($fname === 'get_low_stock_alert') {
                    $final_text = "Dưới đây là danh sách các sản phẩm sắp hết hàng cần lưu ý:";
                } elseif ($fname === 'search_customer_info') {
                    $final_text = "Tôi đã tìm thấy thông tin khách hàng:";
                } elseif ($fname === 'get_order_summary_stats') {
                    $final_text = "Thống kê tổng quan đơn hàng của bạn:";
                } else {
                    $final_text = "Đã thực hiện xong lệnh.";
                }
            } else {
                $final_text = $resp['text'];
            }
        } else {
            // Smart Local Fallback: Nếu API gặp sự cố (429, timeout), tự động phục vụ trực tiếp từ dữ liệu cửa hàng!
            $msg_lower = mb_strtolower($message, 'UTF-8');
            
            if ((stripos($msg_lower, 'doanh thu') !== false || stripos($msg_lower, 'tiền bán') !== false || stripos($msg_lower, 'thu nhập') !== false)
                && (current_user_can('mkv_manage_reports') || current_user_can('manage_options') || current_user_can('mkv_manage_dashboard'))) {
                $period = 'today';
                if (stripos($msg_lower, 'hôm qua') !== false) $period = 'yesterday';
                elseif (stripos($msg_lower, 'tuần này') !== false) $period = 'this_week';
                elseif (stripos($msg_lower, 'tháng này') !== false) $period = 'this_month';
                elseif (stripos($msg_lower, 'tháng trước') !== false) $period = 'last_month';
                
                $fresult = $this->execute_get_revenue_report(array('period' => $period));
                $func_name_executed = 'get_revenue_report';
                $func_data = $fresult;
                $rev = isset($fresult['revenue_formatted']) ? $fresult['revenue_formatted'] : (isset($fresult['revenue']) ? number_format((float)$fresult['revenue'], 0, ',', '.') . ' VNĐ' : '0 VNĐ');
                $range = isset($fresult['date_label']) ? $fresult['date_label'] : 'hôm nay';
                $final_text = "Doanh thu $range là **$rev**.";
            } elseif (stripos($msg_lower, 'hết hàng') !== false || stripos($msg_lower, 'tồn kho') !== false || stripos($msg_lower, 'cảnh báo kho') !== false) {
                $fresult = $this->execute_get_low_stock_alert(array());
                $func_name_executed = 'get_low_stock_alert';
                $func_data = $fresult;
                $final_text = "Dưới đây là danh sách các sản phẩm sắp hết hàng cần lưu ý:";
            } elseif ((stripos($msg_lower, 'đơn hàng') !== false || stripos($msg_lower, 'thống kê') !== false || stripos($msg_lower, 'tổng quan') !== false)
                && current_user_can('mkv_manage_orders')) {
                $fresult = $this->execute_get_order_summary_stats(array());
                $func_name_executed = 'get_order_summary_stats';
                $func_data = $fresult;
                $final_text = "Thống kê tổng quan đơn hàng của bạn:";
            } elseif (preg_match('/MKV-[A-Z0-9]+/i', $message, $m) && current_user_can('mkv_manage_orders')) {
                $fresult = $this->execute_check_order_status(array('order_code' => strtoupper($m[0])));
                $func_name_executed = 'check_order_status';
                $func_data = $fresult;
                $final_text = "Dưới đây là thông tin đơn hàng bạn cần tra cứu:";
            } else {
                $final_text = "Xin lỗi, hiện tại hệ thống AI đang bận (" . $resp->get_error_message() . "). Vui lòng thử lại sau giây lát!";
            }
        }

        // Lưu Log
        $this->save_log($message, 'user', $interaction_id);
        $this->save_log($final_text, 'ai', $interaction_id);
        
        // Chỉ lưu vào History nếu là phản hồi hợp lệ (tránh làm ô nhiễm ngữ cảnh trò chuyện khi có lỗi)
        if (!is_wp_error($resp) || !empty($func_name_executed)) {
            $history[] = array('role' => 'user', 'parts' => array(array('text' => $message)));
            $history[] = array('role' => 'model', 'parts' => array(array('text' => $final_text)));
        }

        wp_send_json_success(array(
            'text' => $final_text,
            'history' => $history,
            'interaction_id' => $interaction_id,
            'function_executed' => $func_name_executed,
            'function_data' => $func_data
        ));
    }

    private function get_tool($tool_name) {
        $tools = array(
            'check_order_status' => array(
                'name' => 'check_order_status',
                'description' => 'Tra cứu thông tin và trạng thái của một đơn hàng dựa vào mã đơn hàng (order_code).',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => array(
                        'order_code' => array('type' => 'string', 'description' => 'Mã đơn hàng, ví dụ: MKV-12345')
                    ),
                    'required' => array('order_code')
                )
            ),
            'search_product_info' => array(
                'name' => 'search_product_info',
                'description' => 'Tìm kiếm thông tin sản phẩm (giá bán, tồn kho) dựa vào tên sản phẩm.',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => array(
                        'keyword' => array('type' => 'string', 'description' => 'Tên hoặc từ khóa sản phẩm cần tìm')
                    ),
                    'required' => array('keyword')
                )
            ),
            'create_order_draft' => array(
                'name' => 'create_order_draft',
                'description' => 'Tạo một đơn hàng nháp mới. Cần thông tin sản phẩm và số lượng.',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => array(
                        'product_name' => array('type' => 'string', 'description' => 'Tên sản phẩm'),
                        'quantity' => array('type' => 'integer', 'description' => 'Số lượng'),
                        'customer_name' => array('type' => 'string', 'description' => 'Tên khách hàng (không bắt buộc)')
                    ),
                    'required' => array('product_name', 'quantity')
                )
            ),
            'get_revenue_report' => array(
                'name' => 'get_revenue_report',
                'description' => 'Tra cứu doanh thu tổng hợp (revenue). Hỗ trợ các mốc: today, yesterday, this_week, this_month, last_month.',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => array(
                        'period' => array('type' => 'string', 'description' => 'Khoảng thời gian: today, yesterday, this_week, this_month, last_month')
                    )
                )
            ),
            'get_low_stock_alert' => array(
                'name' => 'get_low_stock_alert',
                'description' => 'Lấy danh sách các sản phẩm sắp hết hàng (tồn kho dưới định mức, mặc định <= 5).',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => (object)array()
                )
            ),
            'search_customer_info' => array(
                'name' => 'search_customer_info',
                'description' => 'Tìm kiếm thông tin khách hàng dựa vào tên hoặc số điện thoại.',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => array(
                        'keyword' => array('type' => 'string', 'description' => 'Tên hoặc số điện thoại khách hàng')
                    ),
                    'required' => array('keyword')
                )
            ),
            'get_order_summary_stats' => array(
                'name' => 'get_order_summary_stats',
                'description' => 'Thống kê tổng số đơn hàng theo trạng thái (ví dụ: có bao nhiêu đơn đang chờ xử lý).',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => (object)array()
                )
            )
        );
        return isset($tools[$tool_name]) ? $tools[$tool_name] : array();
    }

    private function save_log($message, $type, $interaction_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mkv_ai_logs';
        $wpdb->insert($table, array(
            'user_id' => get_current_user_id(),
            'interaction_id' => $interaction_id,
            'message_type' => $type,
            'message' => is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message,
            'created_at' => current_time('mysql')
        ));
    }

    private function execute_check_order_status($args) {
        global $wpdb;
        $order_code = isset($args['order_code']) ? sanitize_text_field($args['order_code']) : '';
        if (empty($order_code)) return array('error' => 'Mã đơn hàng không hợp lệ');

        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mkv_orders WHERE order_code = %s", $order_code));
        if (!$order) {
            return array('status' => 'not_found', 'message' => 'Không tìm thấy đơn hàng nào với mã ' . $order_code);
        }

        // Get customer name
        $customer_name = 'Khách lẻ';
        if ($order->customer_id > 0) {
            $customer = $wpdb->get_row($wpdb->prepare("SELECT name, phone FROM {$wpdb->prefix}mkv_customers WHERE id = %d", $order->customer_id));
            if ($customer) {
                $customer_name = $customer->name . ($customer->phone ? ' (' . $customer->phone . ')' : '');
            }
        }

        return array(
            'order_code' => $order->order_code,
            'status' => $order->status,
            'total_amount' => number_format($order->total_amount, 0, ',', '.') . ' VNĐ',
            'payment_method' => $order->payment_method,
            'customer' => $customer_name,
            'created_at' => $order->created_at,
            'note' => isset($order->notes) ? $order->notes : ''
        );
    }

    private function execute_search_product_info($args) {
        $keyword = isset($args['keyword']) ? sanitize_text_field($args['keyword']) : '';
        if (empty($keyword)) return array('error' => 'Từ khóa tìm kiếm trống');

        $args_query = array(
            'post_type' => 'mkv_product',
            'post_status' => 'publish',
            'posts_per_page' => 5,
            's' => $keyword
        );
        $query = new WP_Query($args_query);
        $results = array();
        
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $price = get_post_meta($post->ID, '_mkv_price_out', true);
                if ($price === '') $price = get_post_meta($post->ID, '_mkv_price', true);
                $stock = get_post_meta($post->ID, '_mkv_stock', true);
                $sku = get_post_meta($post->ID, '_mkv_sku', true);
                $results[] = array(
                    'name' => $post->post_title,
                    'sku' => $sku ? $sku : 'N/A',
                    'price' => number_format((float)$price, 0, ',', '.') . ' VNĐ',
                    'stock' => $stock !== '' ? $stock : 0
                );
            }
        } else {
            return array('message' => 'Không tìm thấy sản phẩm nào phù hợp với từ khóa: ' . $keyword);
        }
        
        return $results;
    }

    private function execute_get_today_revenue($args) {
        global $wpdb;
        $date = isset($args['date']) ? sanitize_text_field($args['date']) : current_time('Y-m-d');
        
        $table_orders = $wpdb->prefix . 'mkv_orders';
        $revenue = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total_amount) FROM $table_orders WHERE DATE(created_at) = %s AND status IN ('completed', 'paid')",
            $date
        ));
        
        return array(
            'date' => $date,
            'revenue' => (float)$revenue,
            'formatted_revenue' => number_format((float)$revenue, 0, ',', '.') . ' VNĐ'
        );
    }

    private function execute_create_order_draft($args) {
        global $wpdb;
        $product_name = sanitize_text_field($args['product_name'] ?? '');
        $quantity = intval($args['quantity'] ?? 0);
        if ($product_name === '' || $quantity <= 0) {
            return array('error' => 'Tên sản phẩm và số lượng phải hợp lệ.');
        }
        $customer_name = isset($args['customer_name']) ? sanitize_text_field($args['customer_name']) : '';

        // Search for product
        $q_args = array(
            'post_type' => 'mkv_product',
            'post_status' => 'publish',
            's' => $product_name,
            'posts_per_page' => 1
        );
        $products = get_posts($q_args);
        
        if (empty($products)) {
            return array('error' => "Không tìm thấy sản phẩm nào có tên '$product_name'");
        }
        
        $product = $products[0];
        $price = get_post_meta($product->ID, '_mkv_price_out', true);
        if ($price === '') $price = get_post_meta($product->ID, '_mkv_price', true);
        $price = (float)$price;
        
        // Find customer if provided
        $customer_id = 0;
        if (!empty($customer_name)) {
            $table_customers = $wpdb->prefix . 'mkv_customers';
            $cust = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_customers WHERE name LIKE %s LIMIT 1", '%' . $wpdb->esc_like($customer_name) . '%'));
            if ($cust) {
                $customer_id = $cust->id;
            }
        }
        
        // Create order
        $order_code = 'MKV-' . strtoupper(substr(uniqid(), -6));
        $table_orders = $wpdb->prefix . 'mkv_orders';
        $subtotal = $price * $quantity;

        $wpdb->query('START TRANSACTION');
        $order_inserted = $wpdb->insert($table_orders, array(
            'order_code' => $order_code,
            'customer_id' => $customer_id ? $customer_id : null,
            'status' => 'draft',
            'subtotal' => $subtotal,
            'total_amount' => $subtotal,
            'created_by' => get_current_user_id(),
            'note' => 'Tạo bởi Trợ lý AI (Đơn nháp)',
            'created_at' => current_time('mysql')
        ));
        if ($order_inserted === false) {
            $wpdb->query('ROLLBACK');
            return array('error' => 'Không thể tạo đơn nháp.');
        }
        $order_id = $wpdb->insert_id;
        
        // Create order item
        $table_items = $wpdb->prefix . 'mkv_order_items';
        $item_inserted = $wpdb->insert($table_items, array(
            'order_id' => $order_id,
            'product_id' => $product->ID,
            'qty' => $quantity,
            'price' => $price,
            'subtotal' => $subtotal
        ));
        if ($item_inserted === false) {
            $wpdb->query('ROLLBACK');
            return array('error' => 'Không thể lưu sản phẩm trong đơn nháp.');
        }
        $wpdb->query('COMMIT');
        
        return array(
            'success' => true,
            'order_code' => $order_code,
            'product' => $product->post_title,
            'quantity' => $quantity,
            'total_amount' => $subtotal,
            'status' => 'pending',
            'message' => "Đã tạo thành công đơn hàng nháp $order_code"
        );
    }

    private function execute_get_revenue_report($args) {
        global $wpdb;
        $period = isset($args['period']) ? $args['period'] : 'today';
        $start = current_time('Y-m-d 00:00:00');
        $end = current_time('Y-m-d 23:59:59');
        $label = 'hôm nay';

        switch ($period) {
            case 'yesterday':
                $start = date('Y-m-d 00:00:00', strtotime('-1 day', current_time('timestamp')));
                $end = date('Y-m-d 23:59:59', strtotime('-1 day', current_time('timestamp')));
                $label = 'hôm qua';
                break;
            case 'this_week':
                $start = date('Y-m-d 00:00:00', strtotime('monday this week', current_time('timestamp')));
                $end = date('Y-m-d 23:59:59', strtotime('sunday this week', current_time('timestamp')));
                $label = 'tuần này';
                break;
            case 'this_month':
                $start = date('Y-m-01 00:00:00', current_time('timestamp'));
                $end = date('Y-m-t 23:59:59', current_time('timestamp'));
                $label = 'tháng này';
                break;
            case 'last_month':
                $start = date('Y-m-01 00:00:00', strtotime('first day of last month', current_time('timestamp')));
                $end = date('Y-m-t 23:59:59', strtotime('last day of last month', current_time('timestamp')));
                $label = 'tháng trước';
                break;
            default:
                if (isset($args['date'])) {
                    $start = sanitize_text_field($args['date']) . ' 00:00:00';
                    $end = sanitize_text_field($args['date']) . ' 23:59:59';
                    $label = 'ngày ' . sanitize_text_field($args['date']);
                }
                break;
        }

        $table_orders = $wpdb->prefix . 'mkv_orders';
        $revenue = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total_amount) FROM $table_orders WHERE created_at BETWEEN %s AND %s AND status IN ('completed', 'paid')",
            $start, $end
        ));

        return array(
            'period' => $period,
            'date_label' => $label,
            'revenue' => (float)$revenue,
            'revenue_formatted' => number_format((float)$revenue, 0, ',', '.') . ' VNĐ'
        );
    }

    private function execute_get_low_stock_alert($args) {
        $args_query = array(
            'post_type' => 'mkv_product',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'meta_query' => array(
                array(
                    'key' => '_mkv_stock',
                    'value' => 5,
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                )
            ),
            'orderby' => 'meta_value_num',
            'meta_key' => '_mkv_stock',
            'order' => 'ASC'
        );
        $query = new WP_Query($args_query);
        $results = array();
        
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $stock = get_post_meta($post->ID, '_mkv_stock', true);
                $sku = get_post_meta($post->ID, '_mkv_sku', true);
                $results[] = array(
                    'name' => $post->post_title,
                    'sku' => $sku ? $sku : 'N/A',
                    'stock' => $stock !== '' ? $stock : 0
                );
            }
        }
        
        return array('items' => $results, 'count' => count($results));
    }

    private function execute_search_customer_info($args) {
        global $wpdb;
        $keyword = isset($args['keyword']) ? sanitize_text_field($args['keyword']) : '';
        if (empty($keyword)) return array('error' => 'Vui lòng cung cấp tên hoặc số điện thoại.');

        $table_customers = $wpdb->prefix . 'mkv_customers';
        $table_orders = $wpdb->prefix . 'mkv_orders';
        $customer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_customers WHERE name LIKE %s OR phone LIKE %s LIMIT 1",
            '%' . $wpdb->esc_like($keyword) . '%',
            '%' . $wpdb->esc_like($keyword) . '%'
        ));

        if (!$customer) {
            return array('error' => "Không tìm thấy khách hàng nào khớp với '$keyword'.");
        }

        // Stats
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(id) as total_orders, SUM(total_amount) as total_spent FROM $table_orders WHERE customer_id = %d AND status IN ('completed', 'paid')",
            $customer->id
        ));

        return array(
            'name' => $customer->name,
            'phone' => $customer->phone ? $customer->phone : 'Không có',
            'address' => $customer->address ? $customer->address : 'Không có',
            'total_orders' => (int)$stats->total_orders,
            'total_spent' => number_format((float)$stats->total_spent, 0, ',', '.') . ' VNĐ',
            'debt' => number_format((float)$customer->total_debt, 0, ',', '.') . ' VNĐ'
        );
    }

    private function execute_get_order_summary_stats($args) {
        global $wpdb;
        $stats = $wpdb->get_results("SELECT status, COUNT(id) as count FROM {$wpdb->prefix}mkv_orders GROUP BY status");
        
        $pending = 0;
        $completed = 0;
        $cancelled = 0;
        
        foreach ($stats as $row) {
            if ($row->status === 'pending') $pending = (int)$row->count;
            elseif ($row->status === 'completed' || $row->status === 'paid') $completed += (int)$row->count;
            elseif ($row->status === 'cancelled') $cancelled = (int)$row->count;
        }
        
        return array(
            'pending' => $pending,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'total' => $pending + $completed + $cancelled
        );
    }
}
