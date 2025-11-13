<?php

namespace App\Repositories\Contracts;

interface ShopifyProductRepositoryInterface
{
    public function productCreate(array $data, string $shopDomain, string $accessToken): array;
    public function createProductBulkVariants(string $id, string $locationId, array $data, string $shopDomain, string $accessToken): array;
    public function updateProductBulkVariants(string $id, array $variantResponse, array $data, string $shopDomain, string $accessToken): array;
    public function setInventoryQuantities(string $inventoryItemId, int $quantity, string $locationId, string $shopDomain, string $accessToken): array;
}
