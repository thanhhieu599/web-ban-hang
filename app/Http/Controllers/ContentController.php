<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Contact;
use App\Models\Faq;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function news(?string $topic = null)
    {
        $topics = PostCategory::orderBy('sort')->get();
        $current = $topic ? $topics->firstWhere('slug', $topic) ?? abort(404) : null;

        if ($current) {
            return view('news.index', ['topics' => $topics, 'current' => $current,
                'posts' => Post::published()->with('category')->where('post_category_id', $current->id)->latest('published_at')->paginate(12)]);
        }

        $latest = Post::published()->with('category')->latest('published_at')->take(5)->get();

        return view('news.index', [
            'topics' => $topics, 'current' => null, 'latest' => $latest,
            'sections' => $topics->map(fn ($t) => [$t, Post::published()->with('category')->where('post_category_id', $t->id)->latest('published_at')->take(6)->get()])
                ->filter(fn ($s) => $s[1]->isNotEmpty()),
        ]);
    }

    public function post(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->with(['category', 'tags', 'author'])->firstOrFail();
        $post->increment('views');

        return view('news.show', ['post' => $post,
            'related' => Post::published()->with('category')->where('post_category_id', $post->post_category_id)->whereKeyNot($post->id)->latest('published_at')->take(4)->get()]);
    }

    public function page(string $slug)
    {
        return view('page', ['page' => Page::where('is_active', true)->where('slug', $slug)->firstOrFail()]);
    }

    public function faq()
    {
        return view('faq', ['groups' => Faq::where('is_active', true)->orderBy('sort')->get()->groupBy('group')]);
    }

    public function contact()
    {
        return view('contact');
    }

    public function sendContact(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:120', 'phone' => 'required_without:email|nullable|string|max:20', 'email' => 'nullable|email',
            'subject' => 'nullable|string|max:160', 'message' => 'required|string|min:10|max:2000'],
            ['phone.required_without' => 'Vui lòng nhập số điện thoại hoặc email để cửa hàng liên hệ lại.'], ['name' => 'họ tên', 'message' => 'nội dung']);
        Contact::create($data);

        return back()->with('flash', 'Đã gửi. Cửa hàng sẽ liên hệ lại trong giờ làm việc.');
    }

    public function newsletter(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:160'], [], ['email' => 'email']);
        NewsletterSubscriber::updateOrCreate(['email' => strtolower($data['email'])], ['is_active' => true]);

        return back()->with('flash', 'Đã đăng ký nhận tin khuyến mãi.');
    }

    public function adClick(Ad $ad)
    {
        $ad->increment('clicks');

        return redirect($ad->link ?: '/');
    }

    public function sitemap()
    {
        $urls = collect([url('/'), route('news'), route('faq'), route('contact')])
            ->merge(Category::active()->pluck('slug')->map(fn ($s) => route('category', $s)))
            ->merge(Product::published()->pluck('slug')->map(fn ($s) => route('product', $s)))
            ->merge(Post::published()->pluck('slug')->map(fn ($s) => route('news.show', $s)))
            ->merge(Page::where('is_active', true)->pluck('slug')->map(fn ($s) => route('page', $s)));

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }
}
