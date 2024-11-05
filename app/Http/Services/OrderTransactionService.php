<?php

namespace App\Http\Services;

use Illuminate\Support\Str;
use App\Models\OrderTransaction;
use App\Http\Services\ProductService;
use App\Http\Resources\ResponseResource;

class OrderTransactionService
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function getTransaction($paginate = false)
    {
        if ($paginate) {
            $transactions = OrderTransaction::with('student:id,name', 'product:id,name,category_id', 'product.category:id,name')->when(request()->search, function ($query) {
                $query->where('name', 'like', '%' . request()->search . '%');
            })
                ->select(['uuid', 'student_id', 'product_id', 'quantity', 'total_price'])
                ->latest()
                ->paginate(10);

            // append search
            $transactions->appends(['search' => request()->search]);
        } else {
            $transactions = OrderTransaction::with('student:id,name', 'product:id,name,category_id', 'product.category:id,name')->when(request()->search, function ($query) {
                $query->where('name', 'like', '%' . request()->search . '%');
            })
                ->latest()
                ->limit(10)
                ->get(['uuid', 'student_id', 'product_id', 'quantity', 'total_price']);
        }

        return $transactions;
    }

    public function getByFirst(string $column, string $value, bool $relation = false)
    {
        if ($relation) {
            return OrderTransaction::where($column, $value)->with('student:id,name', 'product:id,name')->first();
        }

        return OrderTransaction::where($column, $value)->first();
    }

    public function getByStudent($id)
    {
        return OrderTransaction::where('student_id', $id)
            ->with('student:id,name', 'product:id,name')
            ->latest()->get();
    }

    public function create(array $data, int $product_id)
    {
        // get product
        $getProduct = $this->productService->getByFirst('id', $product_id);

        // insert data
        $data['total_price'] = $getProduct->price * $data['quantity'];

        // kurangi stok product
        $getProduct->decrement('quantity', $data['quantity']);

        return OrderTransaction::create($data);
    }

    public function update(array $data, string $uuid, int $product_id)
    {
        // get product
        $getProduct = $this->productService->getByFirst('id', $product_id);

        // insert data
        $data['total_price'] = $getProduct->price * $data['quantity'];

        $order = OrderTransaction::where('uuid', $uuid)->first();

        // change quantity
        $requestQuantity = $data['quantity'] - $order->quantity;
        // kurangi stok product
        $getProduct->decrement('quantity', $requestQuantity);

        // update
        $order->product_id = $product_id;
        $order->quantity = $data['quantity'];
        $order->total_price = $data['total_price'];
        $order->save();

        return $order;
    }
}
