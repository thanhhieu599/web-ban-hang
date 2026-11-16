<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Contact;
use App\Models\Discount;
use App\Models\Expense;
use App\Models\Faq;
use App\Models\FlashSale;
use App\Models\Label;
use App\Models\NewsletterSubscriber;
use App\Models\Option;
use App\Models\Order;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\Review;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Dữ liệu mẫu để xem giao diện và báo cáo. Toàn bộ thương hiệu, sản phẩm,
 * khách hàng ở đây là giả lập: hãy xóa và nhập dữ liệu thật trước khi bán hàng.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(2026);

        /* ---------- Tài khoản ---------- */
        User::create(['name' => 'Super Admin', 'email' => 'superadmin@example.com', 'password' => 'password', 'role' => 'super_admin']);
        User::create(['name' => 'Quản lý cửa hàng', 'email' => 'admin@example.com', 'password' => 'password', 'role' => 'admin']);
        $demoCustomer = User::create(['name' => 'Nguyễn Minh Anh', 'email' => 'khach@example.com', 'password' => 'password', 'role' => 'customer', 'phone' => '0900000001', 'address' => '12 Đường Mẫu, Quận 1']);

        /* ---------- Thương hiệu (giả lập) ---------- */
        $brands = collect([['Aurin', '#0b5bd3'], ['Kelvo', '#e0121f'], ['Nordel', '#0f766e'], ['Viora', '#7c3aed'], ['Zentek', '#111827'], ['Lumio', '#ea580c']])
            ->map(fn ($b, $i) => Brand::create(['name' => $b[0], 'slug' => Str::slug($b[0]), 'color' => $b[1], 'sort' => $i]));

        /* ---------- Nhãn, tùy chọn ---------- */
        $labels = collect([['Trả góp 0%', '#e0121f'], ['Lắp đặt trong ngày', '#0f766e'], ['Hàng mới về', '#2563eb']])
            ->map(fn ($l) => Label::create(['name' => $l[0], 'color' => $l[1]]));
        $warrantyOption = Option::create(['name' => 'Gói bảo hành mở rộng', 'values' => [
            ['label' => 'Không mua thêm', 'price' => 0], ['label' => 'Thêm 12 tháng', 'price' => 490000], ['label' => 'Thêm 24 tháng', 'price' => 890000],
        ]]);
        $installOption = Option::create(['name' => 'Dịch vụ lắp đặt', 'values' => [
            ['label' => 'Lắp đặt tiêu chuẩn (miễn phí)', 'price' => 0], ['label' => 'Lắp đặt kèm vật tư nâng cao', 'price' => 350000],
        ]]);

        /* ---------- Danh mục 2 cấp ---------- */
        $menu = [
            ['Điện lạnh', 'ac', [['dieu-hoa', 'Điều hòa', 'ac', '-50%'], ['tu-lanh', 'Tủ lạnh', 'fridge', '-40%'], ['tu-lanh-side-by-side', 'Tủ lạnh Side by Side', 'sbs'], ['tu-dong', 'Tủ đông', 'freezer'],
                ['may-loc-khong-khi', 'Máy lọc không khí', 'purifier', '-35%'], ['quat', 'Quạt điện', 'fan'], ['binh-nong-lanh', 'Bình nóng lạnh', 'waterheater'], ['may-suoi', 'Máy sưởi', 'heater', '-40%']]],
            ['Tivi – Âm thanh', 'tv', [['tivi', 'Tivi', 'tv', '-45%'], ['loa', 'Loa – Soundbar', 'speaker'], ['tai-nghe', 'Tai nghe', 'headphone']]],
            ['Giặt sấy', 'washer', [['may-giat-cua-truoc', 'Máy giặt cửa trước', 'washer', '-50%'], ['may-giat-cua-tren', 'Máy giặt cửa trên', 'toploader'], ['may-say', 'Máy sấy quần áo', 'dryer', '-30%'], ['ban-ui', 'Bàn ủi', 'iron']]],
            ['Gia dụng nhà bếp', 'ricecooker', [['noi-com-dien', 'Nồi cơm điện', 'ricecooker', '-30%'], ['noi-chien-khong-dau', 'Nồi chiên không dầu', 'airfryer'], ['bep-tu', 'Bếp từ – hồng ngoại', 'stove', '-50%'], ['lo-vi-song', 'Lò vi sóng', 'microwave', '-25%'],
                ['lo-nuong', 'Lò nướng', 'oven'], ['may-xay', 'Máy xay – ép', 'blender', '-35%'], ['may-rua-chen', 'Máy rửa chén', 'dishwasher', '-30%'], ['am-sieu-toc', 'Ấm siêu tốc', 'kettle']]],
            ['Thiết bị gia đình', 'robot', [['may-hut-bui', 'Máy hút bụi', 'vacuum', '-40%'], ['robot-hut-bui', 'Robot hút bụi', 'robot'], ['may-loc-nuoc', 'Máy lọc nước', 'waterpurifier', '-50%'], ['camera-an-ninh', 'Camera an ninh', 'camera']]],
            ['Điện thoại – Laptop', 'phone', [['dien-thoai', 'Điện thoại', 'phone', '-15%'], ['may-tinh-bang', 'Máy tính bảng', 'tablet'], ['laptop', 'Laptop', 'laptop', '-20%'], ['dong-ho-thong-minh', 'Đồng hồ thông minh', 'watch']]],
            ['Phụ kiện công nghệ', 'powerbank', [['sac-du-phong', 'Sạc dự phòng', 'powerbank'], ['chuot-ban-phim', 'Chuột – bàn phím', 'mouse'], ['o-cam-thong-minh', 'Ổ cắm thông minh', 'plug']]],
            ['Sức khỏe – Làm đẹp', 'hairdryer', [['may-say-toc', 'Máy sấy tóc', 'hairdryer'], ['may-massage', 'Máy massage', 'massage'], ['can-suc-khoe', 'Cân sức khỏe', 'scale']]],
        ];

        // Quy tắc sinh sản phẩm cho các danh mục chính: [tên thuộc tính, [giá trị => giá gốc], hàm đặt tên]
        $specs = [
            'dieu-hoa' => ['Công suất làm lạnh', ['1 HP – 9.000 BTU' => 6.2e6, '1,5 HP – 12.000 BTU' => 8.6e6, '2 HP – 18.000 BTU' => 13.2e6, '2,5 HP – 24.000 BTU' => 19.5e6],
                fn ($b, $s, $i) => "Điều hòa $b ".($i % 3 ? '1 chiều' : '2 chiều').' Inverter '.explode('– ', $s)[1]],
            'tu-lanh' => ['Dung tích tủ lạnh', ['Dưới 200 lít' => 5.2e6, '200 – 400 lít' => 8.9e6, '400 – 600 lít' => 14.5e6],
                fn ($b, $s, $i) => "Tủ lạnh $b Inverter ".[189, 256, 320, 460, 520][$i % 5].' lít '.($i % 2 ? 'ngăn đá trên' : 'ngăn đá dưới')],
            'may-giat-cua-truoc' => ['Khối lượng giặt', ['Dưới 9 kg' => 5.2e6, '9 – 10 kg' => 7.4e6, '10,5 – 12 kg' => 10.5e6],
                fn ($b, $s, $i) => "Máy giặt cửa trước $b Inverter ".[8, 9, 10, 11, 12][$i % 5].' kg'],
            'may-giat-cua-tren' => ['Khối lượng giặt', ['Dưới 9 kg' => 3.3e6, '9 – 10 kg' => 4.9e6, '10,5 – 12 kg' => 6.8e6],
                fn ($b, $s, $i) => "Máy giặt cửa trên $b ".[8, 9, 10, 11, 12][$i % 5].' kg'],
            'may-say' => ['Công nghệ sấy', ['Thông hơi' => 4.6e6, 'Ngưng tụ' => 8.9e6, 'Bơm nhiệt' => 14e6],
                fn ($b, $s, $i) => 'Máy sấy '.mb_strtolower($s)." $b ".[7.5, 8, 9, 10][$i % 4].' kg'],
            'tivi' => ['Kích thước màn hình', ['32 – 43 inch' => 3.6e6, '50 – 55 inch' => 8.9e6, '65 inch' => 13.9e6, '75 inch trở lên' => 24e6],
                fn ($b, $s, $i) => "Smart Tivi $b ".['QLED', '4K UHD', 'Mini LED', 'OLED'][$i % 4].' '.explode(' ', $s)[0].' inch'],
            'quat' => ['Loại quạt', ['Quạt đứng' => 0.9e6, 'Quạt treo tường' => 0.8e6, 'Quạt trần' => 2.6e6, 'Quạt tháp' => 2.1e6],
                fn ($b, $s, $i) => "$s $b ".['5 cánh', 'DC tiết kiệm điện', 'có điều khiển', '3 tốc độ'][$i % 4]],
            'may-suoi' => ['Loại máy sưởi', ['Sưởi dầu' => 2.1e6, 'Sưởi gốm' => 1.2e6, 'Đèn sưởi nhà tắm' => 0.9e6],
                fn ($b, $s, $i) => 'Máy '.mb_strtolower($s)." $b ".[9, 11, 13][$i % 3].' lá '.($i % 2 ? 'có điều khiển' : 'hẹn giờ')],
            'noi-com-dien' => ['Dung tích nồi', ['1 lít' => 0.7e6, '1,5 lít' => 1.2e6, '1,8 lít' => 1.6e6],
                fn ($b, $s, $i) => "Nồi cơm điện tử $b $s ".($i % 2 ? 'cao tần' : 'lòng niêu')],
            'bep-tu' => ['Loại bếp', ['Bếp từ đơn' => 0.9e6, 'Bếp từ đôi' => 6.5e6, 'Bếp từ hồng ngoại' => 7.9e6],
                fn ($b, $s, $i) => "$s $b ".($i % 2 ? 'mặt kính chịu nhiệt' : 'Inverter')],
        ];
        $featureAttr = Attribute::create(['name' => 'Tính năng', 'slug' => 'tinh-nang', 'sort' => 99]);
        $features = collect(['Inverter tiết kiệm điện', 'Kết nối Wi-Fi', 'Điều khiển bằng giọng nói'])->map(fn ($v, $i) => $featureAttr->values()->create(['value' => $v, 'sort' => $i]));
        $attrCache = [];

        $products = collect();
        $featuredSlugs = ['dieu-hoa', 'tu-lanh', 'may-say', 'may-giat-cua-truoc', 'tivi', 'may-loc-khong-khi', 'bep-tu', 'laptop', 'may-suoi', 'noi-com-dien', 'may-xay', 'may-loc-nuoc', 'lo-vi-song', 'dien-thoai', 'may-rua-chen', 'may-hut-bui'];
        $n = 0;
        foreach ($menu as $gi => [$groupName, $groupIcon, $children]) {
            $parent = Category::create(['name' => $groupName, 'slug' => Str::slug($groupName), 'icon' => $groupIcon, 'sort' => $gi, 'sale_badge' => $gi < 3 ? 'SALE' : null]);
            foreach ($children as $ci => $c) {
                [$slug, $name, $icon] = $c;
                $cat = Category::create(['parent_id' => $parent->id, 'name' => $name, 'slug' => $slug, 'icon' => $icon, 'sort' => $ci,
                    'sale_badge' => $c[3] ?? null, 'is_featured' => in_array($slug, $featuredSlugs),
                    'description' => "$name chính hãng, giá tốt, giao nhanh và lắp đặt miễn phí."]);
                $rule = $specs[$slug] ?? null;
                $values = null;
                if ($rule) {
                    $attr = $attrCache[$rule[0]] ??= Attribute::create(['name' => $rule[0], 'slug' => Str::slug($rule[0]), 'sort' => count($attrCache)]);
                    $values = collect(array_keys($rule[1]))->mapWithKeys(fn ($v, $i) => [$v => $attr->values()->firstOrCreate(['value' => $v], ['sort' => $i])]);
                }
                $count = $rule ? 14 : 6;
                for ($i = 0; $i < $count; $i++) {
                    $brand = $brands[mt_rand(0, 5)];
                    $spec = $rule ? array_keys($rule[1])[mt_rand(0, count($rule[1]) - 1)] : null;
                    $base = $rule ? $rule[1][$spec] : [0.6e6, 1.5e6, 3.2e6, 7.5e6][mt_rand(0, 3)];
                    $price = (int) (round($base * (0.82 + mt_rand(0, 60) / 100) / 10000) * 10000 - 1000);
                    $compare = (int) (round($price / (1 - mt_rand(5, 42) / 100) / 10000) * 10000);
                    $pname = $rule ? $rule[2]($brand->name, $spec, $i) : "$name {$brand->name} ".['Pro', 'Plus', 'Smart', 'Lite', 'Max'][$i % 5].' '.chr(65 + $i % 7).mt_rand(100, 899);
                    $n++;
                    $p = Product::create([
                        'category_id' => $cat->id, 'brand_id' => $brand->id, 'name' => $pname, 'slug' => Str::slug($pname).'-'.$n, 'sku' => 'SP'.str_pad((string) $n, 5, '0', STR_PAD_LEFT),
                        'short_description' => "$pname: thiết kế gọn, vận hành êm, tiết kiệm điện. Bảo hành chính hãng, đổi trả trong 30 ngày.",
                        'description' => "<p><strong>$pname</strong> phù hợp cho gia đình 3 đến 5 người. Sản phẩm vận hành êm, dễ vệ sinh và có chế độ tiết kiệm điện.</p><ul><li>Giao hàng nhanh trong 2 giờ tại nội thành</li><li>Lắp đặt miễn phí bởi kỹ thuật viên của cửa hàng</li><li>Đổi mới trong 30 ngày nếu lỗi do nhà sản xuất</li></ul>",
                        'price' => $price, 'compare_price' => $compare, 'cost' => (int) round($price * (0.72 + mt_rand(0, 10) / 100), -3),
                        'stock' => mt_rand(0, 12) === 0 ? mt_rand(0, 4) : mt_rand(8, 80), 'warranty' => ['12 tháng', '24 tháng', '36 tháng máy nén'][mt_rand(0, 2)].' chính hãng',
                        'specs' => array_filter(['Thương hiệu' => $brand->name, $rule[0] ?? 'Dòng sản phẩm' => $spec ?? $name, 'Xuất xứ' => ['Việt Nam', 'Thái Lan', 'Malaysia'][mt_rand(0, 2)], 'Năm ra mắt' => (string) mt_rand(2025, 2026)]),
                        'is_featured' => mt_rand(0, 5) === 0, 'sold_count' => mt_rand(20, 1900), 'views' => mt_rand(200, 9000),
                    ]);
                    if ($values) {
                        $p->attributeValues()->attach($values[$spec]->id);
                    }
                    $p->attributeValues()->attach($features->random(mt_rand(0, 2))->pluck('id'));
                    $p->labels()->attach($labels->random(mt_rand(0, 2))->pluck('id'));
                    if ($price > 4e6) {
                        $p->options()->attach([$warrantyOption->id]);
                    }
                    if (in_array($slug, ['dieu-hoa', 'binh-nong-lanh', 'may-loc-nuoc'])) {
                        $p->options()->attach([$installOption->id]);
                    }
                    $products->push($p);
                }
            }
        }
        $bySlug = fn (string $slug) => $products->where('category_id', Category::where('slug', $slug)->value('id'))->values();

        /* ---------- Bộ sưu tập ---------- */
        $winter = Collection::create(['name' => 'Ấm áp ngày lạnh · Deal sưởi & bếp', 'slug' => 'am-ap-ngay-lanh', 'subtitle' => 'Mùa đông 2026', 'show_on_home' => true]);
        $winter->products()->attach(collect(['may-suoi', 'binh-nong-lanh', 'bep-tu', 'am-sieu-toc', 'noi-com-dien', 'lo-nuong'])->flatMap(fn ($s) => $bySlug($s)->take(2)->pluck('id')));
        $best = Collection::create(['name' => 'Bán chạy nhất tháng', 'slug' => 'ban-chay-nhat-thang', 'subtitle' => 'Được mua nhiều', 'show_on_home' => true, 'sort' => 1]);
        $best->products()->attach($products->sortByDesc('sold_count')->take(12)->pluck('id'));

        /* ---------- Flash sale ---------- */
        $flash = FlashSale::create(['name' => 'Giá sốc mỗi ngày', 'subtitle' => 'Black Friday · 27.11', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDays(7)->endOfDay()]);
        foreach (['may-say', 'may-hut-bui', 'noi-chien-khong-dau', 'may-loc-nuoc', 'robot-hut-bui', 'lo-vi-song', 'dieu-hoa', 'tu-lanh', 'tivi', 'may-suoi', 'may-giat-cua-truoc', 'quat'] as $s) {
            $p = $bySlug($s)->first();
            $flash->items()->create(['product_id' => $p->id, 'price' => (int) (round($p->price * (0.6 + mt_rand(0, 25) / 100) / 10000) * 10000 - 1000), 'quantity' => 10, 'sold' => mt_rand(1, 9)]);
        }

        /* ---------- Mã giảm giá ---------- */
        Discount::create(['code' => 'CHAOBAN', 'name' => 'Giảm 5% cho đơn đầu tiên', 'type' => 'percent', 'value' => 5, 'max_discount' => 500000, 'min_order' => 1000000]);
        Discount::create(['code' => 'BLACKFRIDAY', 'name' => 'Black Friday giảm 300.000đ', 'type' => 'fixed', 'value' => 300000, 'min_order' => 5000000, 'usage_limit' => 500, 'ends_at' => now()->addDays(20)]);
        Discount::create(['code' => 'FREESHIP', 'name' => 'Tặng 30.000đ phí giao hàng', 'type' => 'fixed', 'value' => 30000, 'min_order' => 300000]);

        /* ---------- Quảng cáo ---------- */
        $ads = [
            ['home_hero', 'Black Friday', 'Black Friday · 27.11.2026', 'Black Friday', 'Siêu sale cuối năm', 'Giảm tới 50% · Trả góp 0% · Giao lắp miễn phí', 'red', 'washer', '/#flash-sale'],
            ['home_hero', 'Mùa đông', 'Mùa đông 2026', 'Ấm cả nhà', 'Sưởi – bếp từ giảm 45%', 'Máy sưởi từ 890K · Bếp từ đôi 4.490K', 'warm', 'heater', '/danh-muc/may-suoi'],
            ['home_hero', 'Tuần lễ điện lạnh', 'Tuần lễ điện lạnh', 'Lên đời tủ lạnh', 'Thu cũ trợ giá 2 triệu', 'Tủ lạnh từ 4.090K · Bảo hành 24 tháng', 'ice', 'sbs', '/danh-muc/tu-lanh'],
            ['home_side', 'Thu cũ đổi mới', 'Thu cũ đổi mới', 'Trợ giá tới 2.000.000đ', 'Máy giặt · Tủ lạnh · Điều hòa', null, 'dark', 'washer', '/danh-muc/may-giat-cua-truoc'],
            ['home_side', 'Thành viên', 'Ưu đãi thành viên', 'Giảm thêm 5% đơn đầu', 'Nhập mã CHAOBAN ở giỏ hàng', null, 'ice', 'phone', '/dang-ky'],
            ['home_deal', 'Deal lớn (ô giữa)', '01.11 – 30.11.2026', 'Deal nổ lớn', 'lên đời lớn', 'Giảm sốc tới 50%', 'red', 'tv', '/#flash-sale'],
            ['home_deal', 'Máy giặt cửa trước', null, 'Máy giặt cửa trước', null, 'Giá chỉ từ 5.199.000đ', 'red', 'washer', '/danh-muc/may-giat-cua-truoc'],
            ['home_deal', 'Tủ lạnh', null, 'Tủ lạnh 189 lít', null, 'Từ 5.690.000đ', 'dark', 'fridge', '/danh-muc/tu-lanh'],
            ['home_deal', 'Tivi', null, 'Tivi Full HD', null, 'Từ 3.590.000đ', 'ice', 'tv', '/danh-muc/tivi'],
            ['home_deal', 'Nồi cơm', null, 'Nồi cơm điện tử', null, 'Chỉ 799.000đ', 'warm', 'ricecooker', '/danh-muc/noi-com-dien'],
            ['category_top', 'Banner danh mục 1', 'Black Friday · đến 30.11.2026', 'Giảm tới 50% toàn bộ danh mục', 'Trả góp 0% lãi, giao lắp miễn phí trong ngày tại nội thành.', null, 'red', null, null],
            ['category_top', 'Banner danh mục 2', 'Thu cũ đổi mới', 'Lên đời sản phẩm mới', 'Trợ giá tới 2.000.000đ khi đổi sản phẩm cũ bất kỳ hãng nào.', null, 'dark', null, null],
        ];
        foreach ($ads as $i => $a) {
            Ad::create(['position' => $a[0], 'name' => $a[1], 'eyebrow' => $a[2], 'title' => $a[3], 'subtitle' => $a[4], 'badge' => $a[5], 'theme' => $a[6], 'art' => $a[7], 'link' => $a[8], 'sort' => $i]);
        }

        /* ---------- Bài viết ---------- */
        $topics = collect(['Kinh nghiệm mua tivi', 'Kinh nghiệm mua tủ lạnh', 'Kinh nghiệm mua máy giặt', 'Kinh nghiệm mua điều hòa', 'Khuyến mãi'])
            ->mapWithKeys(fn ($t, $i) => [$t => PostCategory::create(['name' => $t, 'slug' => Str::slug($t), 'sort' => $i])]);
        $tags = collect(['Mẹo hay', 'So sánh', 'Tiết kiệm điện', 'Black Friday'])->map(fn ($t) => Tag::create(['name' => $t, 'slug' => Str::slug($t)]));
        $posts = [
            ['Khuyến mãi', 'Black Friday 2026: lịch săn deal điện máy theo từng khung giờ', 'red', 'tv'],
            ['Khuyến mãi', 'Tuần lễ bếp ấm: giảm tới 45% bếp từ, nồi cơm và lò nướng', 'warm', 'stove'],
            ['Kinh nghiệm mua điều hòa', 'Vệ sinh điều hòa tại nhà trong 15 phút: cần chuẩn bị gì?', 'ice', 'ac'],
            ['Kinh nghiệm mua tủ lạnh', 'Tủ lạnh ngăn đá dưới có thật sự tiện hơn ngăn đá trên?', 'dark', 'fridge'],
            ['Kinh nghiệm mua tivi', 'Tivi 55 inch nên ngồi cách bao xa để xem thoải mái nhất?', 'ice', 'tv'],
            ['Kinh nghiệm mua máy giặt', 'Máy giặt cửa trước hay cửa trên: chọn loại nào cho gia đình 4 người?', 'red', 'washer'],
            ['Kinh nghiệm mua tivi', 'Mini LED và OLED khác nhau thế nào? Giải thích dễ hiểu', 'dark', 'tv'],
            ['Kinh nghiệm mua tủ lạnh', 'Cách tính dung tích tủ lạnh theo số người trong nhà', 'ice', 'sbs'],
            ['Kinh nghiệm mua máy giặt', 'Giặt hơi nước diệt khuẩn: khi nào nên dùng, khi nào không?', 'warm', 'washer'],
            ['Kinh nghiệm mua điều hòa', 'Chọn công suất điều hòa theo diện tích phòng: bảng tra nhanh', 'red', 'ac'],
            ['Kinh nghiệm mua tivi', '5 cài đặt hình ảnh nên đổi ngay khi mua tivi mới', 'red', 'tv'],
            ['Kinh nghiệm mua tủ lạnh', 'Đặt tủ lạnh sát tường có tốn điện hơn không?', 'dark', 'fridge'],
            ['Kinh nghiệm mua máy giặt', 'Vì sao máy giặt rung mạnh khi vắt và cách khắc phục', 'ice', 'toploader'],
            ['Kinh nghiệm mua điều hòa', 'Điều hòa Inverter tiết kiệm điện tới đâu? Ví dụ tính tiền điện', 'dark', 'ac'],
            ['Kinh nghiệm mua tivi', 'Có nên mua tivi 75 inch cho phòng khách 18 m²?', 'warm', 'tv'],
            ['Kinh nghiệm mua tủ lạnh', 'Side by Side hay Multi Door: so sánh chi tiết', 'red', 'sbs'],
            ['Kinh nghiệm mua máy giặt', 'Sấy bơm nhiệt có đáng tiền hơn sấy ngưng tụ?', 'dark', 'dryer'],
            ['Kinh nghiệm mua điều hòa', 'Lắp điều hòa ở đâu trong phòng để mát đều và êm?', 'ice', 'ac'],
            ['Khuyến mãi', 'Thu cũ đổi mới: trợ giá tới 2.000.000đ cho máy giặt, tủ lạnh', 'dark', 'washer'],
        ];
        foreach ($posts as $i => [$topic, $title, $theme, $art]) {
            $post = Post::create([
                'post_category_id' => $topics[$topic]->id, 'user_id' => 2, 'title' => $title, 'slug' => Str::slug($title), 'theme' => $theme, 'art' => $art,
                'excerpt' => 'Bài viết tư vấn ngắn gọn, dễ áp dụng từ đội ngũ kỹ thuật của cửa hàng.',
                'content' => "<p>$title. Đây là nội dung mẫu, bạn có thể sửa trong trang quản trị, mục Nội dung, Bài viết.</p><h2>Điểm chính</h2><ul><li>Xác định nhu cầu và diện tích sử dụng trước khi chọn mua.</li><li>So sánh điện năng tiêu thụ theo nhãn năng lượng.</li><li>Ưu tiên sản phẩm có bảo hành và dịch vụ lắp đặt rõ ràng.</li></ul><p>Cần tư vấn thêm, hãy gọi tổng đài hoặc để lại thông tin ở trang Liên hệ.</p>",
                'published_at' => now()->subDays($i * 2 + 1), 'is_featured' => $i < 2, 'views' => mt_rand(120, 5200),
            ]);
            $post->tags()->attach($tags->random(mt_rand(1, 2))->pluck('id'));
        }

        /* ---------- Trang, FAQ, phản hồi ---------- */
        $pages = [['about', 'Giới thiệu công ty'], ['about', 'Hệ thống cửa hàng'], ['about', 'Tuyển dụng'],
            ['policy', 'Phương thức thanh toán'], ['policy', 'Mua hàng trả góp'], ['policy', 'Giao hàng và lắp đặt'], ['policy', 'Bảo hành và đổi trả'], ['policy', 'Bảo mật thông tin'],
            ['support', 'Hướng dẫn mua hàng online'], ['support', 'Bán hàng doanh nghiệp']];
        foreach ($pages as $i => [$group, $title]) {
            Page::create(['title' => $title, 'slug' => Str::slug($title), 'footer_group' => $group, 'sort' => $i,
                'content' => "<p>Nội dung trang <strong>$title</strong> đang là bản mẫu. Vào trang quản trị, mục Nội dung, Trang nội dung để cập nhật theo chính sách thực tế của cửa hàng.</p>"]);
        }
        $faqs = [
            ['Đặt hàng', 'Tôi có cần tạo tài khoản để mua hàng không?', 'Không. Bạn có thể đặt hàng với tư cách khách, chỉ cần họ tên, số điện thoại và địa chỉ nhận hàng. Tạo tài khoản giúp bạn xem lại đơn và lưu sản phẩm yêu thích.'],
            ['Đặt hàng', 'Làm sao để kiểm tra tình trạng đơn hàng?', 'Vào mục Tra cứu đơn hàng, nhập mã đơn và số điện thoại đã dùng khi đặt.'],
            ['Giao hàng', 'Bao lâu thì tôi nhận được hàng?', 'Nội thành giao trong 2 giờ với hàng có sẵn. Các tỉnh khác từ 1 đến 3 ngày làm việc.'],
            ['Giao hàng', 'Phí giao hàng tính thế nào?', 'Miễn phí cho đơn từ 2.000.000đ. Đơn nhỏ hơn tính phí cố định hiển thị ở bước thanh toán.'],
            ['Bảo hành', 'Tôi muốn trả hàng thì làm thế nào?', 'Đăng nhập, mở đơn hàng đã hoàn thành và bấm Yêu cầu trả hàng. Cửa hàng sẽ liên hệ trong 24 giờ.'],
            ['Thanh toán', 'Cửa hàng hỗ trợ những hình thức thanh toán nào?', 'Thanh toán khi nhận hàng, chuyển khoản ngân hàng và trả góp 0% qua thẻ tín dụng.'],
        ];
        foreach ($faqs as $i => [$g, $q, $a]) {
            Faq::create(['group' => $g, 'question' => $q, 'answer' => $a, 'sort' => $i]);
        }
        foreach ([['Chị Thu Hà', 'Mua máy giặt, Hà Nội', 'Đặt buổi sáng, đầu giờ chiều đã có thợ đến lắp. Nhân viên hướng dẫn kỹ từng chế độ giặt.'],
            ['Anh Quốc Bảo', 'Mua điều hòa, Đà Nẵng', 'Giá tốt hơn mấy nơi tôi đã hỏi, lắp đặt gọn gàng, đi ống đẹp.'],
            ['Cô Minh Châu', 'Mua tủ lạnh, TP.HCM', 'Tủ bị móp nhẹ khi vận chuyển, cửa hàng đổi tủ mới ngay hôm sau mà không hỏi nhiều.']] as $i => [$name, $role, $content]) {
            Testimonial::create(['name' => $name, 'role' => $role, 'content' => $content, 'sort' => $i]);
        }
        Contact::create(['name' => 'Trần Văn Nam', 'phone' => '0900000123', 'subject' => 'Hỏi về trả góp', 'message' => 'Tôi muốn mua tivi 65 inch trả góp 12 tháng thì cần giấy tờ gì?']);
        NewsletterSubscriber::create(['email' => 'docgia@example.com']);

        /* ---------- Khách hàng, đơn hàng, lượt truy cập, chi phí (6 tháng) ---------- */
        $ho = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Vũ', 'Đặng', 'Bùi', 'Đỗ', 'Ngô'];
        $ten = ['Văn An', 'Thị Bình', 'Minh Châu', 'Quốc Dũng', 'Thu Hà', 'Gia Huy', 'Ngọc Lan', 'Đức Minh', 'Thanh Nga', 'Hải Phong', 'Bảo Trâm', 'Anh Tuấn'];
        $tinh = ['Hà Nội', 'TP. Hồ Chí Minh', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ', 'Bắc Ninh', 'Đồng Nai'];
        $people = collect(range(1, 140))->map(fn ($i) => [
            'name' => $ho[mt_rand(0, 9)].' '.$ten[mt_rand(0, 11)], 'phone' => '09'.str_pad((string) (10000000 + $i * 7919 % 89999999), 8, '0', STR_PAD_LEFT),
            'province' => $tinh[mt_rand(0, 6)], 'user_id' => null,
        ])->all();
        $people[0] = ['name' => $demoCustomer->name, 'phone' => $demoCustomer->phone, 'province' => 'TP. Hồ Chí Minh', 'user_id' => $demoCustomer->id];
        foreach (range(1, 25) as $i) {
            $u = User::create(['name' => $people[$i]['name'], 'email' => "khach$i@example.com", 'password' => 'password', 'role' => 'customer', 'phone' => $people[$i]['phone'],
                'created_at' => now()->subDays(mt_rand(1, 170))]);
            $people[$i]['user_id'] = $u->id;
        }

        for ($day = 180; $day >= 0; $day--) {
            $date = now()->subDays($day);
            $growth = 1 + (180 - $day) / 150;               // tăng trưởng dần theo thời gian
            $ordersToday = (int) round((mt_rand(0, 30) / 10) * $growth);
            Visit::create(['date' => $date->toDateString(), 'count' => (int) (mt_rand(140, 260) * $growth)]);
            for ($k = 0; $k < $ordersToday; $k++) {
                // 40% là khách cũ quay lại (nhóm 40 người đầu), còn lại là khách bất kỳ
                $person = $people[mt_rand(0, 9) < 4 ? mt_rand(0, 39) : mt_rand(0, 139)];
                $status = match (true) {
                    $day === 0 => 'pending',
                    $day <= 2 => ['pending', 'confirmed', 'shipping'][mt_rand(0, 2)],
                    $day <= 5 => ['shipping', 'completed'][mt_rand(0, 1)],
                    default => mt_rand(0, 99) < 6 ? 'cancelled' : (mt_rand(0, 99) < 3 ? 'returned' : 'completed'),
                };
                $at = $date->copy()->setTime(mt_rand(8, 21), mt_rand(0, 59));
                $order = new Order([
                    'code' => 'DH'.$at->format('ymd').strtoupper(Str::random(5)), 'user_id' => $person['user_id'], 'customer_name' => $person['name'], 'phone' => $person['phone'],
                    'province' => $person['province'], 'address' => mt_rand(1, 200).' Đường Mẫu, '.$person['province'],
                    'payment_method' => ['cod', 'cod', 'bank', 'installment'][mt_rand(0, 3)], 'status' => $status,
                    'payment_status' => $status === 'completed' ? 'paid' : ($status === 'returned' ? 'refunded' : 'unpaid'),
                    'completed_at' => $status === 'completed' ? $at->copy()->addDays(2) : null,
                ]);
                $order->created_at = $order->updated_at = $at;
                $order->save();
                $subtotal = $cost = 0;
                foreach ($products->random(mt_rand(1, 2)) as $p) {
                    $qty = $p->price < 2e6 ? mt_rand(1, 2) : 1;
                    $order->items()->create(['product_id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'price' => $p->price, 'cost' => $p->cost, 'quantity' => $qty]);
                    $subtotal += $p->price * $qty;
                    $cost += $p->cost * $qty;
                }
                $ship = $subtotal >= 2000000 ? 0 : 30000;
                $order->update(['subtotal' => $subtotal, 'cost_total' => $cost, 'shipping_fee' => $ship, 'total' => $subtotal + $ship, 'updated_at' => $at]);
                if (in_array($status, ['shipping', 'completed', 'returned'])) {
                    $order->shipments()->createQuietly(['carrier' => ['Giao hàng nội bộ', 'Giao Hàng Nhanh', 'Viettel Post'][mt_rand(0, 2)], 'tracking_code' => 'VD'.mt_rand(10000000, 99999999),
                        'status' => $status === 'shipping' ? 'in_transit' : 'delivered', 'fee' => 25000, 'shipped_at' => $at->copy()->addHours(5),
                        'delivered_at' => $status === 'shipping' ? null : $at->copy()->addDays(2), 'created_at' => $at, 'updated_at' => $at]);
                }
                if ($status === 'returned') {
                    $order->returns()->create(['user_id' => $person['user_id'], 'reason' => 'Sản phẩm không đúng kích thước mong muốn.', 'status' => 'refunded', 'refund_amount' => $subtotal, 'created_at' => $at->copy()->addDays(4)]);
                }
            }
        }
        // Một yêu cầu trả hàng đang chờ xử lý để thử tính năng
        if ($done = Order::where('status', 'completed')->where('user_id', $demoCustomer->id)->latest('id')->first()) {
            $done->returns()->create(['user_id' => $demoCustomer->id, 'reason' => 'Máy kêu to hơn mô tả, tôi muốn đổi sang mẫu khác.', 'status' => 'requested']);
        }

        for ($m = 5; $m >= 0; $m--) {
            $month = now()->subMonths($m)->startOfMonth();
            Expense::create(['title' => 'Thuê mặt bằng tháng '.$month->format('m/Y'), 'category' => 'Mặt bằng', 'amount' => 25000000, 'spent_at' => $month->copy()->addDays(1)]);
            Expense::create(['title' => 'Lương nhân viên tháng '.$month->format('m/Y'), 'category' => 'Lương nhân viên', 'amount' => 48000000, 'spent_at' => $month->copy()->addDays(4)]);
            foreach ([6, 13, 20, 27] as $d) {
                if ($month->copy()->addDays($d)->isPast()) {
                    Expense::create(['title' => 'Quảng cáo Facebook, Google', 'category' => 'Quảng cáo', 'amount' => mt_rand(30, 70) * 100000, 'spent_at' => $month->copy()->addDays($d)]);
                }
            }
        }

        /* ---------- Đánh giá ---------- */
        $comments = ['Hàng đúng mô tả, chạy êm. Giao nhanh hơn dự kiến.', 'Giá tốt, nhân viên lắp đặt nhiệt tình.', 'Dùng một tuần thấy ổn, tiết kiệm điện rõ.', 'Đóng gói cẩn thận, sẽ ủng hộ tiếp.', 'Sản phẩm tốt nhưng giao trễ nửa ngày.'];
        foreach ($products->random(90) as $p) {
            foreach (range(1, mt_rand(1, 3)) as $_) {
                Review::withoutEvents(fn () => Review::create(['product_id' => $p->id, 'name' => $ho[mt_rand(0, 9)].' '.$ten[mt_rand(0, 11)], 'rating' => mt_rand(0, 9) < 8 ? 5 : mt_rand(3, 4),
                    'comment' => $comments[mt_rand(0, 4)], 'status' => 'approved', 'created_at' => now()->subDays(mt_rand(1, 120))]));
            }
            $p->refreshRating();
        }
        Review::withoutEvents(fn () => Review::create(['product_id' => $products->first()->id, 'name' => 'Lê Thanh Nga', 'rating' => 4, 'comment' => 'Máy chạy tốt, mong cửa hàng có thêm màu trắng.', 'status' => 'pending']));
    }
}
