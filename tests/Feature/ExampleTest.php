<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** Sản phẩm còn hàng và không nằm trong flash sale (để số lượng mua không bị giới hạn theo suất). */
    protected function regularProduct(): Product
    {
        $flashIds = \App\Models\FlashSale::current()?->items->pluck('product_id') ?? collect();

        return Product::where('stock', '>', 5)->whereNotIn('id', $flashIds)->firstOrFail();
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
        $product = $this->regularProduct();
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

    public function test_flash_sale_quantity_is_capped(): void
    {
        $item = \App\Models\FlashSale::current()->items->first();
        $item->update(['quantity' => 10, 'sold' => 9]);
        \App\Models\FlashSale::forgetCurrent();
        $this->postJson('/gio-hang/them', ['product_id' => $item->product_id, 'quantity' => 5])->assertOk()->assertJson(['count' => 1]);
    }

    public function test_order_emails_are_sent(): void
    {
        Mail::fake();
        $product = $this->regularProduct();
        $this->postJson('/gio-hang/them', ['product_id' => $product->id]);
        $this->post('/thanh-toan', ['customer_name' => 'A', 'phone' => '0912345678', 'email' => 'a@example.com', 'province' => 'HN', 'address' => 'x', 'payment_method' => 'bank']);
        Mail::assertSent(\App\Mail\OrderMail::class, fn ($m) => $m->kind === 'placed' && $m->hasTo('a@example.com'));
        Mail::assertSent(\App\Mail\OrderMail::class, fn ($m) => $m->kind === 'new');
        Order::latest('id')->first()->changeStatus('confirmed');
        Mail::assertSent(\App\Mail\OrderMail::class, fn ($m) => $m->kind === 'confirmed');
    }

    public function test_password_reset_and_change(): void
    {
        Notification::fake();
        $user = User::where('email', 'khach@example.com')->first();
        $this->post('/quen-mat-khau', ['email' => $user->email])->assertRedirect();
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($n) use (&$token) { $token = $n->token; return true; });
        $this->get('/dat-lai-mat-khau/'.$token.'?email='.$user->email)->assertOk();
        $this->post('/dat-lai-mat-khau', ['token' => $token, 'email' => $user->email, 'password' => 'matkhaumoi1', 'password_confirmation' => 'matkhaumoi1'])->assertRedirect('/dang-nhap');
        $this->post('/dang-nhap', ['email' => $user->email, 'password' => 'matkhaumoi1'])->assertRedirect();
        $this->post('/tai-khoan/mat-khau', ['current_password' => 'sai', 'password' => 'abcdefgh9', 'password_confirmation' => 'abcdefgh9'])->assertSessionHasErrors('current_password');
        $this->post('/tai-khoan/mat-khau', ['current_password' => 'matkhaumoi1', 'password' => 'abcdefgh9', 'password_confirmation' => 'abcdefgh9'])->assertSessionHasNoErrors();
    }

    public function test_custom_404_and_exports(): void
    {
        $this->get('/khong-co-trang-nay')->assertNotFound()->assertSee('Không tìm thấy trang');
        $this->get('/admin/in/xuat/don-hang')->assertRedirect();
        $this->actingAs(User::where('role', 'admin')->first());
        foreach (['don-hang', 'san-pham', 'khach-hang', 'email-nhan-tin'] as $type) {
            $csv = $this->get('/admin/in/xuat/'.$type)->assertOk()->streamedContent();
            $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
            $this->assertGreaterThan(1, substr_count($csv, "\n"));
        }
        $this->get('/admin/orders?filters[user_id][value]=3')->assertOk();
    }

    public function test_customer_can_request_return_once(): void
    {
        $user = User::where('email', 'khach@example.com')->first();
        $order = Order::where('user_id', '!=', $user->id)->where('status', 'completed')->whereDoesntHave('returns')->firstOrFail();
        $order->update(['user_id' => $user->id]);
        $this->actingAs($user)->get('/tai-khoan/don-hang/'.$order->code)->assertOk()->assertSee('Yêu cầu trả hàng');
        $this->post('/tai-khoan/don-hang/'.$order->code.'/tra-hang', ['reason' => 'Sản phẩm bị trầy khi nhận hàng.'])->assertSessionHasNoErrors();
        $this->assertSame(1, $order->returns()->count());
        $this->post('/tai-khoan/don-hang/'.$order->code.'/tra-hang', ['reason' => 'Gửi lại lần nữa xem sao.']);
        $this->assertSame(1, $order->returns()->count(), 'Không cho gửi trùng yêu cầu đang xử lý');
        $other = Order::where('user_id', '!=', $user->id)->first();
        $this->get('/tai-khoan/don-hang/'.$other->code)->assertNotFound();
    }
}
