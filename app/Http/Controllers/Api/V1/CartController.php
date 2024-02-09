<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\CartAddRequest;
use App\Services\Api\CartService;
use Exception;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(string $user)
    {
        return response()->json($this->cartService->getList($user));
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
    public function store(CartAddRequest $request, string $user)
    {
        try {
            $params = $request->validated();
            $response = $this->cartService->add($params, $user);
            return response()->json($response);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $user, string $cart)
    {
        try {
            $this->cartService->delete($user, $cart);

            return response()->json(['message' => 'Cart item successfully deleted']);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Get way to pay and blocked specified resource by user
     */
    public function checkout(string $user)
    {
        try {
            $response = $this->cartService->checkout($user);

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Create order and get payment button with link and data for pay order
     */
    public function getPayButton(OrderCreateRequest $request, string $user)
    {
        try {
            $params = $request->validated();

            $response = $this->cartService->getPayButton($params, $user);

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }
}
