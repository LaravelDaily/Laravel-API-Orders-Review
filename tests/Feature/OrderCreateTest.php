<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_successfully()
    {
        $user = User::factory()->create();

        // Create a product with sufficient stock
        $product = Product::factory()->create(['stock' => 10]);

        // Define payload for the new order
        $orderData = [
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
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

        // Send a POST request to create the order with the token in the headers
        $response = $this
            ->actingAs($user)
            ->postJson('/api/v1/orders', $orderData);

        // Assert the response status is 201 (Created)
        $response->assertStatus(201);

        // Assert the order was created in the database
        $this->assertDatabaseHas('orders', [
            'name' => 'Test Order',
            'description' => 'Test Order Description',
        ]);

        // Assert the product stock was decremented
        $this->assertEquals(5, $product->fresh()->stock);
    }

    public function test_create_order_fails_due_to_insufficient_stock()
    {
        $user = User::factory()->create();

        // Create a product with low stock
        $product = Product::factory()->create(['stock' => 2]);

        // Data for the new order (quantity exceeds stock)
        $orderData = [
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
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

        // Send a POST request to create the order
        $response = $this
            ->actingAs($user)
            ->postJson('/api/v1/orders', $orderData);

        // Assert the response status is 422 (Unprocessable Entity)
        $response->assertStatus(422);

        // Assert the order was not created in the database
        $this->assertDatabaseMissing('orders', [
            'name' => 'Test Order',
        ]);

        // Assert the product stock remains unchanged
        $this->assertEquals(2, $product->fresh()->stock);
    }

    public function test_create_order_with_zero_quantity()
    {
        $user = User::factory()->create();

        // Create a product
        $product = Product::factory()->create(['stock' => 10]);

        // Data for the new order with zero quantity
        $orderData = [
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
                    'name' => 'Test Order',
                    'description' => 'Test Order Description',
                    'status' => 'P',
                    'date' => now()->toDateString(),
                ],
                'relationships' => [
                    'products' => [
                        ['id' => $product->id, 'quantity' => 0, 'price' => 100],
                    ],
                ],
            ],
        ];

        // Send a POST request to create the order
        $response = $this
            ->actingAs($user)
            ->postJson('/api/v1/orders', $orderData);

        // Assert the response status is 422 (Unprocessable Entity)
        $response->assertStatus(422);
    }

    public function test_create_order_with_multiple_products()
    {
        $user = User::factory()->create();

        // Create products with sufficient stock
        $product1 = Product::factory()->create(['stock' => 10]);
        $product2 = Product::factory()->create(['stock' => 15]);

        // Data for the new order
        $orderData = [
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
                    'name' => 'Test Order',
                    'description' => 'Test Order Description',
                    'status' => 'P',
                    'date' => now()->toDateString(),
                ],
                'relationships' => [
                    'products' => [
                        ['id' => $product1->id, 'quantity' => 4, 'price' => 100],
                        ['id' => $product2->id, 'quantity' => 5, 'price' => 200],
                    ],
                ],
            ],
        ];

        // Send a POST request to create the order
        $response = $this
            ->actingAs($user)
            ->postJson('/api/v1/orders', $orderData);

        // Assert the response status is 201 (Created)
        $response->assertStatus(201);

        // Assert the product stocks were updated correctly
        $this->assertEquals(6, $product1->fresh()->stock); // 10 - 4 = 6
        $this->assertEquals(10, $product2->fresh()->stock); // 15 - 5 = 10
    }

    public function test_update_order_with_multiple_products()
    {
        $user = User::factory()->create();

        // Create products with sufficient stock
        $product1 = Product::factory()->create(['stock' => 10]);
        $product2 = Product::factory()->create(['stock' => 15]);

        // Create an order with the products
        $order = Order::factory()->create();
        $order->update(['user_id' => $user->id]);
        $order->products()->attach($product1->id, ['quantity' => 3, 'price' => 100]);
        $order->products()->attach($product2->id, ['quantity' => 5, 'price' => 100]);

        // Data to update the order
        $orderData = [
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
                    'name' => 'Test Order',
                    'description' => 'Test Order Description',
                    'status' => 'P',
                    'date' => now()->toDateString(),
                ],
                'relationships' => [
                    'products' => [
                        ['id' => $product1->id, 'quantity' => 4, 'price' => 100],
                        ['id' => $product2->id, 'quantity' => 2, 'price' => 200],
                    ],
                ],
            ],
        ];

        // Send a PUT request to update the order
        $response = $this
            ->actingAs($user)
            ->putJson("/api/v1/orders/{$order->id}", $orderData);

        // Assert the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert the product stocks were updated correctly
        $this->assertEquals(9, $product1->fresh()->stock); // 10 - (4 - 3) = 9
        $this->assertEquals(18, $product2->fresh()->stock); // 15 + (5 - 2) = 18
    }

    public function test_update_order_successfully()
    {
        $user = User::factory()->create();;

        // Create a product with sufficient stock
        $product = Product::factory()->create(['stock' => 10]);

        // Create an order with the product
        $order = Order::factory()->create();
        $order->update(['user_id' => $user->id]);
        $order->products()->attach($product->id, ['quantity' => 3, 'price' => 100]);

        // Data to update the order
        $orderData = [
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
                    'name' => 'Updated Order Name',
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

        // Send a PUT request to update the order
        $response = $this
            ->actingAs($user)
            ->putJson("/api/v1/orders/{$order->id}", $orderData);

        // Assert the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert the order was updated in the database
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'name' => 'Updated Order Name',
        ]);

        // Assert the product stock was updated correctly
        $this->assertEquals(8, $product->fresh()->stock); // 10 - (5 - 3) = 8
    }

    public function test_update_order_fails_due_to_insufficient_stock()
    {
        $user = User::factory()->create();

        // Create a product with low stock
        $product = Product::factory()->create(['stock' => 2]);

        // Create an order with the product
        $order = Order::factory()->create();
        $order->update(['user_id' => $user->id]);
        $order->products()->attach($product->id, ['quantity' => 3, 'price' => 100]);

        // Data to update the order (new quantity exceeds stock)
        $orderData = [
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
                    'name' => 'Updated Order Name',
                    'description' => 'Test Order Description',
                    'status' => 'P',
                    'date' => now()->toDateString(),
                ],
                'relationships' => [
                    'products' => [
                        ['id' => $product->id, 'quantity' => 6, 'price' => 100], // New quantity exceeds stock
                    ],
                ],
            ],
        ];

        // Send a PUT request to update the order
        $response = $this
            ->actingAs($user)
            ->putJson("/api/v1/orders/{$order->id}", $orderData);

        // Assert the response status is 422 (Unprocessable Entity)
        $response->assertStatus(422);

        // Assert the product stock remains unchanged
        $this->assertEquals(2, $product->fresh()->stock);
    }

    public function test_update_order_with_invalid_product_id()
    {
        $user = User::factory()->create();

        // Create an order
        $order = Order::factory()->create();
        $order->update(['user_id' => $user->id]);

        // Data to update the order with an invalid product ID
        $orderData = [
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
                    'name' => 'Test Order',
                    'description' => 'Test Order Description',
                    'status' => 'P',
                    'date' => now()->toDateString(),
                ],
                'relationships' => [
                    'products' => [
                        ['id' => 999, 'quantity' => 4, 'price' => 100],
                    ],
                ],
            ],
        ];

        // Send a PUT request to update the order
        $response = $this
            ->actingAs($user)
            ->putJson("/api/v1/orders/{$order->id}", $orderData);

        // Assert the response status is 422 (Unprocessable Entity)
        $response->assertStatus(422);
    }
}
