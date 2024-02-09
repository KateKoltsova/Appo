<?php

namespace App\Services;

class TotalService
{
    static function total($cartList, string $param = 'full')
    {
        $cartSum = 0;
        $cartCount = 0;
        $paymentConfig = config('constants.db.payment');

        foreach ($cartList as $cartItem) {
            if (!isset($cartItem['message'])) {
                $cartSum += $cartItem['price'];
                $cartCount++;
            }
        }

        switch ($param) {
            case 'full':
            {
                return [
                    'totalSum' => $cartSum,
                    'totalCount' => $cartCount
                ];
            }
            case 'prepayment':
            {
                $cartSum = $paymentConfig['prepayment'][1] * $cartCount;
                return [
                    'totalSum' => $cartSum,
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
                        $params[$payment[0]] = $cartSum;
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
