<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SupplierRequest;
use App\Http\Services\SupplierService;
use App\Http\Resources\ResponseResource;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class SupplierController extends Controller implements HasMiddleware
{

    public function __construct(private SupplierService $supplierService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('owner')
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->paginate) {
            $suppliers = $this->supplierService->getSupplier(true);
        } else {
            $suppliers = $this->supplierService->getSupplier();
        }

        if ($suppliers->isEmpty()) {
            return new ResponseResource(true, 'Suppliers not available', null, [
                'code' => 200
            ], 200);
        }

        return new ResponseResource(true, 'List of suppliers', $suppliers, [
            'code' => 200,
            'total_suppliers' => $suppliers->count()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        $data = $request->validated();

        try {
            $supplier = $this->supplierService->create($data);

            $supplierResponse = [
                'uuid' => $supplier->uuid,
                'code' => $supplier->code,
                'name' => $supplier->name,
                'address' => $supplier->address,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
            ];

            return new ResponseResource(true, 'Supplier created', $supplierResponse, [
                'code' => 201
            ], 201);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), null, [
                'code' => 500
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        $supplier = $this->supplierService->getByFirst('uuid', $uuid);

        if (!$supplier) {
            return new ResponseResource(false, 'Supplier not found with id: ' . $uuid . '', null, [
                'code' => 404
            ], 404);
        }

        return new ResponseResource(true, 'Supplier found', $supplier, [
            'code' => 200
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, string $uuid)
    {
        $data = $request->validated();

        try {
            $supplier = $this->supplierService->getByFirst('uuid', $uuid);

            if (!$supplier) {
                return new ResponseResource(false, 'Supplier not found with id: ' . $uuid . '', null, [
                    'code' => 404
                ], 404);
            }

            $supplier->update($data);

            $supplierResponse = [
                'uuid' => $supplier->uuid,
                'code' => $supplier->code,
                'name' => $supplier->name,
                'address' => $supplier->address,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
            ];

            return new ResponseResource(true, 'Supplier updated', $supplierResponse, [
                'code' => 200
            ], 200);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), null, [
                'code' => 500
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $getSupplier = $this->supplierService->getByFirst('uuid', $uuid);

        if (!$getSupplier) {
            return new ResponseResource(false, 'Supplier not found with id: ' . $uuid . '', null, [
                'code' => 404
            ], 404);
        }

        $getSupplier->delete();

        return new ResponseResource(true, 'Supplier deleted', null, [
            'code' => 200
        ], 200);
    }
}
