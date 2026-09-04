<?php
if (!defined('ABSPATH')) exit;

class MKV_AI_Service
{
    private static $api_url = 'https://generativelanguage.googleapis.com/v1beta/interactions';

    /**
     * Gửi yêu cầu chat đến Google Gemini Interactions API
     */
    public static function generate_chat_response($input, $system_instruction = '', $tools = array(), $previous_interaction_id = '', $response_schema = null)
    {
        $raw_api_key = get_option('mkv_gemini_api_key', '');
        if (empty($raw_api_key)) {
            return new WP_Error('no_api_key', mkv__('Vui lòng cấu hình Google Gemini API Key trong phần Cài đặt.'));
        }

        // Hỗ trợ nhiều API Key (phân cách bằng dấu phẩy hoặc xuống dòng) để xoay vòng tránh 429
        $api_keys = array_values(array_filter(array_map('trim', preg_split('/[,\r\n;]+/', $raw_api_key))));
        if (empty($api_keys)) {
            return new WP_Error('no_api_key', mkv__('Vui lòng cấu hình Google Gemini API Key hợp lệ trong phần Cài đặt.'));
        }

        $key_index = 0;
        $current_key = $api_keys[$key_index];
        $url = add_query_arg('key', $current_key, self::$api_url);

        $model = get_option('mkv_gemini_model', 'models/gemini-3.5-flash-lite');
        if (empty($model) || $model === 'gemini-2.5-flash' || $model === 'gemini-2.5-flash-lite' || strpos($model, 'gemini-3.8') === 0 || strpos($model, 'gemini-3.7') === 0) {
            $model = 'models/gemini-3.5-flash-lite';
            update_option('mkv_gemini_model', $model);
        }

        $body = array(
            'model' => $model,
            'input' => $input,
            'generation_config' => array(
                'temperature' => 0.7,
                'max_output_tokens' => 2048,
                'thinking_level' => 'low' // Tiết kiệm token & tăng tốc độ gấp 3 lần
            )
        );

        if ($response_schema !== null) {
            $system_instruction .= "\n[BẮT BUỘC]: Bạn PHẢI trả về ĐÚNG định dạng JSON sau, không được kèm markdown hay giải thích thêm:\n" . json_encode($response_schema);
        }

        if (!empty($system_instruction)) {
            $body['system_instruction'] = $system_instruction;
        }

        if (!empty($previous_interaction_id)) {
            $body['previous_interaction_id'] = $previous_interaction_id;
        }

        if (!empty($tools)) {
            $interactions_tools = array();
            foreach ($tools as $tool) {
                $tool['type'] = 'function';
                $interactions_tools[] = $tool;
            }
            $body['tools'] = $interactions_tools;
        }

        $args = array(
            'body'        => json_encode($body),
            'headers'     => array(
                'Content-Type' => 'application/json',
            ),
            'timeout'     => 8,
            'data_format' => 'body',
        );

        $max_retries = max(2, count($api_keys) + 1);
        $attempt = 0;
        $data = null;
        $response_code = null;
        $last_error_message = '';
        $tried_fallback_model = false;

        while ($attempt < $max_retries) {
            $attempt++;
            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                $last_error_message = $response->get_error_message();
                $key_index++;
                if ($key_index < count($api_keys)) {
                    $current_key = $api_keys[$key_index];
                    $url = add_query_arg('key', $current_key, self::$api_url);
                    continue;
                }
                break;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            $data = json_decode($response_body, true);

            if ($response_code !== 200) {
                $last_error_message = isset($data['error']['message']) ? $data['error']['message'] : mkv__('Lỗi kết nối đến Google Gemini API.');
                
                // 1. Quota / Rate limit (429, RESOURCE_EXHAUSTED) -> Đổi sang Key tiếp theo ngay lập tức (0s delay)
                if ($response_code == 429 || stripos($last_error_message, 'quota') !== false || stripos($last_error_message, 'rate') !== false) {
                    $key_index++;
                    if ($key_index < count($api_keys)) {
                        $current_key = $api_keys[$key_index];
                        $url = add_query_arg('key', $current_key, self::$api_url);
                        continue;
                    }

                    // Nếu tất cả các key đều chạm hạn mức, thử model nhẹ hơn đúng 1 lần.
                    if (!$tried_fallback_model && $body['model'] !== 'models/gemini-3.5-flash-lite') {
                        $tried_fallback_model = true;
                        $body['model'] = 'models/gemini-3.5-flash-lite';
                        $args['body'] = json_encode($body);
                        $key_index = 0;
                        $current_key = $api_keys[0];
                        $url = add_query_arg('key', $current_key, self::$api_url);
                        continue;
                    }

                    break;
                }

                // 2. Model không tồn tại hoặc bị lỗi
                if (($response_code == 404 || $response_code == 400) && 
                    (stripos($last_error_message, 'model') !== false || stripos($last_error_message, 'no longer available') !== false || stripos($last_error_message, 'not found') !== false)) {
                    
                    if ($body['model'] === 'models/gemini-3.5-flash-lite') {
                        $body['model'] = 'models/gemini-3.5-flash';
                        update_option('mkv_gemini_model', $body['model']);
                        $args['body'] = json_encode($body);
                        continue;
                    }
                }
                
                break;
            }
            
            break;
        }

        if ($response_code !== 200) {
            if (stripos($last_error_message, 'quota') !== false || $response_code == 429) {
                $last_error_message = "Hệ thống AI đang nhận quá nhiều yêu cầu cùng lúc (Vượt giới hạn miễn phí của Google). Bạn vui lòng chờ khoảng 30 giây rồi thử lại nhé!";
            }
            return new WP_Error('api_error', $last_error_message);
        }

        $interaction_id = isset($data['id']) ? $data['id'] : '';
        $steps = isset($data['steps']) ? $data['steps'] : array();

        // Lặp ngược để tìm phản hồi hoặc function_call mới nhất
        foreach (array_reverse($steps) as $step) {
            if (!is_array($step) || empty($step['type'])) {
                continue;
            }
            if ($step['type'] === 'function_call') {
                $arguments = isset($step['arguments']) ? $step['arguments'] : array();
                if (is_string($arguments)) {
                    $decoded_arguments = json_decode($arguments, true);
                    $arguments = is_array($decoded_arguments) ? $decoded_arguments : array();
                }
                return array(
                    'success' => true,
                    'is_function' => true,
                    'function_call' => array(
                        'id' => isset($step['id']) ? $step['id'] : '',
                        'name' => isset($step['name']) ? $step['name'] : '',
                        'args' => $arguments
                    ),
                    'interaction_id' => $interaction_id
                );
            }
            if ($step['type'] === 'model_output') {
                $text = '';
                if (isset($step['content']) && is_array($step['content'])) {
                    foreach ($step['content'] as $c) {
                        if (is_array($c) && isset($c['text'])) {
                            $text .= $c['text'];
                        } elseif (is_string($c)) {
                            $text .= $c;
                        }
                    }
                } elseif (isset($step['content']) && is_string($step['content'])) {
                    $text = $step['content'];
                } elseif (isset($step['text'])) {
                    $text = $step['text'];
                }
                if (!empty($text)) {
                    return array(
                        'success' => true,
                        'is_function' => false,
                        'text' => $text,
                        'interaction_id' => $interaction_id
                    );
                }
            }
        }

        return new WP_Error('api_error', mkv__('Không nhận được phản hồi hợp lệ từ AI.'));
    }
}
