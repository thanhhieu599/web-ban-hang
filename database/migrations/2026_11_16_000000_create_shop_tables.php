<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role', 20)->default('customer')->index(); // super_admin | admin | customer
            $t->string('phone', 20)->nullable();
            $t->string('address')->nullable();
            $t->boolean('is_active')->default(true);
        });

        /* ---------- Danh mục & phân loại ---------- */
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('icon', 40)->nullable();      // hình minh họa mặc định
            $t->string('image')->nullable();
            $t->text('description')->nullable();
            $t->string('sale_badge', 20)->nullable(); // ví dụ: -50%
            $t->boolean('is_featured')->default(false);
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->string('meta_title')->nullable();
            $t->string('meta_description')->nullable();
            $t->timestamps();
        });

        Schema::create('brands', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('logo')->nullable();
            $t->string('color', 20)->nullable();
            $t->text('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('sku', 60)->unique();
            $t->string('short_description', 500)->nullable();
            $t->longText('description')->nullable();
            $t->unsignedBigInteger('price');                    // giá bán
            $t->unsignedBigInteger('compare_price')->nullable(); // giá niêm yết (gạch ngang)
            $t->unsignedBigInteger('cost')->default(0);          // giá vốn
            $t->integer('stock')->default(0);
            $t->unsignedInteger('low_stock_threshold')->default(5);
            $t->json('images')->nullable();
            $t->string('art', 40)->nullable();                   // hình minh họa khi chưa có ảnh
            $t->json('specs')->nullable();                       // thông số kỹ thuật
            $t->string('warranty', 100)->nullable();
            $t->string('status', 20)->default('published')->index(); // draft | pending | published
            $t->boolean('is_featured')->default(false);
            $t->unsignedInteger('sold_count')->default(0);
            $t->decimal('rating_avg', 3, 2)->default(0);
            $t->unsignedInteger('rating_count')->default(0);
            $t->unsignedInteger('views')->default(0);
            $t->string('meta_title')->nullable();
            $t->string('meta_description')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['category_id', 'status']);
        });

        Schema::create('attributes', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->boolean('is_filterable')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });
        Schema::create('attribute_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $t->string('value');
            $t->string('color', 20)->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });
        Schema::create('attribute_value_product', function (Blueprint $t) {
            $t->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->primary(['attribute_value_id', 'product_id']);
        });

        Schema::create('options', function (Blueprint $t) {
            $t->id();
            $t->string('name');                      // ví dụ: Gói bảo hành mở rộng
            $t->boolean('is_required')->default(false);
            $t->json('values');                      // [{label, price}]
            $t->timestamps();
        });
        Schema::create('option_product', function (Blueprint $t) {
            $t->foreignId('option_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->primary(['option_id', 'product_id']);
        });

        Schema::create('collections', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('subtitle')->nullable();
            $t->boolean('show_on_home')->default(false);
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });
        Schema::create('collection_product', function (Blueprint $t) {
            $t->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->primary(['collection_id', 'product_id']);
        });

        Schema::create('labels', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('color', 20)->default('#e0121f');
            $t->timestamps();
        });
        Schema::create('label_product', function (Blueprint $t) {
            $t->foreignId('label_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->primary(['label_id', 'product_id']);
        });

        Schema::create('inventory_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type', 20);                  // in | out | adjust | order | return
            $t->integer('quantity');                 // dương = nhập, âm = xuất
            $t->integer('stock_after');
            $t->string('note')->nullable();
            $t->timestamps();
        });

        /* ---------- Đơn hàng ---------- */
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('code', 20)->unique();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('customer_name');
            $t->string('phone', 20)->index();
            $t->string('email')->nullable();
            $t->string('province', 100)->nullable();
            $t->string('address');
            $t->text('note')->nullable();
            $t->unsignedBigInteger('subtotal')->default(0);
            $t->unsignedBigInteger('discount_total')->default(0);
            $t->unsignedBigInteger('shipping_fee')->default(0);
            $t->unsignedBigInteger('total')->default(0);
            $t->unsignedBigInteger('cost_total')->default(0);
            $t->string('discount_code', 40)->nullable();
            $t->string('payment_method', 20)->default('cod');     // cod | bank | installment
            $t->string('payment_status', 20)->default('unpaid');  // unpaid | paid | refunded
            $t->string('status', 20)->default('pending')->index(); // pending | confirmed | shipping | completed | cancelled | returned
            $t->text('admin_note')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->index('created_at');
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('sku', 60)->nullable();
            $t->unsignedBigInteger('price');
            $t->unsignedBigInteger('cost')->default(0);
            $t->unsignedInteger('quantity');
            $t->json('options')->nullable();
            $t->timestamps();
        });
        Schema::create('shipments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('carrier', 100);
            $t->string('tracking_code', 100)->nullable();
            $t->string('status', 20)->default('pending'); // pending | picked | in_transit | delivered | failed
            $t->unsignedBigInteger('fee')->default(0);
            $t->timestamp('shipped_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->string('note')->nullable();
            $t->timestamps();
        });
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('number', 40)->unique();
            $t->string('company_name')->nullable();
            $t->string('tax_code', 40)->nullable();
            $t->string('address')->nullable();
            $t->unsignedBigInteger('amount');
            $t->string('status', 20)->default('issued'); // issued | paid | cancelled
            $t->timestamp('issued_at')->nullable();
            $t->timestamps();
        });
        Schema::create('order_returns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->text('reason');
            $t->string('status', 20)->default('requested'); // requested | approved | rejected | refunded
            $t->unsignedBigInteger('refund_amount')->default(0);
            $t->text('admin_note')->nullable();
            $t->timestamps();
        });

        /* ---------- Marketing ---------- */
        Schema::create('flash_sales', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('subtitle')->nullable();
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('flash_sale_product', function (Blueprint $t) {
            $t->id();
            $t->foreignId('flash_sale_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('price');
            $t->unsignedInteger('quantity')->default(10);
            $t->unsignedInteger('sold')->default(0);
        });
        Schema::create('discounts', function (Blueprint $t) {
            $t->id();
            $t->string('code', 40)->unique();
            $t->string('name');
            $t->string('type', 20)->default('percent'); // percent | fixed
            $t->unsignedBigInteger('value');
            $t->unsignedBigInteger('min_order')->default(0);
            $t->unsignedBigInteger('max_discount')->nullable();
            $t->unsignedInteger('usage_limit')->nullable();
            $t->unsignedInteger('used')->default(0);
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->unsignedTinyInteger('rating');
            $t->text('comment')->nullable();
            $t->string('status', 20)->default('pending')->index(); // pending | approved | rejected
            $t->text('reply')->nullable();
            $t->timestamps();
        });
        Schema::create('wishlists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['user_id', 'product_id']);
        });
        Schema::create('ads', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('position', 30)->index(); // home_hero | home_side | home_deal | category_top
            $t->string('eyebrow')->nullable();
            $t->string('title')->nullable();
            $t->string('subtitle')->nullable();
            $t->string('badge')->nullable();
            $t->string('image')->nullable();      // ảnh banner tải lên (nếu có sẽ thay phần chữ)
            $t->string('theme', 20)->default('red'); // red | dark | ice | warm
            $t->string('art', 40)->nullable();
            $t->string('link')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->unsignedInteger('clicks')->default(0);
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('newsletter_subscribers', function (Blueprint $t) {
            $t->id();
            $t->string('email')->unique();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        /* ---------- Nội dung (CMS) ---------- */
        Schema::create('post_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });
        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('post_category_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('excerpt', 500)->nullable();
            $t->longText('content')->nullable();
            $t->string('image')->nullable();
            $t->string('theme', 20)->default('red');
            $t->string('art', 40)->nullable();
            $t->string('status', 20)->default('published'); // draft | published
            $t->boolean('is_featured')->default(false);
            $t->timestamp('published_at')->nullable()->index();
            $t->unsignedInteger('views')->default(0);
            $t->string('meta_title')->nullable();
            $t->string('meta_description')->nullable();
            $t->timestamps();
        });
        Schema::create('tags', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });
        Schema::create('post_tag', function (Blueprint $t) {
            $t->foreignId('post_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $t->primary(['post_id', 'tag_id']);
        });
        Schema::create('pages', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->longText('content')->nullable();
            $t->string('footer_group', 30)->nullable(); // about | policy | support
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('is_active')->default(true);
            $t->string('meta_title')->nullable();
            $t->string('meta_description')->nullable();
            $t->timestamps();
        });
        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('role')->nullable();
            $t->text('content');
            $t->string('avatar')->nullable();
            $t->unsignedTinyInteger('rating')->default(5);
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });
        Schema::create('faqs', function (Blueprint $t) {
            $t->id();
            $t->string('group', 100)->default('Chung');
            $t->string('question');
            $t->text('answer');
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });
        Schema::create('contacts', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('phone', 20)->nullable();
            $t->string('subject')->nullable();
            $t->text('message');
            $t->string('status', 20)->default('new'); // new | read | replied
            $t->text('reply')->nullable();
            $t->timestamps();
        });

        /* ---------- Tài chính & hệ thống ---------- */
        Schema::create('expenses', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('category', 50)->default('Khác');
            $t->unsignedBigInteger('amount');
            $t->date('spent_at')->index();
            $t->string('note')->nullable();
            $t->timestamps();
        });
        Schema::create('visits', function (Blueprint $t) {
            $t->id();
            $t->date('date')->unique();
            $t->unsignedInteger('count')->default(0);
        });
        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'visits', 'expenses', 'contacts', 'faqs', 'testimonials', 'pages', 'post_tag', 'tags', 'posts', 'post_categories',
            'newsletter_subscribers', 'ads', 'wishlists', 'reviews', 'discounts', 'flash_sale_product', 'flash_sales', 'order_returns', 'invoices',
            'shipments', 'order_items', 'orders', 'inventory_movements', 'label_product', 'labels', 'collection_product', 'collections',
            'option_product', 'options', 'attribute_value_product', 'attribute_values', 'attributes', 'products', 'brands', 'categories'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'phone', 'address', 'is_active']));
    }
};
