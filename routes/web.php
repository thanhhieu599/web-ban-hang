<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PrintController;
use App\Http\Middleware\TrackVisit;
use Illuminate\Support\Facades\Route;

Route::middleware(TrackVisit::class)->group(function () {
    // Khách vãng lai: xem, tìm kiếm, mua hàng không cần tài khoản
    Route::get('/', HomeController::class)->name('home');
    Route::get('/danh-muc/{slug}', [CatalogController::class, 'category'])->name('category');
    Route::get('/san-pham/{slug}', [CatalogController::class, 'product'])->name('product');
    Route::get('/tim-kiem', [CatalogController::class, 'search'])->name('search');
    Route::get('/so-sanh', [CatalogController::class, 'compare'])->name('compare');

    Route::get('/gio-hang', [CartController::class, 'index'])->name('cart');
    Route::get('/thanh-toan', [CartController::class, 'checkout'])->name('checkout');
    Route::get('/dat-hang-thanh-cong/{code}', [CartController::class, 'success'])->name('order.success');
    Route::get('/tra-cuu-don-hang', [CartController::class, 'track'])->name('track');

    Route::get('/tin-tuc', [ContentController::class, 'news'])->name('news');
    Route::get('/tin-tuc/chuyen-muc/{topic}', [ContentController::class, 'news'])->name('news.topic');
    Route::get('/tin-tuc/{slug}', [ContentController::class, 'post'])->name('news.show');
    Route::get('/trang/{slug}', [ContentController::class, 'page'])->name('page');
    Route::get('/hoi-dap', [ContentController::class, 'faq'])->name('faq');
    Route::get('/lien-he', [ContentController::class, 'contact'])->name('contact');
});

Route::post('/gio-hang/them', [CartController::class, 'add'])->name('cart.add');
Route::post('/gio-hang/cap-nhat', [CartController::class, 'update'])->name('cart.update');
Route::post('/gio-hang/ma-giam-gia', [CartController::class, 'coupon'])->name('cart.coupon');
Route::post('/thanh-toan', [CartController::class, 'place'])->middleware('throttle:10,1')->name('checkout.place');
Route::post('/lien-he', [ContentController::class, 'sendContact'])->middleware('throttle:5,1')->name('contact.send');
Route::post('/nhan-tin', [ContentController::class, 'newsletter'])->middleware('throttle:5,1')->name('newsletter');
Route::post('/yeu-thich', [AccountController::class, 'toggleWishlist'])->name('wishlist.toggle');
Route::get('/qc/{ad}', [ContentController::class, 'adClick'])->name('ad.click');
Route::get('/sitemap.xml', [ContentController::class, 'sitemap'])->name('sitemap');

Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AccountController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AccountController::class, 'login'])->middleware('throttle:8,1');
    Route::get('/dang-ky', [AccountController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AccountController::class, 'register'])->middleware('throttle:5,1');
    Route::get('/quen-mat-khau', [PasswordController::class, 'request'])->name('password.request');
    Route::post('/quen-mat-khau', [PasswordController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/dat-lai-mat-khau/{token}', [PasswordController::class, 'reset'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [PasswordController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
});

// Khách hàng đã đăng nhập
Route::middleware('auth')->group(function () {
    Route::post('/dang-xuat', [AccountController::class, 'logout'])->name('logout');
    Route::get('/tai-khoan', [AccountController::class, 'index'])->name('account');
    Route::post('/tai-khoan', [AccountController::class, 'updateProfile'])->name('account.update');
    Route::post('/tai-khoan/mat-khau', [PasswordController::class, 'change'])->middleware('throttle:5,1')->name('account.password');
    Route::get('/tai-khoan/yeu-thich', [AccountController::class, 'wishlist'])->name('account.wishlist');
    Route::get('/tai-khoan/don-hang/{code}', [AccountController::class, 'order'])->name('account.order');
    Route::post('/tai-khoan/don-hang/{code}/tra-hang', [AccountController::class, 'requestReturn'])->name('account.return');
    Route::post('/tai-khoan/don-hang/{code}/huy', [AccountController::class, 'cancelOrder'])->name('account.cancel');
    Route::post('/san-pham/{product}/danh-gia', [CatalogController::class, 'review'])->middleware('throttle:5,1')->name('product.review');
});

// Trang in dành cho nhân viên
Route::middleware(['auth', 'can:staff'])->prefix('admin/in')->group(function () {
    Route::get('/don-hang/{order}', [PrintController::class, 'order'])->name('admin.orders.print');
    Route::get('/hoa-don/{invoice}', [PrintController::class, 'invoice'])->name('admin.invoices.print');
    Route::get('/xuat/{type}', ExportController::class)->name('admin.export');
});
