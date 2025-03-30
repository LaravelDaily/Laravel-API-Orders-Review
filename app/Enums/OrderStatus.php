<?php

namespace App\Enums;

enum OrderStatus: string
{
    case FULFILLED = 'F';
    case PENDING = 'P';
    case CANCELED = 'C';
}
