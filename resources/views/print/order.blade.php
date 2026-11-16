<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice ? 'Hóa đơn '.$invoice->number : 'Đơn hàng '.$order->code }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #111; max-width: 780px; margin: 24px auto; padding: 0 16px; }
        h1 { font-size: 20px; margin: 0; } table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; } th { background: #f3f3f3; } .r { text-align: right; }
        .head { display: flex; justify-content: space-between; gap: 24px; } .muted { color: #555; } .total { font-size: 15px; font-weight: bold; }
        @media print { .noprint { display: none; } }
    </style>
</head>
<body>
    <p class="noprint"><button onclick="window.print()">In trang này</button></p>
    <div class="head">
        <div>
            <h1>{{ setting('company_name') }}</h1>
            <p class="muted">{{ setting('address') }}<br>Điện thoại: {{ setting('hotline_care') }}@if (setting('tax_code')) · MST: {{ setting('tax_code') }}@endif</p>
        </div>
        <div class="r">
            <h1>{{ $invoice ? 'HÓA ĐƠN BÁN HÀNG' : 'PHIẾU ĐƠN HÀNG' }}</h1>
            <p>Số: <b>{{ $invoice->number ?? $order->code }}</b><br>Ngày: {{ ($invoice->issued_at ?? $order->created_at)->format('d/m/Y') }}@if ($invoice)<br>Đơn hàng: {{ $order->code }}@endif</p>
        </div>
    </div>
    <p><b>Người mua:</b> {{ $invoice->company_name ?? $order->customer_name }}@if ($invoice?->tax_code) · MST: {{ $invoice->tax_code }}@endif<br>
       <b>Điện thoại:</b> {{ $order->phone }}<br><b>Địa chỉ:</b> {{ $invoice->address ?? $order->address.', '.$order->province }}<br>
       <b>Thanh toán:</b> {{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? '' }}</p>
    <table>
        <thead><tr><th>#</th><th>Sản phẩm</th><th>Mã</th><th class="r">Đơn giá</th><th class="r">SL</th><th class="r">Thành tiền</th></tr></thead>
        <tbody>
            @foreach ($order->items as $n => $item)
                <tr><td>{{ $n + 1 }}</td><td>{{ $item->name }}@foreach ($item->options ?? [] as $o)<br><span class="muted">{{ $o['name'] }}: {{ $o['label'] }}</span>@endforeach</td><td>{{ $item->sku }}</td>
                    <td class="r">{{ vnd($item->price) }}</td><td class="r">{{ $item->quantity }}</td><td class="r">{{ vnd($item->line_total) }}</td></tr>
            @endforeach
            <tr><td colspan="5" class="r">Tạm tính</td><td class="r">{{ vnd($order->subtotal) }}</td></tr>
            @if ($order->discount_total)<tr><td colspan="5" class="r">Giảm giá {{ $order->discount_code }}</td><td class="r">-{{ vnd($order->discount_total) }}</td></tr>@endif
            <tr><td colspan="5" class="r">Phí giao hàng</td><td class="r">{{ vnd($order->shipping_fee) }}</td></tr>
            <tr><td colspan="5" class="r total">Tổng cộng</td><td class="r total">{{ vnd($order->total) }}</td></tr>
        </tbody>
    </table>
    @if ($order->note)<p><b>Ghi chú:</b> {{ $order->note }}</p>@endif
    <div class="head" style="margin-top: 40px; text-align: center;"><div style="flex: 1">Người mua hàng<br><span class="muted">(Ký, ghi rõ họ tên)</span></div><div style="flex: 1">Người bán hàng<br><span class="muted">(Ký, ghi rõ họ tên)</span></div></div>
</body>
</html>
