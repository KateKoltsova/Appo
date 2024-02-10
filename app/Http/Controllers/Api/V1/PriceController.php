<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceCreateRequest;
use App\Http\Requests\PriceUpdateRequest;
use App\Services\Api\PriceService;
use Exception;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function __construct(
        private PriceService $priceService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $user)
    {
        $filters['category'] = $request->input('filter.category');

        return response()->json($this->priceService->getList($filters, $user));
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
        try {
            $params = $request->validated();

            $response = $this->priceService->create($params, $user);

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user, string $price)
    {
        try {
            $response = $this->priceService->getById($user, $price);

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
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
        try {
            $params = $request->validated();
            $response = $this->priceService->update($params, $user, $price);

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $user, string $price)
    {
        try {
            $this->priceService->delete($user, $price);

            return response()->json(['message' => 'Price successfully deleted']);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }
}
