<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PayController extends Controller
{
    public function paidStatus(Request $request)
    {
        $data = $request->input('data');
        $signature = $request->input('signature');

        $privateKey = env('LIQPAY_TEST_PRIVATE_KEY');
        $expectedSignature = base64_encode(sha1($privateKey . $data . $privateKey, true));

        if ($signature !== $expectedSignature) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $decodedData = json_decode(base64_decode($data), true);

//        $order = Order::findOrFail($decodedData['order_id']);
//        $order->update(['payment_status' => $decodedData['status']]);

//        return $order;
        $order = Order::where('id', 21)->first();
        $order->update(['payment_status' => $decodedData['status']]);
        return $order;
    }
}
