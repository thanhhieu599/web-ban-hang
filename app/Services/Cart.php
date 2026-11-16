<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Product;
use Illuminate\Support\Collection;

/** Giỏ hàng lưu trong session, dùng được cho cả khách chưa đăng nhập. */
class Cart
{
    protected static ?Collection $lines = null;

    protected static function raw(): array
    {
        return session('cart', []);
    }

    protected static function store(array $cart): void
    {
        session(['cart' => $cart]);
        self::$lines = null;
    }

    /** @param array<int, array{name: string, label: string, price: int}> $options */
    public static function add(Product $product, int $quantity = 1, array $options = []): void
    {
        $key = $product->id.'-'.substr(md5(json_encode($options)), 0, 8);
        $cart = self::raw();
        $current = $cart[$key]['qty'] ?? 0;
        $limit = max(1, $product->stock);
        if ($flash = $product->flashItem()) {
            $limit = min($limit, max(1, $flash->quantity - $flash->sold)); // giá flash sale chỉ áp dụng trong số suất còn lại
        }
        $cart[$key] = ['product_id' => $product->id, 'qty' => min($current + max(1, $quantity), $limit), 'options' => $options];
        self::store($cart);
    }

    public static function update(string $key, int $quantity): void
    {
        $cart = self::raw();
        if (! isset($cart[$key])) {
            return;
        }
        $line = self::lines()->firstWhere('key', $key);
        if ($line && $quantity > $line->qty) {
            $flash = $line->product->flashItem();
            $quantity = min($quantity, $line->product->stock, $flash ? $flash->quantity - $flash->sold : PHP_INT_MAX);
        }
        if ($quantity < 1) {
            unset($cart[$key]);
        } else {
            $cart[$key]['qty'] = $quantity;
        }
        self::store($cart);
    }

    public static function clear(): void
    {
        session()->forget(['cart', 'coupon']);
        self::$lines = null;
    }

    /** Các dòng trong giỏ kèm sản phẩm và giá hiện tại. */
    public static function lines(): Collection
    {
        if (self::$lines) {
            return self::$lines;
        }
        $cart = self::raw();
        $products = Product::published()->with('category')->whereIn('id', array_column($cart, 'product_id'))->get()->keyBy('id');

        return self::$lines = collect($cart)->map(function ($line, $key) use ($products) {
            $product = $products[$line['product_id']] ?? null;
            if (! $product) {
                return null;
            }
            $unit = $product->final_price + (int) array_sum(array_column($line['options'], 'price'));
            $qty = min($line['qty'], max(0, $product->stock));
            if ($flash = $product->flashItem()) {
                $qty = min($qty, max(0, $flash->quantity - $flash->sold));
            }

            return (object) ['key' => $key, 'product' => $product, 'qty' => $qty, 'options' => $line['options'], 'unit' => $unit, 'total' => $unit * $qty];
        })->filter(fn ($l) => $l && $l->qty > 0)->values();
    }

    public static function count(): int
    {
        return (int) array_sum(array_column(self::raw(), 'qty'));
    }

    public static function subtotal(): int
    {
        return (int) self::lines()->sum('total');
    }

    public static function coupon(): ?Discount
    {
        $code = session('coupon');
        $discount = $code ? Discount::where('code', $code)->first() : null;
        if (! $discount || $discount->invalidReason(self::subtotal())) {
            return null;
        }

        return $discount;
    }

    public static function discount(): int
    {
        return self::coupon()?->amountFor(self::subtotal()) ?? 0;
    }

    public static function shipping(): int
    {
        $subtotal = self::subtotal();

        return $subtotal === 0 || $subtotal >= (int) setting('free_ship_from') ? 0 : (int) setting('shipping_fee');
    }

    public static function total(): int
    {
        return max(0, self::subtotal() - self::discount()) + self::shipping();
    }
}
