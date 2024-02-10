<?php

namespace App\Repositories;

use App\Models\Service;

class PriceRepository
{
    public function __construct(protected Service $model)
    {
    }

    public function getAll($filters, string $userId)
    {
        return $this->model->select([
            'services.id as service_id',
            'services.*',
            'prices.id as price_id',
            'prices.price',
        ])
            ->rightJoin('prices', 'prices.service_id', '=', 'services.id')
            ->where('master_id', $userId)
            ->when($filters['category'], function ($query) use ($filters) {
                $query->whereIn('category', $filters['category']);
            })
            ->get();
    }

    public function getById(string $userId, string $priceId)
    {
        return $this->model->select([
            'services.id as service_id',
            'services.*',
            'prices.id as price_id',
            'prices.price',
        ])
            ->rightJoin('prices', 'prices.service_id', '=', 'services.id')
            ->where('master_id', $userId)
            ->where('prices.id', $priceId)
            ->first();
    }
}
