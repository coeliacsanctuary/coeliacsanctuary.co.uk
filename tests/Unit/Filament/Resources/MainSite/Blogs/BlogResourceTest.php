<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Blogs;

use App\Filament\Resources\MainSite\Blogs\BlogResource;
use App\Filament\Resources\MainSite\Blogs\RelationManagers\CommentsRelationManager;
use App\Models\Blogs\Blog;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itListsTheNewestBlogsFirst(): void
    {
        $this->create(Blog::class, 3);

        $this->assertSame([3, 2, 1], BlogResource::getEloquentQuery()->pluck('id')->all());
    }

    #[Test]
    public function itIncludesBlogsThatArentLive(): void
    {
        $this->create(Blog::class, ['live' => true]);
        $this->build(Blog::class)->notLive()->create();
        $this->build(Blog::class)->draft()->create();

        $this->assertCount(3, BlogResource::getEloquentQuery()->get());
    }

    #[Test]
    public function itTransformsTheStatusBeforeSaving(): void
    {
        $data = BlogResource::mutateForSave([
            'status' => 'Live',
            'publish_at' => Carbon::now(),
            'title' => 'How To Make Gluten Free Bread',
        ]);

        $this->assertTrue($data['live']);
        $this->assertNull($data['publish_at']);
        $this->assertArrayNotHasKey('status', $data);
        $this->assertSame('How To Make Gluten Free Bread', $data['title']);
    }

    #[Test]
    public function itManagesComments(): void
    {
        $this->assertSame([CommentsRelationManager::class], BlogResource::getRelations());
    }

    #[Test]
    public function itIsGloballySearchableByTitleAndSlug(): void
    {
        $this->assertSame(['title', 'slug'], BlogResource::getGloballySearchableAttributes());
    }

    #[Test]
    public function itTitlesRecordsByTheirBlogTitle(): void
    {
        $this->assertSame('title', BlogResource::getRecordTitleAttribute());
    }

    #[Test]
    public function itRegistersTheListCreateEditAndMetricsPages(): void
    {
        $this->assertSame(['index', 'create', 'edit', 'metrics'], array_keys(BlogResource::getPages()));
    }

    #[Test]
    public function itLinksToTheMetricsPageForABlog(): void
    {
        $blog = $this->create(Blog::class);

        $this->assertStringEndsWith(
            "/admin/main-site/blogs/{$blog->getRouteKey()}/metrics",
            BlogResource::getUrl('metrics', ['record' => $blog])
        );
    }
}
