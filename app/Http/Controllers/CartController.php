<?php

namespace App\Http\Controllers;

use App\Mail\OrderMail;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Services\Cart;
use App\Services\Checkout;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        return view('cart', ['lines' => Cart::lines(), 'coupon' => Cart::coupon()]);
    }

    public function add(Request $request)
    {
        $data = $request->validate(['product_id' => 'required|integer', 'quantity' => 'nullable|integer|min:1|max:99', 'options' => 'nullable|array']);
        $product = Product::published()->with('options')->findOrFail($data['product_id']);
        if ($product->stock < 1) {
            $message = 'Sản phẩm tạm hết hàng.';

            return $request->expectsJson() ? response()->json(['message' => $message], 422) : back()->with('flash', $message);
        }
        // Chỉ nhận lựa chọn hợp lệ của các tùy chọn gắn với sản phẩm
        $options = [];
        foreach ($product->options as $option) {
            $index = $data['options'][$option->id] ?? ($option->is_required ? 0 : null);
            $value = $index !== null ? ($option->values[(int) $index] ?? null) : null;
            if ($value && ((int) $value['price'] > 0 || $option->is_required)) {
                $options[] = ['name' => $option->name, 'label' => $value['label'], 'price' => (int) $value['price']];
            }
        }
        Cart::add($product, (int) ($data['quantity'] ?? 1), $options);

        if ($request->expectsJson()) {
            return response()->json(['count' => Cart::count(), 'message' => 'Đã thêm vào giỏ: '.$product->name]);
        }

        return $request->boolean('buy_now') ? redirect()->route('checkout') : redirect()->route('cart')->with('flash', 'Đã thêm vào giỏ hàng.');
    }

    public function update(Request $request)
    {
        $data = $request->validate(['key' => 'required|string', 'quantity' => 'required|integer|min:0|max:99']);
        Cart::update($data['key'], (int) $data['quantity']);

        return back();
    }

    public function coupon(Request $request)
    {
        if ($request->boolean('remove')) {
            session()->forget('coupon');

            return back()->with('flash', 'Đã bỏ mã giảm giá.');
        }
        $code = strtoupper(trim((string) $request->input('code')));
        $discount = Discount::where('code', $code)->first();
        $reason = $discount ? $discount->invalidReason(Cart::subtotal()) : 'Mã giảm giá không tồn tại.';
        if ($reason) {
            return back()->withErrors(['code' => $reason]);
        }
        session(['coupon' => $code]);

        return back()->with('flash', 'Đã áp dụng mã '.$code.'.');
    }

    public function checkout()
    {
        if (Cart::lines()->isEmpty()) {
            return redirect()->route('cart');
        }

        return view('checkout', ['lines' => Cart::lines(), 'coupon' => Cart::coupon(), 'user' => auth()->user()]);
    }

    public function place(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:120', 'phone' => ['required', 'regex:/^0\d{9,10}$/'], 'email' => 'nullable|email|max:160',
            'province' => 'required|string|max:100', 'address' => 'required|string|max:255', 'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:'.implode(',', array_keys(Order::PAYMENT_METHODS)),
        ], ['phone.regex' => 'Số điện thoại gồm 10 hoặc 11 chữ số và bắt đầu bằng số 0.'],
            ['customer_name' => 'họ tên', 'phone' => 'số điện thoại', 'province' => 'tỉnh/thành phố', 'address' => 'địa chỉ', 'payment_method' => 'hình thức thanh toán']);

        $order = Checkout::place($data);
        session(['last_order' => $order->code]);
        OrderMail::dispatch($order, 'placed');
        OrderMail::dispatch($order, 'new');

        return redirect()->route('order.success', $order->code);
    }

    public function success(string $code)
    {
        abort_unless(session('last_order') === $code, 404);

        return view('order-success', ['order' => Order::where('code', $code)->with('items')->firstOrFail()]);
    }

    public function track(Request $request)
    {
        $order = null;
        if ($request->filled('code')) {
            $order = Order::where('code', strtoupper(trim($request->query('code'))))->where('phone', trim((string) $request->query('phone')))
                ->with(['items', 'shipments'])->first();
        }

        return view('track', ['order' => $order, 'searched' => $request->filled('code')]);
    }
}
