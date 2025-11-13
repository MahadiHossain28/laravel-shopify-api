<?php

namespace App\Services;

use App\Repositories\Contracts\ShopifyProductRepositoryInterface;
use Exception;

class ShopifyProductService
{
    public function __construct(protected ShopifyProductRepositoryInterface $shopifyProductRepository)
    {

    }

    /**
     * @throws Exception
     */
    public function createProduct(array $data, string $shopDomain, string $accessToken): array
    {
        $response = $this->shopifyProductRepository->productCreate($data, $shopDomain, $accessToken);
        $locationId = $response['data']['productCreate']['shop']['locations']['nodes'][0]['id'];

        $userErrors = $response['data']['productCreate']['userErrors'] ?? [];
        if (!empty($userErrors)) {
            $messages = collect($userErrors)->pluck('message')->implode(', ');
            throw new Exception("Shopify error: {$messages}");
        }

        $createdProduct = $response['data']['productCreate']['product'] ?? [];

        $responseOptions = [];
        foreach ($createdProduct['variants']['edges'][0]['node']['selectedOptions'] as $option) {
            $responseOptions[$option['name']] = $option['value'];
        }

        $matchedVariants = [];
        $nonMatchedVariants = [];

        foreach ($data['variants'] as $variant) {
            if ($variant['options'] == $responseOptions) {
                $matchedVariants[] = $variant;
            } else {
                $nonMatchedVariants[] = $variant;
            }
        }
        // create bulk variant of not created variants
        $this->shopifyProductRepository->createProductBulkVariants($createdProduct['id'], $locationId, $nonMatchedVariants, $shopDomain, $accessToken);
        // add quantity to default created variant
        $inventoryItemId = $createdProduct['variants']['edges'][0]['node']['inventoryItem']['id'];
        $this->shopifyProductRepository->setInventoryQuantities($inventoryItemId, $matchedVariants[0]['inventory_quantity'], $locationId, $shopDomain, $accessToken);
        // update default created variant
        $responseData = $this->shopifyProductRepository->updateProductBulkVariants($createdProduct['id'], $createdProduct['variants']['edges'], $matchedVariants, $shopDomain, $accessToken);

        return $responseData['data']['productVariantsBulkUpdate']['product'];
    }
}
