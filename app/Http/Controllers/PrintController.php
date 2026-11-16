<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;

/** Trang in đơn hàng và hóa đơn cho nhân viên. */
class PrintController extends Controller
{
    public function order(Order $order)
    {
        return view('print.order', ['order' => $order->load('items'), 'invoice' => null]);
    }

    public function invoice(Invoice $invoice)
    {
        return view('print.order', ['order' => $invoice->order->load('items'), 'invoice' => $invoice]);
    }
}
