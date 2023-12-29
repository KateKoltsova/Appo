<?php

namespace App\Services;

class TotalSumService
{
    static function totalSum($cartList, string $param = 'full')
    {
        $totalSum = 0;
        $cartCount = 0;
        $paymentConfig = config('constants.db.payment');

        foreach ($cartList as $cart) {

            if ($cart->status === config('constants.db.status.unavailable') ||
                (!is_null($cart->blocked_by)
                    && $cart->blocked_by != $cart->user()->first()->id
                    && !is_null($cart->blocked_until)
                    && ($cart->blocked_until >= now()->setTimezone('Europe/Kiev'))) ||
                ($cart->date_time < now()->setTimezone('Europe/Kiev'))) {
                continue;
            } else {
                $totalSum += $cart->price;
                $cartCount++;
            }
        }

        switch ($param) {
            case 'full':
            {
                return [
                    'totalSum' => $totalSum,
                    'totalCount' => $cartCount
                ];
            }
            case 'prepayment':
            {
                $totalSum = $paymentConfig['prepayment'][1] * $cartCount;
                return [
                    'totalSum' => $totalSum,
                    'totalCount' => $cartCount
                ];
            }
            case 'payment':
            {
                $paymentConfig = config('constants.db.payment');
                foreach ($paymentConfig as $payment) {
                    if (isset($payment[1])) {
                        $params[$payment[0]] = $payment[1] * $cartCount;
                    } else {
                        $params[$payment[0]] = $totalSum;
                    }
                }
                return [
                    'totalSum' => $params,
                    'totalCount' => $cartCount
                ];
            }
        }
    }
}
