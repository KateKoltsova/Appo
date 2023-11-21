<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceCreateRequest;
use App\Http\Requests\PriceUpdateRequest;
use App\Http\Resources\PriceCollection;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use App\Models\Service;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $user)
    {
        $categories = $request->input('filter.category');
        $prices = Service::select(['services.*', 'prices.price'])
            ->rightJoin('prices', 'service_id', '=', 'services.id')
            ->where('master_id', $user)
            ->when($categories, function ($query) use ($categories) {
                $query->whereIn('category', $categories);
            })
            ->get();
        if (!empty($prices->toArray())) {
            $priceCollection = new PriceCollection($prices);
            return response()->json(['data' => $priceCollection]);
        } else {
            return response()->json(['message' => 'No data'], 404);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PriceCreateRequest $request, string $user)
    {
        $params = $request->validated();
        $price = Price::updateOrCreate(
            ['master_id' => $user, 'service_id' => $params['service_id']],
            ['price' => $params['price']]
        );
        if ($price) {
            return $this->show($price->master_id, $price->service_id);
        } else {
            return response()->json(['message' => 'Bad request'], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user, string $price)
    {
        $priceInstance = Service::select(['services.*', 'prices.price'])
            ->rightJoin('prices', 'service_id', '=', 'services.id')
            ->where('master_id', $user)
            ->where('services.id', $price)
            ->first();
        if (!empty($priceInstance)) {
            $priceResource = new PriceResource($priceInstance);
            return response()->json(['data' => $priceResource]);
        } else {
            return response()->json(['message' => 'No data'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PriceUpdateRequest $request, string $user, string $price)
    {
        $params = $request->validated();
        $priceInstance = Price::where('master_id', $user)->where('service_id', $price);
        if ($priceInstance) {
            $priceInstance->update($params);
            return $this->show($user, $price);
        } else {
            return response()->json(['message' => 'No data'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $user, string $price)
    {
        $priceInstance = Price::where('master_id', $user)->where('service_id', $price)->first();
        if (!$priceInstance) {
            return response()->json(['message' => 'No data'], 404);
        }
        $priceInstance->delete();
        return response()->json(['message' => 'Price successfully deleted']);
    }
}
