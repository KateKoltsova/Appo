<?php

namespace App\Services;

class TotalService
{
    static function total($cartList, $appointmentSchedules, string $param = 'full')
    {
        $cartSum = 0;
        $cartCount = 0;
        $paymentConfig = config('constants.db.payment');

        foreach ($cartList as $key => $cartItem) {
            $userId = $cartItem->user()->first()->id;

            $otherCartItems = $cartList->filter(function ($item, $otherKey) use ($key) {
                return $otherKey !== $key;
            });

            $isValid = ScheduleService::scheduleValidation($otherCartItems, $userId, $appointmentSchedules, $cartItem);

            if ($isValid) {
                $cartSum += $cartItem->price;
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
