<?php

namespace App\Services;

use LiqPay;
use Request;

class LiqpayService
{
    static function getHtml(int $total, int $orderId, string $expired_at, string $resultUrl)
    {
        $public_key = env('LIQPAY_TEST_PUBLIC_KEY');
        $private_key = env('LIQPAY_TEST_PRIVATE_KEY');
        $liqpay = new LiqPay($public_key, $private_key);
        $html = $liqpay->cnb_form(array(
            'version' => '3',
            'action' => 'pay',
            'amount' => $total,
            'currency' => 'UAH',
            'description' => 'Pay for beauty services',
            'order_id' => $orderId,
            'server_url' => env('APP_URL').'api/v1/status',
            'result_url' => $resultUrl,
            'expired_date' => $expired_at
        ));
        return $html;
    }

    static function getResponse(int $orderId)
    {
        $public_key = env('LIQPAY_TEST_PUBLIC_KEY');
        $private_key = env('LIQPAY_TEST_PRIVATE_KEY');
            $liqpay = new LiqPay($public_key, $private_key);
            $res = $liqpay->api("request", array(
                'action'        => 'status',
                'version'       => '3',
                'order_id'      => $orderId
            ));
            return $res;
    }
}
