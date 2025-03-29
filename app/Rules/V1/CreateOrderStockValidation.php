<?php

namespace App\Rules\V1;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CreateOrderStockValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Ensure the value is an array
        if (!is_array($value)) {
            $fail('The Order products must be an array.');
            return;
        }

        // Check stock for each product
        foreach ($value as $product) {
            // Ensure the product has the required keys
            if (!isset($product['id']) || !isset($product['quantity'])) {
                $fail('Each product must have an ID and quantity.');
                return;
            }

            // Find the product in the database
            $dbProduct = Product::find($product['id']);

            // Check if the product exists and has sufficient stock
            if (!$dbProduct) {
                $fail('Product with ID ' . $product['id'] . ' does not exist.');
                return;
            }

            if ($dbProduct->stock < $product['quantity']) {
                $fail('Insufficient stock for product (' . $dbProduct->name . '). Current stock: ' . $dbProduct->stock . '.');
                return;
            }
        }
    }
}
