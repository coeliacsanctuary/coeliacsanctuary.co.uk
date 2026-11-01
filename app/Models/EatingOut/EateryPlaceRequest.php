<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'place_requests')]
class EateryPlaceRequest extends Model
{
    protected $casts = [
        'completed' => 'bool',
    ];
}
