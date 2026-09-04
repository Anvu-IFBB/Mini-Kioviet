<?php
$_SERVER['HTTP_HOST'] = 'localhost:10004';
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once dirname(__DIR__, 4) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';

global $wp_filter;
echo "Admin footer hooks:\n";
if (isset($wp_filter['admin_footer'])) {
    foreach ($wp_filter['admin_footer']->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $idx => $cb) {
            echo "Priority $priority: $idx\n";
        }
    }
}
