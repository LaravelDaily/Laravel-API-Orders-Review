<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_orders_list_successfully()
    {
        $user = User::factory()->create();
        Order::factory(10)->create();

        $response = $this
            ->actingAs($user)
            ->getJson('/api/v1/orders');

        $response->assertStatus(200);
    }

    public function test_returns_order_show_successfully()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this
            ->actingAs($user)
            ->getJson('/api/v1/orders/' . $order->id);

        $response->assertStatus(200);
    }
}
