<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_storefront_pages_load(): void
    {
        foreach (['/', '/danh-muc/dieu-hoa', '/danh-muc/dien-lanh', '/tim-kiem?q=tivi', '/tin-tuc', '/gio-hang', '/hoi-dap', '/lien-he', '/dang-nhap', '/sitemap.xml'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/san-pham/'.Product::first()->slug)->assertOk();
    }

    public function test_guest_can_checkout_and_stock_is_reduced(): void
    {
        $product = Product::where('stock', '>', 2)->first();
        $stock = $product->stock;

        $this->postJson('/gio-hang/them', ['product_id' => $product->id, 'quantity' => 2])->assertOk()->assertJson(['count' => 2]);
        $this->post('/thanh-toan', [
            'customer_name' => 'Khách Thử', 'phone' => '0912345678', 'province' => 'Hà Nội', 'address' => '1 Đường Thử', 'payment_method' => 'cod',
        ])->assertRedirect();

        $order = Order::latest('id')->first();
        $this->assertSame('pending', $order->status);
        $this->assertSame($stock - 2, $product->fresh()->stock);

        $order->changeStatus('cancelled');
        $this->assertSame($stock, $product->fresh()->stock);
    }

    public function test_customer_cannot_open_admin(): void
    {
        $this->actingAs(User::where('role', 'customer')->first())->get('/admin')->assertForbidden();
    }

    public function test_admin_can_run_shop_but_not_system_settings(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/orders')->assertOk();
        $this->actingAs($admin)->get('/admin/settings')->assertForbidden();
        $this->actingAs($admin)->get('/admin/staff')->assertForbidden();
    }

    public function test_super_admin_can_open_system_settings(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->first())->get('/admin/settings')->assertOk();
    }
}
