<?php

declare(strict_types=1);

namespace App\Nova\Resources\AskSealiac;

use App\Models\AskSealiacChat;
use App\Nova\Resource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class AskSealiacChatResource extends Resource
{
    public static $model = AskSealiacChat::class;

    public static $title = 'id';

    public static $search = [
        'id', 'session_id', 'chat_id',
    ];

    public static $clickAction = 'detail';

    public function fields(Request $request): array
    {
        return [
            ID::make(),

            Text::make('Session Id'),

            Text::make('Chat Id'),

            Number::make('Messages Count'),

            Text::make('Summary')
                ->displayUsing(fn () => "<div style=\"min-width: 500px; text-wrap:auto;\">{$this->resource->summary}</div>")
                ->asHtml(),

            DateTime::make('Created At'),

            HasMany::make('Messages', 'messages', AskSealiacChatMessageResource::class),
        ];
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public static function getTitle(): string
    {
        return 'Chats';
    }

    public static function indexQuery(NovaRequest $request, Builder $query)
    {
        return $query->withCount('messages');
    }
}
