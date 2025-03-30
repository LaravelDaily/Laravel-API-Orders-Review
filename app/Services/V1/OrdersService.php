<?php

namespace App\Services\V1;

use App\Http\Requests\Api\V1\BaseOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrdersService
{
    public function createOrderHandleProducts(BaseOrderRequest $request): Order
    {
        return DB::transaction(function () use ($request) {
            // Create new order
            $order = Order::create($request->mappedAttributes());

            // Get products from request
            $incomingProducts = collect($request->input('data.relationships.products', []))->keyBy('id');

            foreach ($incomingProducts as $productId => $productData) {
                $quantity = $productData['quantity'];
                $price = $productData['price'];

                // Lock the product row for update to prevent race conditions
                $lockedProduct = Product::where('id', $productId)->lockForUpdate()->first();

                // Attach product to order with quantity and price
                $order->products()->attach($productId, ['quantity' => $quantity, 'price' => $price]);

                // Decrease stock
                $lockedProduct->decrement('stock', $quantity);
            }

            return $order;
        });
    }

    public function updateOrderHandleProducts(BaseOrderRequest $request, Order $order): void
    {
        DB::transaction(function () use ($request, $order) {
            // Lock the order row
            $order->lockForUpdate();

            // Update order attributes
            $order->update($request->mappedAttributes());

            // Get incoming products from request
            $incomingProducts = collect($request->input('data.relationships.products', []))->keyBy('id');

            // Fetch current products with pivot data and lock for update
            $currentProducts = $order->products()->lockForUpdate()->get()->keyBy('id');

            // Case 1: Detach removed products & increment stock
            foreach ($currentProducts as $productId => $product) {
                if (! $incomingProducts->has($productId)) { // Detach removed products
                    $lockedProduct = Product::where('id', $productId)->lockForUpdate()->first();
                    $lockedProduct->increment('stock', $product->pivot->quantity);
                    $order->products()->detach($productId);
                }
            }

            // Case 2 & 3: Attach new products or update existing ones
            foreach ($incomingProducts as $productId => $productData) {
                $quantity = $productData['quantity'];
                $price = $productData['price'];

                $lockedProduct = Product::where('id', $productId)->lockForUpdate()->first();

                if ($currentProducts->has($productId)) { // Update existing products
                    $currentQuantity = $currentProducts[$productId]->pivot->quantity;
                    $quantityDifference = $quantity - $currentQuantity;

                    if ($quantityDifference > 0) {
                        $lockedProduct->decrement('stock', $quantityDifference);
                    } elseif ($quantityDifference < 0) {
                        $lockedProduct->increment('stock', abs($quantityDifference));
                    }

                    $order->products()->updateExistingPivot($productId, ['quantity' => $quantity, 'price' => $price]);
                } else { // Attach new products
                    $order->products()->attach($productId, ['quantity' => $quantity, 'price' => $price]);
                    $lockedProduct->decrement('stock', $quantity);
                }
            }
        });
    }

    public function deleteOrderHandleProducts(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Fetch current products with pivot data and lock for update
            $currentProducts = $order->products()->lockForUpdate()->get()->keyBy('id');

            // Detach all products
            $order->products()->detach();

            // Increment stock for each product
            foreach ($currentProducts as $productId => $product) {
                $lockedProduct = Product::where('id', $productId)->lockForUpdate()->first();
                $lockedProduct->increment('stock', $product->pivot->quantity);
            }

            // Delete order
            $order->delete();
        });
    }
}
