<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $range
 */
#[RouteKey('key')]
#[Table(name: 'wheretoeat_search_terms')]
class EaterySearchTerm extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (EaterySearchTerm $searchTerm): EaterySearchTerm {
            $pass = false;

            while ($pass === false) {
                $key = Str::random(16);

                if (static::query()->where('key', $key)->count() === 0) {
                    $pass = true;
                }
            }

            $searchTerm->key = $key;

            return $searchTerm;
        });
    }

    public function logSearch(): void
    {
        $this->searches()->create();

        $this->touch();
    }

    /** @return HasMany<EaterySearch, $this> */
    public function searches(): HasMany
    {
        return $this->hasMany(EaterySearch::class, 'search_term_id');
    }
    protected function casts(): array
    {
        return [
            'range' => 'int',
            'from_user_location' => 'bool',
        ];
    }
}
