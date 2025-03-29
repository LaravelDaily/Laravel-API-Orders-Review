<?php

namespace App\Http\Filters\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{
    protected $builder = null;
    protected $sortable = [];

    public function __construct( 
        protected Request $request,
    ) {}

    public function apply( Builder $builder )
    {
        $this->builder = $builder;
        
        foreach ($this->request->all() as $name => $value) {
            if (method_exists($this, $name)) {
                $this->$name($value);
            }
        }
        
        return $this->builder;
    }

    protected function filter( array $filters )
    {
        foreach ($filters as $name => $value) {
            if (method_exists($this, $name)) {
                $this->$name($value);
            }
        }

        return $this->builder;
    }

    protected function sort( $value )
    {
        $sortAttributes = explode(',', $value);

        foreach ($sortAttributes as $sortAttribute) {
            $sortDirection = 'asc';
            if (str_starts_with($sortAttribute, '-')) {
                $sortDirection = 'desc';
                $sortAttribute = substr($sortAttribute, 1);
            }

            if ( ! in_array($sortAttribute, $this->sortable) && ! array_key_exists($sortAttribute, $this->sortable) ) {
                continue;
            }

            $sortColumnName = $this->sortable[$sortAttribute] ?? $sortAttribute;

            $this->builder->orderBy($sortColumnName, $sortDirection);
        }
    }
    
    public function include( $value )
    {
        return $this->builder->with($value);
    }

    public function id( $value )
    {
        return $this->builder->whereIn('id', explode(',', $value));
    }
    
    public function sortByDesc( $value )
    {
        return $this->builder->orderBy($value, 'desc');
    }

    public function sortByAsc( $value )
    {
        return $this->builder->orderBy($value, 'asc');
    }

    public function limit( $value )
    {
        return $this->builder->limit($value);
    }

    public function offset( $value )
    {
        return $this->builder->offset($value);
    }

}