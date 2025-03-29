<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(5)->create();
        $products = Product::factory(50)->create();

        // Create 60 orders
        // For each order, pick a random number of products (between 1 and 6) and attach them to the order
        Order::factory(60)->recycle($users)
            ->create()->each(function ($order) use ($products) {
                // Pick a random number of products for each order (between 1 and 6 products)
                $randomProducts = $products->random(rand(1, 10));

                // Attach each product to the order with a random quantity between 1 and 11
                $randomProducts->each(function ($product) use ($order) {
                    $quantity = rand(1, 7);  // Random quantity between 1 and 11
                    $order->products()->attach($product->id, ['quantity' => $quantity, 'price' => $product->price]);
                });
            }
            );
    }
}
