# Mini KiotViet (MKV) — Hệ Thống Quản Lý Bán Hàng & ERP Bán Lẻ Trên WordPress

<p align="center">
  <img src="https://img.shields.io/badge/Version-3.3.0-blue.svg" alt="Version 3.3.0">
  <img src="https://img.shields.io/badge/WordPress-%3E%3D%206.0-21759B.svg" alt="WordPress 6.0+">
  <img src="https://img.shields.io/badge/PHP-%3E%3D%207.4%20%7C%208.x-777BB4.svg" alt="PHP 7.4+">
  <img src="https://img.shields.io/badge/License-GPL--2.0%2B-green.svg" alt="License GPL-2.0+">
  <img src="https://img.shields.io/badge/Icons-Hugeicons%20Stroke-orange.svg" alt="Hugeicons">
  <img src="https://img.shields.io/badge/AI-Google%20Gemini-4285F4.svg" alt="Gemini AI">
  <img src="https://img.shields.io/badge/Language-English%20%7C%20Ti%E1%BA%BFng%20Vi%E1%BB%87t-success.svg" alt="Bilingual">
</p>

---

## 🌟 Giới Thiệu (Overview)

**Mini KiotViet (MKV)** là giải pháp phần mềm quản lý bán hàng và hoạch định nguồn lực doanh nghiệp bán lẻ (**Retail ERP**) toàn diện, được đóng gói dưới dạng **WordPress Plugin** cao cấp. Hệ thống tái hiện trung thực trải nghiệm quản trị trực quan, tốc độ và tiện lợi của nền tảng **KiotViet**, đồng thời tích hợp sâu với cơ sở dữ liệu WordPress, chuẩn mực bảo mật doanh nghiệp và trợ lý trí tuệ nhân tạo **Google Gemini AI**.

Dự án được kiến trúc theo mô hình **MVC (Model - View - Controller)** chuẩn mực, tối ưu hóa cho các cửa hàng bán lẻ, chuỗi chi nhánh, siêu thị mini, shop thời trang và thương mại đa kênh với khả năng vận hành ổn định, giao dịch cơ sở dữ liệu có tính toàn vẹn cao (Database Transactions) và hỗ trợ song ngữ tức thì.

---

## 🚀 Các Phân Hệ & Tính Năng Chính (Core Modules)

### 1. Quầy Bán Hàng Thu Ngân (POS - Point of Sale)
- **Giao diện bán hàng siêu tốc**: Tối ưu thao tác một chạm trên màn hình cảm ứng, chuột và phím tắt.
- **Tìm kiếm & Quét mã Barcode**: Tự động nhận diện máy quét mã vạch Barcode trực tiếp hoặc tìm kiếm theo tên, SKU.
- **Đa phương thức thanh toán**: Tiền mặt, chuyển khoản ngân hàng tự động (VietQR động sinh theo hóa đơn), quẹt thẻ POS, hoặc ghi nợ vào công nợ khách hàng.
- **In hóa đơn nhiệt chuyên nghiệp**: Định dạng in khổ K80 / K58 chuẩn mực, tự động sinh mã vạch đơn hàng phục vụ kiểm soát giao nhận.

### 2. Quản Lý Đơn Hàng & Giao Vận (Orders & Logistics)
- **Quy trình đơn hàng khép kín**: Theo dõi trạng thái đa giai đoạn: *Chờ xử lý (Pending)*, *Đang giao (Shipping)*, *Đã hoàn thành (Completed)*, *Đã hủy (Cancelled)*.
- **Quản lý thu hộ COD**: Hạch toán độc lập giữa tiền hàng và tiền COD của các đơn vị giao vận.
- **Modal chi tiết đơn hàng (Order Detail Modal)**: Timeline hành trình đơn hàng, địa chỉ giao nhận, chi tiết sản phẩm, công nợ, in phiếu giao hàng.
- **Tra cứu vận đơn Frontend (Public Tracking)**: Tích hợp shortcode `[mkv_tracking]` cho phép người mua tra cứu tiến độ đơn hàng bằng mã đơn hoặc số điện thoại.
- **Webhook giao vận**: Sẵn sàng tiếp nhận webhook cập nhật trạng thái tự động từ các đối tác giao hàng (GHN, GHTK, Viettel Post).

### 3. Hàng Hóa & Quản Lý Tồn Kho (Products & Multi-Warehouse)
- **Quản lý sản phẩm & biến thể**: SKU, mã vạch (JsBarcode EAN-13 / Code128), đơn vị tính, danh mục đa tầng.
- **Giá vốn & Giá bán**: Theo dõi giá nhập lần cuối, hỗ trợ tính toán biên lợi nhuận gộp chính xác.
- **Cảnh báo an toàn kho**: Tự động cảnh báo khi tồn kho xuống dưới định mức tối thiểu (*Low Stock Alert*).
- **Đa kho & Chi nhánh**: Hỗ trợ chuỗi kho bãi, chi nhánh độc lập.
- **Chuyển kho an toàn (Stock Transfer)**: Sử dụng Database Transactions với row-level locking (`FOR UPDATE`), ngăn chặn triệt để hiện tượng sai lệch tồn kho khi chuyển đồng thời.
- **Kiểm kê định kỳ (Stocktake)**: Cân bằng tồn kho thực tế và sổ sách, tự động ghi nhận phiếu chênh lệch thừa/thiếu.
- **Thẻ kho điện tử (Stock Card Audit Trail)**: Nhật ký biến động kho bất biến ghi nhận chi tiết mọi giao dịch nhập/xuất/bán.

### 4. Nhập Hàng & Công Nợ Nhà Cung Cấp (Purchases & Accounts Payable)
- **Lập đơn nhập hàng (Purchase Orders)**: Tính toán tự động tổng tiền, số lượng nhập, tăng tồn kho vật lý tức thì tại thời điểm duyệt nhập hàng.
- **Chuẩn hóa nghiệp vụ ERP**: Nhập kho tăng tồn vật lý; việc thanh toán tiền sau này chỉ quyết toán công nợ, tuyệt đối không làm nhân đôi tồn kho.
- **PO Detail Modal**: Xem chi tiết 4 thẻ KPI tài chính, danh mục hàng nhập, lịch sử chi tiền và lịch sử thẻ kho liên kết.
- **In chứng từ nhập kho**: Xuất phiếu nhập kho khổ A4/A5 tiêu chuẩn để ký nhận đối soát.

### 5. Sổ Quỹ & Quản Lý Dòng Tiền (Cashbook & Treasury)
- **Quản lý Thu / Chi**: Hạch toán minh bạch dòng tiền mặt và tiền gửi ngân hàng.
- **Phân bổ công nợ FIFO**: Khi lập Phiếu Chi trả nợ NCC, hệ thống tự động phân bổ trừ nợ cho các phiếu nhập cũ nhất theo thứ tự thời gian.
- **Thu nợ khách hàng**: Tự động khấu trừ nợ của khách hàng ngay khi xác nhận Phiếu Thu.
- **Báo cáo tồn quỹ**: Thống kê tồn đầu kỳ, tổng thu trong kỳ, tổng chi trong kỳ và tồn quỹ cuối kỳ theo khoảng thời gian tùy chọn.

### 6. Khách Hàng & Đối Tác (CRM & Partners)
- **Hồ sơ đối tác toàn diện**: Tên, số điện thoại, email, địa chỉ, mã số thuế, phân nhóm khách hàng.
- **Lịch sử giao dịch & Tích điểm**: Tra cứu lịch sử mua hàng, công nợ hiện tại, điểm tích lũy và quy đổi điểm khi thanh toán POS.

### 7. Báo Cáo & Thống Kê Tài Chính (Analytics & Reports)
- **Dashboard trực quan**: Biểu đồ doanh thu tương tác thời gian thực (Chart.js) so sánh theo Ngày, Tuần, Tháng, Năm.
- **Chỉ số kinh doanh trọng yếu**: Doanh thu thuần, giá vốn hàng bán (COGS), lợi nhuận gộp, số lượng giao dịch.
- **Báo cáo chuyên sâu**: Báo cáo bán hàng, báo cáo lợi nhuận chi tiết theo mặt hàng, sổ quỹ chốt ca cuối ngày.

### 8. Phân Quyền Vai Trò & Nhân Sự (HR & RBAC)
- **Phân quyền Role-Based Access Control (RBAC)**: Thiết lập 4 vai trò chuyên biệt với 13 capabilities độc lập:
  - `administrator`: Quản trị viên toàn quyền hệ thống.
  - `mkv_manager`: Cửa hàng trưởng (quản lý vận hành, báo cáo, sổ quỹ, nhân viên).
  - `mkv_sales`: Nhân viên bán hàng (POS, tạo đơn, xem sản phẩm).
  - `mkv_warehouse`: Thủ kho (nhập hàng, chuyển kho, kiểm kê, danh mục hàng).
- **Chấm công điện tử (Timesheets)**: Nhân viên Check-in / Check-out ca làm việc trực tiếp trên giao diện Dashboard.

### 9. Trợ Lý Trí Tuệ Nhân Tạo (Gemini AI Copilot)
- **Tích hợp Google Gemini AI**: Trợ lý phân tích kinh doanh hiển thị dưới dạng Drawer tương tác trượt từ mép phải màn hình.
- **Hỏi đáp kinh doanh thông minh**: Trả lời báo cáo doanh thu hôm nay, phát hiện hàng tồn đọng lâu ngày, gợi ý mặt hàng bán chạy và tư vấn tối ưu tồn kho.

### 10. Chuyển Đổi Song Ngữ Tức Thì (Bilingual: English ↔ Tiếng Việt)
- **Bản địa hóa toàn diện (Full-App Localization)**: Dịch thuật trọn vẹn 100% giao diện: 8 menu chính, menu con, nút chức năng, bảng dữ liệu, bộ lọc, modal và thông báo realtime.
- **Đồng bộ hóa đa tầng**: Kết hợp tức thì giữa Client Cookie, LocalStorage và WordPress User Meta, loại bỏ hoàn toàn hiện tượng race condition hoặc lệch ngôn ngữ khi F5/Reload.

### 11. Giao Diện Tối & Chuẩn Thiết Kế Hiện Đại (Dark Mode & Modern UI)
- **Dark Mode**: Chuyển đổi giao diện sáng/tối một chạm trên Header, lưu cấu hình vào localStorage chống chớp màn hình (anti-flash).
- **Hệ thống Icon Hugeicons Stroke**: 100% icon vector đồng nhất, sắc nét và hiện đại.
- **Bố cục Topbar & Navigation cố định**: Đảm bảo kích thước và tỉ lệ cân đối tuyệt đối trên mọi độ phân giải màn hình máy tính (1920x1080, 1536x864, 1366x768).

### 12. Nhật Ký Bảo Mật Bất Biến (Security Audit Logging)
- **Hệ thống Audit Logger (`MKV_Audit_Logger`)**: Ghi nhận tự động và bất biến mọi hành động nhạy cảm trong hệ thống (tạo/hủy đơn, nhập kho, phiếu thu/chi, thay đổi quyền, đăng nhập/đăng xuất).
- **Lưu trữ dữ liệu toàn diện**: Thời gian thực, User ID, IP Address, User Agent, Action Type và Payload JSON chi tiết.

---

## 🏗 Cấu Trúc Thư Mục Dự Án (Project Structure)

```
mini-kiotviet/
├── assets/
│   ├── css/
│   │   ├── admin-layout.css         # Hệ sinh thái layout, CSS variables & theme tokens
│   │   ├── admin-dashboard.css      # CSS widget và thẻ thống kê Dashboard
│   │   ├── admin-pos.css            # CSS màn hình bán hàng thu ngân POS
│   │   ├── admin-wp-overrides.css   # Tùy biến thanh điều hướng WordPress Admin
│   │   ├── admin-ai-assistant.css   # Giao diện Drawer trợ lý Gemini AI
│   │   ├── input.css                # CSS nguồn Tailwind/PostCSS
│   │   └── tailwind-admin.css       # CSS compiled tiện ích
│   └── js/
│       ├── admin-global.js          # Core utilities, format tiền tệ, Toast, Dark mode, i18n
│       ├── admin-pos.js             # Logic giỏ hàng, quét Barcode, tính tiền, in hóa đơn
│       ├── dashboard-chart.js       # Biểu đồ tương tác Chart.js đa khung thời gian
│       ├── admin-ai-assistant.js    # Streaming tương tác với Google Gemini API
│       └── notifications-poll.js    # Polling thông báo đơn hàng & tồn kho thời gian thực
├── docs/
│   └── reports/                     # Lưu trữ các báo cáo kiểm thử & chứng nhận phát hành
│       ├── BACKEND-12_COMPLETION_REPORT.md
│       ├── BACKEND-13_COMPLETION_REPORT.md
│       ├── BACKEND-14-FINAL-REPORT.md
│       ├── BACKEND-15_FINAL_REPORT.md
│       ├── BACKEND-16-FINAL-REPORT.md
│       ├── FRONTEND-01_COMPLETION_REPORT.md
│       ├── FRONTEND-06_COMPLETION_REPORT.md
│       ├── FRONTEND-07_COMPLETION_REPORT.md
│       ├── FRONTEND-08_COMPLETION_REPORT.md
│       ├── FRONTEND-09_COMPLETION_REPORT.md
│       ├── FRONTEND-10_COMPLETION_REPORT.md
│       ├── FRONTEND-11_COMPLETION_REPORT.md
│       ├── FRONTEND-17-FINAL-REPORT.md
│       ├── FINAL_RELEASE_AUDIT_REPORT.md
│       ├── FINAL_RELEASE_CHECKLIST.md
│       └── FINAL_TEST_MATRIX.md
├── includes/
│   ├── admin/
│   │   └── class-settings.php       # Quản trị cấu hình shop, API keys, mẫu in, VietQR
│   ├── controllers/                 # 13 Controllers điều hướng nghiệp vụ
│   │   ├── class-dashboard.php      # Tổng quan kinh doanh & thống kê nhanh
│   │   ├── class-products.php       # Quản lý hàng hóa, danh mục, SKU
│   │   ├── class-inventory.php      # Kho bãi, chuyển kho, kiểm kê, thẻ kho
│   │   ├── class-orders.php         # Bán hàng POS, tạo đơn, quản lý đơn hàng
│   │   ├── class-purchases.php      # Nhập hàng & quản lý công nợ NCC
│   │   ├── class-cashbook.php       # Sổ quỹ thu chi & phân bổ dòng tiền
│   │   ├── class-customers.php      # Hồ sơ khách hàng, công nợ, điểm thưởng
│   │   ├── class-employees.php      # Quản lý nhân viên & bảng chấm công
│   │   ├── class-reports.php        # Báo cáo bán hàng, lợi nhuận, cuối ngày
│   │   ├── class-notifications.php  # Thông báo hệ thống & realtime polling
│   │   ├── class-ai-controller.php  # Điều phối xử lý yêu cầu Gemini AI
│   │   ├── class-frontend.php       # Shortcode tra cứu vận đơn ngoài frontend
│   │   └── class-webhook.php        # Tiếp nhận Webhook đơn vị vận chuyển
│   ├── models/                      # 5 Models dữ liệu & Dịch vụ lõi
│   │   ├── class-db-schema.php      # Quản lý cấu trúc 18 bảng CSDL & nâng cấp dbDelta
│   │   ├── class-analytics-service.php # Tính toán doanh số, COGS, lãi gộp
│   │   ├── class-audit-logger.php   # Ghi nhận nhật ký bảo mật bất biến
│   │   ├── class-shipping-service.php  # Định giá & tra cứu cước vận chuyển
│   │   └── class-ai-service.php     # Kết nối API Google Gemini
│   ├── views/                       # 20 Templates giao diện người dùng
│   │   ├── header-kiotviet.php      # Topbar trắng + Navigation xanh KiotViet
│   │   ├── footer-kiotviet.php      # Chân trang & script dùng chung
│   │   ├── view-dashboard.php       # Giao diện trang tổng quan
│   │   ├── view-pos.php             # Giao diện quầy thu ngân POS
│   │   ├── view-orders.php          # Danh sách quản lý đơn hàng
│   │   ├── view-order-detail.php    # Chi tiết đơn hàng & hành trình vận đơn
│   │   ├── view-inventory.php       # Quản lý tồn kho & thẻ kho
│   │   ├── view-purchases.php       # Nhập hàng & chi tiết đơn nhập
│   │   ├── view-cashbook.php        # Quản lý thu/chi & sổ quỹ
│   │   ├── view-customers.php       # Danh sách khách hàng
│   │   ├── view-categories.php      # Danh mục hàng hóa
│   │   ├── view-employees.php       # Danh sách nhân sự & chấm công
│   │   ├── view-reports.php         # Báo cáo thống kê kinh doanh
│   │   ├── view-notifications.php   # Trung tâm thông báo hệ thống
│   │   ├── view-settings.php        # Cài đặt cửa hàng, in ấn, VietQR
│   │   ├── view-audit-logs.php      # Nhật ký bảo mật hệ thống
│   │   ├── view-ai-drawer.php       # Giao diện Drawer trợ lý Gemini AI
│   │   ├── view-support-modal.php   # Modal trung tâm hỗ trợ & hotline
│   │   ├── view-tracking.php        # Tra cứu vận đơn cho khách hàng
│   │   └── view-product-metabox.php # Metabox cấu hình sản phẩm trong admin
│   └── mkv-i18n.php                 # Hệ thống từ điển đa ngôn ngữ (VI ↔ EN)
├── mini-kiotviet.php                # Entry point chính của WordPress plugin
├── .gitignore                       # Cấu hình lọc mã nguồn & bảo mật
├── package.json                     # Quản lý gói dependencies Node.js
└── README.md                        # Tài liệu hướng dẫn kỹ thuật toàn diện
```

---

## 🗄 Kiến Trúc Cơ Sở Dữ Liệu (Database Schema)

Plugin tự động khởi tạo và quản lý **18 bảng CSDL chuyên biệt** với tiền tố `{$wpdb->prefix}mkv_*`:

| Tên Bảng | Vai Trò & Chức Năng |
| :--- | :--- |
| `wp_mkv_products` | Thuộc tính mở rộng của sản phẩm (SKU, barcode, giá vốn, min stock). |
| `wp_mkv_inventory` | Tồn kho vật lý theo từng kho và chi nhánh. |
| `wp_mkv_inventory_logs` | Thẻ kho bất biến (ghi nhận mọi biến động nhập, xuất, bán, kiểm kê). |
| `wp_mkv_orders` | Thông tin đơn hàng (mã đơn, khách hàng, tổng tiền, COD, trạng thái). |
| `wp_mkv_order_items` | Danh sách mặt hàng, số lượng, đơn giá trong từng đơn hàng. |
| `wp_mkv_customers` | Hồ sơ khách hàng, công nợ phải thu, điểm thưởng tích lũy. |
| `wp_mkv_suppliers` | Hồ sơ nhà cung cấp, công nợ phải trả. |
| `wp_mkv_purchases` | Phiếu nhập hàng từ nhà cung cấp (PO). |
| `wp_mkv_purchase_items` | Chi tiết danh sách sản phẩm trong phiếu nhập hàng. |
| `wp_mkv_cashbook` | Sổ quỹ ghi nhận phiếu thu và phiếu chi (tiền mặt / ngân hàng). |
| `wp_mkv_transfers` | Phiếu chuyển hàng giữa các kho. |
| `wp_mkv_transfer_items` | Chi tiết sản phẩm luân chuyển giữa các kho. |
| `wp_mkv_stocktakes` | Phiếu kiểm kê kho thực tế định kỳ. |
| `wp_mkv_stocktake_items` | Chi tiết chênh lệch thừa/thiếu khi kiểm kê từng mặt hàng. |
| `wp_mkv_shifts` | Bảng ghi nhận Check-in / Check-out ca làm việc của nhân viên. |
| `wp_mkv_notifications` | Thông báo realtime về đơn hàng mới và cảnh báo hết hàng. |
| `wp_mkv_webhooks` | Nhật ký lưu vết webhook từ các đơn vị giao hàng đối tác. |
| `wp_mkv_audit_logs` | Nhật ký bảo mật ghi nhận các thao tác quản trị trọng yếu. |

---

## ⚙️ Yêu Cầu Hệ Thống (System Requirements)

| Thành phần | Yêu cầu tối thiểu | Khuyến nghị tối ưu |
| :--- | :--- | :--- |
| **WordPress** | Version 6.0 trở lên | Phiên bản mới nhất (6.4+) |
| **PHP** | 7.4 | PHP 8.1 hoặc PHP 8.2 |
| **Database** | MySQL 5.7 / MariaDB 10.3 | MySQL 8.0 / MariaDB 10.6+ |
| **PHP Extensions** | `mysqli`, `curl`, `json`, `mbstring` | Kích hoạt đầy đủ |
| **Trình duyệt** | Chrome, Edge, Safari, Firefox | Các phiên bản hiện đại hỗ trợ ES6+ |

---

## 📦 Hướng Dẫn Cài Đặt (Installation Guide)

### Bước 1: Tải về và cài đặt plugin
Nhân bản (clone) mã nguồn hoặc copy thư mục plugin vào thư mục `wp-content/plugins/` của WordPress:
```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/Anvu-IFBB/Mini-Kioviet.git mini-kiotviet
```

### Bước 2: Cài đặt Node dependencies (Tùy chọn dành cho Developer)
Nếu cần biên dịch lại CSS Tailwind hoặc chạy test runner:
```bash
cd mini-kiotviet
npm install
```

### Bước 3: Kích hoạt plugin trên WordPress
1. Đăng nhập vào trang quản trị WordPress (`/wp-admin`).
2. Điều hướng tới menu **Gói mở rộng (Plugins)**.
3. Tìm **Mini KiotViet Dashboard** và nhấn **Kích hoạt (Activate)**.
4. Hệ thống sẽ tự động tạo đầy đủ 18 bảng CSDL cần thiết và phân quyền mặc định cho tài khoản Administrator.
5. Menu **Mini KiotViet** sẽ xuất hiện trên thanh điều hướng bên trái với đầy đủ các phân hệ quản lý.

---

## 🛡 Bảo Mật & Toàn Vẹn Dữ Liệu (Security & Data Integrity)

1. **Chống CSRF (Cross-Site Request Forgery)**: 100% form thao tác, request AJAX và link tác vụ đều được bảo vệ bởi WordPress Nonce (`check_admin_referer`, `check_ajax_referer`).
2. **Kiểm soát quyền chặt chẽ (RBAC)**: Mọi endpoint và hành động backend đều kiểm tra thẩm quyền bằng `current_user_can()` với capability tương ứng trước khi xử lý.
3. **Chống SQL Injection**: Toàn bộ câu lệnh SQL tùy biến đều sử dụng Prepared Statements thông qua `$wpdb->prepare()`.
4. **Bảo vệ toàn vẹn giao dịch tài chính & kho bãi**:
   - Các hành vi cập nhật số dư, trừ tồn kho, chuyển kho, khấu trừ công nợ đều được bọc kín trong `START TRANSACTION`, `COMMIT` và `ROLLBACK` khi có lỗi.
   - Sử dụng cơ chế khóa dòng (`SELECT ... FOR UPDATE`) để triệt tiêu hiện tượng tranh chấp dữ liệu đồng thời (*Concurrency Race Condition*).
5. **Sanitization & Escaping**: Đầu vào được làm sạch với `sanitize_text_field()`, `sanitize_textarea_field()`, và đầu ra HTML được bảo vệ bằng `esc_html()`, `esc_attr()`, `esc_url()`.

---

## 📄 Bản Quyền & Giấy Phép (License)

Dự án được phát hành dưới giấy phép mã nguồn mở **GPL-2.0-or-later**.

- **Tác giả**: An Vũ  
- **Phiên bản hiện tại**: `3.3.0`  
- **Kho lưu trữ mã nguồn**: [Mini KiotViet GitHub Repository](https://github.com/Anvu-IFBB/Mini-Kioviet)
