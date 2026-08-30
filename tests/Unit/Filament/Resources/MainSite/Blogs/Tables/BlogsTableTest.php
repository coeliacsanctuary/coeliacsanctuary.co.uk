<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Blogs\Tables;

use App\Filament\Resources\MainSite\Blogs\BlogResource;
use App\Filament\Resources\MainSite\Blogs\Pages\ListBlogs;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogsTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itShowsEveryBlogWhateverItsStatus(): void
    {
        $live = $this->create(Blog::class, ['live' => true]);
        $notLive = $this->build(Blog::class)->notLive()->create();
        $draft = $this->build(Blog::class)->draft()->create();

        Livewire::test(ListBlogs::class)->assertCanSeeTableRecords([$live, $notLive, $draft]);
    }

    #[Test]
    public function itShowsTheNewestBlogsFirst(): void
    {
        $blogs = $this->create(Blog::class, 3);

        Livewire::test(ListBlogs::class)->assertCanSeeTableRecords($blogs->reverse()->values(), inOrder: true);
    }

    #[Test]
    #[DataProvider('columns')]
    public function itShowsTheBlogColumns(string $column): void
    {
        $this->create(Blog::class);

        Livewire::test(ListBlogs::class)->assertTableColumnExists($column);
    }

    public static function columns(): array
    {
        return [
            'id' => ['id'],
            'title' => ['title'],
            'primary tag' => ['primaryTag.tag'],
            'status' => ['status'],
            'created at' => ['created_at'],
        ];
    }

    #[Test]
    public function itLabelsTheIdColumn(): void
    {
        Livewire::test(ListBlogs::class)
            ->assertTableColumnExists('id', fn (TextColumn $column): bool => $column->getLabel() === 'ID');
    }

    #[Test]
    public function itShowsThePrimaryTagOfABlog(): void
    {
        $tag = $this->create(BlogTag::class, ['tag' => 'Baking']);
        $blog = $this->create(Blog::class, ['primary_tag_id' => $tag->id]);

        Livewire::test(ListBlogs::class)->assertTableColumnStateSet('primaryTag.tag', 'Baking', $blog);
    }

    #[Test]
    #[DataProvider('searchableColumns')]
    public function itCanBeSearchedByColumn(string $column): void
    {
        Livewire::test(ListBlogs::class)
            ->assertTableColumnExists($column, fn (TextColumn $c): bool => $c->isSearchable());
    }

    public static function searchableColumns(): array
    {
        return [
            'id' => ['id'],
            'title' => ['title'],
        ];
    }

    #[Test]
    public function itFindsABlogByTitle(): void
    {
        $wanted = $this->create(Blog::class, ['title' => 'How To Make Gluten Free Bread']);
        $other = $this->create(Blog::class, ['title' => 'Where To Eat In Crewe']);

        Livewire::test(ListBlogs::class)
            ->searchTable('Gluten Free Bread')
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itFindsABlogById(): void
    {
        $blogs = $this->create(Blog::class, 2);

        Livewire::test(ListBlogs::class)
            ->searchTable((string) $blogs->last()->id)
            ->assertCanSeeTableRecords([$blogs->last()])
            ->assertCanNotSeeTableRecords([$blogs->first()]);
    }

    #[Test]
    public function itShowsTheCreatedDateAsADateTime(): void
    {
        Livewire::test(ListBlogs::class)
            ->assertTableColumnExists('created_at', fn (TextColumn $c): bool => $c->isDateTime());
    }

    #[Test]
    public function itLinksEachRowToTheEditPage(): void
    {
        $blog = $this->create(Blog::class);

        $this->assertSame(
            BlogResource::getUrl('edit', ['record' => $blog]),
            Livewire::test(ListBlogs::class)->instance()->getTable()->getRecordUrl($blog)
        );
    }

    #[Test]
    public function itOffersAViewLinkForALiveBlog(): void
    {
        $blog = $this->create(Blog::class, ['live' => true]);

        Livewire::test(ListBlogs::class)
            ->assertActionVisible(TestAction::make('view')->table($blog));
    }

    #[Test]
    public function itHidesTheViewLinkForABlogThatIsntLive(): void
    {
        $blog = $this->build(Blog::class)->notLive()->create();

        Livewire::test(ListBlogs::class)
            ->assertActionHidden(TestAction::make('view')->table($blog));
    }

    #[Test]
    public function theViewLinkOpensTheBlogOnTheWebsiteInANewTab(): void
    {
        $blog = $this->create(Blog::class, ['live' => true]);

        Livewire::test(ListBlogs::class)->assertActionExists(
            TestAction::make('view')->table($blog),
            fn (Action $action): bool => $action->getUrl() === $blog->absolute_link && $action->shouldOpenUrlInNewTab(),
        );
    }

    #[Test]
    public function itOffersAMetricsLinkForEveryBlog(): void
    {
        $blog = $this->build(Blog::class)->notLive()->create();

        Livewire::test(ListBlogs::class)->assertActionExists(
            TestAction::make('metrics')->table($blog),
            fn (Action $action): bool => $action->getUrl() === BlogResource::getUrl('metrics', ['record' => $blog]),
        );
    }

    #[Test]
    public function itOffersAnEditActionForEveryBlog(): void
    {
        $blog = $this->create(Blog::class);

        Livewire::test(ListBlogs::class)->assertActionExists(TestAction::make(EditAction::class)->table($blog));
    }
}
