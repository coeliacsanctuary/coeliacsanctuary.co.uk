<?php

declare(strict_types=1);

namespace Jpeters8889\HiddenWritableField;

use Laravel\Nova\Fields\Field;

class HiddenWritableField extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'hidden-writable-field';

    public function fillModelWithData(object $model, mixed $value, string $attribute): void
    {
        //
    }
}
