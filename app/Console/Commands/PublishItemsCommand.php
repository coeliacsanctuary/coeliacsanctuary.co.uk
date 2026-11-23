<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Signature;
use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\Recipes\Recipe;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Signature('coeliac:publish-items')]
class PublishItemsCommand extends Command
{
    public function handle(): void
    {
        /** @var class-string<Model>[] $models */
        $models = [Blog::class, Recipe::class, Collection::class];

        foreach ($models as $model) {
            /** @var Builder<Blog | Recipe | Collection> $query */
            $query = $model::query();

            $query->withoutGlobalScopes()
                ->where('live', false)
                ->where('draft', false)
                ->where('publish_at', '<', now())
                ->get()
                ->each(function ($model): void {
                    $model->update([
                        'live' => true,
                        'created_at' => now(),
                    ]);
                });
        }

    }
}
