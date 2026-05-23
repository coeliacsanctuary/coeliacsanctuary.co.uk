<?php

declare(strict_types=1);

namespace App\Nova\FieldOverrides;

use App\Nova\FieldOverrides\Repeaters\HasMany;
use Laravel\Nova\Exceptions\NovaException;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;

class Repeater extends \Laravel\Nova\Fields\Repeater
{
    public function asHasMany($resourceClass = null)
    {
        /** @var class-string<\Laravel\Nova\Resource>|null $resourceClass */
        $resourceClass ??= ResourceRelationshipGuesser::guessResource($this->name); // @phpstan-ignore varTag.nativeType

        if ($resourceClass) {
            $this->resourceClass = $resourceClass;
            $this->resourceName = $resourceClass::uriKey();

            return $this->preset(new HasMany())->onlyOnForms();
        }

        throw NovaException::missingResourceForRepeater($this->name);
    }
}
