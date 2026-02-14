<?php

declare(strict_types=1);

namespace App\Nova\Resources\AskSealiac;

use App\Models\AskSealiacChatMessage;
use App\Nova\Resource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class AskSealiacChatMessageResource extends Resource
{
    public static $model = AskSealiacChatMessage::class;

    public static $title = 'id';

    public static $clickAction = 'preview';

    public function fields(Request $request): array
    {
        $prefix = '<div style="display: flex; flex-direction: column; gap: 1rem; min-width: 500px; text-wrap:auto;">';
        $suffix = '</div>';

        return [
            ID::make(),

            Text::make('Prompt')
                ->displayUsing(fn () => Str::of($this->resource->prompt)->markdown()->prepend($prefix)->append($suffix)->toString())
                ->asHtml(),

            Text::make('Response')
                ->displayUsing(fn () => Str::of($this->resource->response)->markdown()->prepend($prefix)->append($suffix)->toString())
                ->asHtml(),

            Code::make('Tool Uses')->json(),

            BelongsTo::make('AskSealiacChat', 'askSealiacChat', AskSealiacChatResource::class),
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

    public static function indexQuery(NovaRequest $request, Builder $query): Builder
    {
        return $query->reorder('created_at', 'asc');
    }
}
