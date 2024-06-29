<?php

namespace App\Services;

use App\Services\Contracts\PayService;
use Exception;
use Illuminate\Http\Request;
use LiqPay;

class LiqpayService implements PayService
{
    public function getHtml(int $total, int $orderId, string $expired_at, string $resultUrl, string $description): string
    {
        $public_key = env('LIQPAY_TEST_PUBLIC_KEY');
        $private_key = env('LIQPAY_TEST_PRIVATE_KEY');
        $liqpay = new LiqPay($public_key, $private_key);
        $html = $liqpay->cnb_form(array(
            'version' => '3',
            'action' => 'pay',
            'amount' => $total,
            'currency' => 'UAH',
            'description' => $description,
            'order_id' => $orderId,
            'server_url' => env('APP_URL') . 'api/v1/callback',
            'result_url' => $resultUrl,
            'expired_date' => $expired_at
        ));
        return $html;
    }

    public function getResponse(int $orderId): mixed
    {
        $public_key = env('LIQPAY_TEST_PUBLIC_KEY');
        $private_key = env('LIQPAY_TEST_PRIVATE_KEY');
        $liqpay = new LiqPay($public_key, $private_key);
        $res = $liqpay->api("request", array(
            'action' => 'status',
            'version' => '3',
            'order_id' => $orderId
        ));
        return $res;
    }

    public function getCallback(Request $request)
    {
        $data = $request->input('data');
        $signature = $request->input('signature');

        $privateKey = env('LIQPAY_TEST_PRIVATE_KEY');
        $expectedSignature = base64_encode(sha1($privateKey . $data . $privateKey, true));

        if ($signature !== $expectedSignature) {
            return false;
        }

        return json_decode(base64_decode($data), true);
    }
}
