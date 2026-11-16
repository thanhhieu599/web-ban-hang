<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended(Auth::user()->isStaff() ? '/admin' : route('account'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120', 'email' => 'required|email|max:160|unique:users,email',
            'phone' => ['nullable', 'regex:/^0\d{9,10}$/'], 'password' => ['required', 'confirmed', Password::min(8)],
        ], ['phone.regex' => 'Số điện thoại gồm 10 hoặc 11 chữ số và bắt đầu bằng số 0.', 'email.unique' => 'Email này đã có tài khoản. Hãy đăng nhập.'],
            ['name' => 'họ tên', 'password' => 'mật khẩu']);
        $user = User::create($data + ['role' => 'customer']);
        // Gắn các đơn đã đặt bằng số điện thoại này khi còn là khách vãng lai
        if ($user->phone) {
            Order::whereNull('user_id')->where('phone', $user->phone)->update(['user_id' => $user->id]);
        }
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account')->with('flash', 'Tạo tài khoản thành công.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function index(Request $request)
    {
        return view('account.index', ['user' => $request->user(), 'orders' => $request->user()->orders()->withCount('items')->latest('id')->paginate(10)]);
    }

    public function order(Request $request, string $code)
    {
        $order = $request->user()->orders()->where('code', $code)->with(['items.product', 'shipments', 'returns'])->firstOrFail();

        return view('account.order', compact('order'));
    }

    public function requestReturn(Request $request, string $code)
    {
        $order = $request->user()->orders()->where('code', $code)->where('status', 'completed')->firstOrFail();
        $data = $request->validate(['reason' => 'required|string|min:10|max:1000'], [], ['reason' => 'lý do']);
        if ($order->returns()->whereIn('status', ['requested', 'approved'])->exists()) {
            return back()->with('flash', 'Đơn này đã có yêu cầu trả hàng đang xử lý.');
        }
        $order->returns()->create($data + ['user_id' => $request->user()->id, 'refund_amount' => $order->total]);

        return back()->with('flash', 'Đã gửi yêu cầu trả hàng. Cửa hàng sẽ liên hệ trong 24 giờ.');
    }

    public function cancelOrder(Request $request, string $code)
    {
        $order = $request->user()->orders()->where('code', $code)->where('status', 'pending')->firstOrFail();
        $order->changeStatus('cancelled');

        return back()->with('flash', 'Đã hủy đơn hàng.');
    }

    public function wishlist(Request $request)
    {
        return view('account.wishlist', ['items' => $request->user()->wishlists()->with('product.category', 'product.labels')->latest()->get()->filter->product]);
    }

    public function toggleWishlist(Request $request)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Cần đăng nhập'], 401);
        }
        $data = $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $existing = Wishlist::where('user_id', $request->user()->id)->where('product_id', $data['product_id'])->first();
        if ($existing) {
            $existing->delete();

            return response()->json(['saved' => false, 'message' => 'Đã bỏ khỏi danh sách yêu thích.']);
        }
        Wishlist::create(['user_id' => $request->user()->id, 'product_id' => $data['product_id']]);

        return response()->json(['saved' => true, 'message' => 'Đã lưu vào danh sách yêu thích.']);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:120', 'phone' => ['nullable', 'regex:/^0\d{9,10}$/'], 'address' => 'nullable|string|max:255'],
            ['phone.regex' => 'Số điện thoại gồm 10 hoặc 11 chữ số và bắt đầu bằng số 0.'], ['name' => 'họ tên']);
        $request->user()->update($data);

        return back()->with('flash', 'Đã cập nhật thông tin.');
    }
}
