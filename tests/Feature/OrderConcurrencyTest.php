<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class OrderConcurrencyTest extends TestCase
{
    use RefreshDatabase; // Ensures a fresh DB for each test

    public function test_concurrent_orders_handle_stock_correctly()
    {
        // Create a product with limited stock
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 75,
            'stock' => 10, // Only 10 in stock
        ]);

        // Define order payload
        $orderData = [
            'data' => [
                'attributes' => [
                    'user_id' => 1,
                    'name' => 'Test Order',
                    'description' => 'Test Order Description',
                    'status' => 'P',
                    'date' => now()->toDateString(),
                ],
                'relationships' => [
                    'products' => [
                        ['id' => $product->id, 'quantity' => 5, 'price' => 100],
                    ],
                ],
            ],
        ];

        // Simulate 3 concurrent requests
        $responses = [];

        DB::beginTransaction(); // Start transaction for concurrency simulation
        try {
            [$token] = $this->createAuthUserToken();

            // Simulate concurrent order requests using async HTTP requests
            $responses = [];

            \Illuminate\Support\Facades\Queue::fake();

            // Dispatch multiple HTTP requests
            for ($i = 0; $i < 3; $i++) {
                $responses[] = $this->withHeaders([
                    'Authorization' => 'Bearer '.$token,
                ])->postJson('/api/v1/orders', $orderData);
            }

            // Process responses
            foreach ($responses as $response) {
                Log::info('Order Response: '.$response->status());
            }

            DB::commit(); // Commit if all succeed
        } catch (\Throwable $e) {
            DB::rollback(); // Rollback on failure
            Log::error('Concurrency Test Failed: '.$e->getMessage());
        }

        // Check how many orders were actually created
        $ordersCount = Order::count();
        $remainingStock = Product::find($product->id)->stock;

        // Assertions
        $this->assertLessThanOrEqual(2, $ordersCount, 'Too many orders created.');
        $this->assertEquals(0, $remainingStock, 'Stock mismatch, should be zero or minimal. Orders created: '.$ordersCount);
        // output the remaining stock
        Log::info('Remaining Stock: '.$remainingStock);
    }
}
