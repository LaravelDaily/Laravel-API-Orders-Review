<?php

namespace App\Http\Filters\V1;

class OrderFilter extends QueryFilter
{
    protected $sortable = [
        'id', 
        'name', 
        'description', 
        'date', 
        'status',
        'createdAt' => 'created_at', 
        'updatedAt' => 'updated_at',
    ];

    public function status( $value )
    {
        return $this->builder->whereIn('status', explode(',', $value));
    }

    public function date( $value )
    {
        $dates = explode(',', $value);
        return count($dates) > 1 
            ? $this->builder->whereBetween('date', $dates)
            : $this->builder->where('date', $value);
    }

    public function name( $value )
    {
        $like_str = str_replace('*', '%', $value);
        return $this->builder->where('name', 'like', $like_str);
    }

    public function description( $value )
    {
        $like_str = str_replace('*', '%', $value);
        return $this->builder->where('description', 'like', $like_str);
    }

}