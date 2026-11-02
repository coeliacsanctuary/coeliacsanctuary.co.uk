<?php

declare(strict_types=1);

namespace App\Nova;

use Laravel\Nova\Fields\Field;

/**
 * @codeCoverageIgnore
 */
class NovaMacros
{
    public static function register(): void
    {
        $self = new static();

        $self->handle();
    }

    protected function handle(): void
    {
        Field::macro('deferrable', function (): object {
            /** @phpstan-ignore-next-line */
            $this->deferrable = true;

            return $this;
        });
    }
}
