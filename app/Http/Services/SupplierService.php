<?php

namespace App\Http\Services;

use App\Models\Supplier;
use Illuminate\Support\Str;

class SupplierService
{
    public function getSupplier($paginate = false)
    {
        if ($paginate) {
            $suppliers = Supplier::when(request()->search, function ($query) {
                $query->where('name', 'like', '%' . request()->search . '%');
            })->latest()->paginate(10);

            // append search
            $suppliers->appends(['search' => request()->search]);
        } else {
            $suppliers = Supplier::when(request()->search, function ($query) {
                $query->where('name', 'like', '%' . request()->search . '%');
            })->get();
        }

        return $suppliers;
    }

    public function getByFirst(string $column, string $value)
    {
        return Supplier::where($column, $value)->first();
    }

    public function create(array $data)
    {
        $data['slug'] = Str::slug($data['name']);

        return Supplier::create($data);
    }
}
