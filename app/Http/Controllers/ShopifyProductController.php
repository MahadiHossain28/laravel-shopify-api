<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateShopifyProductRequest;
use App\Services\ShopifyProductService;
use Illuminate\Http\JsonResponse;

class ShopifyProductController extends Controller
{
    public function __construct(protected ShopifyProductService $shopifyProductService)
    {

    }

    public function store(CreateShopifyProductRequest $request): JsonResponse
    {
        $shopDomain = $request->header('X-Shopify-Shop-Domain');
        $accessToken = $request->header('X-Shopify-Access-Token');

        if (!$shopDomain || !$accessToken) {
            return response()->json(['error' => 'Missing Shopify credentials in headers.'], 400);
        }

        try {
            $product = $this->shopifyProductService->createProduct($request->validated(), $shopDomain, $accessToken);

            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully on Shopify.',
                'product' => $product,
            ],201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
