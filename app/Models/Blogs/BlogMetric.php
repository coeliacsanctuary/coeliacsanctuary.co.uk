<?php

declare(strict_types=1);

namespace App\Models\Blogs;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Carbon $created_at
 */
class BlogMetric extends Model
{
    /** @return BelongsTo<Blog, $this> */
    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
