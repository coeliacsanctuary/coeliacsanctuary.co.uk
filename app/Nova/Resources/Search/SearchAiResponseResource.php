<?php

declare(strict_types=1);

namespace App\Nova\Resources\Search;

use App\Models\Search\SearchAiResponse;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Textarea;

class SearchAiResponseResource extends Resource
{
    public static $model = SearchAiResponse::class;

    public function authorizedToView(Request $request): bool
    {
        return true;
    }

    public function authorizedToUpdate(Request $request)
    {
        return true;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return true;
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->hide(),

            Number::make('Blogs Score', 'blogs')->rules(['required', 'min:0', 'max:100'])->min(0)->max(100),

            Number::make('Recipes Score', 'recipes')->rules(['required', 'min:0', 'max:100'])->min(0)->max(100),

            Number::make('Eating Out Score', 'eateries')->rules(['required', 'min:0', 'max:100'])->min(0)->max(100),

            Number::make('Shop Score', 'shop')->rules(['required', 'min:0', 'max:100'])->min(0)->max(100),

            Textarea::make('Explanation')->readonly()->alwaysShow()->fullWidth(),

            DateTime::make('Created At')->exceptOnForms(),
        ];
    }
}
