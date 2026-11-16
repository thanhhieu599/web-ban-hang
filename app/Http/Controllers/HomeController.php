<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Category;
use App\Models\Collection;
use App\Models\FlashSale;
use App\Models\Post;
use App\Models\Product;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function __invoke()
    {
        $with = ['category', 'labels'];
        $flash = FlashSale::current();
        $flashProducts = $flash
            ? Product::published()->with([...$with, 'category.parent'])->whereIn('id', $flash->items->pluck('product_id'))->get()->filter->flashItem()->values()
            : collect();

        $offerTabs = Category::active()->where('is_featured', true)->whereNotNull('sale_badge')->orderBy('sort')->take(5)->get()
            ->each(fn ($c) => $c->setRelation('products', Product::published()->with($with)->where('category_id', $c->id)->orderByDesc('sold_count')->take(12)->get()))
            ->filter(fn ($c) => $c->products->isNotEmpty())->values();

        return view('home', [
            'heroAds' => Ad::live('home_hero')->get(),
            'sideAds' => Ad::live('home_side')->take(2)->get(),
            'dealAds' => Ad::live('home_deal')->take(5)->get(),
            'tiles' => Category::active()->where('is_featured', true)->orderBy('parent_id')->orderBy('sort')->take(16)->get(),
            'flash' => $flash,
            'flashProducts' => $flashProducts,
            'offerTabs' => $offerTabs,
            'collections' => Collection::where('is_active', true)->where('show_on_home', true)->orderBy('sort')
                ->with(['products' => fn ($q) => $q->where('status', 'published')->with($with)->take(12)])->get(),
            'posts' => Post::published()->with('category')->latest('published_at')->take(4)->get(),
            'testimonials' => Testimonial::where('is_active', true)->orderBy('sort')->take(3)->get(),
        ]);
    }
}
