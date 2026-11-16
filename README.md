# NEXA Điện Máy: website bán hàng và trang quản trị

Website bán điện máy viết bằng **Laravel 13 · Blade · Tailwind CSS 4 · Alpine.js · Vite**. Trang quản trị dùng **Filament 5**.

> Tên "NEXA", các thương hiệu (Aurin, Kelvo, Nordel, Viora, Zentek, Lumio), sản phẩm, khách hàng và đơn hàng trong dữ liệu mẫu đều là giả lập.
> Trước khi bán thật, hãy đổi tên, hotline, thông tin công ty trong **Hệ thống → Cấu hình cửa hàng** và nhập dữ liệu thật.

---

## 1. Tính năng

### Cửa hàng (khách xem)

| Trang | Đường dẫn | Ghi chú |
|---|---|---|
| Trang chủ | `/` | Banner trượt, danh mục nổi bật, flash sale có đếm ngược, ưu đãi theo danh mục, ô deal, bộ sưu tập, tin tức, phản hồi khách |
| Danh mục | `/danh-muc/{slug}` | Menu 2 cấp, lọc theo thương hiệu, thuộc tính, khoảng giá, có sắp xếp và phân trang |
| Sản phẩm | `/san-pham/{slug}` | Ảnh, giá, flash sale, tùy chọn mua kèm, thông số, đánh giá, sản phẩm tương tự, dữ liệu Schema.org cho SEO |
| So sánh | `/so-sanh` | So sánh tối đa 3 sản phẩm |
| Tìm kiếm | `/tim-kiem?q=` | |
| Giỏ hàng, thanh toán | `/gio-hang`, `/thanh-toan` | Mua không cần tài khoản, mã giảm giá, phí giao hàng tự động |
| Tra cứu đơn | `/tra-cuu-don-hang` | Tra bằng mã đơn và số điện thoại |
| Tin tức | `/tin-tuc` | Chuyên mục, bài viết, bài liên quan |
| Trang tĩnh, Hỏi đáp, Liên hệ | `/trang/{slug}`, `/hoi-dap`, `/lien-he` | |
| Tài khoản | `/tai-khoan` | Đơn hàng, hủy đơn, yêu cầu trả hàng, sản phẩm yêu thích, thông tin cá nhân, đổi mật khẩu |
| Quên mật khẩu | `/quen-mat-khau` | Gửi liên kết đặt lại qua email |
| Email tự động | | Khách nhận email khi đặt hàng và mỗi lần đơn đổi trạng thái; cửa hàng nhận email khi có đơn mới |
| Trang lỗi | | 404, 403, 419, 429, 500, 503 theo giao diện cửa hàng |
| SEO | `/sitemap.xml`, `robots.txt` | Có thẻ title, description, canonical, Open Graph |

### Trang quản trị (`/admin`)

- **Báo cáo kinh doanh:** doanh thu, lợi nhuận, chi phí, giá trị đơn trung bình, tổng đơn, khách mua, tỷ lệ chuyển đổi. Mỗi chỉ số có so sánh với kỳ trước. Kèm biểu đồ doanh số và lợi nhuận, khách mới, tỷ lệ giữ chân khách. Có thêm bảng đơn mới nhất, sản phẩm bán chạy, sản phẩm sắp hết hàng. Lọc theo 7 / 30 / 90 / 365 ngày.
- **Bán hàng:**
  - Đơn hàng (tab Đơn mới / Chưa hoàn thành / Hoàn thành / Trả lại, xác nhận, giao hàng, hủy, nhận trả hàng, in đơn)
  - Vận chuyển (vận đơn; giao xong thì đơn tự hoàn thành)
  - Hóa đơn (xuất và in)
  - Trả hàng (duyệt, từ chối, hoàn tiền)
- **Sản phẩm:** Sản phẩm (nhiều ảnh, giá, giá vốn, tồn kho, thông số, SEO, duyệt sản phẩm), Danh mục 2 cấp, Thương hiệu, Thuộc tính, Tùy chọn, Bộ sưu tập, Nhãn, Nhập xuất kho (có lịch sử).
- **Marketing:** Flash sale (đếm ngược, số suất), Mã giảm giá, Đánh giá (duyệt, phản hồi), Khách hàng (hạng thành viên, tổng chi tiêu), Quảng cáo (banner theo vị trí, đếm lượt bấm), Email nhận tin.
- **Nội dung:** Bài viết, Chuyên mục, Thẻ, Trang nội dung, Phản hồi khách hàng, Câu hỏi thường gặp, Liên hệ.
- **Tài chính:** Chi phí (dùng để tính lợi nhuận).
- **Hệ thống** (chỉ Super Admin): Tài khoản quản trị, Cấu hình cửa hàng (tên, logo, favicon, hotline, phí giao hàng, thông tin chuyển khoản, mạng xã hội, SEO).
- **Tiện ích chung:**
  - Xuất Excel (CSV) cho đơn hàng, sản phẩm, khách hàng, email nhận tin.
  - Thùng rác sản phẩm, khôi phục được sản phẩm đã xóa.
  - Lịch sử nhập xuất kho, gồm cả tồn kho ban đầu.
  - Trả lời liên hệ qua email.
  - Từ trang Khách hàng mở nhanh danh sách đơn của khách đó.
  - Tìm nhanh bằng phím tắt Ctrl+K.

### Chỗ thay ảnh (tải ảnh lên trong trang quản trị)

Mọi vị trí có ảnh hoặc icon đều thay được bằng ảnh thật. Chưa tải ảnh thì website tự dùng hình minh họa hoặc banner dựng bằng chữ, nên lúc nào trang cũng đầy đủ.

| Vị trí ngoài cửa hàng | Tải ảnh ở đâu | Kích thước khuyên dùng |
|---|---|---|
| Logo đầu trang, chân trang, trang quản trị; favicon | Hệ thống → Cấu hình cửa hàng | Logo ngang cao ~80 px; favicon 64×64 |
| Banner chính trang chủ (slider) | Marketing → Quảng cáo, vị trí *Trang chủ: banner chính* | 1200×450 (thêm ảnh điện thoại 800×600) |
| 2 banner phụ bên phải | Quảng cáo, vị trí *banner phụ* | 600×340 |
| Các ô "Deal nổ lớn" | Quảng cáo, vị trí *ô deal* | 600×600; ô lớn giữa 1200×1200 |
| Banner đầu trang danh mục | Sản phẩm → Danh mục → *Ảnh banner* (riêng từng danh mục), hoặc Quảng cáo vị trí *Trang danh mục* (dùng chung) | 1200×300 |
| Icon danh mục (menu 2 cấp, Danh mục nổi bật, thanh danh mục) | Sản phẩm → Danh mục → *Ảnh icon* | 200×200, nền trong suốt |
| Logo thương hiệu ở bộ lọc | Sản phẩm → Thương hiệu | 240×80 |
| Ảnh sản phẩm (nhiều ảnh, kéo để sắp xếp) | Sản phẩm → Sản phẩm → tab *Hình ảnh* | 800×800, nền trắng |
| Ảnh bài viết | Nội dung → Bài viết | 1200×675 |
| Ảnh khách hàng ở mục phản hồi | Nội dung → Phản hồi khách hàng | 200×200 |

Ô tải ảnh có sẵn công cụ cắt, xoay ảnh và ghi kích thước khuyên dùng ngay dưới ô.

### Vai trò

| Vai trò | Quyền |
|---|---|
| **Super Admin** | Toàn quyền, gồm cấu hình hệ thống và tài khoản quản trị |
| **Admin** | Vận hành cửa hàng: duyệt sản phẩm, xử lý đơn, trả hàng, báo cáo, marketing, nội dung |
| **Customer** | Mua hàng, quản lý đơn, yêu thích, đánh giá, yêu cầu trả hàng |
| **Guest** | Xem, tìm kiếm, mua hàng không cần đăng ký |

**Tài khoản mẫu** (mật khẩu đều là `password`; **đổi ngay khi đưa lên mạng**):

- **Super Admin:** `superadmin@example.com`
- **Admin:** `admin@example.com`
- **Khách hàng:** `khach@example.com`

---

## 2. Chạy trên máy cá nhân

**Cần có:** PHP 8.3 trở lên (bật `pdo_sqlite` hoặc `pdo_mysql`, `intl`, `gd`, `zip`, `mbstring`), Composer, Node 20 trở lên.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

Mở http://localhost:8000 để xem cửa hàng, và http://localhost:8000/admin để vào trang quản trị.

Thư mục `public/build` (CSS/JS đã build) đã có sẵn trong repo, nên máy chủ không cần cài Node. Mỗi khi sửa giao diện, chạy lại `npm run build` rồi commit.

---

## 3. Triển khai lên PA Vietnam

### Cách A: Hosting cPanel

1. **Chọn PHP:** vào cPanel → *Select PHP Version*, chọn **8.3 trở lên**. Bật các extension `pdo_mysql`, `intl`, `gd`, `zip`, `mbstring`, `fileinfo`.
2. **Tạo database:** vào *MySQL Databases*, tạo database và user, gán toàn quyền.
3. **Tải mã nguồn lên:**
   - **Nếu gói có SSH (khuyên dùng):**
     ```bash
     cd ~ && git clone https://github.com/thanhhieu599/web-ban-hang.git
     ```
   - **Nếu không có SSH:** chạy `composer install --no-dev` trên máy bạn, nén cả thư mục (kèm `vendor`), rồi tải lên bằng *File Manager* và giải nén vào `~/web-ban-hang`.
4. **Trỏ tên miền vào thư mục `public`:**
   - **Cách tốt nhất:** vào *Domains*, đặt Document Root của tên miền là `web-ban-hang/public`.
   - **Nếu không đổi được:** xóa `public_html` rồi tạo liên kết `ln -s ~/web-ban-hang/public ~/public_html` (qua SSH).
5. **Cấu hình `.env`:** copy `.env.example` thành `.env` rồi sửa:
   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://ten-mien-cua-ban.vn
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=ten_database
   DB_USERNAME=ten_user
   DB_PASSWORD=mat_khau
   SESSION_DRIVER=database
   CACHE_STORE=database
   QUEUE_CONNECTION=database

   # Gửi email (đơn hàng, quên mật khẩu): tạo hộp thư trong cPanel → Email Accounts
   MAIL_MAILER=smtp
   MAIL_HOST=mail.ten-mien-cua-ban.vn
   MAIL_PORT=465
   MAIL_SCHEME=smtps
   MAIL_USERNAME=no-reply@ten-mien-cua-ban.vn
   MAIL_PASSWORD=mat_khau_hop_thu
   MAIL_FROM_ADDRESS=no-reply@ten-mien-cua-ban.vn
   MAIL_FROM_NAME="${APP_NAME}"
   ```
   Nếu chưa cấu hình email, website vẫn chạy bình thường, chỉ không gửi được email.
6. **Cài đặt (qua SSH, trong thư mục dự án):**
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force   # chỉ chạy nếu muốn có dữ liệu mẫu
   php artisan storage:link
   php artisan filament:assets
   php artisan optimize
   ```
   Không có SSH thì dùng *Terminal* trong cPanel (nếu gói có), hoặc nhờ kỹ thuật PA chạy giúp.
7. **Đặt lịch chạy:** vào cPanel → *Cron Jobs*, thêm một lệnh chạy mỗi phút:
   ```
   * * * * * cd ~/web-ban-hang && php artisan schedule:run >> /dev/null 2>&1
   ```
8. **Bật HTTPS:** vào *SSL/TLS Status*, chọn *Run AutoSSL*.

**Cập nhật phiên bản mới:**

```bash
cd ~/web-ban-hang && git pull && composer install --no-dev -o && php artisan migrate --force && php artisan optimize
```

### Cách B: Cloud VPS (Ubuntu 24.04)

```bash
# 1. Phần mềm
sudo apt update && sudo apt install -y nginx mysql-server redis-server git unzip \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis php8.3-intl php8.3-gd php8.3-zip php8.3-mbstring php8.3-xml php8.3-curl php8.3-bcmath
curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer

# 2. Database
sudo mysql -e "CREATE DATABASE nexa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER 'nexa'@'localhost' IDENTIFIED BY 'DOI_MAT_KHAU'; GRANT ALL ON nexa.* TO 'nexa'@'localhost';"

# 3. Mã nguồn
sudo mkdir -p /var/www && cd /var/www && sudo git clone https://github.com/thanhhieu599/web-ban-hang.git && cd web-ban-hang
sudo composer install --no-dev -o && sudo cp .env.example .env   # sửa .env như cách A, có thể dùng CACHE_STORE=redis
sudo php artisan key:generate && sudo php artisan migrate --force && sudo php artisan storage:link && sudo php artisan filament:assets && sudo php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
```

**Nginx** (`/etc/nginx/sites-available/nexa`):

```nginx
server {
    listen 80;
    server_name ten-mien-cua-ban.vn www.ten-mien-cua-ban.vn;
    root /var/www/web-ban-hang/public;
    index index.php;
    client_max_body_size 20M;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~* \.(css|js|woff2|svg|png|jpg|webp)$ { expires 30d; access_log off; }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/nexa /etc/nginx/sites-enabled/ && sudo nginx -t && sudo systemctl reload nginx
sudo apt install -y certbot python3-certbot-nginx && sudo certbot --nginx -d ten-mien-cua-ban.vn -d www.ten-mien-cua-ban.vn
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/web-ban-hang && php artisan schedule:run >> /dev/null 2>&1") | crontab -
```

**Trỏ tên miền:** tạo bản ghi **A** trỏ về IP của VPS (trong quản lý tên miền của PA, hoặc qua Cloudflare).

### Sau khi chạy thật

1. Đổi mật khẩu 3 tài khoản mẫu, hoặc xóa chúng trong **Hệ thống → Tài khoản quản trị** và **Marketing → Khách hàng**.
2. Cập nhật tên cửa hàng, hotline, địa chỉ, MST, thông tin chuyển khoản trong **Cấu hình cửa hàng**.
3. Xóa dữ liệu mẫu và nhập sản phẩm thật. Ảnh nên dùng WebP, khoảng 800×800 px.
4. Kiểm tra `APP_DEBUG=false` để không lộ thông tin lỗi ra ngoài.

---

## 4. Cấu trúc mã nguồn

```
app/
  Filament/Resources/   # các module quản trị
  Filament/Widgets/     # chỉ số và biểu đồ của báo cáo
  Http/Controllers/     # trang cửa hàng
  Models/               # 35 bảng dữ liệu
  Services/
    Cart.php            # giỏ hàng
    Checkout.php        # đặt hàng, trừ kho
    Report.php          # tính số liệu báo cáo
config/art.php          # hình minh họa sản phẩm (dùng khi chưa có ảnh thật)
database/migrations/    # cấu trúc database
database/seeders/       # dữ liệu mẫu
resources/views/        # giao diện Blade
```

**Cách tính số liệu báo cáo:**

- **Doanh thu:** tổng tiền các đơn *Đã xác nhận*, *Đang giao* và *Hoàn thành*.
- **Lợi nhuận:** doanh thu trừ giá vốn sản phẩm, trừ các khoản trong mục *Chi phí*.
- **Tỷ lệ chuyển đổi:** số đơn chia cho số lượt truy cập (mỗi phiên trình duyệt được tính một lượt mỗi ngày).
