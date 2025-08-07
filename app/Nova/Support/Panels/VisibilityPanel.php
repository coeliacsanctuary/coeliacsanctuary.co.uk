<?php

declare(strict_types=1);

namespace App\Nova\Support\Panels;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * @codeCoverageIgnore
 */
class VisibilityPanel
{
    public static function make(): Panel
    {
        return new Panel('Visibility', [
            Select::make('Status', '_status')
                ->resolveUsing(fn ($foo, ?Model $resource) => match (true) {
                    $resource?->live => 'live',
                    (bool) $resource?->publish_at => 'future',
                    default => 'draft',
                })
                ->fullWidth()
                ->default('draft')
                ->onlyOnForms()
                ->displayUsingLabels()
                ->required()
                ->options([
                    'draft' => 'Draft',
                    'live' => 'Published',
                    'future' => 'Scheduled',
                ]),

            Boolean::make('Published', 'live')
                ->exceptOnForms()
                ->dependsOn('_status', function (Boolean $field, NovaRequest $request, FormData $formData): void {
                    $field->setValue($formData->_status === 'live');
                }),

            DateTime::make('Or Publish At', 'publish_at')
                ->onlyOnForms()
                ->default(fn () => Carbon::now()->addDay())
                ->dependsOn('_status', function (DateTime $field, NovaRequest $request, FormData $formData): void {
                    if ($formData->_status !== 'future') {
                        $field->hide();
                    }
                })
                ->rules(['required_if:_status,future']),
        ]);
    }
}
