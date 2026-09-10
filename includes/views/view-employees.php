<?php
if (!defined('ABSPATH')) exit;
require_once MKV_DIR . 'includes/views/header-kiotviet.php';
?>

<div class="mkv-page-header">
    <h1 class="mkv-page-title">
        <i class="hgi-stroke hgi-user-id-verification"></i> <?php echo esc_html(mkv__('Quản lý Nhân Viên')); ?>
    </h1>
    <div class="mkv-page-actions">
        <?php if (!$my_today_record || !$my_today_record->check_in_time): ?>
            <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
                <input type="hidden" name="action" value="mkv_employee_checkin">
                <input type="hidden" name="check_action" value="checkin">
                <input type="hidden" name="redirect_tab" value="<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('mkv_checkin_action'); ?>
                <button type="submit" class="mkv-btn mkv-btn-success">
                    <i class="hgi-stroke hgi-time-02"></i> <?php echo esc_html(mkv__('Check-in Ngay')); ?>
                </button>
            </form>
        <?php elseif (!$my_today_record->check_out_time): ?>
            <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
                <input type="hidden" name="action" value="mkv_employee_checkin">
                <input type="hidden" name="check_action" value="checkout">
                <input type="hidden" name="redirect_tab" value="<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('mkv_checkin_action'); ?>
                <button type="submit" class="mkv-btn mkv-btn-danger">
                    <i class="hgi-stroke hgi-logout-02"></i> <?php echo esc_html(mkv__('Check-out')); ?>
                </button>
            </form>
        <?php else: ?>
            <span class="mkv-badge mkv-badge-green" style="padding:7px 12px; font-size:12px;">
                <i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Đã hoàn thành ca làm hôm nay')); ?>
            </span>
        <?php endif; ?>
        <?php if (current_user_can('administrator') || current_user_can('mkv_manager')): ?>
            <button class="mkv-btn mkv-btn-primary" onclick="document.getElementById('mkv-add-employee-modal').style.display='flex'">
                <i class="hgi-stroke hgi-user-add-01"></i> <?php echo esc_html(mkv__('Thêm nhân viên mới')); ?>
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success'): ?>
<div class="mkv-alert mkv-alert-success"><i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Thao tác chấm công thành công!')); ?></div>
<?php endif; ?>
<?php if (isset($_GET['message']) && $_GET['message'] === 'created'): ?>
<div class="mkv-alert mkv-alert-success"><i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Thêm nhân viên thành công!')); ?></div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'exists'): ?>
<div class="mkv-alert mkv-alert-error" style="background:#fef2f2; color:#ef4444; border-color:#fecaca;"><i class="hgi-stroke hgi-alert-02"></i> <?php echo esc_html(mkv__('Tên đăng nhập hoặc Email đã tồn tại!')); ?></div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'failed'): ?>
<div class="mkv-alert mkv-alert-error" style="background:#fef2f2; color:#ef4444; border-color:#fecaca;"><i class="hgi-stroke hgi-alert-02"></i> <?php echo esc_html(mkv__('Có lỗi xảy ra khi tạo nhân viên.')); ?></div>
<?php endif; ?>
<?php if (isset($_GET['message']) && $_GET['message'] === 'updated'): ?>
<div class="mkv-alert mkv-alert-success"><i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Cập nhật nhân viên thành công!')); ?></div>
<?php endif; ?>
<?php if (isset($_GET['message']) && $_GET['message'] === 'deleted'): ?>
<div class="mkv-alert mkv-alert-success"><i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Đã xóa nhân viên thành công.')); ?></div>
<?php endif; ?>

<!-- Tabs -->
<div class="mkv-tabs-bar">
    <a href="?page=mkv-employees&tab=list"
       class="mkv-tab-link <?php echo $current_tab === 'list' ? 'active' : ''; ?>">
        <i class="hgi-stroke hgi-user-list"></i> <?php echo esc_html(mkv__('Danh sách nhân viên')); ?>
    </a>
    <a href="?page=mkv-employees&tab=timesheets"
       class="mkv-tab-link <?php echo $current_tab === 'timesheets' ? 'active' : ''; ?>">
        <i class="hgi-stroke hgi-clock-01"></i> <?php echo esc_html(mkv__('Bảng chấm công')); ?>
    </a>
</div>

<!-- Tab: Danh sách nhân viên -->
<?php if ($current_tab === 'list'): ?>

<!-- KPI Status Summary Cards -->
<div class="mkv-stats-grid" style="margin-bottom:16px;">
    <div class="mkv-stat-card blue">
        <h3><?php echo esc_html(mkv__('Tổng nhân sự')); ?></h3>
        <p><?php echo intval($total_staff ?? count($users)); ?></p>
        <i class="hgi-stroke hgi-user-group stat-icon"></i>
    </div>
    <div class="mkv-stat-card green">
        <h3><?php echo esc_html(mkv__('Đang trong ca')); ?></h3>
        <p><?php echo intval($count_working ?? 0); ?></p>
        <i class="hgi-stroke hgi-time-02 stat-icon"></i>
    </div>
    <div class="mkv-stat-card yellow">
        <h3><?php echo esc_html(mkv__('Chưa vào ca')); ?></h3>
        <p><?php echo intval($count_not_checked_in ?? 0); ?></p>
        <i class="hgi-stroke hgi-clock-01 stat-icon"></i>
    </div>
    <div class="mkv-stat-card purple">
        <h3><?php echo esc_html(mkv__('Đã tan ca')); ?></h3>
        <p><?php echo intval($count_completed ?? 0); ?></p>
        <i class="hgi-stroke hgi-checkmark-circle-02 stat-icon"></i>
    </div>
</div>

<div class="mkv-table-wrap">
    <table class="mkv-table">
        <thead>
            <tr>
                <th><?php echo esc_html(mkv__('Tên hiển thị')); ?></th>
                <th><?php echo esc_html(mkv__('Email')); ?></th>
                <th><?php echo esc_html(mkv__('Chức vụ')); ?></th>
                <th><?php echo esc_html(mkv__('Trạng thái')); ?></th>
                <th style="width:220px; text-align:right;"><?php echo esc_html(mkv__('Hành động')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $u): ?>
                    <?php
                    $role_names = [
                        'administrator' => mkv__('Admin (Quản trị)'),
                        'mkv_manager'   => mkv__('Quản lý / Cửa hàng trưởng'),
                        'mkv_sales'     => mkv__('Nhân viên Bán hàng'),
                        'mkv_warehouse' => mkv__('Thủ kho'),
                    ];
                    $u_roles = [];
                    foreach ($u->roles as $role) {
                        if (isset($role_names[$role])) $u_roles[] = $role_names[$role];
                    }
                    $emp_status = get_user_meta($u->ID, 'mkv_employee_status', true) ?: 'active';
                    $today_rec  = $today_attendance[$u->ID] ?? null;
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($u->display_name); ?></strong></td>
                        <td style="color:#6b7280;"><?php echo esc_html($u->user_email); ?></td>
                        <td><?php echo !empty($u_roles) ? implode(', ', $u_roles) : '<span style="color:#9ca3af">'.esc_html(mkv__('Khác')).'</span>'; ?></td>
                        <td>
                            <?php if ($emp_status === 'inactive'): ?>
                                <div class="mkv-emp-status-cell">
                                    <span class="mkv-badge mkv-badge-red">
                                        <i class="hgi-stroke hgi-cancel-circle"></i> <?php echo esc_html(mkv__('Đã nghỉ việc')); ?>
                                    </span>
                                    <span class="mkv-emp-status-meta meta-muted">
                                        <?php echo esc_html(mkv__('Tài khoản đã khóa')); ?>
                                    </span>
                                </div>
                            <?php elseif ($today_rec && $today_rec->check_in_time && !$today_rec->check_out_time): ?>
                                <div class="mkv-emp-status-cell">
                                    <span class="mkv-badge mkv-badge-green">
                                        <span class="mkv-pulse-dot"></span> <?php echo esc_html(mkv__('Đang trong ca')); ?>
                                    </span>
                                    <span class="mkv-emp-status-meta meta-green">
                                        <i class="hgi-stroke hgi-time-02"></i> <?php echo esc_html(mkv__('Vào:')); ?> <?php echo date('H:i', strtotime($today_rec->check_in_time)); ?>
                                    </span>
                                </div>
                            <?php elseif ($today_rec && $today_rec->check_in_time && $today_rec->check_out_time): ?>
                                <div class="mkv-emp-status-cell">
                                    <span class="mkv-badge mkv-badge-blue">
                                        <i class="hgi-stroke hgi-checkmark-circle-02"></i> <?php echo esc_html(mkv__('Đã tan ca')); ?>
                                    </span>
                                    <span class="mkv-emp-status-meta meta-blue">
                                        <?php
                                        $diff = strtotime($today_rec->check_out_time) - strtotime($today_rec->check_in_time);
                                        $h = floor($diff / 3600);
                                        $m = floor(($diff / 60) % 60);
                                        $dur = "{$h}h" . ($m > 0 ? " {$m}m" : "");
                                        ?>
                                        <i class="hgi-stroke hgi-logout-02"></i> <?php echo esc_html(mkv__('Ra:')); ?> <?php echo date('H:i', strtotime($today_rec->check_out_time)); ?> (<?php echo $dur; ?>)
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="mkv-emp-status-cell">
                                    <span class="mkv-badge mkv-badge-gray">
                                        <i class="hgi-stroke hgi-clock-01"></i> <?php echo esc_html(mkv__('Chưa vào ca')); ?>
                                    </span>
                                    <span class="mkv-emp-status-meta meta-muted">
                                        <?php echo esc_html(mkv__('Chưa check-in')); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end; flex-wrap:nowrap;">
                                <?php if ($emp_status !== 'inactive' && (current_user_can('administrator') || current_user_can('mkv_manager'))): ?>
                                    <?php if (!$today_rec || !$today_rec->check_in_time): ?>
                                        <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" style="margin:0;">
                                            <input type="hidden" name="action" value="mkv_employee_checkin">
                                            <input type="hidden" name="check_action" value="checkin">
                                            <input type="hidden" name="target_user_id" value="<?php echo $u->ID; ?>">
                                            <input type="hidden" name="redirect_tab" value="list">
                                            <?php wp_nonce_field('mkv_checkin_action'); ?>
                                            <button type="submit" class="mkv-btn mkv-btn-sm" style="background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; padding:4px 8px; font-size:12px;" title="<?php echo esc_attr(mkv__('Điểm danh vào ca')); ?>">
                                                <i class="hgi-stroke hgi-time-02"></i> <?php echo esc_html(mkv__('Vào ca')); ?>
                                            </button>
                                        </form>
                                    <?php elseif (!$today_rec->check_out_time): ?>
                                        <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" style="margin:0;">
                                            <input type="hidden" name="action" value="mkv_employee_checkin">
                                            <input type="hidden" name="check_action" value="checkout">
                                            <input type="hidden" name="target_user_id" value="<?php echo $u->ID; ?>">
                                            <input type="hidden" name="redirect_tab" value="list">
                                            <?php wp_nonce_field('mkv_checkin_action'); ?>
                                            <button type="submit" class="mkv-btn mkv-btn-sm" style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; padding:4px 8px; font-size:12px;" title="<?php echo esc_attr(mkv__('Điểm danh tan ca')); ?>">
                                                <i class="hgi-stroke hgi-logout-02"></i> <?php echo esc_html(mkv__('Tan ca')); ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($u->ID != get_current_user_id()): ?>
                                <button type="button" class="mkv-btn mkv-btn-sm mkv-btn-secondary" aria-label="<?php echo esc_attr(mkv__('Sửa nhân viên')); ?>" onclick="editEmployee(<?php echo $u->ID; ?>, '<?php echo esc_js($u->display_name); ?>', '<?php echo esc_js($u->user_email); ?>', '<?php echo isset($u->roles[0]) ? esc_js($u->roles[0]) : ''; ?>', '<?php echo esc_js($emp_status); ?>');">
                                    <i class="hgi-stroke hgi-edit-01"></i> <?php echo esc_html(mkv__('Sửa')); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=mkv_delete_employee&user_id='.$u->ID), 'mkv_delete_employee_action'); ?>" class="mkv-btn mkv-btn-sm mkv-btn-danger" aria-label="<?php echo esc_attr(mkv__('Xóa nhân viên')); ?>" onclick="return confirm('<?php echo esc_js(mkv__('Bạn có chắc muốn xóa nhân viên này? Toàn bộ phiếu thu, hóa đơn của họ sẽ được tự động gán cho bạn.')); ?>');">
                                    <i class="hgi-stroke hgi-delete-02"></i>
                                </a>
                                <?php else: ?>
                                <span style="color:#9ca3af; font-size:12px; white-space:nowrap;"><?php echo esc_html(mkv__('Tài khoản của bạn')); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5"><div class="mkv-empty"><i class="hgi-stroke hgi-user-group"></i><p><?php echo esc_html(mkv__('Chưa có nhân viên nào.')); ?></p></div></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Tab: Bảng chấm công -->
<?php if ($current_tab === 'timesheets'): ?>

<div class="mkv-filter-bar">
    <form method="GET" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; width:100%;">
        <input type="hidden" name="page" value="mkv-employees">
        <input type="hidden" name="tab" value="timesheets">
        <div class="mkv-form-group">
            <label class="mkv-label"><?php echo esc_html(mkv__('Từ ngày')); ?></label>
            <input type="date" name="start_date" class="mkv-input" value="<?php echo esc_attr($start_date); ?>" style="width:150px;">
        </div>
        <div class="mkv-form-group">
            <label class="mkv-label"><?php echo esc_html(mkv__('Đến ngày')); ?></label>
            <input type="date" name="end_date" class="mkv-input" value="<?php echo esc_attr($end_date); ?>" style="width:150px;">
        </div>
        <div class="mkv-form-group" style="margin-bottom:0;">
            <button type="submit" class="mkv-btn mkv-btn-secondary"><i class="hgi-stroke hgi-filter"></i> <?php echo esc_html(mkv__('Lọc')); ?></button>
        </div>
    </form>
</div>

<div class="mkv-table-wrap">
    <table class="mkv-table">
        <thead>
            <tr>
                <th><?php echo esc_html(mkv__('Ngày làm việc')); ?></th>
                <th><?php echo esc_html(mkv__('Nhân viên')); ?></th>
                <th><?php echo esc_html(mkv__('Giờ vào (Check-in)')); ?></th>
                <th><?php echo esc_html(mkv__('Giờ ra (Check-out)')); ?></th>
                <th><?php echo esc_html(mkv__('Tổng giờ làm')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($timesheets)): ?>
                <?php foreach ($timesheets as $t): ?>
                    <?php
                    $hours = '--';
                    if ($t->check_in_time && $t->check_out_time) {
                        $diff  = strtotime($t->check_out_time) - strtotime($t->check_in_time);
                        $h     = floor($diff / 3600);
                        $m     = floor(($diff / 60) % 60);
                        $hours = "{$h}h {$m}m";
                    }
                    ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($t->work_date)); ?></td>
                        <td><strong><?php echo esc_html($t->display_name); ?></strong></td>
                        <td style="color:#389e0d; font-weight:500;">
                            <?php echo $t->check_in_time ? date('H:i:s', strtotime($t->check_in_time)) : '<span style="color:#9ca3af">—</span>'; ?>
                        </td>
                        <td style="color:var(--mkv-red); font-weight:500;">
                            <?php echo $t->check_out_time ? date('H:i:s', strtotime($t->check_out_time)) : '<span style="color:#9ca3af">—</span>'; ?>
                        </td>
                        <td><span class="mkv-badge mkv-badge-blue"><?php echo $hours; ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5"><div class="mkv-empty"><i class="hgi-stroke hgi-clock-01"></i><p><?php echo esc_html(mkv__('Chưa có dữ liệu chấm công.')); ?></p></div></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Modal Thêm nhân viên -->
<div id="mkv-add-employee-modal" class="mkv-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="mkv-add-employee-title" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999; align-items:center; justify-content:center;">
    <div class="mkv-modal" style="background:#fff; border-radius:12px; width:400px; padding:24px; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
        <div class="mkv-modal-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 id="mkv-add-employee-title" class="mkv-modal-title" style="margin:0; font-size:18px;"><i class="hgi-stroke hgi-user-add-01"></i> <?php echo esc_html(mkv__('Thêm nhân viên mới')); ?></h3>
            <button type="button" class="mkv-modal-close" aria-label="<?php echo esc_attr(mkv__('Đóng')); ?>" onclick="document.getElementById('mkv-add-employee-modal').style.display='none'" style="background:none; border:none; font-size:24px; cursor:pointer; color:#6b7280;">&times;</button>
        </div>
        
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
            <input type="hidden" name="action" value="mkv_add_employee">
            <?php wp_nonce_field('mkv_add_employee_action'); ?>
            
            <div class="mkv-form-group" style="margin-bottom:12px;">
                <label class="mkv-label"><?php echo esc_html(mkv__('Tên hiển thị')); ?> <span style="color:red">*</span></label>
                <input type="text" name="display_name" class="mkv-input" required placeholder="<?php echo esc_attr(mkv__('Ví dụ: Nguyễn Văn A')); ?>">
            </div>
            
            <div class="mkv-form-group" style="margin-bottom:12px;">
                <label class="mkv-label"><?php echo esc_html(mkv__('Tên đăng nhập (Username)')); ?> <span style="color:red">*</span></label>
                <input type="text" name="username" class="mkv-input" required placeholder="nguyenvana">
            </div>
            
            <div class="mkv-form-group" style="margin-bottom:12px;">
                <label class="mkv-label"><?php echo esc_html(mkv__('Email')); ?> <span style="color:red">*</span></label>
                <input type="email" name="email" class="mkv-input" required placeholder="nv.a@example.com">
            </div>
            
            <div class="mkv-form-group" style="margin-bottom:12px;">
                <label class="mkv-label"><?php echo esc_html(mkv__('Mật khẩu')); ?> <span style="color:red">*</span></label>
                <input type="password" name="password" class="mkv-input" required placeholder="********">
            </div>
            
            <div class="mkv-form-group" style="margin-bottom:24px;">
                <label class="mkv-label"><?php echo esc_html(mkv__('Chức vụ (Role)')); ?> <span style="color:red">*</span></label>
                <select name="role" class="mkv-input" required>
                    <option value="mkv_sales"><?php echo esc_html(mkv__('Nhân viên Bán hàng (Xem POS, Đơn, Khách)')); ?></option>
                    <option value="mkv_warehouse"><?php echo esc_html(mkv__('Thủ kho (Xem Kho, Sản phẩm, Mua hàng)')); ?></option>
                    <?php if (current_user_can('administrator')): ?>
                    <option value="mkv_manager"><?php echo esc_html(mkv__('Cửa hàng trưởng (Quản lý chung)')); ?></option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="mkv-btn mkv-btn-secondary" onclick="document.getElementById('mkv-add-employee-modal').style.display='none'"><?php echo esc_html(mkv__('Hủy')); ?></button>
                <button type="submit" class="mkv-btn mkv-btn-primary"><?php echo esc_html(mkv__('Tạo nhân viên')); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sửa nhân viên -->
<div id="mkv-edit-employee-modal" class="mkv-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="mkv-edit-employee-title" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999; align-items:center; justify-content:center;">
    <div class="mkv-modal" style="background:#fff; border-radius:12px; width:400px; padding:24px; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
        <div class="mkv-modal-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 id="mkv-edit-employee-title" class="mkv-modal-title" style="margin:0; font-size:18px;"><i class="hgi-stroke hgi-user-edit-01"></i> <?php echo esc_html(mkv__('Sửa thông tin nhân viên')); ?></h3>
            <button type="button" class="mkv-modal-close" aria-label="<?php echo esc_attr(mkv__('Đóng')); ?>" onclick="document.getElementById('mkv-edit-employee-modal').style.display='none'" style="background:none; border:none; font-size:24px; cursor:pointer; color:#6b7280;">&times;</button>
        </div>
        
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
            <input type="hidden" name="action" value="mkv_edit_employee">
            <input type="hidden" name="user_id" id="edit_user_id" value="">
            <?php wp_nonce_field('mkv_edit_employee_action'); ?>
            
            <div class="mkv-form-group" style="margin-bottom:12px;">
                <label class="mkv-label"><?php echo esc_html(mkv__('Tên hiển thị')); ?> <span style="color:red">*</span></label>
                <input type="text" name="display_name" id="edit_display_name" class="mkv-input" required>
            </div>
            
            <div class="mkv-form-group" style="margin-bottom:12px;">
                <label class="mkv-label"><?php echo esc_html(mkv__('Email')); ?> <span style="color:red">*</span></label>
                <input type="email" name="email" id="edit_email" class="mkv-input" required>
            </div>
            
            <div class="mkv-form-group" style="margin-bottom:12px;">
                <label class="mkv-label"><?php echo esc_html(mkv__('Mật khẩu mới (Để trống nếu không đổi)')); ?></label>
                <input type="password" name="password" class="mkv-input" placeholder="********">
            </div>
            
            <div class="mkv-form-group" style="margin-bottom:16px;">
                <label class="mkv-label"><?php echo esc_html(mkv__('Chức vụ (Role)')); ?> <span style="color:red">*</span></label>
                <select name="role" id="edit_role" class="mkv-input" required>
                    <option value="mkv_sales"><?php echo esc_html(mkv__('Nhân viên Bán hàng (Xem POS, Đơn, Khách)')); ?></option>
                    <option value="mkv_warehouse"><?php echo esc_html(mkv__('Thủ kho (Xem Kho, Sản phẩm, Mua hàng)')); ?></option>
                    <?php if (current_user_can('administrator')): ?>
                    <option value="mkv_manager"><?php echo esc_html(mkv__('Cửa hàng trưởng (Quản lý chung)')); ?></option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mkv-form-group" style="margin-bottom:24px;">
                <label class="mkv-label"><?php echo esc_html(mkv__('Trạng thái nhân sự')); ?> <span style="color:red">*</span></label>
                <select name="employee_status" id="edit_employee_status" class="mkv-input" required>
                    <option value="active"><?php echo esc_html(mkv__('Đang làm việc (Hoạt động)')); ?></option>
                    <option value="inactive"><?php echo esc_html(mkv__('Đã nghỉ việc / Tạm khóa tài khoản')); ?></option>
                </select>
                <span style="font-size:11.5px; color:#64748b; margin-top:4px; display:block;">
                    <?php echo esc_html(mkv__('Khóa tài khoản sẽ chặn nhân viên đăng nhập POS/Quản trị nhưng bảo toàn lịch sử hóa đơn.')); ?>
                </span>
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="mkv-btn mkv-btn-secondary" onclick="document.getElementById('mkv-edit-employee-modal').style.display='none'"><?php echo esc_html(mkv__('Hủy')); ?></button>
                <button type="submit" class="mkv-btn mkv-btn-primary"><?php echo esc_html(mkv__('Lưu thay đổi')); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function editEmployee(id, name, email, role, status) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_display_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    if (document.getElementById('edit_employee_status')) {
        document.getElementById('edit_employee_status').value = status || 'active';
    }
    document.getElementById('mkv-edit-employee-modal').style.display = 'flex';
}
document.getElementById('mkv-topbar-title') && (document.getElementById('mkv-topbar-title').textContent = '<?php echo esc_js(mkv__('Nhân viên')); ?>');
</script>

<?php require_once MKV_DIR . 'includes/views/footer-kiotviet.php'; ?>
