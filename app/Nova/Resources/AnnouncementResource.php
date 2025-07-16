<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Models\Announcement;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class AnnouncementResource extends Resource
{
    public static $model = Announcement::class;

    public static $title = 'title';

    public static $search = [
        'id', 'title', 'text',
    ];

    public function fields(Request $request): array
    {
        return [
            ID::make(),

            Text::make('Title')
                ->fullWidth()
                ->rules('required'),

            Textarea::make('Text')
                ->fullWidth()
                ->rules('required'),

            Boolean::make('Live')
                ->fullWidth()
                ->filterable(),

            Boolean::make('Expired', fn () => $this->resource->expires_at->isBefore(now()))
                ->onlyOnIndex()
                ->filterable(),

            Date::make('Expires At')
                ->fullWidth()
                ->default(now()->addWeek())
                ->min(now())
                ->rules('required', 'date', 'after:today'),
        ];
    }

    public static function label()
    {
        return 'Announcements';
    }
}
