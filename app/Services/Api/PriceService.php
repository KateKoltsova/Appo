<?php

namespace App\Services\Api;

use App\Http\Resources\PriceResource;
use App\Models\Price;
use App\Repositories\PriceRepository;
use Exception;

class PriceService
{
    public function __construct(
        private PriceRepository $priceRepository,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function getList($filters, string $userId)
    {
        $prices = $this->priceRepository->getAll($filters, $userId);

        $priceCollection = PriceResource::collection($prices);

        return ['data' => $priceCollection];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create($params, string $userId)
    {
        $price = Price::updateOrCreate(
            ['master_id' => $userId, 'service_id' => $params['service_id']],
            ['price' => $params['price']]
        );

        if ($price) {
            return $this->getById($userId, $price->id);
        } else {
            throw new Exception('Bad request', 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function getById(string $userId, string $priceId)
    {
        $price = $this->priceRepository->getById($userId, $priceId);

        if (!empty($price)) {
            $priceResource = PriceResource::make($price);

            return ['data' => $priceResource];
        } else {
            throw new Exception('Price not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($params, string $userId, string $priceId)
    {
        $price = Price::where('master_id', $userId)->where('id', $priceId)->first();

        if (!empty($price)) {
            $price->update($params);

            return $this->getById($userId, $priceId);
        } else {
            throw new Exception('Price not found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $userId, string $priceId)
    {
        $price = Price::where('master_id', $userId)->where('id', $priceId)->first();

        if (!empty($price)) {
            $price->delete();

            return true;

        } else {
            throw new Exception('Price not found', 404);
        }
    }

}
