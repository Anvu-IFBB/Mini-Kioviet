<?php if (!defined('ABSPATH')) exit; ?>
<?php require_once MKV_DIR . 'includes/views/header-kiotviet.php'; ?>
<?php if ($action === 'list'): ?>

    <div class="mkv-page-header">
        <h1 class="mkv-page-title"><i class="hgi-stroke hgi-user-group"></i> <?php echo esc_html(mkv__('Quản Lý Khách Hàng')); ?></h1>
        <div class="mkv-page-actions">
            <a href="?page=mkv-customers&action=edit" class="mkv-btn mkv-btn-primary">
                <i class="hgi-stroke hgi-user-add"></i> <?php echo esc_html(mkv__('Thêm khách hàng')); ?>
            </a>
        </div>
    </div>

    <?php if (isset($_GET['saved'])): ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html(mkv__('Đã lưu thông tin khách hàng thành công!')); ?></p></div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-warning is-dismissible"><p><?php echo esc_html(mkv__('Đã xóa khách hàng.')); ?></p></div>
    <?php endif; ?>

    <div class="mkv-card">
        <div class="mkv-card-header" style="background:#fff; padding:16px 20px;">
            <form method="get" style="margin:0; width:100%;">
                <input type="hidden" name="page" value="mkv-customers">
                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>"
                        class="mkv-input" placeholder="<?php echo esc_attr(mkv__('Tìm tên, SĐT hoặc email...')); ?>" style="width:300px;">
                    
                    <select name="orderby" class="mkv-select" style="width:160px;">
                        <option value="id" <?php selected(isset($_GET['orderby']) ? $_GET['orderby'] : '', 'id'); ?>><?php echo esc_html(mkv__('Mới nhất')); ?></option>
                        <option value="total_spent" <?php selected(isset($_GET['orderby']) ? $_GET['orderby'] : '', 'total_spent'); ?>><?php echo esc_html(mkv__('Tổng chi tiêu')); ?></option>
                        <option value="points" <?php selected(isset($_GET['orderby']) ? $_GET['orderby'] : '', 'points'); ?>><?php echo esc_html(mkv__('Điểm tích lũy')); ?></option>
                    </select>
                    
                    <select name="order" class="mkv-select" style="width:130px;">
                        <option value="DESC" <?php selected(isset($_GET['order']) ? $_GET['order'] : '', 'DESC'); ?>><?php echo esc_html(mkv__('Giảm dần')); ?></option>
                        <option value="ASC" <?php selected(isset($_GET['order']) ? $_GET['order'] : '', 'ASC'); ?>><?php echo esc_html(mkv__('Tăng dần')); ?></option>
                    </select>

                    <button type="submit" class="mkv-btn mkv-btn-secondary"><i class="hgi-stroke hgi-filter"></i> <?php echo esc_html(mkv__('Lọc & Tìm kiếm')); ?></button>
                    
                    <?php if (!empty($search) || isset($_GET['orderby'])): ?>
                        <a href="?page=mkv-customers" class="mkv-btn mkv-btn-secondary"><?php echo esc_html(mkv__('Xóa bộ lọc')); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="mkv-card-body" style="padding:0;">
            <div class="mkv-table-responsive">
                <table class="mkv-table">
            <thead>
                <tr>
                    <th><?php echo esc_html(mkv__('Tên khách hàng')); ?></th>
                    <th><?php echo esc_html(mkv__('Điện thoại')); ?></th>
                    <th><?php echo esc_html(mkv__('Email')); ?></th>
                    <th><?php echo esc_html(mkv__('Địa chỉ')); ?></th>
                    <th style="width:90px;"><?php echo esc_html(mkv__('Điểm')); ?></th>
                    <th style="width:140px;"><?php echo esc_html(mkv__('Tổng chi tiêu')); ?></th>
                    <th style="width:150px;"><?php echo esc_html(mkv__('Hành động')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--mkv-text-muted);">
                        <i class="hgi-stroke hgi-user-cross" style="font-size:40px; display:block; margin-bottom:10px;"></i>
                        <?php echo empty($search) ? esc_html(mkv__('Chưa có khách hàng nào.')) : esc_html(mkv__('Không tìm thấy khách hàng phù hợp.')); ?>
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                    <tr>
                        <td>
                            <strong><a href="?page=mkv-customers&action=edit&id=<?php echo $c->id; ?>"><?php echo esc_html($c->name); ?></a></strong>
                            <div class="row-actions">
                                <span><a href="?page=mkv-customers&action=edit&id=<?php echo $c->id; ?>"><?php echo esc_html(mkv__('Xem & Sửa')); ?></a></span> |
                                <span class="trash"><a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=mkv_delete_customer&id=' . $c->id), 'mkv_del_cust_' . $c->id); ?>"
                                    onclick="return confirm('<?php echo esc_js(mkv__('Bạn có chắc muốn xóa khách hàng này?')); ?>');" style="color:var(--mkv-red);"><?php echo esc_html(mkv__('Xóa')); ?></a></span>
                            </div>
                        </td>
                        <td><?php echo esc_html($c->phone ?: '—'); ?></td>
                        <td><?php echo esc_html($c->email ?: '—'); ?></td>
                        <td><?php echo esc_html($c->address ?: '—'); ?></td>
                        <td><span class="mkv-badge mkv-badge-purple"><?php echo intval($c->points); ?> <?php echo esc_html(mkv__('pts')); ?></span></td>
                        <td><strong style="color:var(--mkv-primary);"><?php echo number_format($c->total_spent, 0, ',', '.'); ?> ₫</strong></td>
                        <td>
                            <a href="?page=mkv-customers&action=edit&id=<?php echo $c->id; ?>" class="mkv-btn mkv-btn-sm mkv-btn-secondary">
                                <i class="hgi-stroke hgi-edit-01"></i> <?php echo esc_html(mkv__('Chi tiết')); ?>
                            </a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=mkv_delete_customer&id=' . $c->id), 'mkv_del_cust_' . $c->id); ?>"
                                class="mkv-btn mkv-btn-sm mkv-btn-danger"
                                onclick="return confirm('<?php echo esc_js(mkv__('Bạn có chắc muốn xóa khách hàng này?')); ?>');">
                                <i class="hgi-stroke hgi-delete-02"></i> <?php echo esc_html(mkv__('Xóa')); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>
    </div>

    <?php if ($total_pages > 1): ?>
    <div style="margin-top:16px; display:flex; align-items:center; justify-content:space-between;">
        <span style="color:var(--mkv-text-muted);"><?php echo esc_html(mkv__('Tổng cộng:')); ?> <strong><?php echo $total_items; ?></strong> <?php echo esc_html(mkv__('khách hàng')); ?></span>
        <div class="tablenav-pages">
            <?php echo paginate_links(array(
                'base'      => add_query_arg('paged', '%#%'),
                'format'    => '',
                'prev_text' => mkv__('← Trước'),
                'next_text' => mkv__('Sau →'),
                'total'     => $total_pages,
                'current'   => $paged,
            )); ?>
        </div>
    </div>
    <?php endif; ?>

<?php elseif ($action === 'edit'): ?>

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
        <h1 class="mkv-page-title" style="margin:0;">
            <i class="hgi-stroke hgi-<?php echo $customer ? 'user-check-01' : 'user-add'; ?>"></i>
            <?php echo $customer ? esc_html(mkv__('Hồ Sơ Khách Hàng:')) . ' ' . esc_html($customer->name) : esc_html(mkv__('Thêm Khách Hàng Mới')); ?>
        </h1>
        <a href="?page=mkv-customers" class="button"><i class="hgi-stroke hgi-arrow-left-01"></i> <?php echo esc_html(mkv__('Quay lại danh sách')); ?></a>
    </div>

    <div style="display:grid; grid-template-columns: <?php echo $customer ? '400px 1fr' : '640px'; ?>; gap:24px; align-items:start;">

        <!-- Form thông tin -->
        <div class="mkv-card">
            <h3><i class="hgi-stroke hgi-user-edit"></i> <?php echo esc_html(mkv__('Thông tin cá nhân')); ?></h3>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="mkv_save_customer">
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                <?php wp_nonce_field('mkv_save_customer_nonce', '_wpnonce'); ?>

                <div style="display:flex; flex-direction:column; gap:16px;">
                    <div>
                        <label><?php echo esc_html(mkv__('Họ và tên')); ?> <span style="color:var(--mkv-red)">*</span></label>
                        <input name="c_name" type="text" value="<?php echo $customer ? esc_attr($customer->name) : ''; ?>"
                            required style="width:100%; margin-top:6px;">
                    </div>
                    <div>
                        <label><?php echo esc_html(mkv__('Số điện thoại')); ?></label>
                        <input name="c_phone" type="text" value="<?php echo $customer ? esc_attr($customer->phone) : ''; ?>"
                            placeholder="09..." style="width:100%; margin-top:6px;">
                    </div>
                    <div>
                        <label><?php echo esc_html(mkv__('Email')); ?></label>
                        <input name="c_email" type="email" value="<?php echo $customer ? esc_attr($customer->email) : ''; ?>"
                            placeholder="email@domain.com" style="width:100%; margin-top:6px;">
                    </div>
                    <div>
                        <label><?php echo esc_html(mkv__('Địa chỉ nhận hàng')); ?></label>
                        <textarea name="c_address" rows="3" style="width:100%; margin-top:6px;"><?php echo $customer ? esc_textarea($customer->address) : ''; ?></textarea>
                    </div>
                </div>

                <?php if ($customer): ?>
                <div style="background:var(--mkv-bg); border:1px solid var(--mkv-border); border-radius:10px; padding:16px; margin-top:20px; display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <div style="font-size:11px; color:var(--mkv-text-muted); text-transform:uppercase; letter-spacing:.5px;"><?php echo esc_html(mkv__('Điểm tích lũy')); ?></div>
                        <div style="font-size:22px; font-weight:700; color:var(--mkv-green);"><?php echo number_format($customer->points); ?> <?php echo esc_html(mkv__('pts')); ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:var(--mkv-text-muted); text-transform:uppercase; letter-spacing:.5px;"><?php echo esc_html(mkv__('Tổng chi tiêu')); ?></div>
                        <div style="font-size:20px; font-weight:700; color:var(--mkv-primary);"><?php echo number_format($customer->total_spent, 0, ',', '.'); ?> ₫</div>
                    </div>
                </div>
                <?php endif; ?>

                <div style="margin-top:24px; text-align:right;">
                    <button type="submit" class="button button-primary" style="width:100%;">
                        <i class="hgi-stroke hgi-save-01"></i> <?php echo $customer ? esc_html(mkv__('Cập Nhật Thông Tin')) : esc_html(mkv__('Tạo Khách Hàng')); ?>
                    </button>
                </div>
            </form>
        </div>

        <?php if ($customer): ?>
        <!-- Tab / Danh sách Lịch sử mua hàng -->
        <div class="mkv-card" style="padding:0;">
            <div style="padding:20px; border-bottom:1px solid var(--mkv-border); display:flex; align-items:center; justify-content:space-between;">
                <h3 style="margin:0; padding:0; border:none;"><i class="hgi-stroke hgi-invoice-01"></i> <?php echo esc_html(mkv__('Lịch Sử Mua Hàng')); ?> (<?php echo count($order_history); ?> <?php echo esc_html(mkv__('đơn')); ?>)</h3>
                <a href="<?php echo admin_url('admin.php?page=mkv-pos'); ?>" class="button button-small button-primary"><i class="hgi-stroke hgi-shopping-cart-01"></i> <?php echo esc_html(mkv__('Bán hàng cho khách')); ?></a>
            </div>
            <table class="mkv-table">
                <thead>
                    <tr>
                        <th><?php echo esc_html(mkv__('Mã đơn hàng')); ?></th>
                        <th><?php echo esc_html(mkv__('Ngày mua')); ?></th>
                        <th style="width:80px;"><?php echo esc_html(mkv__('Số món')); ?></th>
                        <th style="width:110px;"><?php echo esc_html(mkv__('Trạng thái')); ?></th>
                        <th style="width:130px;"><?php echo esc_html(mkv__('Tổng tiền')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($order_history)): ?>
                        <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--mkv-text-muted);">
                            <?php echo esc_html(mkv__('Khách hàng này chưa có đơn hàng nào.')); ?>
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($order_history as $ord): 
                            $status_map = array(
                                'draft'     => array(mkv__('Nháp'), 'mkv-badge-yellow'),
                                'pending'   => array(mkv__('Chờ duyệt'), 'mkv-badge-yellow'),
                                'paid'      => array(mkv__('Đã thanh toán'), 'mkv-badge-blue'),
                                'shipping'  => array(mkv__('Đang giao'), 'mkv-badge-purple'),
                                'completed' => array(mkv__('Hoàn thành'), 'mkv-badge-green'),
                                'cancelled' => array(mkv__('Đã hủy'), 'mkv-badge-red'),
                            );
                            $st = $status_map[$ord->status] ?? array($ord->status, '');
                        ?>
                        <tr>
                            <td><strong><a href="?page=mkv-orders&id=<?php echo $ord->id; ?>"><?php echo esc_html($ord->order_code); ?></a></strong></td>
                            <td><?php echo esc_html(date('d/m/Y H:i', strtotime($ord->created_at))); ?></td>
                            <td><?php echo intval($ord->item_count); ?> <?php echo esc_html(mkv__('sản phẩm')); ?></td>
                            <td><span class="mkv-badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span></td>
                            <td><strong style="color:var(--mkv-primary);"><?php echo number_format($ord->total_amount, 0, ',', '.'); ?> ₫</strong></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>

<?php endif; ?>

<script>document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Khách hàng')); ?>');</script>
<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>