<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordController extends Controller
{
    public function request()
    {
        return view('auth.forgot');
    }

    public function email(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['email' => 'Chưa gửi được email. Vui lòng thử lại sau hoặc gọi '.setting('hotline_care').'.']);
        }

        // Luôn báo cùng một câu để không lộ email nào đã đăng ký
        return back()->with('flash', 'Nếu email có tài khoản, bạn sẽ nhận được liên kết đặt lại mật khẩu trong vài phút.');
    }

    public function reset(string $token, Request $request)
    {
        return view('auth.reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request)
    {
        $request->validate(['token' => 'required', 'email' => 'required|email', 'password' => ['required', 'confirmed', PasswordRule::min(8)]], [], ['password' => 'mật khẩu']);
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password) {
            $user->forceFill(['password' => $password])->save();
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('flash', 'Đã đặt lại mật khẩu. Hãy đăng nhập bằng mật khẩu mới.')
            : back()->withErrors(['email' => 'Liên kết đã hết hạn hoặc không đúng. Hãy yêu cầu liên kết mới.']);
    }

    public function change(Request $request)
    {
        $request->validate(['current_password' => 'required|current_password', 'password' => ['required', 'confirmed', PasswordRule::min(8)]],
            ['current_password.current_password' => 'Mật khẩu hiện tại không đúng.'], ['password' => 'mật khẩu mới', 'current_password' => 'mật khẩu hiện tại']);
        $request->user()->update(['password' => $request->input('password')]);

        return back()->with('flash', 'Đã đổi mật khẩu.');
    }
}
