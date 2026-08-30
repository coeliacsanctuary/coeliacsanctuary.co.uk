<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Blogs\Forms\Components;

use App\Filament\Resources\MainSite\Blogs\Forms\Components\BlogTagsInput;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use Livewire\Features\SupportTesting\Testable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\BuildsFilamentSchemas;
use Tests\TestCase;

class BlogTagsInputTest extends TestCase
{
    use BuildsFilamentSchemas;

    protected Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blog = $this->create(Blog::class);
    }

    #[Test]
    public function itLoadsTheTagNamesOffTheBlog(): void
    {
        $this->blog->tags()->attach([
            $this->create(BlogTag::class, ['tag' => 'baking'])->id,
            $this->create(BlogTag::class, ['tag' => 'bread'])->id,
        ]);

        $this->mountTagsInput()->assertSchemaComponentStateSet('tags', ['baking', 'bread']);
    }

    #[Test]
    public function itLoadsNothingWhenThereIsNoBlog(): void
    {
        $this->mountSchema([BlogTagsInput::make('tags')], 'create')
            ->assertSchemaComponentStateSet('tags', []);
    }

    #[Test]
    public function itCreatesAndAttachesTagsThatDoNotExistYet(): void
    {
        $this->assertDatabaseEmpty(BlogTag::class);

        $this->saveTags(['baking', 'bread']);

        $this->assertDatabaseCount(BlogTag::class, 2);
        $this->assertSame(['baking', 'bread'], $this->blog->tags()->pluck('tag')->all());
    }

    #[Test]
    public function itReusesAnExistingTagRatherThanCreatingADuplicate(): void
    {
        $this->create(BlogTag::class, ['tag' => 'baking']);

        $this->saveTags(['baking']);

        $this->assertDatabaseCount(BlogTag::class, 1);
        $this->assertSame(['baking'], $this->blog->tags()->pluck('tag')->all());
    }

    #[Test]
    public function itDetachesTagsThatHaveBeenRemoved(): void
    {
        $this->blog->tags()->attach($this->create(BlogTag::class, ['tag' => 'baking'])->id);

        $this->saveTags(['bread']);

        $this->assertSame(['bread'], $this->blog->tags()->pluck('tag')->all());
    }

    #[Test]
    public function itDetachesEveryTagWhenTheFieldIsEmptied(): void
    {
        $this->blog->tags()->attach($this->create(BlogTag::class, ['tag' => 'baking'])->id);

        $this->saveTags([]);

        $this->assertCount(0, $this->blog->tags()->get());
    }

    #[Test]
    public function itSavesNothingWhenThereIsNoBlog(): void
    {
        $component = $this->mountSchema([BlogTagsInput::make('tags')], 'create')->set('data.tags', ['baking']);

        $component->instance()->getSchema('form')->saveRelationships();

        $this->assertDatabaseEmpty(BlogTag::class);
    }

    #[Test]
    public function itIsNeverDehydratedIntoTheSaveData(): void
    {
        $this->assertFalse(
            $this->mountedComponent('tags', [BlogTagsInput::make('tags')], 'edit', $this->blog)->isDehydrated()
        );
    }

    protected function mountTagsInput(): Testable
    {
        return $this->mountSchema([BlogTagsInput::make('tags')], 'edit', $this->blog);
    }

    /** @param array<int, string> $tags */
    protected function saveTags(array $tags): void
    {
        $component = $this->mountTagsInput()->set('data.tags', $tags);

        $component->instance()->getSchema('form')->saveRelationships();
    }
}
