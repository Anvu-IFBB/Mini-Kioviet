# Mini KiotViet (MKV) — Hệ Thống Quản Lý Bán Hàng & ERP Bán Lẻ Trên WordPress

<p align="center">
  <img src="https://img.shields.io/badge/Version-3.0.0-blue.svg" alt="Version 3.0.0">
  <img src="https://img.shields.io/badge/WordPress-%3E%3D%206.0-21759B.svg" alt="WordPress 6.0+">
  <img src="https://img.shields.io/badge/PHP-%3E%3D%207.4%20%7C%208.x-777BB4.svg" alt="PHP 7.4+">
  <img src="https://img.shields.io/badge/License-GPL--2.0%2B-green.svg" alt="License GPL-2.0+">
  <img src="https://img.shields.io/badge/Icons-Hugeicons%20Stroke-orange.svg" alt="Hugeicons">
  <img src="https://img.shields.io/badge/AI-Google%20Gemini-4285F4.svg" alt="Gemini AI">
</p>

---

## 🌟 Tổng Quan Dự Án

**Mini KiotViet (MKV)** là giải pháp phần mềm quản lý bán hàng và hoạch định nguồn lực doanh nghiệp bán lẻ (**Retail ERP**) toàn diện, được đóng gói dưới dạng WordPress Plugin cao cấp. Hệ thống tái hiện trung thực trải nghiệm quản trị trực quan, hiện đại và tốc độ của nền tảng **KiotViet**, đồng thời tích hợp sâu với cơ sở dữ liệu WordPress và trợ lý trí tuệ nhân tạo **Google Gemini AI**.

Dự án được kiến trúc theo mô hình **MVC (Model - View - Controller)** chuẩn mực, tối ưu hóa cho các cửa hàng bán lẻ, chuỗi chi nhánh, siêu thị mini, shop thời trang và thương mại đa kênh.

---

## 🚀 Các Tính Năng Nổi Bật (Core Features)

### 1. Quầy Bán Hàng Thu Ngân (POS - Point of Sale)
- **Giao diện bán hàng siêu tốc**: Tối ưu thao tác một chạm trên màn hình cảm ứng, chuột và phím tắt.
- **Tìm kiếm thông minh**: Quét mã vạch Barcode trực tiếp từ máy quét hoặc gõ tìm kiếm theo tên, mã SKU.
- **Đa phương thức thanh toán**: Tiền mặt, chuyển khoản ngân hàng (tích hợp mã QR VietQR), quẹt thẻ, ghi nợ khách hàng.
- **Hóa đơn nhiệt chuyên nghiệp**: Tự động tạo mã đơn, định dạng in hóa đơn khổ K80/K58 kèm mã vạch đơn hàng.

### 2. Quản Lý Đơn Hàng & Giao Vận (Orders & Delivery)
- **Quy trình đơn hàng khép kín**: Theo dõi trạng thái (Chờ xử lý, Đang giao, Đã hoàn thành, Đã hủy).
- **Quản lý thu hộ (COD)**: Hạch toán độc lập giữa tiền hàng và tiền COD của đơn vị vận chuyển.
- **Hệ thống Tracking đơn hàng**: Cho phép khách hàng tra cứu tiến độ vận chuyển trực tiếp trên trang chủ/frontend qua mã đơn hoặc số điện thoại.

### 3. Quản Lý Sản Phẩm & Danh Mục (Products Catalog)
- **Phân loại đa tầng**: Danh mục, thương hiệu, nhóm hàng hóa.
- **Quản lý biến thể & ĐVT**: Màu sắc, kích thước, đơn vị tính (Cái, Hộp, Thùng,...).
- **Tự động sinh Barcode**: Tích hợp thư viện JsBarcode chuẩn EAN-13 / Code128.
- **Giá vốn & Giá bán**: Theo dõi giá nhập lần cuối, hỗ trợ tính toán biên lợi nhuận gộp chính xác.
- **Cảnh báo tồn kho an toàn**: Tự động thông báo khi lượng tồn xuống dưới ngưỡng tối thiểu (*Min Stock Alert*).

### 4. Quản Lý Đa Kho & Chi Nhánh (Multi-Warehouse Inventory)
- **Mô hình chuỗi kho bãi**: Quản lý không giới hạn số lượng kho trung tâm và cửa hàng chi nhánh.
- **Chuyển kho an toàn (Stock Transfer)**: Luân chuyển hàng hóa giữa các kho sử dụng **Database Transactions**, đảm bảo tuyệt đối không thất thoát dữ liệu.
- **Kiểm kho (Stocktake)**: Cân bằng tồn kho thực tế và sổ sách, tự động ghi nhận lượng chênh lệch thừa/thiếu.
- **Thẻ kho (Inventory Audit Trail)**: Nhật ký ghi nhận chi tiết mọi biến động nhập/xuất/bán hàng theo thời gian thực.

### 5. Quản Lý Nhập Hàng & Công Nợ NCC (Purchases & Accounts Payable)
- **Lập phiếu nhập hàng**: Tính toán đơn giá nhập, tự động cộng tồn kho tức thì vào kho nhận hàng.
- **Chuẩn hóa nghiệp vụ ERP**: Nhập kho tăng tồn vật lý ngay lập tức; việc thanh toán tiền sau này chỉ quyết toán công nợ, không làm nhân đôi tồn kho.
- **Modal chi tiết phiếu nhập (PO Detail Modal)**: Hiển thị 4 thẻ KPI tài chính, bảng sản phẩm nhập kèm tồn kho đối chiếu tại chỗ, lịch sử chi tiền và lịch sử thẻ kho.
- **In phiếu nhập kho**: Tạo mẫu in chứng từ nhập kho chuyên nghiệp để ký nhận giữa Thủ kho và Nhà cung cấp.

### 6. Sổ Quỹ & Quản Lý Dòng Tiền (Cashbook & Treasury)
- **Theo dõi Thu / Chi**: Hạch toán rõ ràng dòng tiền mặt và tiền gửi ngân hàng.
- **Phân bổ công nợ FIFO**: Khi chi tiền trả nợ NCC, hệ thống tự động phân bổ giảm nợ cho các phiếu nhập cũ nhất theo thứ tự thời gian.
- **Thu nợ khách hàng**: Tự động khấu trừ nợ của khách khi lập Phiếu Thu.
- **Báo cáo tồn quỹ**: Thống kê tồn đầu kỳ, tổng thu, tổng chi và tồn quỹ cuối kỳ theo khoảng thời gian tùy chọn.

### 7. Quản Lý Khách Hàng & Nhà Cung Cấp (Partners & CRM)
- **Hồ sơ đối tác toàn diện**: Tên, số điện thoại, địa chỉ, mã số thuế, nhóm khách.
- **Theo dõi lịch sử giao dịch**: Lịch sử mua hàng, tích lũy điểm thưởng và công nợ chi tiết.

### 8. Báo Cáo & Thống Kê Trực Quan (Analytics & Reports)
- **Biểu đồ doanh thu tương tác**: So sánh doanh số theo ngày, tuần, tháng, năm.
- **Phân tích tài chính**: Doanh thu thuần, giá vốn hàng bán (COGS), lợi nhuận gộp.
- **Xếp hạng bán chạy**: Top sản phẩm mang lại doanh thu cao nhất.

### 9. Quản Lý Nhân Viên & Phân Quyền (HR & RBAC)
- **Hệ thống phân quyền Role-Based**: Định nghĩa các quyền hạn chuyên biệt (`mkv_manage_pos`, `mkv_manage_inventory`, `mkv_manage_purchases`, `mkv_manage_cashbook`,...).
- **Chấm công điện tử (Timesheet)**: Nhân viên Check-in / Check-out ca làm việc trực tiếp trên Dashboard.

### 10. Trợ Lý Trí Tuệ Nhân Tạo (Gemini AI Assistant)
- **Tích hợp Gemini 1.5/2.0**: Trợ lý phân tích kinh doanh thông minh hiển thị dưới dạng Drawer tương tác trượt từ cạnh màn hình.
- **Tư vấn dựa trên dữ liệu thực tế**: Trả lời báo cáo doanh thu hôm nay, phát hiện hàng tồn đọng, dự báo xu hướng mua sắm của khách hàng.

---

## 🏗 Kiến Trúc Kỹ Thuật (Architecture & Tech Stack)

```
mini-kiotviet/
├── assets/
│   ├── css/
│   │   ├── admin-layout.css         # Hệ thống layout, theme variables & components
│   │   ├── admin-dashboard.css      # CSS trang tổng quan & widget
│   │   ├── admin-pos.css            # CSS màn hình bán hàng thu ngân
│   │   ├── admin-wp-overrides.css   # Tùy biến thanh quản trị WP
│   │   └── admin-ai-assistant.css   # Giao diện Drawer trợ lý AI
│   └── js/
│       ├── admin-global.js          # Core utilities, format tiền tệ, toast thông báo
│       ├── admin-pos.js             # Logic giỏ hàng, barcode, thanh toán quầy
│       ├── dashboard-chart.js       # Biểu đồ doanh thu Chart.js
│       ├── admin-ai-assistant.js    # Logic chat & streaming với Gemini API
│       └── notifications-poll.js    # Realtime notification polling
├── includes/
│   ├── admin/
│   │   └── class-settings.php       # Cài đặt hệ thống, API keys, cấu hình shop
│   ├── controllers/                 # 13 Controllers xử lý luồng nghiệp vụ
│   │   ├── class-dashboard.php
│   │   ├── class-products.php
│   │   ├── class-inventory.php
│   │   ├── class-orders.php
│   │   ├── class-purchases.php
│   │   ├── class-cashbook.php
│   │   ├── class-customers.php
│   │   ├── class-employees.php
│   │   ├── class-reports.php
│   │   ├── class-notifications.php
│   │   ├── class-ai-controller.php
│   │   ├── class-frontend.php
│   │   └── class-webhook.php
│   ├── models/                      # 4 Models dữ liệu & dịch vụ lõi
│   │   ├── class-db-schema.php      # Quản lý cấu trúc bảng CSDL & auto-migration
│   │   ├── class-analytics-service.php
│   │   ├── class-shipping-service.php
│   │   └── class-ai-service.php
│   ├── views/                       # 18 Templates giao diện
│   └── mkv-i18n.php                 # Bản địa hóa ngôn ngữ Tiếng Việt / Tiếng Anh
├── mini-kiotviet.php                # Entry point khởi tạo plugin & lifecycle
├── .gitignore                       # Cấu hình bảo mật mã nguồn
└── README.md                        # Tài liệu hướng dẫn kỹ thuật
```

### Tiêu Chuẩn Kỹ Thuật:
- **Ngôn ngữ**: PHP 7.4+ / PHP 8.x, Vanilla JavaScript (ES6+), jQuery.
- **Giao diện**: Thuần CSS tối ưu hóa theo phong cách KiotViet, Google Fonts Inter, 100% **Hugeicons Stroke**.
- **Cơ sở dữ liệu**: MySQL / MariaDB thông qua `$wpdb` với cơ chế tự động nâng cấp `dbDelta()`.
- **Bảo mật**:
  - Mã hóa token bảo vệ CSRF (`wp_nonce_field`, `check_admin_referer`, `check_ajax_referer`).
  - Kiểm tra thẩm quyền nghiêm ngặt trên từng hành động (`current_user_can`).
  - Sử dụng Prepared Statements (`$wpdb->prepare`) chống tấn công SQL Injection.
  - Xử lý phân tách giao dịch an toàn với `START TRANSACTION`, `COMMIT`, `ROLLBACK`.

---

## ⚙️ Yêu Cầu Hệ Thống (Requirements)

| Thành phần | Yêu cầu tối thiểu | Khuyến nghị |
| :--- | :--- | :--- |
| **PHP** | 7.4 | 8.1 hoặc 8.2 |
| **WordPress** | 6.0 | Bản phát hành mới nhất |
| **MySQL / MariaDB** | MySQL 5.7 / MariaDB 10.3 | MySQL 8.0 / MariaDB 10.6+ |
| **PHP Extensions** | `mysqli`, `curl`, `json`, `mbstring` | Kích hoạt đầy đủ |
| **Trình duyệt** | Chrome, Edge, Safari, Firefox | Các phiên bản hiện đại |

---

## 📦 Hướng Dẫn Cài Đặt (Installation)

1. Tải về hoặc nhân bản (clone) thư mục này vào thư mục plugins của WordPress:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/Anvu-IFBB/Mini-Kioviet.git mini-kiotviet
   ```
2. Đăng nhập vào trang quản trị WordPress (`wp-admin`).
3. Truy cập **Gói mở rộng (Plugins)** -> Tìm **Mini KiotViet Dashboard** và bấm **Kích hoạt (Activate)**.
4. Plugin sẽ tự động tạo cấu trúc bảng CSDL cần thiết và thiết lập quyền truy cập cho tài khoản Quản trị viên (`Administrator`).
5. Menu **Mini KiotViet** sẽ xuất hiện trên thanh điều hướng bên trái với đầy đủ các phân hệ quản lý.

---

## 🔒 Bảo Mật & Đóng Góp (Security & Contribution)

- Tệp cấu hình [.gitignore](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/mini-kiotviet/app/public/wp-content/plugins/mini-kiotviet/.gitignore) đã được tối ưu để tự động chặn các tệp nhạy cảm (API Keys, file `.env`, runtime logs, scratch scripts, cache IDE).
- Khi đóng góp tính năng mới, vui lòng tuân thủ:
  - Giữ nguyên mô hình kiến trúc MVC.
  - Sử dụng icon chuẩn thư viện **Hugeicons** (`hgi-stroke hgi-*`).
  - Đảm bảo các câu lệnh CSDL tuân thủ Prepared Statement và Database Transaction khi có biến động tồn kho hoặc dòng tiền.

---

## 📄 Bản Quyền (License)

Dự án được phát hành dưới giấy phép mã nguồn mở **GPL-2.0-or-later**.

**Tác giả**: An Vũ  
**Website**: [Mini KiotViet Project](https://github.com/Anvu-IFBB/Mini-Kioviet)
