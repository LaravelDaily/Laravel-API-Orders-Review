<?php

namespace Tests\Feature;

use App\Models\Order;
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
            ->getJson('/api/v1/orders/'.$order->id);

        $response->assertStatus(200);
    }

    public function test_returns_404_for_non_existing_order()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson('/api/v1/orders/999');

        $response->assertStatus(404)
            ->assertJson([
                'errors' => 'Order not found',
            ]);
    }

    public function test_returns_403_for_order_by_other_user()
    {
        $user1 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user1->id]);

        $user2 = User::factory()->create();
        $response = $this
            ->actingAs($user2)
            ->getJson('/api/v1/orders/'.$order->id);

        $response->assertStatus(403)
            ->assertJson([
                'errors' => 'You are not authorized.',
            ]);
    }
}
