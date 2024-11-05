<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Services\ProductService;
use App\Http\Resources\ResponseResource;
use App\Http\Requests\OrderTransactionRequest;
use App\Http\Services\OrderTransactionService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class OrderTransactionController extends Controller implements HasMiddleware
{
    public function __construct(
        private OrderTransactionService $orderTransactionService,
        private ProductService $productService
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('owner', only: ['index']),
        ];
    }

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
    public function store(OrderTransactionRequest $request)
    {
        // cek validasi
        $data = $request->validated();

        DB::beginTransaction();

        try {
            // get product
            $getProduct = $this->productService->getByFirst('id', $data['product_id']);

            // cek stock
            if ($getProduct->quantity < $data['quantity']) {
                // misal : roti di database = 2, roti permintaan user = 3, maka gagal
                return new ResponseResource(false, 'Stock not available', null, [
                    'code' => 200,
                    'product_name' => $getProduct->name,
                    'product_stock' => $getProduct->quantity,
                    'request_stock' => $data['quantity']
                ], 200);
            }

            // insert
            $order = $this->orderTransactionService->create($data, $getProduct->id);

            // custom response
            $orderResponse = [
                'uuid' => $order->uuid,
                'product_id' => $order->product_id,
                'quantity' => $order->quantity,
                'total_price' => 'Rp. ' . number_format($order->total_price, 0, '.', ',')
            ];

            DB::commit();

            return new ResponseResource(true, 'Order Transaction created', $orderResponse, [
                'code' => 201,
                'product_name' => $getProduct->name,
            ], 201);
        } catch (\Exception $error) {
            DB::rollBack();

            return new ResponseResource(false, $error->getMessage(), null, [
                'code' => 500
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        $getOrder = $this->orderTransactionService->getByFirst('uuid', $uuid, true);

        if (!$getOrder) {
            return new ResponseResource(false, 'Order not found with uuid: ' . $uuid . '', null, [
                'code' => 404
            ], 404);
        }

        $getOrder->total_price = 'Rp. ' . number_format($getOrder->total_price, 0, '.', ',');

        return new ResponseResource(true, 'Order transaction found', $getOrder, [
            'code' => 200
        ], 200);
    }

    public function showByStudent(string $id)
    {
        $getStudentOrder = $this->orderTransactionService->getByStudent($id);

        // cek student
        if ($id != auth()->user()->id && auth()->user()->role != 'owner') {
            return new ResponseResource(false, 'Unauthorized', null, [
                'code' => 401
            ], 401);
        }

        if ($getStudentOrder->isEmpty()) {
            return new ResponseResource(false, 'Order not found with student id: ' . $id . '', null, [
                'code' => 404
            ], 404);
        }

        $getStudentOrder->map(function ($order) {
            $total_price = (int) $order->total_price;
            $order->formatted_total_price = 'Rp. ' . number_format($total_price, 0, '.', ',');

            return $order;
        });

        $total_transactions = 'Rp. ' . number_format($getStudentOrder->sum('total_price'), 0, '.', ',');

        return new ResponseResource(true, 'Order transaction found', $getStudentOrder, [
            'code' => 200,
            'total_transactions' => $total_transactions
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderTransactionRequest $request, string $uuid)
    {
        // cek validasi
        $data = $request->validated();

        DB::beginTransaction();

        try {
            // get order
            $getOrder = $this->orderTransactionService->getByFirst('uuid', $uuid, true);

            if (!$getOrder) {
                return new ResponseResource(false, 'Order not found with uuid: ' . $uuid . '', null, [
                    'code' => 404
                ], 404);
            }

            // get product
            $getProduct = $this->productService->getByFirst('id', $data['product_id']);

            // cek stock
            $requestQuantity = $data['quantity'] - $getOrder->quantity;

            if ($getProduct->quantity < $requestQuantity) {
                // misal : roti di database = 2, roti permintaan user = 3, maka gagal
                return new ResponseResource(false, 'Stock not available', null, [
                    'code' => 200,
                    'product_name' => $getProduct->name,
                    'product_stock' => $getProduct->quantity,
                    'request_stock' => $data['quantity']
                ], 200);
            }

            // insert
            $order = $this->orderTransactionService->update($data, $getOrder->uuid, $getProduct->id);

            // custom response
            $orderResponse = [
                'uuid' => $order->uuid,
                'product_id' => $order->product_id,
                'quantity' => $order->quantity,
                'total_price' => 'Rp. ' . number_format($order->total_price, 0, '.', ',')
            ];

            DB::commit();

            return new ResponseResource(true, 'Order Transaction updated', $orderResponse, [
                'code' => 200,
                'product_name' => $getProduct->name,
            ], 200);
        } catch (\Exception $error) {
            DB::rollBack();

            return new ResponseResource(false, $error->getMessage(), null, [
                'code' => 500
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $getOrder = $this->orderTransactionService->getByFirst('uuid', $uuid);

        if (!$getOrder) {
            return new ResponseResource(false, 'Order not found with uuid: ' . $uuid . '', null, [
                'code' => 404
            ], 404);
        }

        $getOrder->delete();

        return new ResponseResource(true, 'Order deleted successfully', null, [
            'code' => 200
        ], 200);
    }
}
