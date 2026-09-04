<?php
if (!defined('ABSPATH')) exit;

class MKV_DB_Schema
{
    const DB_VERSION = '2.5.0';

    public static function init()
    {
        $current_version = get_option('mkv_db_version', '0');
        if ($current_version !== self::DB_VERSION) {
            self::create_tables();
            update_option('mkv_db_version', self::DB_VERSION);
        }
    }

    public static function create_tables()
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // ===== 1. Kho / Chi nhánh =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_locations (
            id         bigint(20)   NOT NULL AUTO_INCREMENT,
            name       varchar(255) NOT NULL,
            code       varchar(50)  DEFAULT '',
            address    text,
            status     varchar(20)  DEFAULT 'active',
            created_at datetime     DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status)
        ) $charset;";
        dbDelta($sql);

        // Kho mặc định
        if (!$wpdb->get_var("SELECT id FROM {$wpdb->prefix}mkv_locations LIMIT 1")) {
            $wpdb->insert("{$wpdb->prefix}mkv_locations", array(
                'name' => 'Kho Trung Tâm', 'code' => 'KTC', 'status' => 'active',
            ));
        }

        // ===== 2. Tồn kho theo kho =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_inventory_stock (
            id          bigint(20) NOT NULL AUTO_INCREMENT,
            product_id  bigint(20) NOT NULL,
            location_id bigint(20) NOT NULL,
            stock       int(11)    DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY product_location (product_id, location_id),
            KEY product_id  (product_id),
            KEY location_id (location_id)
        ) $charset;";
        dbDelta($sql);

        // ===== 3. Lịch sử kho (hỗ trợ chuyển kho) =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_inventory_logs (
            id              bigint(20)  NOT NULL AUTO_INCREMENT,
            product_id      bigint(20)  NOT NULL,
            location_id     bigint(20)  NOT NULL,
            location_id_to  bigint(20)  DEFAULT NULL,
            type            varchar(50) NOT NULL,
            qty             int(11)     NOT NULL,
            note            text,
            created_by      bigint(20),
            created_at      datetime    DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY created_at (created_at)
        ) $charset;";
        dbDelta($sql);

        // ===== 4. Khách hàng =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_customers (
            id          bigint(20)     NOT NULL AUTO_INCREMENT,
            name        varchar(255)   NOT NULL,
            email       varchar(255)   DEFAULT '',
            phone       varchar(50)    DEFAULT '',
            address     text,
            points      int(11)        DEFAULT 0,
            total_spent decimal(15,2)  DEFAULT 0.00,
            total_debt  decimal(15,2)  DEFAULT 0.00,
            created_at  datetime       DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY phone (phone),
            KEY total_spent (total_spent)
        ) $charset;";
        dbDelta($sql);

        // ===== 5. Đơn hàng =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_orders (
            id             bigint(20)     NOT NULL AUTO_INCREMENT,
            order_code     varchar(50)    NOT NULL,
            customer_id    bigint(20)     DEFAULT NULL,
            warehouse_id   bigint(20)     DEFAULT 1,
            status         varchar(50)    NOT NULL DEFAULT 'pending',
            payment_method varchar(50)    DEFAULT 'cash',
            subtotal       decimal(15,2)  DEFAULT 0.00,
            discount       decimal(15,2)  DEFAULT 0.00,
            discount_type  varchar(20)    DEFAULT 'amount',
            points_used    int(11)        DEFAULT 0,
            tax            decimal(15,2)  DEFAULT 0.00,
            total_amount   decimal(15,2)  NOT NULL DEFAULT 0.00,
            paid_amount    decimal(15,2)  DEFAULT 0.00,
            debt_amount    decimal(15,2)  DEFAULT 0.00,
            cost_total     decimal(15,2)  DEFAULT 0.00,
            note           text,
            shipping_provider varchar(50) DEFAULT NULL,
            tracking_code  varchar(100)   DEFAULT NULL,
            shipping_fee   decimal(15,2)  DEFAULT 0.00,
            customer_address text,
            created_by     bigint(20),
            created_at     datetime       DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY order_code (order_code),
            KEY status     (status),
            KEY created_at (created_at),
            KEY customer_id (customer_id),
            KEY tracking_code (tracking_code)
        ) $charset;";
        dbDelta($sql);

        // ===== 6. Chi tiết đơn hàng =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_order_items (
            id         bigint(20)    NOT NULL AUTO_INCREMENT,
            order_id   bigint(20)    NOT NULL,
            product_id bigint(20)    NOT NULL,
            qty        int(11)       NOT NULL,
            price      decimal(15,2) NOT NULL,
            cost_price decimal(15,2) DEFAULT 0.00,
            subtotal   decimal(15,2) NOT NULL,
            PRIMARY KEY (id),
            KEY order_id   (order_id),
            KEY product_id (product_id)
        ) $charset;";
        dbDelta($sql);

        // ===== 7. Sổ quỹ (Cashbook) =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_cashbook (
            id           bigint(20)    NOT NULL AUTO_INCREMENT,
            type         varchar(20)   NOT NULL DEFAULT 'thu',
            method       varchar(50)   NOT NULL DEFAULT 'cash',
            amount       decimal(15,2) NOT NULL DEFAULT 0.00,
            reference_id bigint(20)    DEFAULT NULL,
            customer_id  bigint(20)    DEFAULT NULL,
            supplier_id  bigint(20)    DEFAULT NULL,
            note         text,
            created_by   bigint(20),
            created_at   datetime      DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY created_at (created_at)
        ) $charset;";
        dbDelta($sql);

        // ===== 8. Bảng thông báo nội bộ =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_notifications (
            id         bigint(20)  NOT NULL AUTO_INCREMENT,
            type       varchar(50) NOT NULL DEFAULT 'info',
            title      varchar(255) NOT NULL,
            message    text,
            is_read    tinyint(1)  DEFAULT 0,
            created_at datetime    DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY is_read    (is_read),
            KEY created_at (created_at)
        ) $charset;";
        dbDelta($sql);

        // ===== 9. Bảng chấm công (Timesheets) =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_timesheets (
            id             bigint(20) NOT NULL AUTO_INCREMENT,
            user_id        bigint(20) NOT NULL,
            work_date      date       NOT NULL,
            check_in_time  datetime   DEFAULT NULL,
            check_out_time datetime   DEFAULT NULL,
            note           text,
            created_at     datetime   DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY work_date (work_date)
        ) $charset;";
        dbDelta($sql);

        // ===== 10. Nhà cung cấp =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_suppliers (
            id          bigint(20)   NOT NULL AUTO_INCREMENT,
            name        varchar(255) NOT NULL,
            phone       varchar(50)  DEFAULT '',
            email       varchar(255) DEFAULT '',
            address     text,
            total_debt  decimal(15,2) DEFAULT 0.00,
            created_at  datetime     DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;";
        dbDelta($sql);

        // ===== 11. Phiếu nhập hàng =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_purchase_orders (
            id             bigint(20)    NOT NULL AUTO_INCREMENT,
            code           varchar(50)   NOT NULL,
            supplier_id    bigint(20)    DEFAULT NULL,
            total_amount   decimal(15,2) NOT NULL DEFAULT 0.00,
            paid_amount    decimal(15,2) NOT NULL DEFAULT 0.00,
            status         varchar(50)   NOT NULL DEFAULT 'completed',
            note           text,
            created_by     bigint(20),
            created_at     datetime      DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY code (code)
        ) $charset;";
        dbDelta($sql);

        // ===== 12. Chi tiết phiếu nhập =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_purchase_order_items (
            id         bigint(20)    NOT NULL AUTO_INCREMENT,
            po_id      bigint(20)    NOT NULL,
            product_id bigint(20)    NOT NULL,
            qty        int(11)       NOT NULL,
            price      decimal(15,2) NOT NULL,
            subtotal   decimal(15,2) NOT NULL,
            PRIMARY KEY (id)
        ) $charset;";
        dbDelta($sql);

        // ===== 13. Phiếu kiểm kho =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_stocktakes (
            id           bigint(20)    NOT NULL AUTO_INCREMENT,
            code         varchar(50)   NOT NULL,
            location_id  bigint(20)    NOT NULL,
            status       varchar(50)   NOT NULL DEFAULT 'draft',
            total_diff   int(11)       DEFAULT 0,
            note         text,
            created_by   bigint(20),
            created_at   datetime      DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY code (code)
        ) $charset;";
        dbDelta($sql);

        // ===== 14. Chi tiết phiếu kiểm kho =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_stocktake_items (
            id            bigint(20)    NOT NULL AUTO_INCREMENT,
            stocktake_id  bigint(20)    NOT NULL,
            product_id    bigint(20)    NOT NULL,
            sys_qty       int(11)       NOT NULL,
            actual_qty    int(11)       NOT NULL,
            diff_qty      int(11)       NOT NULL,
            PRIMARY KEY (id)
        ) $charset;";
        dbDelta($sql);

        // ===== 15. Phiếu trả hàng =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_returns (
            id             bigint(20)    NOT NULL AUTO_INCREMENT,
            return_code    varchar(50)   NOT NULL,
            order_id       bigint(20)    NOT NULL,
            customer_id    bigint(20)    DEFAULT NULL,
            location_id    bigint(20)    NOT NULL,
            total_refund   decimal(15,2) NOT NULL DEFAULT 0.00,
            status         varchar(50)   NOT NULL DEFAULT 'completed',
            note           text,
            created_by     bigint(20),
            created_at     datetime      DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY return_code (return_code)
        ) $charset;";
        dbDelta($sql);

        // ===== 16. Chi tiết phiếu trả hàng =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_return_items (
            id            bigint(20)    NOT NULL AUTO_INCREMENT,
            return_id     bigint(20)    NOT NULL,
            product_id    bigint(20)    NOT NULL,
            qty           int(11)       NOT NULL,
            price         decimal(15,2) NOT NULL,
            subtotal      decimal(15,2) NOT NULL,
            PRIMARY KEY (id)
        ) $charset;";
        dbDelta($sql);

        // ===== 17. AI Chat Logs =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_ai_logs (
            id             bigint(20)   NOT NULL AUTO_INCREMENT,
            user_id        bigint(20)   NOT NULL,
            interaction_id varchar(100) DEFAULT '',
            message_type   varchar(20)  DEFAULT 'user', -- 'user', 'ai', 'function'
            message        text,
            created_at     datetime     DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset;";
        dbDelta($sql);
    }
}
