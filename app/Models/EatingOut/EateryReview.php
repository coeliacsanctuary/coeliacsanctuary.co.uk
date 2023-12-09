<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use App\Scopes\LiveScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Carbon $created_at
 * @property string $human_date
 * @property string $rating
 * @property array | null $price
 */
class EateryReview extends Model
{
    public const HOW_EXPENSIVE_LABELS = [
        1 => 'Cheap Eats',
        2 => 'Great Value',
        3 => 'Average / Mid Range',
        4 => 'A special treat',
        5 => 'Expensive',
    ];

    protected $table = 'wheretoeat_reviews';

    protected $casts = [
        'admin_review' => 'bool',
        'approved' => 'bool',
        'how_expensive' => 'int',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new LiveScope('approved'));
    }

    /** @return BelongsTo<Eatery, EateryReview> */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'wheretoeat_id', 'id');
    }

    /** @return Attribute<string | null, never> */
    public function averageRating(): Attribute
    {
        return Attribute::get(function () {
            if ( ! $this->relationLoaded('eatery')) {
                return null;
            }

            /** @var float $average */
            $average = $this->eatery?->reviews()->average('rating');

            return number_format($average, 1);
        });
    }

    /** @return Attribute<int | null, never> */
    public function numberOfRatings(): Attribute
    {
        return Attribute::get(function () {
            if ( ! $this->relationLoaded('eatery')) {
                return null;
            }

            return $this->eatery?->reviews()->count();
        });
    }

    /** @return Attribute<string, never> */
    public function humanDate(): Attribute
    {
        return Attribute::get(fn () => $this->created_at->diffForHumans());
    }

    /** @return Attribute<array{value: int, label: string} | null, never> */
    public function price(): Attribute
    {
        return Attribute::get(function () {
            if ( ! $this->how_expensive) {
                return null;
            }

            return [
                'value' => $this->how_expensive,
                'label' => self::HOW_EXPENSIVE_LABELS[$this->how_expensive],
            ];
        });
    }

    /** @return HasMany<EateryReviewImage> */
    public function images(): HasMany
    {
        return $this->hasMany(EateryReviewImage::class, 'wheretoeat_review_id', 'id');
    }
}
