<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryReviewImage;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Image;

/**
 * @codeCoverageIgnore
 */
class ReviewImage extends Resource
{
    public static $model = EateryReviewImage::class;

    public static $clickAction = 'preview';

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function fields(Request $request): array
    {
        return [
            BelongsTo::make('review', resource: Reviews::class),

            BelongsTo::make('eatery', resource: Eateries::class)
                ->hideFromIndex()
                ->displayUsing(fn (Eateries $eatery) => $eatery->resource->load(['town', 'county', 'country'])->full_name),

            Image::make('Image', 'raw_thumb')->disk('review-images')->onlyOnIndex(),

            Image::make('Image', 'raw_path')
                ->disk('review-images')
                ->onlyOnPreview()
                ->maxWidth(512),
        ];
    }
}
