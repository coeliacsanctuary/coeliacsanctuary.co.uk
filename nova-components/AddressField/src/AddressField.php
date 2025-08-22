<?php

declare(strict_types=1);

namespace Jpeters8889\AddressField;

use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class AddressField extends Field
{
    public $component = 'address-field';

    public static $latitude = 'latitude';

    public static $longitude = 'longitude';

    public $showOnIndex = false;

    public $showOnDetail = true;

    public $showOnCreation = true;

    public $showOnUpdate = true;

    public $fullWidth = true;

    public function latitudeField($field): self
    {
        self::$latitude = $field;

        return $this;
    }

    public function longitudeField($field): self
    {
        self::$longitude = $field;

        return $this;
    }

    protected function resolveAttribute($resource, $attribute): mixed
    {
        $address = $resource->$attribute;
        $latitude = $resource->{self::$latitude};
        $longitude = $resource->{self::$longitude};

        return json_encode(compact('address', 'latitude', 'longitude'));
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute): void
    {
        $fields = json_decode($request->input($requestAttribute), true);

        $model->$attribute = $fields['address'];
        $model->{self::$latitude} = $fields['latitude'];
        $model->{self::$longitude} = $fields['longitude'];
    }

    public function isValidNullValue(mixed $value): bool
    {
        if ( ! $value || ! Str::isJson($value)) {
            return true;
        }
        $value = json_decode($value, true);

        return count(array_filter($value)) === 0;
    }

//    public function resolveDefaultCallback(NovaRequest $request): mixed
//    {
//        return \call_user_func($this->defaultCallback, $request);
//    }
}
