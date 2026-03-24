<?php

declare(strict_types=1);

namespace Jpeters8889\EateryCollectionsQueryBuilder;

use Laravel\Nova\Fields\Field;

class EateryCollectionsQueryBuilder extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'eatery-collections-query-builder';

    public $fullWidth = true;
}
