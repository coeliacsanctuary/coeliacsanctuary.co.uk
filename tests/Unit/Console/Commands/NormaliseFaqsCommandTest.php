<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\OneTime\FilamentMigration\NormaliseFaqsCommand;
use App\Models\Blogs\Blog;
use App\Models\Recipes\Recipe;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NormaliseFaqsCommandTest extends TestCase
{
    /** @param  class-string<Model>  $model */
    #[Test]
    #[DataProvider('faqableModels')]
    public function itUnwrapsLegacyWrappedFaqs(string $model, string $seedMethod): void
    {
        $this->{$seedMethod}(1);

        $record = $model::query()->first();

        $record->update([
            'faqs' => [
                ['fields' => ['question' => 'Is this gluten free?', 'answer' => 'Yes!']],
                ['fields' => ['question' => 'Can I freeze it?', 'answer' => 'Absolutely.']],
            ],
        ]);

        $this->artisan(NormaliseFaqsCommand::class)->run();

        $this->assertEquals([
            ['question' => 'Is this gluten free?', 'answer' => 'Yes!'],
            ['question' => 'Can I freeze it?', 'answer' => 'Absolutely.'],
        ], $record->fresh()->faqs);
    }

    /** @param  class-string<Model>  $model */
    #[Test]
    #[DataProvider('faqableModels')]
    public function itLeavesAlreadyFlatFaqsUnchanged(string $model, string $seedMethod): void
    {
        $this->{$seedMethod}(1);

        $record = $model::query()->first();

        $record->update([
            'faqs' => [
                ['question' => 'Is this gluten free?', 'answer' => 'Yes!'],
            ],
        ]);

        $this->artisan(NormaliseFaqsCommand::class)->run();

        $this->assertEquals([
            ['question' => 'Is this gluten free?', 'answer' => 'Yes!'],
        ], $record->fresh()->faqs);
    }

    /** @param  class-string<Model>  $model */
    #[Test]
    #[DataProvider('faqableModels')]
    public function itSkipsRecordsWithNullFaqs(string $model, string $seedMethod): void
    {
        $this->{$seedMethod}(1);

        $record = $model::query()->first();

        $this->artisan(NormaliseFaqsCommand::class)->run();

        $this->assertNull($record->fresh()->faqs);
    }

    public static function faqableModels(): array
    {
        return [
            'Blogs' => [Blog::class, 'withBlogs'],
            'Recipes' => [Recipe::class, 'withRecipes'],
        ];
    }
}
