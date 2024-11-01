<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ResponseResource;
use App\Http\Services\OrderTransactionService;

class OrderTransactionController extends Controller
{
    public function __construct(private OrderTransactionService $orderTransactionService) {}


    public function index()
    {
        // select all, select paginate, search
        $paginate = request()->paginate ? true : false;

        $order = $this->orderTransactionService->getTransaction($paginate);

        if ($order->isEmpty()) {
            return new ResponseResource(true, 'Order Transaction not available', null, [
                'code' => 200
            ], 200);
        }

        $orderResponse = $order->map(function ($product) {
            $product->total_price = 'Rp. ' . number_format($product->total_price, 0, '.', ',');

            return $product;
        });

        return new ResponseResource(true, 'List of order', $orderResponse, [
            'code' => 200,
            'total_order' => $order->count()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
