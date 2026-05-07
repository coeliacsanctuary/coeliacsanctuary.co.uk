<?php

declare(strict_types=1);

namespace App\Nova\Chartables\Metrics\Blogs;

class CommentViews extends BlogMetric
{
    protected function column(): string
    {
        return 'page_comment_views';
    }
}
