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
            $schedule = $cart->schedule()->first();
            if ($cart->status === config('constants.db.status.unavailable') ||
                (!is_null($schedule->blocked_by)
                    && $schedule->blocked_by != $cart->user()->first()->id
                    && !is_null($schedule->blocked_until)
                    && ($schedule->blocked_until >= now())) ||
                ($schedule->date < now()->format('Y-m-d') ||
                    ($schedule->date == now()->format('Y-m-d')
                        && $schedule->time <= now()->format('H:i:s')))) {
                continue;
            } else {
                $totalSum += $cart->price;
                $cartCount++;
            }
        }
        switch ($param) {
            case 'full':
            {
                return $totalSum;
            }
            case 'prepayment':
            {
                $totalSum = $paymentConfig['prepayment'][1] * $cartCount;
                return $totalSum;
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
                return $params;
            }
        }
    }
}
