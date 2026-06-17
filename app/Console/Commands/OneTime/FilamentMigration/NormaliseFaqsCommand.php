<?php

declare(strict_types=1);

namespace App\Console\Commands\OneTime\FilamentMigration;

use App\Models\Blogs\Blog;
use App\Models\Recipes\Recipe;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

use function Laravel\Prompts\progress;

class NormaliseFaqsCommand extends Command
{
    protected $signature = 'coeliac:one-time:filament:normalise-faqs';

    public function handle(): void
    {
        /** @var class-string<Model>[] $models */
        $models = [Blog::class, Recipe::class];

        foreach ($models as $model) {
            $records = $model::withoutGlobalScopes()->whereNotNull('faqs')->get();

            if ($records->isEmpty()) {
                continue;
            }

            progress(
                label: "Normalising {$model} FAQs",
                steps: $records,
                callback: $this->normaliseFaqs(...),
            );
        }
    }

    protected function normaliseFaqs(Model $record): void
    {
        $faqs = $record->getAttribute('faqs');

        $normalised = collect(is_array($faqs) ? $faqs : [])
            ->map(fn (array $faq): array => $faq['fields'] ?? $faq)
            ->all();

        $record->setAttribute('faqs', $normalised)->save();
    }
}
