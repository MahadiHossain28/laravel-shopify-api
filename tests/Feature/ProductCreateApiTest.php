<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;
use App\Services\ShopifyProductService;

class ProductCreateApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Close Mockery after each test
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected string $apiUrl = '/api/v1/shopify/products';
    protected string $shopDomain = 'test.myshopify.com';
    protected string $accessToken = "access_token";

    /**
     * Helper to return default Shopify headers
     */
    protected function shopifyHeaders(): array
    {
        return [
            'X-Shopify-Shop-Domain' => $this->shopDomain,
            'X-Shopify-Access-Token' => $this->accessToken,
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_required_fields():void
    {
        $response = $this->withHeaders($this->shopifyHeaders())
            ->postJson($this->apiUrl, []); // Empty payload

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'description',
                'vendor',
                'product_type',
                'status',
                'options',
                'variants',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_image_urls():void
    {
        $productData = [
            'title' => 'Test Product',
            'description' => 'This is a test product description.',
            'vendor' => 'Test Vendor',
            'product_type' => 'Test Type',
            'status' => 'active',
            'options' => [
                ['name' => 'Color', 'values' => ['Red', 'Green']],
                ['name' => 'Size', 'values' => ['S', 'M']],
            ],
            'variants' => [
                [
                    'options' => ['Red', 'S'],
                    'price' => 10.00,
                    'sku' => 'TEST-RED-S',
                    'inventory_quantity' => 10,
                ],
            ],
            'images' => [
                ['src' => 'invalid-url', 'alt' => 'Product Image']
            ]
        ];

        $response = $this->withHeaders($this->shopifyHeaders())
            ->postJson($this->apiUrl, $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images.0.src']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_status_enum():void
    {
        $productData = [
            'title' => 'Test Product',
            'description' => 'This is a test product description.',
            'vendor' => 'Test Vendor',
            'product_type' => 'Test Type',
            'status' => 'invalid_status', // Invalid
            'options' => [
                ['name' => 'Color', 'values' => ['Red', 'Green']],
                ['name' => 'Size', 'values' => ['S', 'M']],
            ],
            'variants' => [
                [
                    'options' => ['Red', 'S'],
                    'price' => 10.00,
                    'sku' => 'TEST-RED-S',
                    'inventory_quantity' => 10,
                ],
            ],
        ];

        $response = $this->withHeaders($this->shopifyHeaders())
            ->postJson($this->apiUrl, $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_error_if_shopify_credentials_are_missing():void
    {
        $productData = [
            'title' => 'Test Product',
            'description' => 'This is a test product description.',
            'vendor' => 'Test Vendor',
            'product_type' => 'Test Type',
            'status' => 'active',
            'options' => [
                ['name' => 'Color', 'values' => ['Red', 'Green']],
                ['name' => 'Size', 'values' => ['S', 'M']],
            ],
            'variants' => [
                [
                    'options' => ['Red', 'S'],
                    'price' => 10.00,
                    'sku' => 'TEST-RED-S',
                    'inventory_quantity' => 10,
                ],
            ],
        ];

        $response = $this->postJson($this->apiUrl, $productData);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Missing Shopify credentials in headers.',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_shopify_service_exceptions():void
    {
        $mockShopifyProductService = Mockery::mock(ShopifyProductService::class);
        $this->app->instance(ShopifyProductService::class, $mockShopifyProductService);

        $productData = [
            'title' => 'Test Product',
            'description' => 'This is a test product description.',
            'vendor' => 'Test Vendor',
            'product_type' => 'Test Type',
            'status' => 'active',
            'options' => [
                ['name' => 'Color', 'values' => ['Red', 'Green']],
                ['name' => 'Size', 'values' => ['S', 'M']],
            ],
            'variants' => [
                [
                    'options' => ['S', 'Red'],
                    'price' => 19.99,
                    'sku' => 'TSHIRT-S-RED',
                    'inventory_quantity' => 100,
                ],
                [
                    'options' => ['S', 'Green'],
                    'price' => 19.99,
                    'sku' => 'TSHIRT-S-GREEN',
                    'inventory_quantity' => 50,
                ],
            ],
        ];

        $mockShopifyProductService->shouldReceive('createProduct')
            ->once()
            ->andThrow(new \Exception('Shopify API error: Something went wrong.'));

        $response = $this->withHeaders($this->shopifyHeaders())
            ->postJson($this->apiUrl, $productData);

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'message' => 'Shopify API error: Something went wrong.',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_shopify_product_successfully():void
    {
        $mockShopifyProductService = Mockery::mock(ShopifyProductService::class);
        $this->app->instance(ShopifyProductService::class, $mockShopifyProductService);

        $productData = [
            'title' => 'Test Product',
            'description' => 'This is a test product description.',
            'vendor' => 'Test Vendor',
            'product_type' => "Apparel",
            'status' => 'active',
            'options' => [
                ['name' => 'Color', 'values' => ['Red', 'Green']],
                ['name' => 'Size', 'values' => ['S', 'M']],
            ],
            'variants' => [
                [
                    'options' => ['Size' => 'S', 'Color' => 'Red'],
                    'price' => 19.99,
                    'sku' => 'TSHIRT-S-RED',
                    'inventory_quantity' => 100,
                ],
                [
                    'options' => ['Size' => 'S', 'Color' => 'Blue'],
                    'price' => 19.99,
                    'sku' => 'TSHIRT-S-BLUE',
                    'inventory_quantity' => 50,
                ],
            ],
            'images' => [
                [
                    'src' => "https://cdn.shopify.com/s/files/1/0533/2089/files/placeholder-images-image_large.png",
                    'alt' => 'Product Image'
                ]
            ]
        ];

        $expectedResponseProduct = [
            'title' => 'Test Product',
        ];

        $mockShopifyProductService->shouldReceive('createProduct')
            ->once()
            ->with(
                Mockery::subset($productData),
                $this->shopDomain,
                $this->accessToken
            )
            ->andReturn($expectedResponseProduct);

        $response = $this->withHeaders($this->shopifyHeaders())
            ->postJson($this->apiUrl, $productData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Product created successfully on Shopify.',
                'product' => $expectedResponseProduct,
            ]);
    }
}
