@php
    $lead = [
        'placed' => 'Cảm ơn bạn đã đặt hàng. Cửa hàng sẽ gọi điện xác nhận trong giờ làm việc.',
        'new' => 'Có đơn hàng mới cần xác nhận.',
        'confirmed' => 'Đơn hàng của bạn đã được xác nhận và đang chuẩn bị giao.',
        'shipping' => 'Đơn hàng đã được giao cho đơn vị vận chuyển.',
        'completed' => 'Đơn hàng đã giao thành công. Cảm ơn bạn đã mua sắm.',
        'cancelled' => 'Đơn hàng đã được hủy. Nếu bạn không yêu cầu hủy, hãy liên hệ cửa hàng.',
        'returned' => 'Cửa hàng đã nhận lại hàng và hoàn tiền cho đơn này.',
    ][$kind];
    $shipment = $order->shipments()->latest('id')->first();
@endphp
<!DOCTYPE html>
<html lang="vi"><body style="margin:0;background:#f3f2f4;font-family:Arial,sans-serif;color:#16161a">
<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px 12px">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border-radius:12px;overflow:hidden">
    <tr><td style="background:#e0121f;color:#fff;padding:18px 24px;font-size:20px;font-weight:bold">{{ setting('site_name') }} <span style="font-size:12px;font-weight:normal">{{ setting('site_tagline') }}</span></td></tr>
    <tr><td style="padding:24px">
        <p style="margin:0 0 6px;font-size:18px;font-weight:bold">{{ str_replace(':code', $order->code, \App\Mail\OrderMail::SUBJECTS[$kind]) }}</p>
        <p style="margin:0 0 16px;font-size:14px;color:#52525c">{{ $lead }}</p>
        @if ($shipment && $kind === 'shipping')
            <p style="margin:0 0 16px;font-size:14px">Đơn vị vận chuyển: <b>{{ $shipment->carrier }}</b>@if ($shipment->tracking_code) · Mã vận đơn: <b>{{ $shipment->tracking_code }}</b>@endif</p>
        @endif
        <p style="margin:0 0 12px;font-size:14px"><b>{{ $order->customer_name }}</b> · {{ $order->phone }}<br>{{ $order->address }}, {{ $order->province }}</p>
        <table width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-size:13px">
            @foreach ($order->items as $item)
                <tr style="border-bottom:1px solid #eee"><td>{{ $item->name }} × {{ $item->quantity }}</td><td align="right">{{ vnd($item->line_total) }}</td></tr>
            @endforeach
            @if ($order->discount_total)<tr><td>Giảm giá</td><td align="right">-{{ vnd($order->discount_total) }}</td></tr>@endif
            <tr><td>Phí giao hàng</td><td align="right">{{ $order->shipping_fee ? vnd($order->shipping_fee) : 'Miễn phí' }}</td></tr>
            <tr><td style="font-size:15px;font-weight:bold">Tổng cộng</td><td align="right" style="font-size:15px;font-weight:bold;color:#e0121f">{{ vnd($order->total) }}</td></tr>
        </table>
        @if ($kind === 'placed' && $order->payment_method === 'bank')
            <p style="margin:16px 0 0;font-size:13px;background:#f6f6f7;padding:12px;border-radius:8px">Chuyển khoản: {{ setting('bank_info') }}<br>Nội dung: <b>{{ $order->code }}</b></p>
        @endif
        <p style="margin:20px 0 0">
            @if ($kind === 'new')
                <a href="{{ url('/admin/orders/'.$order->id.'/edit') }}" style="background:#e0121f;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:bold">Mở đơn trong trang quản trị</a>
            @else
                <a href="{{ route('track', ['code' => $order->code, 'phone' => $order->phone]) }}" style="background:#e0121f;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:bold">Theo dõi đơn hàng</a>
            @endif
        </p>
    </td></tr>
    <tr><td style="padding:14px 24px;background:#f6f6f7;font-size:12px;color:#6e6e7a">{{ setting('company_name') }} · Hotline {{ setting('hotline_care') }} · {{ setting('email') }}</td></tr>
</table></td></tr></table>
</body></html>
