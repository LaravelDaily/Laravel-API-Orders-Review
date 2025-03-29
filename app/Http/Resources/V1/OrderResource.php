<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    // public static $wrap = 'order_details';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'order',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
                'date' => $this->date,
            ],
            'relationships' => [
                'products' => $this->products,
            ],
            $this->mergeWhen($this->relationLoaded('user'), [
                'includes' => $this->whenLoaded('user',
                    [
                        'owner' => [
                            'data' => [
                                'type' => 'user',
                                'id' => $this->user->id,
                                'attributes' => $this->user->only(['name', 'email']),
                            ],
                            'links' => [
                                'self' => 'todo', // route('users.show', ['user' => $this->user->id]),
                            ],
                        ],
                    ],
                ),
            ]),
            // 'includes' => $this->whenLoaded('user'),
            'links' => [
                'self' => route('orders.show', ['order' => $this->id]),
            ],
        ];
    }
}
