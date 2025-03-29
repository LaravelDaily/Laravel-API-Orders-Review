<?php

namespace App\Http\Filters\V1;

class OwnerFilter extends QueryFilter
{
    protected $sortable = [
        'id',
        'name',
        'email',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];

    public function name($value)
    {
        $like_str = str_replace('*', '%', $value);

        return $this->builder->where('name', 'like', $like_str);
    }

    public function email($value)
    {
        $like_str = str_replace('*', '%', $value);

        return $this->builder->where('email', 'like', $like_str);
    }
}
