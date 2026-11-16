<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;

/** Đếm lượt truy cập theo ngày (mỗi phiên tính một lần) để tính tỷ lệ chuyển đổi. */
class TrackVisit
{
    public function handle(Request $request, Closure $next)
    {
        $today = now()->toDateString();
        if ($request->isMethod('GET') && ! $request->ajax() && session('visited_on') !== $today) {
            session(['visited_on' => $today]);
            Visit::firstOrCreate(['date' => $today])->increment('count');
        }

        return $next($request);
    }
}
