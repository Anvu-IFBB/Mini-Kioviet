<?php
if (!defined('ABSPATH')) exit;

class MKV_DB_Schema
{
    const DB_VERSION = '3.3.0';

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
            payment_status varchar(30)    NOT NULL DEFAULT 'unpaid',
            payment_method varchar(50)    DEFAULT 'cash',
            subtotal       decimal(15,2)  DEFAULT 0.00,
            discount       decimal(15,2)  DEFAULT 0.00,
            discount_type  varchar(20)    DEFAULT 'amount',
            points_used    int(11)        DEFAULT 0,
            tax            decimal(15,2)  DEFAULT 0.00,
            total_amount   decimal(15,2)  NOT NULL DEFAULT 0.00,
            paid_amount    decimal(15,2)  DEFAULT 0.00,
            debt_amount    decimal(15,2)  DEFAULT 0.00,
            customer_debt_amount decimal(15,2) DEFAULT 0.00,
            cost_total     decimal(15,2)  DEFAULT 0.00,
            note           text,
            shipping_provider varchar(50) DEFAULT NULL,
            tracking_code  varchar(100)   DEFAULT NULL,
            shipping_fee   decimal(15,2)  DEFAULT 0.00,
            customer_address text,
            sales_channel  varchar(30)    NOT NULL DEFAULT 'pos',
            fulfillment_status varchar(30) NOT NULL DEFAULT 'pending',
            cod_amount     decimal(15,2)  DEFAULT 0.00,
            cod_settled_at datetime       DEFAULT NULL,
            cod_settlement_ref varchar(100) DEFAULT NULL,
            shipping_phone varchar(50)    DEFAULT NULL,
            created_by     bigint(20),
            created_at     datetime       DEFAULT CURRENT_TIMESTAMP,
            updated_at     datetime       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY order_code (order_code),
            KEY status     (status),
            KEY payment_status (payment_status),
            KEY created_at (created_at),
            KEY updated_at (updated_at),
            KEY idx_status_created (status, created_at),
            KEY customer_id (customer_id),
            KEY tracking_code (tracking_code),
            KEY sales_channel (sales_channel),
            KEY fulfillment_status (fulfillment_status),
            KEY cod_settled_at (cod_settled_at)
        ) $charset;";
        dbDelta($sql);

        // Migration: Add updated_at if not exists
        $row = $wpdb->get_row("SELECT * FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '{$wpdb->prefix}mkv_orders' AND column_name = 'updated_at'");
        if (!$row) {
            $wpdb->query("ALTER TABLE {$wpdb->prefix}mkv_orders ADD COLUMN updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ADD INDEX updated_at (updated_at)");
        }

        // Migration 1: Reclassify COD orders - if COD is pending or COD amount > 0, debt_amount & customer_debt_amount must be 0
        $wpdb->query(
            "UPDATE {$wpdb->prefix}mkv_orders
             SET customer_debt_amount = 0,
                 debt_amount = 0
             WHERE cod_amount > 0 AND payment_status = 'cod_pending'"
        );

        // Migration 2: Older delivery orders stored unpaid COD as customer debt. Reclassify.
        $wpdb->query(
            "UPDATE {$wpdb->prefix}mkv_orders
             SET cod_amount = GREATEST(0, total_amount - paid_amount),
                 debt_amount = 0,
                 customer_debt_amount = 0,
                 payment_status = 'cod_pending'
             WHERE sales_channel <> 'pos'
               AND shipping_provider IS NOT NULL
               AND shipping_provider <> ''
               AND total_amount > paid_amount
               AND (cod_amount = 0 OR cod_amount IS NULL)"
        );

        // Migration 3: Populate customer_debt_amount on older POS / counter orders
        $wpdb->query(
            "UPDATE {$wpdb->prefix}mkv_orders
             SET debt_amount = GREATEST(0, total_amount - paid_amount),
                 customer_debt_amount = GREATEST(0, total_amount - paid_amount),
                 payment_status = CASE WHEN paid_amount > 0 THEN 'partially_paid' ELSE 'unpaid' END
             WHERE status NOT IN ('cancelled', 'draft')
               AND total_amount > paid_amount
               AND (cod_amount = 0 OR cod_amount IS NULL)
               AND (customer_debt_amount = 0 OR customer_debt_amount IS NULL)
               AND (shipping_provider IS NULL OR shipping_provider = '')"
        );

        // Migration 4: Assign correct payment state to all orders
        $wpdb->query(
            "UPDATE {$wpdb->prefix}mkv_orders
             SET payment_status = CASE
                 WHEN status = 'draft' THEN 'unpaid'
                 WHEN cod_amount > 0 THEN 'cod_pending'
                 WHEN paid_amount >= total_amount AND total_amount > 0 THEN 'paid'
                 WHEN paid_amount > 0 THEN 'partially_paid'
                 ELSE 'unpaid'
             END
             WHERE payment_status IN ('unpaid', 'pending', '')"
        );

        // Migration 5: Resynchronize customer total_debt to match actual unpaid orders
        $wpdb->query(
            "UPDATE {$wpdb->prefix}mkv_customers c
             SET c.total_debt = COALESCE(
                 (SELECT SUM(o.customer_debt_amount)
                  FROM {$wpdb->prefix}mkv_orders o
                  WHERE o.customer_id = c.id
                    AND o.status <> 'cancelled'
                    AND o.payment_status IN ('unpaid', 'partially_paid')), 0
             )"
        );

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
            KEY created_at (created_at),
            KEY idx_type_created (type, created_at)
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
            location_id    bigint(20)    DEFAULT 1,
            total_amount   decimal(15,2) NOT NULL DEFAULT 0.00,
            paid_amount    decimal(15,2) NOT NULL DEFAULT 0.00,
            status         varchar(50)   NOT NULL DEFAULT 'completed',
            note           text,
            created_by     bigint(20),
            created_at     datetime      DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY code (code),
            KEY supplier_id (supplier_id),
            KEY location_id (location_id),
            KEY created_at (created_at)
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
            PRIMARY KEY (id),
            KEY po_id (po_id),
            KEY product_id (product_id)
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
            PRIMARY KEY (id),
            KEY idx_stocktake_id (stocktake_id),
            KEY idx_product_id (product_id)
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
            PRIMARY KEY (id),
            KEY idx_return_id (return_id),
            KEY idx_product_id (product_id)
        ) $charset;";
        dbDelta($sql);

        // ===== 17. AI Chat Logs =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_ai_logs (
            id             bigint(20)   NOT NULL AUTO_INCREMENT,
            user_id        bigint(20)   NOT NULL,
            interaction_id varchar(100) DEFAULT '',
            message_type   varchar(20)  DEFAULT 'user',
            message        text,
            created_at     datetime     DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset;";
        dbDelta($sql);

        // ===== 18. Nhật ký kiểm toán bảo mật (Phase 3G) =====
        $sql = "CREATE TABLE {$wpdb->prefix}mkv_audit_logs (
            id          bigint(20)   NOT NULL AUTO_INCREMENT,
            user_id     bigint(20)   DEFAULT 0,
            event_type  varchar(50)  NOT NULL,
            action      varchar(50)  NOT NULL,
            object_type varchar(50)  DEFAULT '',
            object_id   varchar(100) DEFAULT '',
            description text,
            ip_address  varchar(100) DEFAULT '',
            user_agent  text,
            created_at  datetime     DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY created_at (created_at),
            KEY user_id (user_id),
            KEY event_type (event_type),
            KEY action (action),
            KEY idx_event_created (event_type, created_at),
            KEY idx_action_created (action, created_at)
        ) $charset;";
        dbDelta($sql);

        // Phase 3A: Idempotent Migration for Performance Indexes
        self::migrate_indexes();
    }

    /**
     * Add index to table if it does not already exist.
     * Safe, non-destructive, and idempotent.
     *
     * @param string $table Full table name (with prefix)
     * @param string $index_name Name of the index
     * @param string $columns Column list, e.g. '`status`, `created_at`'
     * @return bool True if added, false if already existed or failed
     */
    public static function add_index_if_not_exists($table, $index_name, $columns)
    {
        global $wpdb;
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(1) FROM information_schema.statistics 
             WHERE table_schema = DATABASE() 
               AND table_name = %s 
               AND index_name = %s",
            $table,
            $index_name
        ));

        if (!$exists) {
            $result = $wpdb->query("ALTER TABLE `{$table}` ADD INDEX `{$index_name}` ({$columns})");
            return ($result !== false);
        }
        return false;
    }

    /**
     * Migrate Phase 3A approved performance indexes.
     */
    public static function migrate_indexes()
    {
        global $wpdb;

        // 1. wp_mkv_stocktake_items
        self::add_index_if_not_exists("{$wpdb->prefix}mkv_stocktake_items", 'idx_stocktake_id', '`stocktake_id`');
        self::add_index_if_not_exists("{$wpdb->prefix}mkv_stocktake_items", 'idx_product_id', '`product_id`');

        // 2. wp_mkv_return_items
        self::add_index_if_not_exists("{$wpdb->prefix}mkv_return_items", 'idx_return_id', '`return_id`');
        self::add_index_if_not_exists("{$wpdb->prefix}mkv_return_items", 'idx_product_id', '`product_id`');

        // 3. wp_mkv_orders
        self::add_index_if_not_exists("{$wpdb->prefix}mkv_orders", 'idx_status_created', '`status`, `created_at`');

        // 4. wp_mkv_cashbook
        self::add_index_if_not_exists("{$wpdb->prefix}mkv_cashbook", 'idx_type_created', '`type`, `created_at`');

        // 5. wp_mkv_audit_logs (Phase 3H composite indexes for high performance querying)
        self::add_index_if_not_exists("{$wpdb->prefix}mkv_audit_logs", 'idx_event_created', '`event_type`, `created_at`');
        self::add_index_if_not_exists("{$wpdb->prefix}mkv_audit_logs", 'idx_action_created', '`action`, `created_at`');
    }
}

