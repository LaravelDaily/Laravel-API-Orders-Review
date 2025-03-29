<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BaseOrderRequest extends FormRequest
{
    public function mappedAttributes()
    {
        $attributeMap = [
            'data.attributes.name' => 'name',
            'data.attributes.description' => 'description',
            'data.attributes.status' => 'status',
            'data.attributes.date' => 'date',
            'data.attributes.createdAt' => 'created_at',
            'data.attributes.updatedAt' => 'updated_at',
            'data.attributes.user_id' => 'user_id',
        ];

        $attributesToUpdate = [];
        foreach ($attributeMap as $key => $attribute) {
            if ($this->has($key)) {
                $attributesToUpdate[$attribute] = $this->input($key);
            }
        }

        return $attributesToUpdate;
    }

    public function messages(): array
    {
        return [
            'data.attributes.user_id.required' => 'The user_id field is required.',
            'data.attributes.user_id.exists' => 'The selected user_id is invalid.',
            'data.attributes.name.required' => 'The name field is required.',
            'data.attributes.name.string' => 'The name field must be a string.',
            'data.attributes.description.required' => 'The description field is required.',
            'data.attributes.description.string' => 'The description field must be a string.',
            'data.attributes.status.required' => 'The status field is required.',
            'data.attributes.status.string' => 'The status field must be a string.',
            'data.attributes.status.in' => 'The status field must be one of: P, F, C.',
            'data.attributes.date.required' => 'The date field is required.',
            'data.attributes.date.date' => 'The date field must be a date.',
            'data.relationships.products.required' => 'There must be at least one product in the order.',
            'data.relationships.products.array' => 'The products must be grouped in an array.',
            'data.relationships.products.*.id.required' => 'The products.id field is required.',
            'data.relationships.products.*.id.exists' => 'The selected products.id is invalid.',
            'data.relationships.products.*.quantity.required' => 'The products.quantity field is required.',
            'data.relationships.products.*.quantity.integer' => 'The products.quantity field must be an integer.',
            'data.relationships.products.*.quantity.min' => 'The products.quantity field must be at least 1.',
            'data.relationships.products.*.price.required' => 'The products.price field is required.',
            'data.relationships.products.*.price.numeric' => 'The products.price field must be a number.',
            'data.relationships.products.*.price.min' => 'The products.price field must be at least 0.',
        ];
    }
}
