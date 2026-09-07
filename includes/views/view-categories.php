<?php
if (!defined('ABSPATH')) exit;

require_once MKV_DIR . 'includes/views/header-kiotviet.php';

// Check if in Edit mode
$editing_term = null;
if (!empty($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $term_obj = get_term($edit_id, 'mkv_product_cat');
    if ($term_obj && !is_wp_error($term_obj)) {
        $editing_term = $term_obj;
    }
}

// Fetch all categories
$categories = get_terms(array(
    'taxonomy'   => 'mkv_product_cat',
    'hide_empty' => false,
));
?>
<div class="mkv-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <h1 class="mkv-page-title"><i class="hgi-stroke hgi-folder-02"></i> <?php echo esc_html(mkv__('Quản Lý Danh Mục')); ?></h1>
        <p style="color:var(--mkv-text-muted); font-size:13px; margin:4px 0 0 0;"><?php echo esc_html(mkv__('Quản lý phân loại sản phẩm của cửa hàng')); ?></p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="<?php echo admin_url('edit.php?post_type=mkv_product'); ?>" class="mkv-btn mkv-btn-secondary">
            <i class="hgi-stroke hgi-package"></i> <?php echo esc_html(mkv__('Quay lại Sản phẩm')); ?>
        </a>
    </div>
</div>

<div class="mkv-tabs-bar">
    <a href="<?php echo admin_url('admin.php?page=mkv-categories'); ?>" class="mkv-tab-link active">
        <i class="hgi-stroke hgi-list-view"></i> Tất cả danh mục
    </a>
</div>

<?php if (isset($_GET['added'])): ?>
    <div class="notice notice-success is-dismissible" style="border-left-color:var(--mkv-green); margin:12px 0 16px 0;">
        <p><strong>Thành công!</strong> Đã thêm danh mục mới.</p>
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated'])): ?>
    <div class="notice notice-success is-dismissible" style="border-left-color:var(--mkv-green); margin:12px 0 16px 0;">
        <p><strong>Thành công!</strong> Đã cập nhật danh mục.</p>
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="notice notice-success is-dismissible" style="border-left-color:var(--mkv-green); margin:12px 0 16px 0;">
        <p><strong>Thành công!</strong> Đã xóa danh mục khỏi hệ thống.</p>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])):
    $err_map = array(
        'invalid' => 'Thông tin không hợp lệ, vui lòng kiểm tra lại.',
        'db'      => 'Lỗi cơ sở dữ liệu, vui lòng thử lại.',
        'exists'  => 'Tên danh mục hoặc chuỗi đường dẫn (slug) đã tồn tại.'
    );
    $err_msg = !empty($_GET['err_msg']) ? sanitize_text_field(urldecode($_GET['err_msg'])) : ($err_map[$_GET['error']] ?? 'Có lỗi xảy ra.'); ?>
    <div class="notice notice-error is-dismissible" style="border-left-color:var(--mkv-red); margin:12px 0 16px 0;">
        <p><strong>Lỗi:</strong> <?php echo esc_html($err_msg); ?></p>
    </div>
<?php endif; ?>

<div class="mkv-two-col-form" style="display:grid; grid-template-columns:360px 1fr; gap:24px; align-items:start;">
    <!-- Form Card (Thêm / Chỉnh sửa) -->
    <div class="mkv-card" style="position:sticky; top:140px;">
        <div class="mkv-card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3 class="mkv-card-title">
                <?php if ($editing_term): ?>
                    <i class="hgi-stroke hgi-edit-02" style="color:var(--mkv-primary);"></i> Cập Nhật Danh Mục
                <?php else: ?>
                    <i class="hgi-stroke hgi-add-square" style="color:var(--mkv-green);"></i> Thêm Danh Mục Mới
                <?php endif; ?>
            </h3>
            <?php if ($editing_term): ?>
                <a href="<?php echo admin_url('admin.php?page=mkv-categories'); ?>" class="mkv-badge mkv-badge-gray" style="text-decoration:none;" title="Hủy sửa và tạo mới">
                    <i class="hgi-stroke hgi-cancel-01"></i> Hủy
                </a>
            <?php endif; ?>
        </div>
        <div class="mkv-card-body">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php if ($editing_term): ?>
                    <input type="hidden" name="action" value="mkv_edit_category">
                    <input type="hidden" name="cat_id" value="<?php echo esc_attr($editing_term->term_id); ?>">
                    <?php wp_nonce_field('mkv_edit_category_nonce'); ?>
                <?php else: ?>
                    <input type="hidden" name="action" value="mkv_add_category">
                    <?php wp_nonce_field('mkv_add_category_nonce'); ?>
                <?php endif; ?>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:var(--mkv-text-main); margin-bottom:6px;">
                        Tên danh mục <span style="color:var(--mkv-red)">*</span>
                    </label>
                    <input type="text" name="cat_name" required class="mkv-input" style="width:100%;"
                           placeholder="VD: Áo thun, Quần jean..."
                           value="<?php echo $editing_term ? esc_attr($editing_term->name) : ''; ?>">
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:var(--mkv-text-main); margin-bottom:6px;">
                        Đường dẫn (Slug)
                    </label>
                    <input type="text" name="cat_slug" class="mkv-input" style="width:100%;"
                           placeholder="VD: ao-thun"
                           value="<?php echo $editing_term ? esc_attr($editing_term->slug) : ''; ?>">
                    <small style="display:block; color:var(--mkv-text-muted); font-size:11.5px; margin-top:4px;">
                        Chữ thường không dấu, dùng dấu gạch ngang (để trống sẽ tự sinh).
                    </small>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:var(--mkv-text-main); margin-bottom:6px;">
                        Danh mục cha
                    </label>
                    <select name="cat_parent" class="mkv-input" style="width:100%;">
                        <option value="0">Trống (Là danh mục gốc)</option>
                        <?php if (!is_wp_error($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <?php
                                // Don't allow selecting itself as parent
                                if ($editing_term && $cat->term_id == $editing_term->term_id) continue;
                                $selected = ($editing_term && $editing_term->parent == $cat->term_id) ? 'selected' : '';
                                ?>
                                <option value="<?php echo esc_attr($cat->term_id); ?>" <?php echo $selected; ?>>
                                    <?php echo esc_html($cat->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:var(--mkv-text-main); margin-bottom:6px;">
                        Mô tả
                    </label>
                    <textarea name="cat_desc" rows="4" class="mkv-input" style="width:100%; resize:vertical;" placeholder="Mô tả danh mục (tùy chọn)..."><?php echo $editing_term ? esc_textarea($editing_term->description) : ''; ?></textarea>
                </div>

                <div style="display:flex; gap:10px;">
                    <?php if ($editing_term): ?>
                        <button type="submit" class="mkv-btn mkv-btn-primary" style="flex:1; justify-content:center; height:38px; border-radius:8px;">
                            <i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Lưu thay đổi')); ?>
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=mkv-categories'); ?>" class="mkv-btn mkv-btn-secondary" style="justify-content:center; height:38px; border-radius:8px;">
                            <?php echo esc_html(mkv__('Hủy')); ?>
                        </a>
                    <?php else: ?>
                        <button type="submit" class="mkv-btn mkv-btn-primary" style="width:100%; justify-content:center; height:38px; border-radius:8px;">
                            <i class="hgi-stroke hgi-floppy-disk"></i> <?php echo esc_html(mkv__('Thêm danh mục')); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Bảng danh sách danh mục -->
    <div class="mkv-card" style="padding:0; flex:1; overflow:hidden;">
        <div style="padding:14px 18px; border-bottom:1px solid var(--mkv-border); background:var(--mkv-border-light); display:flex; justify-content:space-between; align-items:center;">
            <strong style="font-size:13.5px; color:var(--mkv-text-main);">
                <i class="hgi-stroke hgi-folder-favourite"></i> <?php echo esc_html(mkv__('Danh sách phân loại')); ?>
            </strong>
            <span class="mkv-badge mkv-badge-gray">
                <?php echo esc_html(mkv__('Tổng cộng:')); ?> <?php echo (!is_wp_error($categories)) ? count($categories) : 0; ?>
            </span>
        </div>

        <div class="mkv-table-wrap" style="border:none; box-shadow:none; border-radius:0; margin-bottom:0; max-height:calc(100vh - 280px);">
            <table class="mkv-table" style="margin:0; min-width:580px;">
            <thead>
                <tr>
                    <th style="width:30%;"><?php echo esc_html(mkv__('Tên danh mục')); ?></th>
                    <th style="width:30%;"><?php echo esc_html(mkv__('Mô tả')); ?></th>
                    <th style="width:20%;"><?php echo esc_html(mkv__('Chuỗi đường dẫn (Slug)')); ?></th>
                    <th style="width:100px; text-align:center;"><?php echo esc_html(mkv__('Số sản phẩm')); ?></th>
                    <th style="width:100px; text-align:center;"><?php echo esc_html(mkv__('Hành động')); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($categories) || is_wp_error($categories)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding:40px; color:var(--mkv-text-muted);">
                        <i class="hgi-stroke hgi-folder-02" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.5;"></i>
                        <?php echo esc_html(mkv__('Chưa có danh mục nào. Hãy thêm danh mục đầu tiên bên trái!')); ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php
                if (!function_exists('mkv_render_category_tree')) {
                    function mkv_render_category_tree($categories, $parent_id = 0, $level = 0, $editing_id = 0) {
                        foreach ($categories as $cat) {
                            if ($cat->parent == $parent_id) {
                                $prefix = str_repeat('— ', $level);
                                $is_current = ($editing_id > 0 && $editing_id == $cat->term_id);
                                $row_style = $is_current ? 'background:rgba(0,82,204,0.06);' : '';
                                ?>
                                <tr style="<?php echo $row_style; ?>">
                                    <td>
                                        <span style="display:inline-flex; align-items:center; gap:6px;">
                                            <?php if ($level > 0): ?>
                                                <span style="color:#94a3b8; font-weight:400;"><?php echo $prefix; ?></span>
                                            <?php endif; ?>
                                            <i class="hgi-stroke <?php echo $level > 0 ? 'hgi-folder-02' : 'hgi-folder-01'; ?>" style="color:var(--mkv-primary); font-size:15px;"></i>
                                            <strong style="color:var(--mkv-text-main); font-size:13.5px;">
                                                <?php echo esc_html($cat->name); ?>
                                            </strong>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="color:var(--mkv-text-muted); font-size:13px;">
                                            <?php echo esc_html($cat->description ?: '—'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:12px; color:#475569;">
                                            <?php echo esc_html($cat->slug); ?>
                                        </code>
                                    </td>
                                    <td style="text-align:center;">
                                        <a href="<?php echo admin_url('edit.php?post_type=mkv_product&mkv_product_cat=' . $cat->slug); ?>"
                                           class="mkv-badge mkv-badge-blue" style="text-decoration:none;" title="Xem sản phẩm trong danh mục">
                                            <?php echo intval($cat->count); ?>
                                        </a>
                                    </td>
                                    <td style="text-align:center;">
                                        <div style="display:flex; justify-content:center; gap:6px;">
                                            <a href="<?php echo admin_url('admin.php?page=mkv-categories&edit=' . $cat->term_id); ?>"
                                               class="mkv-btn mkv-btn-icon"
                                               style="<?php echo $is_current ? 'background:var(--mkv-primary); color:#fff;' : ''; ?>"
                                               title="Sửa danh mục này">
                                                <i class="hgi-stroke hgi-edit-02"></i>
                                            </a>
                                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>"
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục \'<?php echo esc_js($cat->name); ?>\'? Các sản phẩm thuộc danh mục này sẽ không bị xóa.');"
                                                  style="margin:0; display:inline;">
                                                <input type="hidden" name="action" value="mkv_delete_category">
                                                <input type="hidden" name="cat_id" value="<?php echo esc_attr($cat->term_id); ?>">
                                                <?php wp_nonce_field('mkv_delete_category_nonce'); ?>
                                                <button type="submit" class="mkv-btn mkv-btn-icon" style="color:var(--mkv-red);" title="Xóa danh mục">
                                                    <i class="hgi-stroke hgi-delete-02"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                mkv_render_category_tree($categories, $cat->term_id, $level + 1, $editing_id);
                            }
                        }
                    }
                }
                $editing_id = $editing_term ? $editing_term->term_id : 0;
                mkv_render_category_tree($categories, 0, 0, $editing_id);
                ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>
