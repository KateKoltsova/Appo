<?php

namespace App\Repositories;

use App\Models\Cart;

class CartRepository
{
    public function __construct(protected Cart $model)
    {
    }

    public function getAllByUserId($userId)
    {
        return Cart::select([
            'carts.*',
            'services.title',
            'services.category',
            'masters.firstname as master_firstname',
            'masters.lastname as master_lastname',
            'schedules.date_time',
            'schedules.status',
            'schedules.blocked_until',
            'schedules.blocked_by',
            'prices.price',
        ])
            ->join('services', 'carts.service_id', '=', 'services.id')
            ->join('schedules', 'carts.schedule_id', '=', 'schedules.id')
            ->join('users as masters', 'schedules.master_id', '=', 'masters.id')
            ->join('prices', 'carts.price_id', '=', 'prices.id')
            ->where('carts.client_id', '=', $userId)
            ->get();
    }

}
