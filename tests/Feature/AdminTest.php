<?php

namespace Tests\Feature;

use App\Filament\Pages\Settings;
use App\Filament\Resources;
use App\Models;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Kiểm tra các thao tác quản trị chính: tạo, sửa, xử lý đơn, kho, marketing, cấu hình. */
class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('role', 'super_admin')->first());
    }

    public function test_create_product_and_adjust_stock(): void
    {
        Livewire::test(Resources\Products\Pages\CreateProduct::class)
            ->fillForm([
                'name' => 'Máy lọc nước thử nghiệm', 'slug' => 'may-loc-nuoc-thu-nghiem', 'sku' => 'TEST001',
                'category_id' => Models\Category::where('slug', 'may-loc-nuoc')->value('id'), 'price' => 3990000, 'cost' => 3000000, 'stock' => 10, 'status' => 'published',
            ])->call('create')->assertHasNoFormErrors();
        $product = Models\Product::where('sku', 'TEST001')->firstOrFail();
        $this->assertSame(10, $product->stock);
        $this->assertSame(1, $product->inventoryMovements()->count(), 'Tồn kho ban đầu phải được ghi vào lịch sử kho');

        Livewire::test(Resources\Products\Pages\ListProducts::class)
            ->callAction(TestAction::make('stock')->table($product), ['type' => 'in', 'quantity' => 5, 'note' => 'Nhập thêm'])
            ->assertHasNoFormErrors();
        $this->assertSame(15, $product->fresh()->stock);

        Livewire::test(Resources\Products\Pages\EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm(['price' => 3790000])->call('save')->assertHasNoFormErrors();
        $this->assertSame(3790000, $product->fresh()->price);
        $this->get('/san-pham/may-loc-nuoc-thu-nghiem')->assertOk()->assertSee('3.790.000đ');
    }

    public function test_order_lifecycle_actions(): void
    {
        $order = Models\Order::where('status', 'pending')->firstOrFail();
        $list = Livewire::test(Resources\Orders\Pages\ListOrders::class);
        $list->callAction(TestAction::make('confirm')->table($order));
        $this->assertSame('confirmed', $order->fresh()->status);

        $list->callAction(TestAction::make('ship')->table($order), ['carrier' => 'Viettel Post', 'tracking_code' => 'VD123', 'fee' => 30000]);
        $this->assertSame('shipping', $order->fresh()->status);

        $list->callAction(TestAction::make('invoice')->table($order), ['company_name' => 'Công ty A', 'tax_code' => '0101', 'address' => 'HN']);
        $this->assertSame(1, $order->invoices()->count());

        $shipment = $order->shipments()->first();
        Livewire::test(Resources\Shipments\Pages\ManageShipments::class)->callAction(TestAction::make('delivered')->table($shipment));
        $this->assertSame('completed', $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);

        $this->get(route('admin.orders.print', $order))->assertOk()->assertSee($order->code);
        $this->get(route('admin.invoices.print', $order->invoices()->first()))->assertOk()->assertSee('HÓA ĐƠN');
    }

    public function test_create_order_from_admin_deducts_stock(): void
    {
        $product = Models\Product::where('stock', '>', 3)->firstOrFail();
        $stock = $product->stock;
        Livewire::test(Resources\Orders\Pages\CreateOrder::class)
            ->fillForm([
                'customer_name' => 'Khách tại quầy', 'phone' => '0911111111', 'address' => 'Cửa hàng', 'payment_method' => 'cod', 'payment_status' => 'paid',
                'items' => [['product_id' => $product->id, 'name' => $product->name, 'price' => $product->price, 'quantity' => 2, 'sku' => $product->sku, 'cost' => $product->cost]],
            ])->call('create')->assertHasNoFormErrors();
        $order = Models\Order::latest('id')->first();
        $this->assertSame($product->price * 2, (int) $order->total);
        $this->assertSame($stock - 2, $product->fresh()->stock);
    }

    public function test_return_request_flow(): void
    {
        $return = Models\OrderReturn::where('status', 'requested')->firstOrFail();
        $page = Livewire::test(Resources\OrderReturns\Pages\ManageOrderReturns::class);
        $page->callAction(TestAction::make('approve')->table($return));
        $this->assertSame('approved', $return->fresh()->status);
        $page->callAction(TestAction::make('refund')->table($return));
        $this->assertSame('refunded', $return->fresh()->status);
        $this->assertSame('returned', $return->order->fresh()->status);
    }

    public function test_marketing_modules(): void
    {
        Livewire::test(Resources\Discounts\Pages\ManageDiscounts::class)
            ->callAction('create', ['code' => 'tet2027', 'name' => 'Tết', 'type' => 'percent', 'value' => 10, 'min_order' => 0, 'is_active' => true])
            ->assertHasNoFormErrors();
        $this->assertTrue(Models\Discount::where('code', 'TET2027')->exists());

        $review = Models\Review::where('status', 'pending')->firstOrFail();
        Livewire::test(Resources\Reviews\Pages\ManageReviews::class)->callAction(TestAction::make('approve')->table($review));
        $this->assertSame('approved', $review->fresh()->status);

        $product = Models\Product::first();
        Livewire::test(Resources\FlashSales\Pages\CreateFlashSale::class)
            ->fillForm(['name' => 'Sale trưa', 'starts_at' => now()->subHour(), 'ends_at' => now()->addHours(3), 'is_active' => true,
                'items' => [['product_id' => $product->id, 'price' => 1000000, 'quantity' => 5, 'sold' => 0]]])
            ->call('create')->assertHasNoFormErrors();

        Livewire::test(Resources\Ads\Pages\ManageAds::class)
            ->callAction('create', ['name' => 'Banner mới', 'position' => 'home_hero', 'title' => 'Sale Tết', 'theme' => 'red', 'sort' => 0, 'is_active' => true])
            ->assertHasNoFormErrors();
        $this->get('/')->assertSee('Sale Tết');
    }

    public function test_content_modules(): void
    {
        Livewire::test(Resources\Posts\Pages\CreatePost::class)
            ->fillForm(['title' => 'Bài viết thử', 'slug' => 'bai-viet-thu', 'status' => 'published', 'published_at' => now()->subMinute(), 'content' => '<p>Nội dung</p>'])
            ->call('create')->assertHasNoFormErrors();
        $this->get('/tin-tuc/bai-viet-thu')->assertOk();

        Livewire::test(Resources\Pages\Pages\CreatePage::class)
            ->fillForm(['title' => 'Chính sách mới', 'slug' => 'chinh-sach-moi', 'footer_group' => 'policy', 'is_active' => true, 'content' => '<p>A</p>'])
            ->call('create')->assertHasNoFormErrors();
        $this->get('/')->assertSee('Chính sách mới');

        Livewire::test(Resources\Faqs\Pages\ManageFaqs::class)
            ->callAction('create', ['group' => 'Chung', 'question' => 'Có xuất hóa đơn VAT không?', 'answer' => 'Có.', 'sort' => 0, 'is_active' => true])
            ->assertHasNoFormErrors();
        $this->get('/hoi-dap')->assertSee('Có xuất hóa đơn VAT không?');
    }

    public function test_categories_brands_attributes(): void
    {
        $parent = Models\Category::whereNull('parent_id')->first();
        Livewire::test(Resources\Categories\Pages\ManageCategories::class)
            ->callAction('create', ['name' => 'Máy pha cà phê', 'slug' => 'may-pha-ca-phe', 'parent_id' => $parent->id, 'icon' => 'kettle', 'sort' => 9, 'is_active' => true])
            ->assertHasNoFormErrors();
        $this->get('/danh-muc/may-pha-ca-phe')->assertOk();
        $this->get('/')->assertSee('Máy pha cà phê');

        Livewire::test(Resources\Brands\Pages\ManageBrands::class)
            ->callAction('create', ['name' => 'Hanami', 'slug' => 'hanami', 'sort' => 0, 'is_active' => true])->assertHasNoFormErrors();

        Livewire::test(Resources\Attributes\Pages\ManageAttributes::class)
            ->callAction('create', ['name' => 'Màu sắc', 'slug' => 'mau-sac', 'is_filterable' => true, 'sort' => 5, 'values' => [['value' => 'Trắng'], ['value' => 'Đen']]])
            ->assertHasNoFormErrors();
        $this->assertSame(2, Models\Attribute::where('slug', 'mau-sac')->first()->values()->count());
    }

    public function test_settings_and_staff(): void
    {
        Livewire::test(Settings::class)->fillForm(['site_name' => 'Điện Máy Hoàng Gia', 'hotline_sales' => '1900 1234'])->call('save')->assertHasNoFormErrors();
        $this->get('/')->assertSee('Điện Máy Hoàng Gia')->assertSee('1900 1234');

        Livewire::test(Resources\Staff\Pages\ManageStaff::class)
            ->callAction('create', ['name' => 'NV Kho', 'email' => 'kho@example.com', 'password' => 'matkhau123', 'role' => 'admin', 'is_active' => true])
            ->assertHasNoFormErrors();
        $this->assertTrue(User::where('email', 'kho@example.com')->where('role', 'admin')->exists());
    }

    public function test_dashboard_widgets_render(): void
    {
        foreach ([\App\Filament\Widgets\SalesStats::class, \App\Filament\Widgets\SalesChart::class, \App\Filament\Widgets\NewCustomersChart::class,
            \App\Filament\Widgets\RetentionChart::class, \App\Filament\Widgets\LatestOrders::class, \App\Filament\Widgets\TopProducts::class, \App\Filament\Widgets\LowStock::class] as $widget) {
            Livewire::test($widget, ['pageFilters' => ['period' => '90']])->assertOk();
        }
    }

    public function test_uploaded_images_replace_icons_and_banners(): void
    {
        Storage::fake('public');
        $category = Models\Category::where('slug', 'tivi')->first();
        Livewire::test(Resources\Categories\Pages\ManageCategories::class)
            ->callAction(TestAction::make('edit')->table($category), [
                'image' => UploadedFile::fake()->image('icon.png', 200, 200),
                'banner' => UploadedFile::fake()->image('banner.jpg', 1200, 300),
            ])->assertHasNoFormErrors();
        $category->refresh();
        $this->assertNotNull($category->image);
        $this->assertNotNull($category->banner);
        $this->get('/')->assertSee('storage/'.$category->image);                 // menu + ô danh mục nổi bật
        $this->get('/danh-muc/tivi')->assertSee('storage/'.$category->banner);   // banner riêng của danh mục

        Livewire::test(Resources\Ads\Pages\ManageAds::class)
            ->callAction('create', ['name' => 'Ảnh hero', 'position' => 'home_hero', 'theme' => 'red', 'sort' => -1, 'is_active' => true,
                'image' => UploadedFile::fake()->image('hero.jpg', 1200, 450), 'image_mobile' => UploadedFile::fake()->image('hero-m.jpg', 800, 600)])
            ->assertHasNoFormErrors();
        $ad = Models\Ad::where('name', 'Ảnh hero')->first();
        $this->get('/')->assertSee('storage/'.$ad->image)->assertSee('storage/'.$ad->image_mobile);

        Livewire::test(Settings::class)->fillForm(['logo' => UploadedFile::fake()->image('logo.png', 240, 80)])->call('save')->assertHasNoFormErrors();
        $this->assertNotEmpty(Models\Setting::get('logo'));
        $this->get('/')->assertSee('storage/'.Models\Setting::get('logo'));
    }
}
