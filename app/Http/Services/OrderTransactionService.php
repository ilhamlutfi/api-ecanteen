<?php

namespace App\Http\Services;

use App\Models\OrderTransaction;
use Illuminate\Support\Str;

class OrderTransactionService
{
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
            ->get(['uuid', 'student_id', 'product_id','quantity', 'total_price']);
        }

        return $transactions;
    }

    public function getByFirst(string $column, string $value, bool $relation = false)
    {
        if ($relation) {
            return OrderTransaction::where($column, $value)->with('category:id,name', 'supplier:id,name')->first();
        }

        return OrderTransaction::where($column, $value)->first();
    }

    public function create(array $data)
    {
        $data['slug'] = Str::slug($data['name']);

        return OrderTransaction::create($data);
    }

    public function update(array $data, string $uuid)
    {
        $data['slug'] = Str::slug($data['name']);

        return OrderTransaction::where('uuid', $uuid)->update($data);
    }
}
