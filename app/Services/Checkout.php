<?php

namespace App\Services;

use App\Models\FlashSale;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Checkout
{
    /** Tạo đơn từ giỏ hàng: kiểm tra tồn kho, trừ kho, ghi nhận mã giảm giá và suất flash sale. */
    public static function place(array $customer): Order
    {
        return DB::transaction(function () use ($customer) {
            $lines = Cart::lines();
            if ($lines->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
            }
            foreach ($lines as $line) {
                $fresh = $line->product->newQuery()->lockForUpdate()->find($line->product->id);
                if ($fresh->stock < $line->qty) {
                    throw ValidationException::withMessages(['cart' => "Sản phẩm \"{$fresh->name}\" chỉ còn {$fresh->stock} chiếc. Vui lòng giảm số lượng."]);
                }
            }

            $coupon = Cart::coupon();
            $order = Order::create($customer + [
                'code' => Order::newCode(),
                'user_id' => auth()->id(),
                'subtotal' => Cart::subtotal(),
                'discount_total' => Cart::discount(),
                'discount_code' => $coupon?->code,
                'shipping_fee' => Cart::shipping(),
                'total' => Cart::total(),
                'cost_total' => (int) $lines->sum(fn ($l) => $l->product->cost * $l->qty),
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line->product->id, 'name' => $line->product->name, 'sku' => $line->product->sku,
                    'price' => $line->unit, 'cost' => $line->product->cost, 'quantity' => $line->qty, 'options' => $line->options ?: null,
                ]);
                $line->product->adjustStock(-$line->qty, 'order', 'Đơn '.$order->code);
                $line->product->increment('sold_count', $line->qty);
                $line->product->flashItem()?->increment('sold', $line->qty);
            }
            $coupon?->increment('used');
            FlashSale::forgetCurrent();
            Cart::clear();

            return $order;
        });
    }
}
