<?php

namespace App\Repositories\Eloquents\Product;

use App\Repositories\Contracts\ShopifyProductRepositoryInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ShopifyProductRepository implements ShopifyProductRepositoryInterface
{
    public function __construct()
    {
        //
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private function apiRequest(string $shopDomain, string $token, string $query, ?array $variables = null): array
    {
        $client = new Client(['base_uri' => "https://{$shopDomain}/admin/api/2025-07/graphql.json"]);

        try {
            $payload = ['query' => $query];
            if ($variables) $payload['variables'] = $variables;

            $response = $client->post('', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Shopify-Access-Token' => $token,
                ],
                'json' => $payload,
            ]);

            $body = $response->getBody()->getContents();
            Log::info($body);
            return json_decode($body, true);
        } catch (RequestException $e) {
            $error = $e->hasResponse()
                ? $e->getResponse()->getBody()->getContents()
                : $e->getMessage();
            Log::error('Shopify API Error', ['error' => $error]);
            throw new Exception("Shopify API request failed: {$error}");
        }
    }


    /**
     * @throws GuzzleException
     */
    public function productCreate(array $data, string $shopDomain, string $accessToken): array
    {
        $mutation = <<<GQL
        mutation createProduct(\$input: ProductInput!, \$media: [CreateMediaInput!]) {
            productCreate(input: \$input, media: \$media) {

                product {
                    id
                    title
                    status
                    options {
                        id
                        name
                        values
                        optionValues {
                            id
                            name
                        }
                    }
                    variants(first: 5) {
                        edges {
                            node {
                                id
                                selectedOptions {
                                    name,
                                    value
                                }
                                inventoryItem {
                                    id,
                                }
                            }
                        }
                    }
                }
                shop {
                    locations (first: 5){
                        nodes {
                            id
                            name
                            isActive
                            isPrimary
                        }
                    }
                }
                userErrors {
                    field
                    message
                }
            }
        }
        GQL;

        $getMedia =[];

        foreach ($data['images'] as $image) {
            $getMedia = [
                'alt' => $image['alt'],
                'mediaContentType' => "IMAGE",
                'originalSource' => $image['src'],
            ];
        }

        $variables = [
            'input' => [
                'title' => $data['title'],
                'descriptionHtml' => $data['description'],
                'vendor' => $data['vendor'],
                'productType' => $data['product_type'],
                'status' => strtoupper($data['status']),
                'productOptions' => array_map(function ($option) {
                    return [
                        'name' => $option['name'],
                        'values' => array_map(function ($value) {
                            return ['name' => $value];
                        }, $option['values']),
                    ];
                }, $data['options']),
            ],
            'media' => $getMedia,
        ];

        return $this->apiRequest($shopDomain, $accessToken, $mutation, $variables);
    }

    /**
     * @throws GuzzleException
     */
    public function createProductBulkVariants(string $id, string $locationId, array $data, string $shopDomain, string $accessToken): array
    {
        $mutation = <<<GQL
        mutation ProductVariantsCreate(\$productId: ID!, \$variants: [ProductVariantsBulkInput!]!) {
            productVariantsBulkCreate(productId: \$productId, variants: \$variants) {
                productVariants {
                    id
                    title
                }
                userErrors {
                    field
                    message
                }
            }
        }
        GQL;

        $variables = [
            'productId' => $id,
            'variants' => collect($data)->map(function ($variant) use ($locationId) {
                return [
                    'inventoryItem' => [
                        'sku' => $variant['sku']
                    ],
                    'inventoryQuantities' => [
                        'availableQuantity' => $variant['inventory_quantity'],
                        'locationId' => $locationId,
                    ],
                    'price' => $variant['price'],
                    'optionValues' => collect($variant['options'])->map(function ($optionValue, $optionName) {
                        return [
                            'name' => $optionValue,
                            'optionName' => $optionName
                        ];
                    })->values()->toArray(),
                ];
            })->toArray()
        ];

        return $this->apiRequest($shopDomain, $accessToken, $mutation, $variables);
    }

    /**
     * @throws GuzzleException
     */
    public function updateProductBulkVariants(string $id, array $variantResponse, array $data, string $shopDomain, string $accessToken): array
    {
        $mutation = <<<GQL
        mutation productVariantsBulkUpdate(\$productId: ID!, \$variants: [ProductVariantsBulkInput!]!) {
            productVariantsBulkUpdate(productId: \$productId, variants: \$variants) {
                product {
                    id
                    title
                }
                userErrors {
                    field
                    message
                }
            }
        }
        GQL;

        $variables = [
            'productId' => $id,
            'variants' => collect($data)->map(function ($variant) use ($variantResponse) {
                return [
                    'id' => $variantResponse[0]['node']['id'],
                    'inventoryItem' => [
                        'sku' => $variant['sku'],
                        'tracked' => true,
                    ],
                    'price' => $variant['price'],
                    'optionValues' => collect($variant['options'])->map(function ($optionValue, $optionName) {
                        return [
                            'name' => $optionValue,
                            'optionName' => $optionName
                        ];
                    })->values()->toArray(),
                ];
            })->toArray()
        ];

        return $this->apiRequest($shopDomain, $accessToken, $mutation, $variables);
    }

    /**
     * @throws GuzzleException
     */
    public function setInventoryQuantities(string $inventoryItemId, int $quantity, string $locationId, string $shopDomain, string $accessToken): array
    {
        $mutation = <<<GQL
        mutation InventorySet(\$input: InventorySetQuantitiesInput!) {
            inventorySetQuantities(input: \$input) {
                userErrors {
                    field
                    message
                }
                inventoryAdjustmentGroup {
                    createdAt
                    reason
                    changes {
                        name
                        delta
                    }
                }
            }
        }
        GQL;

        $variables = [
            'input' => [
                'ignoreCompareQuantity' => true,
                "reason" => "correction",
                "name" => "available",
                "quantities" => [
                    [
                        "quantity" => $quantity,
                        "inventoryItemId" => $inventoryItemId,
                        "locationId" => $locationId
                    ]
                ]
            ]
        ];

        return $this->apiRequest($shopDomain, $accessToken, $mutation, $variables);
    }
}
