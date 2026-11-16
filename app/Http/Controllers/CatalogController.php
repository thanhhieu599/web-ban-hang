<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public const PRICE_RANGES = [
        '0-2' => ['Dưới 2 triệu', 0, 2000000], '2-5' => ['2 – 5 triệu', 2000000, 5000000], '5-10' => ['5 – 10 triệu', 5000000, 10000000],
        '10-20' => ['10 – 20 triệu', 10000000, 20000000], '20-' => ['Trên 20 triệu', 20000000, PHP_INT_MAX],
    ];

    public const SORTS = ['ban-chay' => 'Bán chạy', 'gia-tang' => 'Giá thấp → cao', 'gia-giam' => 'Giá cao → thấp', 'moi-nhat' => 'Mới nhất'];

    public function category(Request $request, string $slug)
    {
        $category = Category::active()->where('slug', $slug)->with(['parent.children', 'children'])->firstOrFail();
        // Danh mục cấp 1 gom sản phẩm của tất cả danh mục con
        $ids = $category->parent_id ? [$category->id] : $category->children->pluck('id')->push($category->id)->all();
        $base = Product::published()->whereIn('category_id', $ids);

        return $this->listing($request, $base, [
            'category' => $category,
            'siblings' => $category->parent_id ? $category->parent->children->where('is_active', true) : $category->children->where('is_active', true),
            'title' => $category->name,
            'banners' => Ad::live('category_top')->get(),
            'posts' => Post::published()->with('category')->latest('published_at')->take(4)->get(),
        ]);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $base = Product::published()->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
            foreach (preg_split('/\s+/', $q) as $word) {
                $w->where('name', 'like', '%'.$word.'%');
            }
        }));

        return $this->listing($request, $base, ['category' => null, 'siblings' => collect(), 'title' => $q !== '' ? "Kết quả cho \"$q\"" : 'Tất cả sản phẩm', 'banners' => collect(), 'posts' => collect(), 'q' => $q]);
    }

    protected function listing(Request $request, $base, array $data)
    {
        $productIds = (clone $base)->pluck('id');
        $brands = Brand::where('is_active', true)->whereIn('id', (clone $base)->distinct()->pluck('brand_id')->filter())->orderBy('sort')->get();
        $attributes = Attribute::where('is_filterable', true)->orderBy('sort')
            ->with(['values' => fn ($q) => $q->whereHas('products', fn ($p) => $p->whereIn('products.id', $productIds))])
            ->get()->filter(fn ($a) => $a->values->isNotEmpty())->values();

        $selBrands = array_filter((array) $request->query('thuong-hieu', []));
        $selValues = array_map('intval', array_filter((array) $request->query('loc', [])));
        $price = self::PRICE_RANGES[$request->query('gia')] ?? null;
        $sort = array_key_exists($request->query('sap-xep'), self::SORTS) ? $request->query('sap-xep') : 'ban-chay';

        $query = $base->with(['category', 'labels'])
            ->when($selBrands, fn ($q) => $q->whereIn('brand_id', Brand::whereIn('slug', $selBrands)->pluck('id')))
            ->when($price, fn ($q) => $q->whereBetween('price', [$price[1], $price[2] - 1]));
        // Mỗi nhóm thuộc tính là một điều kiện VÀ, các giá trị trong cùng nhóm là HOẶC
        foreach ($attributes as $attribute) {
            $chosen = $attribute->values->pluck('id')->intersect($selValues);
            if ($chosen->isNotEmpty()) {
                $query->whereHas('attributeValues', fn ($q) => $q->whereIn('attribute_values.id', $chosen));
            }
        }
        match ($sort) {
            'gia-tang' => $query->orderBy('price'),
            'gia-giam' => $query->orderByDesc('price'),
            'moi-nhat' => $query->latest('id'),
            default => $query->orderByDesc('sold_count'),
        };

        return view('category', $data + [
            'products' => $query->paginate(20)->withQueryString(),
            'brands' => $brands, 'attributes' => $attributes,
            'selBrands' => $selBrands, 'selValues' => $selValues, 'selPrice' => $request->query('gia'), 'sort' => $sort,
        ]);
    }

    public function product(string $slug)
    {
        $product = Product::published()->where('slug', $slug)
            ->with(['category.parent', 'brand', 'labels', 'options', 'attributeValues.attribute'])->firstOrFail();
        $product->increment('views');

        return view('product', [
            'product' => $product,
            'reviews' => $product->approvedReviews()->take(20)->get(),
            'related' => Product::published()->with(['category', 'labels'])->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)->orderByDesc('sold_count')->take(6)->get(),
            'wished' => auth()->check() && auth()->user()->wishlists()->where('product_id', $product->id)->exists(),
        ]);
    }

    public function review(Request $request, Product $product)
    {
        $data = $request->validate(['rating' => 'required|integer|between:1,5', 'comment' => 'required|string|min:10|max:1000'], [], ['rating' => 'số sao', 'comment' => 'nội dung']);
        Review::create($data + ['product_id' => $product->id, 'user_id' => $request->user()->id, 'name' => $request->user()->name, 'status' => 'pending']);

        return back()->with('flash', 'Cảm ơn bạn. Đánh giá sẽ hiển thị sau khi được duyệt.');
    }

    public function compare(Request $request)
    {
        $ids = array_slice(array_filter(array_map('intval', explode(',', (string) $request->query('ids')))), 0, 3);
        $products = Product::published()->with(['brand', 'category', 'attributeValues.attribute'])->whereIn('id', $ids)->get();
        $specKeys = $products->flatMap(fn ($p) => array_keys($p->specs ?? []))->unique()->values();

        return view('compare', compact('products', 'specKeys'));
    }
}
