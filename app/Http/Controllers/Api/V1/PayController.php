<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PayController extends Controller
{
    public function paidStatus(Request $request)
    {
        $order = Order::whereId(21)->first();
        $order->update(['payment_status' => $request->input('data')]);
        return $order;
    }
}
