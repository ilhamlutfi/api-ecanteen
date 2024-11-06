<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\FileService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProductRequest;
use App\Http\Services\ProductService;
use App\Http\Resources\ResponseResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class ProductController extends Controller implements HasMiddleware
{

    public function __construct(
        private ProductService $productService,
        private FileService $fileService
        ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('owner', except: ['index', 'productInt']),
            new Middleware('apikey', only: ['productInt', 'index']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // select all, select paginate, search
        $paginate = request()->paginate ? true : false;

        $products = $this->productService->getProduct($paginate);

        if ($products->isEmpty()) {
            return new ResponseResource(true, 'Products not available', null, [
                'code' => 200
            ], 200);
        }

        $productsResponse = $products->map(function ($product) {
            $product->price = 'Rp. ' . number_format($product->price, 0, '.', ',');
            $product->image = url(asset('storage/' . $product->image));
            // $product->makeHidden(['created_at', 'updated_at']); // hide field

            return $product;
        });

        return new ResponseResource(true, 'List of products', $productsResponse, [
            'code' => 200,
            'total_products' => $products->count()
        ], 200);
    }

    public function productInt()
    {
        $ip = 'product-int' . request()->ip();

        $executed = RateLimiter::attempt($ip,
            $perMinute = 2,
            function () {
                // select all, select paginate, search
                $paginate = request()->paginate ? true : false;

                $products = $this->productService->getProduct($paginate);

                if ($products->isEmpty()) {
                    return new ResponseResource(true, 'Products not available', null, [
                        'code' => 200
                    ], 200);
                }

                $productsResponse = $products->map(function ($product) {
                    $product->price = 'Rp. ' . number_format($product->price, 0, '.', ',');
                    $product->image = url(asset('storage/' . $product->image));
                    // $product->makeHidden(['created_at', 'updated_at']); // hide field

                    return $product;
                });

                return new ResponseResource(true, 'List of products', $productsResponse, [
                    'code' => 200,
                    'total_products' => $products->count()
                ], 200);
            }
        );

        if (!$executed) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests, please try again later.',
                'code' => 429
            ], 429);
        }

        return $executed;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        try {
            $uploadImage = $this->fileService->upload($data['image'], 'images');

            $data['image'] = $uploadImage;
            $product = $this->productService->create($data);

            $productResponse = [
                'uuid' => $product->uuid,
                'category_id' => $product->category_id,
                'supplier_id' => $product->supplier_id,
                'name' => $product->name,
                'image' => $product->image,
                'price' => $product->price,
                'quantity' => $product->quantity,
                'description' => $product->description
            ];

            $productMeta = $this->productService->getByFirst('uuid', $product->uuid, true);

            return new ResponseResource(true, 'Product created successfully', $productResponse, [
                'code' => 201,
                'category_name' => $productMeta->category->name,
                'supplier_name' => $productMeta->supplier->name,
                'image_url' => url(asset('storage/'. $productMeta->image))
            ], 201);

        } catch (\Exception $th) {
            return new ResponseResource(false, $th->getMessage(), null, [
                'code' => 500
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        $getProduct = $this->productService->getByFirst('uuid', $uuid, true);

        if (!$getProduct) {
            return new ResponseResource(false, 'Product not found with uuid: ' . $uuid . '', null, [
                'code' => 404
            ], 404);
        }

        $getProduct->price = 'Rp. ' . number_format($getProduct->price, 0, '.', ',');
        $getProduct->image = url(asset('storage/' . $getProduct->image));

        return new ResponseResource(true, 'Product found', $getProduct, [
            'code' => 200
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, string $uuid)
    {
        $data = $request->validated();

        DB::beginTransaction();


        try {
            $getProduct = $this->productService->getByFirst('uuid', $uuid, true);

            if (!$getProduct) {
                return new ResponseResource(false, 'Product not found with uuid: ' . $uuid . '', null, [
                    'code' => 404
                ], 404);
            }

            if ($request->hasFile('image')) {
                // unlink image
                $this->fileService->delete($getProduct->image);

                $uploadImage = $this->fileService->upload($data['image'], 'images');

                $data['image'] = $uploadImage;
            } else {
                $data['image'] = $getProduct->image;
            }

            $getProduct->update($data);

            $productResponse = [
                'uuid' => $getProduct->uuid,
                'category_id' => $getProduct->category_id,
                'supplier_id' => $getProduct->supplier_id,
                'name' => $getProduct->name,
                'image' => $getProduct->image,
                'price' => $getProduct->price,
                'quantity' => $getProduct->quantity,
                'description' => $getProduct->description
            ];

            DB::commit();

            return new ResponseResource(true, 'Product updated successfully', $productResponse, [
                'code' => 200,
                'category_name' => $getProduct->category->name,
                'supplier_name' => $getProduct->supplier->name,
                'image_url' => url(asset('storage/' . $getProduct->image))
            ], 200);
        } catch (\Exception $th) {
            DB::rollBack();

            if (isset($data['image'])) {
                $this->fileService->delete($data['image'], 'images');
            }

            return new ResponseResource(false, $th->getMessage(), null, [
                'code' => 500
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $getProduct = $this->productService->getByFirst('uuid', $uuid, true);

        if (!$getProduct) {
            return new ResponseResource(false, 'Product not found with uuid: ' . $uuid . '', null, [
                'code' => 404
            ], 404);
        }

        // unlink image
        $this->fileService->delete($getProduct->image);

        $getProduct->delete();

        return new ResponseResource(true, 'Product deleted successfully', null, [
            'code' => 200
        ], 200);
    }
}
