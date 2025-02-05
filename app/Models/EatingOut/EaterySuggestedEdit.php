<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use App\Support\EatingOut\SuggestEdits\Fields\AddressField;
use App\Support\EatingOut\SuggestEdits\Fields\CuisineField;
use App\Support\EatingOut\SuggestEdits\Fields\EditableField;
use App\Support\EatingOut\SuggestEdits\Fields\FeaturesField;
use App\Support\EatingOut\SuggestEdits\Fields\GfMenuLinkField;
use App\Support\EatingOut\SuggestEdits\Fields\InfoField;
use App\Support\EatingOut\SuggestEdits\Fields\OpeningTimesField;
use App\Support\EatingOut\SuggestEdits\Fields\PhoneField;
use App\Support\EatingOut\SuggestEdits\Fields\VenueTypeField;
use App\Support\EatingOut\SuggestEdits\Fields\WebsiteField;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EaterySuggestedEdit extends Model
{
    protected $table = 'wheretoeat_suggested_edits';

    protected $casts = [
        'accepted' => 'bool',
        'rejected' => 'bool',
    ];

    /** @return BelongsTo<Eatery, $this> */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'wheretoeat_id');
    }

    /** @return array<string, class-string<EditableField>> */
    public static function processorMaps(): array
    {
        return [
            'address' => AddressField::class,
            'cuisine' => CuisineField::class,
            'features' => FeaturesField::class,
            'gf_menu_link' => GfMenuLinkField::class,
            'info' => InfoField::class,
            'opening_times' => OpeningTimesField::class,
            'phone' => PhoneField::class,
            'venue_type' => VenueTypeField::class,
            'website' => WebsiteField::class,
        ];
    }

    /** @return Attribute<string, never> */
    public function status(): Attribute
    {
        return Attribute::get(function () {
            if ($this->accepted) {
                return 'Accepted';
            }

            if ($this->rejected) {
                return 'Rejected';
            }

            return 'New';
        });
    }
}
