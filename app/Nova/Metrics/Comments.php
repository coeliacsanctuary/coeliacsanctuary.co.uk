<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\Comments\Comment;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use SaintSystems\Nova\LinkableMetrics\LinkableValue;

class Comments extends Value
{
    use LinkableValue;

    public $icon = 'chat-alt';

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->result(Comment::query()->withoutGlobalScopes()->where('approved', false)->count());
    }

    public function name()
    {
        return 'New Comments';
    }
}
