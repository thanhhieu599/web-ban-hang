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
