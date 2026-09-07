<?php
if (!defined('ABSPATH')) exit;

class MKV_Products
{
    public function __construct()
    {
        add_action('init',                                    array($this, 'register_cpt_and_tax'));
        add_action('add_meta_boxes',                          array($this, 'add_product_metaboxes'));
        add_action('save_post_mkv_product',                   array($this, 'save_product_meta'), 10, 2);
        add_action('admin_notices',                           array($this, 'show_admin_notices'));
        add_action('admin_menu',                              array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts',                   array($this, 'enqueue_scripts'));

        add_filter('manage_mkv_product_posts_columns',        array($this, 'set_custom_columns'));
        add_action('manage_mkv_product_posts_custom_column',  array($this, 'custom_column_data'), 10, 2);
        add_filter('post_row_actions',                        array($this, 'remove_quick_edit'), 10, 2);
        add_filter('manage_edit-mkv_product_sortable_columns',array($this, 'sortable_columns'));
        add_action('restrict_manage_posts',                   array($this, 'filter_by_category'));
        add_filter('parse_query',                             array($this, 'perform_filtering'));
        add_action('admin_init',                              array($this, 'export_csv_action'));
        add_action('admin_init',                              array($this, 'import_csv_action'));
        add_action('in_admin_header',                         array($this, 'inject_custom_layout_header'));

        // Custom Category CRUD
        add_action('admin_post_mkv_add_category',             array($this, 'handle_add_category'));
        add_action('admin_post_mkv_edit_category',            array($this, 'handle_edit_category'));
        add_action('admin_post_mkv_delete_category',          array($this, 'handle_delete_category'));
        add_action('admin_init',                              array($this, 'redirect_legacy_taxonomy_pages'));

        // REST API cho AJAX data-table
        add_action('rest_api_init',                           array($this, 'register_rest_routes'));
    }

    public function register_cpt_and_tax()
    {
        register_taxonomy('mkv_product_cat', array('mkv_product'), array(
            'hierarchical'      => true,
            'labels'            => array(
                'name'              => mkv__('Danh mục'),
                'singular_name'     => mkv__('Danh mục'),
                'search_items'      => mkv__('Tìm danh mục'),
                'all_items'         => mkv__('Tất cả danh mục'),
                'parent_item'       => mkv__('Danh mục cha'),
                'parent_item_colon' => mkv__('Danh mục cha:'),
                'edit_item'         => mkv__('Sửa danh mục'),
                'update_item'       => mkv__('Cập nhật danh mục'),
                'add_new_item'      => mkv__('Thêm danh mục mới'),
                'new_item_name'     => mkv__('Tên danh mục mới'),
                'menu_name'         => mkv__('Danh mục'),
            ),
            'show_ui'           => true,
            'show_in_menu'      => false, // Hide native menu, we use custom view
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'mkv-category'),
            'capabilities'      => array(
                'manage_terms' => 'edit_posts',
                'edit_terms'   => 'edit_posts',
                'delete_terms' => 'edit_posts',
                'assign_terms' => 'edit_posts',
            ),
        ));

        register_post_type('mkv_product', array(
            'labels' => array(
                'name'                  => mkv__('Sản phẩm'),
                'singular_name'         => mkv__('Sản phẩm'),
                'menu_name'             => mkv__('Sản phẩm'),
                'name_admin_bar'        => mkv__('Sản phẩm'),
                'add_new'               => mkv__('Thêm mới'),
                'add_new_item'          => mkv__('Thêm sản phẩm mới'),
                'new_item'              => mkv__('Sản phẩm mới'),
                'edit_item'             => mkv__('Sửa sản phẩm'),
                'view_item'             => mkv__('Xem sản phẩm'),
                'all_items'             => mkv__('Tất cả sản phẩm'),
                'search_items'          => mkv__('Tìm kiếm sản phẩm'),
                'parent_item_colon'     => mkv__('Sản phẩm cha:'),
                'not_found'             => mkv__('Không tìm thấy sản phẩm'),
                'not_found_in_trash'    => mkv__('Không có sản phẩm trong thùng rác'),
                'featured_image'        => mkv__('Ảnh đại diện'),
                'set_featured_image'    => mkv__('Đặt ảnh đại diện'),
                'remove_featured_image' => mkv__('Xóa ảnh đại diện'),
                'use_featured_image'    => mkv__('Sử dụng làm ảnh đại diện'),
                'archives'              => mkv__('Lưu trữ sản phẩm'),
                'insert_into_item'      => mkv__('Chèn vào sản phẩm'),
                'uploaded_to_this_item' => mkv__('Đã tải lên sản phẩm này'),
                'filter_items_list'     => mkv__('Lọc danh sách sản phẩm'),
                'items_list_navigation' => mkv__('Điều hướng danh sách sản phẩm'),
                'items_list'            => mkv__('Danh sách sản phẩm'),
            ),
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => false, // Managed explicitly via add_admin_menu to prevent duplicates
            'capability_type' => 'post',
            'hierarchical'    => false,
            'supports'        => array('title', 'editor', 'thumbnail'),
            'has_archive'     => false,
            'rewrite'         => false,
            'capabilities'    => array(
                'edit_post'          => 'mkv_manage_products',
                'read_post'          => 'mkv_manage_products',
                'delete_post'        => 'mkv_manage_products',
                'edit_posts'         => 'mkv_manage_products',
                'edit_others_posts'  => 'mkv_manage_products',
                'publish_posts'      => 'mkv_manage_products',
                'read_private_posts' => 'mkv_manage_products',
                'create_posts'       => 'mkv_manage_products',
                'delete_posts'       => 'mkv_manage_products',
            ),
        ));
    }

    public function enqueue_scripts($hook)
    {
        if ($hook === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'mkv_product') {
            add_action('admin_footer', function() {
                ?>
                <script>
                jQuery(document).ready(function($){
                    // Nút Thêm sản phẩm mới
                    $('.page-title-action').html('<i class="hgi-stroke hgi-add-square" style="font-size:16px;"></i> Thêm sản phẩm mới');

                    // Xóa triệt để sọc đứng | giữa các tab trạng thái (Tất cả | Đã xuất bản)
                    $('.subsubsub li').each(function() {
                        $(this).contents().each(function() {
                            if (this.nodeType === 3) {
                                this.nodeValue = '';
                            }
                        });
                    });

                    // Tích hợp ô Tìm kiếm trực tiếp vào thanh công cụ .tablenav.top
                    function mkvUnifyToolbar() {
                        const $search = $('#posts-filter .search-box, .wrap > .search-box');
                        const $toolbar = $('#posts-filter .tablenav.top');
                        if ($search.length && $toolbar.length && !$toolbar.find('.search-box').length) {
                            $toolbar.prepend($search);
                            $('#post-search-input').attr('placeholder', 'Tìm tên sản phẩm, mã SKU...');
                            $('#search-submit').val('Tìm kiếm');
                        }
                    }
                    mkvUnifyToolbar();
                    setTimeout(mkvUnifyToolbar, 50);
                    setTimeout(mkvUnifyToolbar, 300);

                    // Defeat WP's sticky column headers and toolbar jumping completely
                    function mkvDefeatSticky() {
                        $(window).off('scroll.pin-menu');
                        $('.tablenav.top').removeClass('is-position-fixed').css({
                            'position': 'static',
                            'top': 'auto',
                            'width': 'auto',
                            'box-sizing': 'border-box'
                        });
                        $('.wp-list-table thead, .wp-list-table thead th').css({
                            'position': 'static',
                            'top': 'auto'
                        });
                        $('.is-placeholder, .tablenav-pages.is-placeholder, .sticky-placeholder').remove();
                        $('#wpbody-content').css({'padding-top': '0', 'margin-top': '0'});
                    }
                    mkvDefeatSticky();
                    setTimeout(mkvDefeatSticky, 100);
                    setTimeout(mkvDefeatSticky, 400);
                    setTimeout(mkvDefeatSticky, 1200);
                    $(window).on('scroll.mkv_fix resize.mkv_fix', mkvDefeatSticky);

                    <?php if (mkv_get_lang() === 'vi'): ?>
                    // Việt hóa các nút và select mặc định của WP
                    $('#post-query-submit').val('Lọc');
                    $('#doaction, #doaction2').val('Áp dụng');
                    $('select[name="action"] option[value="-1"], select[name="action2"] option[value="-1"]').text('Hành động hàng loạt');
                    $('select[name="action"] option[value="edit"], select[name="action2"] option[value="edit"]').text('Chỉnh sửa hàng loạt');
                    $('select[name="action"] option[value="trash"], select[name="action2"] option[value="trash"]').text('Chuyển vào thùng rác');
                    $('select[name="m"] option[value="0"]').text('Tất cả thời gian');
                    
                    // Việt hóa các tháng (VD: August 2026 -> Tháng 8 2026)
                    const m_names = {
                        'January': 'Tháng 1', 'February': 'Tháng 2', 'March': 'Tháng 3',
                        'April': 'Tháng 4', 'May': 'Tháng 5', 'June': 'Tháng 6',
                        'July': 'Tháng 7', 'August': 'Tháng 8', 'September': 'Tháng 9',
                        'October': 'Tháng 10', 'November': 'Tháng 11', 'December': 'Tháng 12'
                    };
                    $('select[name="m"] option').each(function() {
                        let text = $(this).text();
                        for (let eng in m_names) {
                            if (text.includes(eng)) {
                                $(this).text(text.replace(eng, m_names[eng]));
                                break;
                            }
                        }
                    });
                    
                    // Việt hóa Row Actions (Edit, Quick Edit, Trash, View)
                    $('.row-actions .edit a').text('Sửa');
                    $('.row-actions .inline .editinline').text('Sửa nhanh'); // Thường là thẻ button
                    $('.row-actions .trash .submitdelete').text('Xóa tạm');
                    $('.row-actions .view a').text('Xem');
                    
                    // Việt hóa bộ lọc trạng thái (All, Published, Trash)
                    $('.subsubsub .all a').each(function(){ $(this).html($(this).html().replace('All', 'Tất cả')); });
                    $('.subsubsub .publish a').each(function(){ $(this).html($(this).html().replace('Published', 'Đã xuất bản')); });
                    $('.subsubsub .trash a').each(function(){ $(this).html($(this).html().replace('Trash', 'Thùng rác')); });
                    
                    // Việt hóa dòng thông báo trống
                    $('.no-items').text('Không tìm thấy sản phẩm nào.');
                    
                    // Việt hóa khối Sửa nhanh (Quick Edit)
                    let $inline = $('#inline-edit');
                    if ($inline.length) {
                        $inline.find('.inline-edit-legend').text('Sửa nhanh');
                        $inline.find('.title').each(function(){
                            let txt = $(this).text();
                            if(txt.includes('Title')) $(this).text('Tên SP');
                            if(txt.includes('Date')) $(this).text('Ngày');
                            if(txt.includes('Password')) $(this).text('Mật khẩu');
                            if(txt.includes('Status')) $(this).text('Trạng thái');
                            if(txt.includes('Categories')) $(this).text('Danh mục');
                        });
                        $inline.find('.inline-edit-or').text(' -HOẶC- ');
                        $inline.find('.checkbox-title').text('Riêng tư');
                        $inline.find('.cancel').text('Hủy');
                        $inline.find('.save').text('Cập nhật');
                        
                        $inline.find('select[name="_status"] option[value="publish"]').text('Đã xuất bản');
                        $inline.find('select[name="_status"] option[value="pending"]').text('Chờ duyệt');
                        $inline.find('select[name="_status"] option[value="draft"]').text('Bản nháp');
                        
                        $inline.find('select[name="mm"] option').each(function(){
                            let text = $(this).text();
                            for (let eng in m_names) {
                                if (text.includes(eng)) {
                                    $(this).text(text.replace(eng, m_names[eng]));
                                    break;
                                }
                            }
                        });
                    }
                    
                    // Việt hóa cột Ngày tạo (Published ... at ...)
                    $('td.column-date').each(function() {
                        let html = $(this).html();
                        html = html.replace('Published', 'Đã xuất bản');
                        html = html.replace(' at ', ' lúc ');
                        html = html.replace('am', 'SA').replace('pm', 'CH');
                        $(this).html(html);
                    });
                    
                    // Việt hóa nút Tìm kiếm và đếm số mục (items)
                    $('#search-submit').val('Tìm kiếm');
                    $('.tablenav-pages .displaying-num').each(function() {
                        let text = $(this).text();
                        $(this).text(text.replace('items', 'mục').replace('item', 'mục'));
                    });
                    <?php endif; ?>
                });
                </script>
                <?php
            });
        }

        // Taxonomy pages (Categories)
        if ($hook === 'edit-tags.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'mkv_product_cat') {
            add_action('admin_footer', function() {
                ?>
                <script>
                jQuery(document).ready(function($){
                    // Disable WP's sticky column headers that cause scroll jumping
                    $(window).off('scroll.pin-menu');
                });
                </script>
                <?php
            });
        }

        // Shared inline styles for all MKV CPT pages (back button)
        $is_mkv_page = (
            ($hook === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'mkv_product') ||
            ($hook === 'edit-tags.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'mkv_product_cat') ||
            (in_array($hook, array('post.php', 'post-new.php')) && isset($GLOBALS['typenow']) && $GLOBALS['typenow'] === 'mkv_product')
        );
        if ($is_mkv_page) {
            add_action('admin_head', function() {
                echo '<style>
                .mkv-cpt-back-btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; padding: 6px 14px; border-radius: 8px; font-size: 13px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; }
                .mkv-cpt-back-btn:hover { background: #f1f5f9; color: #1e293b; border-color: #cbd5e1; }
                </style>';
            });
        }

        if (!in_array($hook, array('post.php', 'post-new.php'))) return;
    }

    /**
     * Inject a self-contained MKV header (topbar + navbar) on CPT pages.
     * Sets $mkv_cpt_standalone_mode so header-kiotviet.php skips wrapper divs
     * and AI drawer, preventing DOM corruption on WP native pages.
     */
    public function inject_custom_layout_header()
    {
        global $pagenow, $typenow;
        
        $post_type = $typenow;
        if (empty($post_type) && isset($_GET['post'])) {
            $post_type = get_post_type(intval($_GET['post']));
        }
        if (empty($post_type) && isset($_GET['post_type'])) {
            $post_type = sanitize_key($_GET['post_type']);
        }
        
        $is_mkv_cpt = ($post_type === 'mkv_product');
        $is_mkv_tax = (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'mkv_product_cat');

        if (!$is_mkv_cpt && !$is_mkv_tax) {
            return;
        }

        // Tell header-kiotviet.php to output in standalone mode:
        // - Closes mkv-app-layout immediately after navbar
        // - Skips AI drawer and wrapper divs (mkv-app-body, mkv-page-scroll)
        $mkv_cpt_standalone_mode = true;
        require MKV_DIR . 'includes/views/header-kiotviet.php';

        // Back button on edit/new product pages
        if (in_array($pagenow, array('post.php', 'post-new.php')) && $is_mkv_cpt) {
            echo '<div class="mkv-cpt-back-bar" style="padding: 16px 20px 0 20px;">
                    <a href="' . esc_url(admin_url('edit.php?post_type=mkv_product')) . '" class="mkv-cpt-back-btn">
                        <i class="hgi-stroke hgi-arrow-left-01"></i> ' . esc_html(mkv__('Quay lại danh sách sản phẩm')) . '
                    </a>
                  </div>';
        }
        // Back button on taxonomy edit page
        if ($is_mkv_tax && ($pagenow === 'term.php' || ($pagenow === 'edit-tags.php' && isset($_GET['action']) && $_GET['action'] === 'edit'))) {
            echo '<div class="mkv-cpt-back-bar" style="padding: 16px 20px 0 20px;">
                    <a href="' . esc_url(admin_url('admin.php?page=mkv-categories')) . '" class="mkv-cpt-back-btn">
                        <i class="hgi-stroke hgi-arrow-left-01"></i> ' . esc_html(mkv__('Quay lại danh mục')) . '
                    </a>
                  </div>';
        }
    }

    public function add_admin_menu()
    {
        add_submenu_page(
            'mini-kiotviet',
            'Sản phẩm',
            'Sản phẩm',
            'mkv_manage_products',
            'edit.php?post_type=mkv_product'
        );

        add_submenu_page(
            'mini-kiotviet',
            'Danh mục',
            'Danh mục',
            'mkv_manage_products',
            'mkv-categories',
            array($this, 'render_categories_page')
        );
    }

    public function render_categories_page()
    {
        // Load the custom React/Vue-like view for Categories
        require MKV_DIR . 'includes/views/view-categories.php';
    }

    public function add_product_metaboxes()
    {
        add_meta_box('mkv_product_details', 'Thông tin chi tiết sản phẩm',
            array($this, 'render_meta_box_content'), 'mkv_product', 'normal', 'high');
    }

    public function render_meta_box_content($post)
    {
        wp_nonce_field('mkv_save_product_meta', 'mkv_product_nonce');

        $sku       = get_post_meta($post->ID, '_mkv_sku', true);
        $barcode   = get_post_meta($post->ID, '_mkv_barcode', true);
        $price_in  = get_post_meta($post->ID, '_mkv_price_in', true);
        $price_out = get_post_meta($post->ID, '_mkv_price_out', true);
        $stock     = get_post_meta($post->ID, '_mkv_stock', true);
        $min_stock = get_post_meta($post->ID, '_mkv_min_stock', true);
        $can_cost  = current_user_can('mkv_view_cost_price');
        include MKV_DIR . 'includes/views/view-product-metabox.php';
    }

    public function save_product_meta($post_id, $post)
    {
        if (!isset($_POST['mkv_product_nonce']) || !wp_verify_nonce($_POST['mkv_product_nonce'], 'mkv_save_product_meta')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if ($post->post_status === 'trash') return;

        $sku       = sanitize_text_field($_POST['mkv_sku'] ?? '');
        $barcode   = sanitize_text_field($_POST['mkv_barcode'] ?? '');
        $price_in  = floatval($_POST['mkv_price_in'] ?? 0);
        $price_out = floatval($_POST['mkv_price_out'] ?? 0);
        $stock     = intval($_POST['mkv_stock'] ?? 0);
        $min_stock = intval($_POST['mkv_min_stock'] ?? get_option('mkv_min_stock_threshold', 5));

        // Validation
        if (empty($_POST['post_title'])) {
            set_transient('mkv_product_error_' . $post_id, 'Tên sản phẩm là bắt buộc.', 45);
            return;
        }
        if ($price_in < 0 || $price_out <= 0) {
            set_transient('mkv_product_error_' . $post_id, 'Giá bán phải lớn hơn 0 và giá nhập không được âm.', 45);
            return;
        }
        if (current_user_can('mkv_view_cost_price') && $price_out < $price_in) {
            set_transient('mkv_product_error_' . $post_id, 'Giá bán không được nhỏ hơn giá nhập.', 45);
            return;
        }

        // Tự sinh barcode
        if (empty($barcode)) {
            $barcode = '893' . str_pad($post_id, 6, '0', STR_PAD_LEFT) . rand(100, 999);
        }

        // Unique SKU
        global $wpdb;
        $sku_valid = true;
        if ($sku) {
            $sku_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_mkv_sku' AND meta_value=%s AND post_id!=%d",
                $sku, $post_id
            ));
            if ($sku_exists) {
                set_transient('mkv_product_error_' . $post_id, "SKU '{$sku}' đã tồn tại.", 45);
                $sku_valid = false;
            }
        }
        
        // Unique Barcode
        $barcode_valid = true;
        if ($barcode) {
            $barcode_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_mkv_barcode' AND meta_value=%s AND post_id!=%d",
                $barcode, $post_id
            ));
            if ($barcode_exists) {
                set_transient('mkv_product_error_' . $post_id, "Mã vạch (Barcode) '{$barcode}' đã tồn tại ở sản phẩm khác.", 45);
                $barcode_valid = false;
            }
        }

        if ($sku_valid) update_post_meta($post_id, '_mkv_sku', $sku);
        if ($barcode_valid) update_post_meta($post_id, '_mkv_barcode', $barcode);
        update_post_meta($post_id, '_mkv_price_in',  $price_in);
        update_post_meta($post_id, '_mkv_price_out', $price_out);
        update_post_meta($post_id, '_mkv_min_stock', $min_stock);

        // Đồng bộ số tồn xuống CSDL quản lý Kho 1 (Location ID = 1) có lưu log
        $wpdb->query('START TRANSACTION');
        try {
            $old_stock = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT stock FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d AND location_id=1 FOR UPDATE", $post_id
            ));
            
            $diff = $stock - $old_stock;
            if ($diff !== 0) {
                $wpdb->query($wpdb->prepare(
                    "INSERT INTO {$wpdb->prefix}mkv_inventory_stock (product_id, location_id, stock) 
                     VALUES (%d, 1, %d) 
                     ON DUPLICATE KEY UPDATE stock = %d",
                    $post_id, $stock, $stock
                ));
                
                $wpdb->insert("{$wpdb->prefix}mkv_inventory_logs", array(
                    'product_id'  => $post_id,
                    'location_id' => 1,
                    'type'        => $diff > 0 ? 'in' : 'out',
                    'qty'         => abs($diff),
                    'note'        => 'Cập nhật từ trang sửa sản phẩm',
                    'created_by'  => get_current_user_id()
                ));
            }
            
            // Cập nhật lại tổng tồn
            $total = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(stock) FROM {$wpdb->prefix}mkv_inventory_stock WHERE product_id=%d FOR UPDATE", $post_id
            ));
            update_post_meta($post_id, '_mkv_stock', $total);
            
            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
        }
    }

    public function show_admin_notices()
    {
        global $post;
        $pid = $post ? $post->ID : 0;
        $key = 'mkv_product_error_' . $pid;
        if ($err = get_transient($key)) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($err) . '</p></div>';
            delete_transient($key);
        }
        
        if (isset($_GET['imported']) && intval($_GET['imported']) > 0) {
            $num = intval($_GET['imported']);
            echo '<div class="notice notice-success is-dismissible"><p>Đã nhập thành công <strong>' . $num . '</strong> sản phẩm từ file CSV.</p></div>';
        }
    }

    public function set_custom_columns($columns)
    {
        return array(
            'cb'                       => $columns['cb'],
            'mkv_image'                => mkv__('Ảnh'),
            'title'                    => mkv__('Tên sản phẩm'),
            'mkv_sku'                  => mkv__('SKU'),
            'taxonomy-mkv_product_cat' => mkv__('Danh mục'),
            'mkv_price_out'            => mkv__('Giá bán'),
            'mkv_price_in'             => mkv__('Giá nhập'),
            'mkv_stock'                => mkv__('Tồn kho'),
            'date'                     => mkv__('Ngày tạo'),
        );
    }

    public function remove_quick_edit($actions, $post)
    {
        if ($post->post_type === 'mkv_product') {
            unset($actions['inline hide-if-no-js']); // Removes "Quick Edit"
        }
        return $actions;
    }

    public function custom_column_data($column, $post_id)
    {
        switch ($column) {
            case 'mkv_image':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, array(44, 44), array('style' => 'width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid var(--mkv-border);display:block;margin:0 auto;'));
                } else {
                    echo '<div style="width:44px;height:44px;background:var(--mkv-border-light);border-radius:8px;display:flex;align-items:center;justify-content:center;margin:0 auto;border:1px solid var(--mkv-border);color:var(--mkv-text-muted);"><i class="hgi-stroke hgi-image-02" style="font-size:18px;"></i></div>';
                }
                break;
            case 'mkv_sku':
                $sku = get_post_meta($post_id, '_mkv_sku', true);
                echo $sku ? '<span style="background:var(--mkv-border-light);color:var(--mkv-text-main);font-weight:600;padding:3px 8px;border-radius:6px;border:1px solid var(--mkv-border);font-family:monospace;font-size:12px;display:inline-block;">' . esc_html($sku) . '</span>' : '<span style="color:var(--mkv-text-muted);">—</span>';
                break;
            case 'mkv_price_out':
                $price = get_post_meta($post_id, '_mkv_price_out', true);
                echo $price ? '<strong style="color:var(--mkv-primary);font-size:13.5px;font-weight:700;">' . number_format((float)$price, 0, ',', '.') . ' ₫</strong>' : '<span style="color:var(--mkv-text-muted);">—</span>';
                break;
            case 'mkv_price_in':
                if (!current_user_can('mkv_view_cost_price')) {
                    echo '<span style="color:var(--mkv-text-muted);font-size:12px;display:inline-flex;align-items:center;gap:4px;"><i class="hgi-stroke hgi-circle-lock-01"></i> ' . esc_html(mkv__('Ẩn')) . '</span>';
                    break;
                }
                $price = get_post_meta($post_id, '_mkv_price_in', true);
                echo $price ? '<span style="color:var(--mkv-text-muted);font-size:13px;font-weight:500;">' . number_format((float)$price, 0, ',', '.') . ' ₫</span>' : '<span style="color:var(--mkv-text-muted);">—</span>';
                break;
            case 'mkv_stock':
                $stock     = (int) get_post_meta($post_id, '_mkv_stock', true);
                $min_stock = (int) get_post_meta($post_id, '_mkv_min_stock', true) ?: (int) get_option('mkv_min_stock_threshold', 5);
                if ($stock <= 0) {
                    echo '<span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-weight:600;font-size:12px;background:#fee2e2;color:#dc2626;border:1px solid #fecaca;">' . esc_html(mkv__('Hết hàng')) . '</span>';
                } elseif ($stock <= $min_stock) {
                    echo '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-weight:600;font-size:12px;background:#fef3c7;color:#d97706;border:1px solid #fde68a;"><i class="hgi-stroke hgi-alert-02"></i> ' . number_format($stock, 0, ',', '.') . '</span>';
                } else {
                    echo '<span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-weight:600;font-size:12px;background:#dcfce7;color:#16a34a;border:1px solid #bbf7d0;">' . number_format($stock, 0, ',', '.') . '</span>';
                }
                break;
        }
    }

    public function sortable_columns($columns)
    {
        $columns['mkv_price_out'] = 'mkv_price_out';
        $columns['mkv_stock']     = 'mkv_stock';
        return $columns;
    }

    public function filter_by_category()
    {
        global $typenow;
        if ($typenow !== 'mkv_product') return;
        
        $terms = get_terms(array('taxonomy' => 'mkv_product_cat', 'hide_empty' => false));
        if (!empty($terms) && !is_wp_error($terms)) {
            $selected = isset($_GET['mkv_product_cat']) ? $_GET['mkv_product_cat'] : '';
            wp_dropdown_categories(array(
                'show_option_all' => 'Tất cả danh mục',
                'taxonomy'        => 'mkv_product_cat',
                'name'            => 'mkv_product_cat',
                'orderby'         => 'name',
                'selected'        => $selected,
                'show_count'      => true,
                'hide_empty'      => false,
                'value_field'     => 'slug',
            ));
        }
        
        // Export button
        echo '<button type="submit" name="mkv_export_csv" value="1" class="button button-secondary" style="margin-left:8px; display:inline-flex; align-items:center; gap:6px;"><i class="hgi-stroke hgi-download-02" style="font-size:15px;"></i> ' . esc_html(mkv__('Xuất CSV')) . '</button>';
        
        // Import button and hidden form
        echo '<button type="button" class="button button-secondary" style="margin-left:8px; display:inline-flex; align-items:center; gap:6px;" onclick="document.getElementById(\'mkv_import_file\').click();"><i class="hgi-stroke hgi-upload-02" style="font-size:15px;"></i> ' . esc_html(mkv__('Nhập CSV')) . '</button>';
        
        // Add JS and Hidden Form to body via footer
        add_action('admin_footer', function() {
            ?>
            <form id="mkv_import_form" method="post" enctype="multipart/form-data" style="display:none;">
                <?php wp_nonce_field('mkv_import_nonce', 'mkv_import_nonce_val'); ?>
                <input type="hidden" name="post_type" value="mkv_product">
                <input type="hidden" name="mkv_import_submit" value="1">
                <input type="file" name="mkv_import_csv" id="mkv_import_file" accept=".csv" onchange="if(this.value) { document.getElementById('mkv_import_form').submit(); }">
            </form>
            <?php
        });
    }

    public function perform_filtering($query)
    {
        global $pagenow;
        $type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
        if ('mkv_product' !== $type || !is_admin() || $pagenow !== 'edit.php') return;
        if (isset($_GET['orderby'])) {
            $map = array('mkv_price_out' => '_mkv_price_out', 'mkv_stock' => '_mkv_stock');
            if (isset($map[$_GET['orderby']])) {
                $query->query_vars['meta_key'] = $map[$_GET['orderby']];
                $query->query_vars['orderby']  = 'meta_value_num';
            }
        }
    }

    public function export_csv_action()
    {
        if (!isset($_GET['mkv_export_csv']) || !isset($_GET['post_type']) || $_GET['post_type'] !== 'mkv_product') return;
        if (!current_user_can('mkv_manage_products')) return;

        $can_cost = current_user_can('mkv_view_cost_price');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=products_' . date('Y-m-d') . '.csv');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        $headers = array('ID', 'Tên sản phẩm', 'SKU', 'Barcode', 'Giá bán', 'Tồn kho', 'Ngưỡng cảnh báo');
        if ($can_cost) array_splice($headers, 4, 0, array('Giá nhập'));
        fputcsv($out, $headers);

        $products = get_posts(array('post_type' => 'mkv_product', 'posts_per_page' => -1, 'post_status' => 'publish'));
        foreach ($products as $p) {
            $row = array(
                $p->ID,
                $p->post_title,
                get_post_meta($p->ID, '_mkv_sku', true),
                get_post_meta($p->ID, '_mkv_barcode', true),
                get_post_meta($p->ID, '_mkv_price_out', true),
                get_post_meta($p->ID, '_mkv_stock', true),
                get_post_meta($p->ID, '_mkv_min_stock', true),
            );
            if ($can_cost) array_splice($row, 4, 0, array(get_post_meta($p->ID, '_mkv_price_in', true)));
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    public function import_csv_action()
    {
        global $wpdb;

        if (!isset($_POST['mkv_import_submit']) || !isset($_FILES['mkv_import_csv'])) return;
        if (!isset($_POST['mkv_import_nonce_val']) || !wp_verify_nonce($_POST['mkv_import_nonce_val'], 'mkv_import_nonce')) return;
        if (!current_user_can('mkv_manage_products')) return;

        if ($_FILES['mkv_import_csv']['error'] !== UPLOAD_ERR_OK) {
            wp_die('Không thể tải lên file CSV.');
        }

        $file = $_FILES['mkv_import_csv']['tmp_name'];
        if (!$file) return;

        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            wp_redirect(admin_url('edit.php?post_type=mkv_product&imported=0'));
            exit;
        }
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $headers = array_map('trim', $headers);

        $imported = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($headers)) continue;
            $data = array_combine($headers, $row);
            $title = sanitize_text_field($data['Tên sản phẩm'] ?? $data['Tên'] ?? '');
            $sku = sanitize_text_field($data['SKU'] ?? '');
            $barcode = sanitize_text_field($data['Barcode'] ?? '');
            $price_in = max(0, floatval($data['Giá nhập'] ?? 0));
            $price_out = max(0, floatval($data['Giá bán'] ?? 0));
            $stock = max(0, intval($data['Tồn kho'] ?? 0));
            if (empty($title)) continue;
            if ($price_out <= 0) continue;
            if ($price_out < $price_in) continue;

            $pid = wp_insert_post(array('post_title' => $title, 'post_type' => 'mkv_product', 'post_status' => 'publish'));
            if (!is_wp_error($pid)) {
                update_post_meta($pid, '_mkv_sku',       $sku);
                update_post_meta($pid, '_mkv_barcode',   $barcode ?: '893' . rand(100000000, 999999999));
                update_post_meta($pid, '_mkv_price_in',  $price_in);
                update_post_meta($pid, '_mkv_price_out', $price_out);
                $wpdb->query($wpdb->prepare(
                    "INSERT INTO {$wpdb->prefix}mkv_inventory_stock (product_id, location_id, stock)
                     VALUES (%d, 1, %d) ON DUPLICATE KEY UPDATE stock = %d",
                    $pid, $stock, $stock
                ));
                update_post_meta($pid, '_mkv_stock', $stock);
                $imported++;
            }
        }
        fclose($handle);
        wp_redirect(admin_url("edit.php?post_type=mkv_product&imported={$imported}"));
        exit;
    }

    public function register_rest_routes()
    {
        register_rest_route('mkv/v1', '/products', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'api_list_products'),
            'permission_callback' => function () { return current_user_can('mkv_manage_products'); },
        ));
    }

    public function api_list_products($request)
    {
        $search   = sanitize_text_field($request->get_param('search') ?? '');
        $category = sanitize_text_field($request->get_param('category') ?? '');
        $page     = max(1, intval($request->get_param('page') ?? 1));
        $per_page = 20;

        $args = array(
            'post_type'      => 'mkv_product',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            's'              => $search,
        );
        if ($category) {
            $args['tax_query'] = array(array('taxonomy' => 'mkv_product_cat', 'field' => 'slug', 'terms' => $category));
        }

        $query   = new WP_Query($args);
        $results = array();
        $can_cost = current_user_can('mkv_view_cost_price');

        foreach ($query->posts as $p) {
            $row = array(
                'id'        => $p->ID,
                'name'      => $p->post_title,
                'sku'       => get_post_meta($p->ID, '_mkv_sku', true),
                'price_out' => (float) get_post_meta($p->ID, '_mkv_price_out', true),
                'stock'     => (int) get_post_meta($p->ID, '_mkv_stock', true),
            );
            if ($can_cost) $row['price_in'] = (float) get_post_meta($p->ID, '_mkv_price_in', true);
            $results[] = $row;
        }

        return rest_ensure_response(array(
            'products'    => $results,
            'total'       => $query->found_posts,
            'total_pages' => $query->max_num_pages,
        ));
    }
    public function handle_add_category()
    {
        if ((!current_user_can('mkv_manage_products') && !current_user_can('manage_options')) || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'mkv_add_category_nonce')) {
            wp_die('Bạn không có quyền thực hiện hành động này hoặc phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
        }

        $name   = sanitize_text_field($_POST['cat_name'] ?? '');
        $slug   = sanitize_title($_POST['cat_slug'] ?? '');
        $parent = intval($_POST['cat_parent'] ?? 0);
        $desc   = sanitize_textarea_field($_POST['cat_desc'] ?? '');

        if (empty($name)) {
            wp_redirect(admin_url('admin.php?page=mkv-categories&error=invalid'));
            exit;
        }

        $args = array(
            'description' => $desc,
            'parent'      => $parent,
        );
        if (!empty($slug)) {
            $args['slug'] = $slug;
        }

        $result = wp_insert_term($name, 'mkv_product_cat', $args);

        if (is_wp_error($result)) {
            $err_msg = $result->get_error_message();
            wp_redirect(admin_url('admin.php?page=mkv-categories&error=custom&err_msg=' . urlencode($err_msg)));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=mkv-categories&added=1'));
        exit;
    }

    public function handle_delete_category()
    {
        if ((!current_user_can('mkv_manage_products') && !current_user_can('manage_options')) || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'mkv_delete_category_nonce')) {
            wp_die('Bạn không có quyền thực hiện hành động này hoặc phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
        }

        $term_id = intval($_POST['cat_id'] ?? 0);
        if ($term_id > 0) {
            wp_delete_term($term_id, 'mkv_product_cat');
            wp_redirect(admin_url('admin.php?page=mkv-categories&deleted=1'));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=mkv-categories&error=invalid'));
        exit;
    }

    public function handle_edit_category()
    {
        if ((!current_user_can('mkv_manage_products') && !current_user_can('manage_options')) || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'mkv_edit_category_nonce')) {
            wp_die('Bạn không có quyền thực hiện hành động này hoặc phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
        }

        $term_id = intval($_POST['cat_id'] ?? 0);
        $name    = sanitize_text_field($_POST['cat_name'] ?? '');
        $slug    = sanitize_title($_POST['cat_slug'] ?? '');
        $parent  = intval($_POST['cat_parent'] ?? 0);
        $desc    = sanitize_textarea_field($_POST['cat_desc'] ?? '');

        if ($term_id <= 0 || empty($name)) {
            wp_redirect(admin_url('admin.php?page=mkv-categories&error=invalid'));
            exit;
        }

        // Prevent circular parenting
        if ($parent === $term_id) {
            $parent = 0;
        }

        $args = array(
            'name'        => $name,
            'description' => $desc,
            'parent'      => $parent,
        );
        if (!empty($slug)) {
            $args['slug'] = $slug;
        }

        $result = wp_update_term($term_id, 'mkv_product_cat', $args);

        if (is_wp_error($result)) {
            $err_msg = $result->get_error_message();
            wp_redirect(admin_url('admin.php?page=mkv-categories&error=custom&edit=' . $term_id . '&err_msg=' . urlencode($err_msg)));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=mkv-categories&updated=1'));
        exit;
    }

    public function redirect_legacy_taxonomy_pages()
    {
        global $pagenow;
        if ($pagenow === 'edit-tags.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'mkv_product_cat') {
            wp_safe_redirect(admin_url('admin.php?page=mkv-categories'));
            exit;
        }
        if ($pagenow === 'term.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'mkv_product_cat' && isset($_GET['tag_ID'])) {
            $cat_id = intval($_GET['tag_ID']);
            wp_safe_redirect(admin_url('admin.php?page=mkv-categories&edit=' . $cat_id));
            exit;
        }
    }
}


