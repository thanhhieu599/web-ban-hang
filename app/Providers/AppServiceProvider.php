<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Services\Cart;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('staff', fn (User $user) => $user->is_active && $user->isStaff());
        Paginator::defaultView('partials.pagination');

        // Email đặt lại mật khẩu bằng tiếng Việt
        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(fn ($user, string $token) => (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Đặt lại mật khẩu · '.setting('site_name'))
            ->greeting('Chào '.$user->name.',')
            ->line('Bạn (hoặc ai đó) vừa yêu cầu đặt lại mật khẩu cho tài khoản này.')
            ->action('Đặt lại mật khẩu', route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->line('Liên kết có hiệu lực trong 60 phút. Nếu bạn không yêu cầu, hãy bỏ qua email này.')
            ->salutation(setting('site_name')));

        // Dữ liệu dùng chung cho mọi trang của cửa hàng: menu 2 cấp, cấu hình, giỏ hàng, link chân trang
        View::composer('layouts.app', function ($view) {
            $view->with([
                'menu' => Category::tree(),
                'site' => Setting::allCached(),
                'cartCount' => Cart::count(),
                'footerPages' => Page::where('is_active', true)->whereNotNull('footer_group')->orderBy('sort')->get(['title', 'slug', 'footer_group'])->groupBy('footer_group'),
            ]);
        });
    }
}
