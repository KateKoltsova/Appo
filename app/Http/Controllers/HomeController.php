<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Services\LiqpayService;
use App\Services\TotalService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
//        $user = User::findOrFail(1);
//        $orderParams = [
//            'user_id' => $user->id,
//            'total' => 100,
//            'payment_status' => null
//        ];
//        $order = Order::create($orderParams);
//        $html = LiqpayService::getHtml(100, 53);
//        return view('pay', compact('html'));
        $user = User::findOrFail(1);
        $carts = Cart::where('client_id', 1)
            ->select([
                'carts.*',
                'prices.price'
            ])
            ->join('prices', 'carts.price_id', '=', 'prices.id')
            ->get();
        $totalSum = TotalService::totalSum($carts, 'full');
        $orderParams = [
            'user_id' => $user->id,
            'total' => $totalSum,
            'payment' => 'full',
            'payment_status' => null
        ];
        $order = Order::create($orderParams);
        $expired_at = $carts->first()->schedule()->first()->blocked_until;
        $resultUrl = env('APP_URL');
        $paidParams['payment'] = 'full';
        $paidParams['html_button'] = LiqpayService::getHtml($totalSum, $order->id, $expired_at, $resultUrl);
        $paidParams['order_id'] = $order->id;
//        return $paidParams;
        return view('pay', compact('paidParams'));

    }


    public function status(Request $request)
    {
        $status = LiqpayService::getResponse(72);
        $order = Order::where('id', 72)->where('user_id', 1)->first();
        $order->update(['payment_status' => $status->status]);
        $orderNew = Order::whereId(72)->first();
        return $orderNew;
    }
}
