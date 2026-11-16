<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Email về đơn hàng: gửi khách khi đặt và khi đổi trạng thái, gửi cửa hàng khi có đơn mới. */
class OrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public const SUBJECTS = [
        'placed' => 'Đã nhận đơn hàng :code',
        'new' => 'Đơn hàng mới :code',
        'confirmed' => 'Đơn hàng :code đã được xác nhận',
        'shipping' => 'Đơn hàng :code đang được giao',
        'completed' => 'Đơn hàng :code đã giao thành công',
        'cancelled' => 'Đơn hàng :code đã bị hủy',
        'returned' => 'Đơn hàng :code đã được hoàn tiền',
    ];

    public function __construct(public Order $order, public string $kind) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: str_replace(':code', $this->order->code, self::SUBJECTS[$this->kind]).' · '.setting('site_name'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order');
    }

    /** Gửi email nhưng không làm hỏng thao tác chính nếu máy chủ mail lỗi. */
    public static function dispatch(Order $order, string $kind): void
    {
        try {
            if ($kind === 'new') {
                if ($shop = setting('email')) {
                    \Mail::to($shop)->send(new self($order->loadMissing('items'), 'new'));
                }

                return;
            }
            if ($order->email) {
                \Mail::to($order->email)->send(new self($order->loadMissing('items'), $kind));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
