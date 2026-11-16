<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

/** Xuất dữ liệu ra file CSV (mở được bằng Excel, giữ đúng tiếng Việt). */
class ExportController extends Controller
{
    public function __invoke(Request $request, string $type)
    {
        [$headers, $query, $row] = match ($type) {
            'don-hang' => [
                ['Mã đơn', 'Ngày đặt', 'Khách hàng', 'SĐT', 'Email', 'Tỉnh/TP', 'Địa chỉ', 'Sản phẩm', 'Tạm tính', 'Giảm giá', 'Phí giao', 'Tổng tiền', 'Giá vốn', 'Thanh toán', 'Tình trạng TT', 'Trạng thái'],
                Order::with('items')->latest('id')
                    ->when($request->query('from'), fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                    ->when($request->query('to'), fn ($q, $d) => $q->whereDate('created_at', '<=', $d)),
                fn (Order $o) => [$o->code, $o->created_at->format('d/m/Y H:i'), $o->customer_name, $o->phone, $o->email, $o->province, $o->address,
                    $o->items->map(fn ($i) => $i->name.' x'.$i->quantity)->join('; '), $o->subtotal, $o->discount_total, $o->shipping_fee, $o->total, $o->cost_total,
                    Order::PAYMENT_METHODS[$o->payment_method] ?? $o->payment_method, Order::PAYMENT_STATUSES[$o->payment_status] ?? '', $o->status_label],
            ],
            'san-pham' => [
                ['SKU', 'Tên sản phẩm', 'Danh mục', 'Thương hiệu', 'Giá bán', 'Giá niêm yết', 'Giá vốn', 'Tồn kho', 'Đã bán', 'Đánh giá', 'Trạng thái'],
                Product::with(['category', 'brand'])->orderBy('id'),
                fn (Product $p) => [$p->sku, $p->name, $p->category->name ?? '', $p->brand->name ?? '', $p->price, $p->compare_price, $p->cost, $p->stock, $p->sold_count,
                    $p->rating_avg, Product::STATUSES[$p->status] ?? $p->status],
            ],
            'khach-hang' => [
                ['Họ tên', 'Email', 'SĐT', 'Địa chỉ', 'Số đơn', 'Tổng chi tiêu', 'Ngày đăng ký'],
                User::where('role', 'customer')->withCount('orders')
                    ->withSum(['orders' => fn ($q) => $q->whereIn('status', Order::REVENUE_STATUSES)], 'total')->orderBy('id'),
                fn (User $u) => [$u->name, $u->email, $u->phone, $u->address, $u->orders_count, (int) $u->orders_sum_total, $u->created_at->format('d/m/Y')],
            ],
            'email-nhan-tin' => [
                ['Email', 'Còn nhận tin', 'Ngày đăng ký'],
                NewsletterSubscriber::orderBy('id'),
                fn ($s) => [$s->email, $s->is_active ? 'Có' : 'Không', $s->created_at->format('d/m/Y')],
            ],
            default => abort(404),
        };

        return response()->streamDownload(function () use ($headers, $query, $row) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM để Excel đọc đúng tiếng Việt
            fputcsv($out, $headers);
            $query->chunk(500, function ($records) use ($out, $row) {
                foreach ($records as $record) {
                    fputcsv($out, $row($record));
                }
            });
            fclose($out);
        }, $type.'-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
