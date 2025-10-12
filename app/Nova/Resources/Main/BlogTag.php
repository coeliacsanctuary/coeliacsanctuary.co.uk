<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Blogs\BlogTag as BlogTagModel;
use App\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<BlogTagModel> */
/**
 * @codeCoverageIgnore
 */
class BlogTag extends Resource
{
    public static string $model = BlogTagModel::class;

    public static $title = 'novaTitle';

    public static $search = ['tag'];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id'),

            Text::make('Tag'),
        ];
    }
}
