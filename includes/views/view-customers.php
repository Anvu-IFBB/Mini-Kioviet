<?php if (!defined('ABSPATH')) exit; ?>
<?php require_once MKV_DIR . 'includes/views/header-kiotviet.php'; ?>
<?php if ($action === 'list'): ?>

    <div class="mkv-page-header">
        <h1 class="mkv-page-title"><i class="hgi-stroke hgi-user-group"></i> <?php echo esc_html(mkv__('Quản Lý Khách Hàng')); ?></h1>
        <div class="mkv-page-actions">
            <a href="?page=mkv-customers&action=edit" class="mkv-btn mkv-btn-primary">
                <i class="hgi-stroke hgi-user-add-01"></i> <?php echo esc_html(mkv__('Thêm khách hàng')); ?>
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
        <div class="mkv-card-header mkv-card-header--search">
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

        <div class="mkv-card-body mkv-card-body--flush">
            <div class="mkv-table-wrap" style="border:none; box-shadow:none; border-radius:0; margin-bottom:0;">

                <table class="mkv-table" style="min-width: 950px;">
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
                        <i class="hgi-stroke hgi-user-block-01" style="font-size:40px; display:block; margin-bottom:10px;"></i>
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
                            <a href="?page=mkv-customers&action=edit&id=<?php echo intval($c->id); ?>" class="mkv-btn mkv-btn-sm mkv-btn-secondary">
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

    <div class="mkv-page-header" style="margin-bottom: 20px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="?page=mkv-customers" class="mkv-btn mkv-btn-secondary" title="<?php echo esc_attr(mkv__('Quay lại danh sách')); ?>">
                <i class="hgi-stroke hgi-arrow-left-01"></i> <?php echo esc_html(mkv__('Quay lại')); ?>
            </a>
            <h1 class="mkv-page-title" style="margin:0;">
                <i class="hgi-stroke hgi-<?php echo $customer ? 'user-check-01' : 'user-add'; ?>"></i>
                <?php echo $customer ? esc_html(mkv__('Hồ Sơ Khách Hàng:')) . ' ' . esc_html($customer->name) : esc_html(mkv__('Thêm Khách Hàng Mới')); ?>
            </h1>
        </div>
        <?php if ($customer): ?>
        <div class="mkv-page-actions">
            <a href="<?php echo admin_url('admin.php?page=mkv-pos&customer_id=' . $customer->id); ?>" class="mkv-btn mkv-btn-primary">
                <i class="hgi-stroke hgi-shopping-cart-01"></i> <?php echo esc_html(mkv__('Bán hàng cho khách')); ?>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($customer): ?>
    <!-- 4 Khối Thống Kê Tài Chính Của Khách Hàng -->
    <div class="mkv-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 20px;">
        <div class="mkv-stat-box mkv-border-green">
            <h3><?php echo esc_html(mkv__('Điểm tích lũy')); ?></h3>
            <p class="mkv-text-green"><?php echo number_format($customer->points); ?> <span style="font-size:14px; font-weight:500;">pts</span></p>
            <i class="hgi-stroke hgi-award-01 icon-bg"></i>
        </div>
        <div class="mkv-stat-box mkv-border-blue">
            <h3><?php echo esc_html(mkv__('Tổng chi tiêu')); ?></h3>
            <p class="mkv-text-blue"><?php echo number_format($customer->total_spent, 0, ',', '.'); ?> ₫</p>
            <i class="hgi-stroke hgi-money-bag-02 icon-bg"></i>
        </div>
        <div class="mkv-stat-box mkv-border-purple">
            <h3><?php echo esc_html(mkv__('Đơn đã mua')); ?></h3>
            <p style="color:var(--mkv-purple);"><?php echo count($order_history); ?> <span style="font-size:14px; font-weight:500;"><?php echo esc_html(mkv__('đơn')); ?></span></p>
            <i class="hgi-stroke hgi-invoice-01 icon-bg"></i>
        </div>
        <div class="mkv-stat-box <?php echo (float) $customer->total_debt > 0 ? 'mkv-border-red' : 'mkv-border-green'; ?>">
            <h3><?php echo esc_html(mkv__('Công nợ hiện tại')); ?></h3>
            <p class="<?php echo (float) $customer->total_debt > 0 ? 'mkv-text-red' : 'mkv-text-green'; ?>">
                <?php echo number_format((float) $customer->total_debt, 0, ',', '.'); ?> ₫
            </p>
            <i class="hgi-stroke hgi-time-02 icon-bg"></i>
        </div>
    </div>
    <?php endif; ?>

    <div class="mkv-customer-detail-grid" style="<?php echo !$customer ? 'grid-template-columns: 560px;' : ''; ?>">

        <!-- Form thông tin cá nhân -->
        <div class="mkv-card" style="margin-bottom:0;">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title">
                    <i class="hgi-stroke hgi-user-edit-01"></i> <?php echo esc_html(mkv__('Thông tin cá nhân')); ?>
                </h3>
            </div>
            <div class="mkv-card-body">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="mkv_save_customer">
                    <input type="hidden" name="customer_id" value="<?php echo intval($customer_id); ?>">
                    <?php wp_nonce_field('mkv_save_customer_nonce', '_wpnonce'); ?>

                    <div style="display:flex; flex-direction:column; gap:16px;">
                        <div class="mkv-form-group" style="margin-bottom:0;">
                            <label class="mkv-label" style="font-weight:600;"><?php echo esc_html(mkv__('Họ và tên')); ?> <span style="color:var(--mkv-red)">*</span></label>
                            <input name="c_name" type="text" class="mkv-input" value="<?php echo $customer ? esc_attr($customer->name) : ''; ?>" required placeholder="<?php echo esc_attr(mkv__('Nhập họ tên...')); ?>" style="height:38px;">
                        </div>
                        <div class="mkv-form-group" style="margin-bottom:0;">
                            <label class="mkv-label" style="font-weight:600;"><?php echo esc_html(mkv__('Số điện thoại')); ?></label>
                            <input name="c_phone" type="text" class="mkv-input" value="<?php echo $customer ? esc_attr($customer->phone) : ''; ?>" placeholder="09..." style="height:38px;">
                        </div>
                        <div class="mkv-form-group" style="margin-bottom:0;">
                            <label class="mkv-label" style="font-weight:600;"><?php echo esc_html(mkv__('Email')); ?></label>
                            <input name="c_email" type="email" class="mkv-input" value="<?php echo $customer ? esc_attr($customer->email) : ''; ?>" placeholder="email@domain.com" style="height:38px;">
                        </div>
                        <div class="mkv-form-group" style="margin-bottom:0;">
                            <label class="mkv-label" style="font-weight:600;"><?php echo esc_html(mkv__('Địa chỉ nhận hàng')); ?></label>
                            <textarea name="c_address" rows="3" class="mkv-input" style="height:auto; padding:10px 12px; resize:vertical; line-height:1.5;" placeholder="<?php echo esc_attr(mkv__('Số nhà, tên đường, phường/xã, quận/huyện...')); ?>"><?php echo $customer ? esc_textarea($customer->address) : ''; ?></textarea>
                        </div>
                    </div>

                    <div style="margin-top:20px;">
                        <button type="submit" class="mkv-btn mkv-btn-primary" style="width:100%; height:40px; justify-content:center; font-weight:600; border-radius:8px;">
                            <i class="hgi-stroke hgi-tick-02"></i> <?php echo $customer ? esc_html(mkv__('Cập Nhật Thông Tin')) : esc_html(mkv__('Tạo Khách Hàng')); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($customer): ?>
        <!-- Tab / Danh sách Lịch sử mua hàng -->
        <div class="mkv-card" style="padding:0; margin-bottom:0;">
            <div class="mkv-card-header">
                <h3 class="mkv-card-title">
                    <i class="hgi-stroke hgi-invoice-01"></i> <?php echo esc_html(mkv__('Lịch Sử Mua Hàng')); ?>
                </h3>
                <span class="mkv-badge mkv-badge-blue"><?php echo count($order_history); ?> <?php echo esc_html(mkv__('đơn hàng')); ?></span>
            </div>
            <div class="mkv-card-body" style="padding:0;">
                <div class="mkv-customer-orders-scroll">
                <table class="mkv-table" style="min-width:700px; margin:0;">
                    <thead>
                        <tr>
                            <th style="width:115px;"><?php echo esc_html(mkv__('Mã đơn')); ?></th>
                            <th style="width:95px;"><?php echo esc_html(mkv__('Ngày mua')); ?></th>
                            <th style="width:95px;"><?php echo esc_html(mkv__('Kênh bán')); ?></th>
                            <th style="width:70px; text-align:center;"><?php echo esc_html(mkv__('Số món')); ?></th>
                            <th style="width:95px;"><?php echo esc_html(mkv__('Trạng thái')); ?></th>
                            <th style="width:110px;"><?php echo esc_html(mkv__('Thanh toán')); ?></th>
                            <th style="width:105px; text-align:right;"><?php echo esc_html(mkv__('Tổng tiền')); ?></th>
                            <th style="width:85px; text-align:center;"><?php echo esc_html(mkv__('Hành động')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($order_history)): ?>
                            <tr><td colspan="8" style="text-align:center; padding:30px; color:var(--mkv-text-muted);">
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
                                    'returned'  => array(mkv__('Đã trả hàng'), 'mkv-badge-gray'),
                                );
                                $st = $status_map[$ord->status] ?? array($ord->status, '');
                                $channel_names = array('pos' => mkv__('Tại quầy'), 'online' => mkv__('Online'), 'social' => mkv__('Facebook / Zalo'), 'marketplace' => mkv__('Sàn TMĐT'));
                                $is_cod_pending = ($ord->payment_status ?? '') === 'cod_pending';
                                $unpaid_balance = max(0.0, (float) $ord->total_amount - (float) $ord->paid_amount);

                                if ($is_cod_pending) {
                                    $payment_due = (float) ($ord->cod_amount ?? 0) > 0 ? (float) $ord->cod_amount : $unpaid_balance;
                                } else {
                                    if ((float) ($ord->customer_debt_amount ?? 0) > 0) {
                                        $payment_due = (float) $ord->customer_debt_amount;
                                    } elseif ((float) ($ord->debt_amount ?? 0) > 0) {
                                        $payment_due = (float) $ord->debt_amount;
                                    } else {
                                        $payment_due = $unpaid_balance;
                                    }
                                }
                                if ($ord->status === 'cancelled') {
                                    $payment_due = 0.0;
                                }
                            ?>
                            <tr>
                                <td><strong><a href="?page=mkv-orders&id=<?php echo intval($ord->id); ?>"><?php echo esc_html($ord->order_code); ?></a></strong></td>
                                <td><?php echo esc_html(date('d/m/Y H:i', strtotime($ord->created_at))); ?></td>
                                <td><span class="mkv-badge mkv-badge-blue"><?php echo esc_html($channel_names[$ord->sales_channel ?? 'pos'] ?? mkv__('Tại quầy')); ?></span></td>
                                <td style="text-align:center;"><?php echo intval($ord->item_count); ?></td>
                                <td><span class="mkv-badge <?php echo esc_attr($st[1]); ?>"><?php echo esc_html($st[0]); ?></span></td>
                                <td>
                                    <?php if ($payment_due > 0 && $ord->status !== 'cancelled'): ?>
                                        <span class="mkv-badge <?php echo $is_cod_pending ? 'mkv-badge-purple' : 'mkv-badge-red'; ?>"><?php echo esc_html($is_cod_pending ? mkv__('COD') : mkv__('Còn nợ')); ?></span>
                                        <div style="font-size:12px; margin-top:3px; font-weight:600; color:var(--mkv-red);"><?php echo number_format($payment_due, 0, ',', '.'); ?> ₫</div>
                                    <?php else: ?>
                                        <span class="mkv-badge mkv-badge-green"><?php echo esc_html(mkv__('Đã thanh toán')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;"><strong style="color:var(--mkv-primary);"><?php echo number_format($ord->total_amount, 0, ',', '.'); ?> ₫</strong></td>
                                <td style="text-align:center;">
                                    <?php if ($payment_due > 0 && $ord->status !== 'cancelled'): ?>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                                        <input type="hidden" name="action" value="mkv_collect_order_debt">
                                        <input type="hidden" name="order_id" value="<?php echo (int) $ord->id; ?>">
                                        <?php wp_nonce_field('mkv_collect_order_debt_' . $ord->id); ?>
                                        <input type="hidden" name="amount" value="<?php echo esc_attr((string) (int) round($payment_due)); ?>">
                                        <input type="hidden" name="method" value="cash">
                                        <button type="submit" class="mkv-action-btn mkv-action-success" style="height:26px; font-size:11.5px; padding:0 8px;" title="<?php echo esc_attr($is_cod_pending ? mkv__('Xác nhận đối soát COD') : mkv__('Thu tiền nợ từ khách')); ?>">
                                            <i class="hgi-stroke hgi-money-receive-02"></i> <?php echo esc_html($is_cod_pending ? mkv__('Đối soát') : mkv__('Thu đủ')); ?>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span style="color:var(--mkv-text-muted);">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

<?php endif; ?>

<script>document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Khách hàng')); ?>');</script>
<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>