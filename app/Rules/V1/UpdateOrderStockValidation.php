<?php

namespace App\Rules\V1;

use App\Models\Order;
use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateOrderStockValidation implements ValidationRule
{
    public function __construct(
        protected Order $order,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Ensure the value is an array
        if (! is_array($value)) {
            $fail('The Order products must be an array.');

            return;
        }

        $order = Order::findOrFail($this->order->id)?->load('products');

        // Check stock for each product
        foreach ($value as $product) {
            // Ensure the product has the required keys
            if (! isset($product['id']) || ! isset($product['quantity'])) {
                $fail('Each product must have an ID and quantity.');

                return;
            }

            $productId = $product['id'];

            // Find the product in the database
            $dbProduct = Product::find($productId);
            // Check if the product exists and has sufficient stock
            if (! $dbProduct) {
                $fail('Product with ID '.$product['id'].' does not exist.');

                return;
            }

            // Get the new quantity of the product (in the request)
            $newQuantity = $product['quantity'];
            // Get the current quantity of the product in the order (if it exists)
            $currentQuantity = $order->products->find($productId)->pivot->quantity ?? 0;
            if ($newQuantity > $currentQuantity) {
                $stockDiff = $newQuantity - $currentQuantity;
                if ($dbProduct->stock < $stockDiff) {
                    $fail('Insufficient stock for product ('.$dbProduct->name.'). Current stock: '.$dbProduct->stock.'.');

                    return;
                }
            }
        }
    }
}
