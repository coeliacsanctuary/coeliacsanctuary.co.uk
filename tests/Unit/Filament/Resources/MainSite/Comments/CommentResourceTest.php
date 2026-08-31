<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Comments;

use App\Filament\Resources\MainSite\Comments\CommentResource;
use App\Models\Blogs\Blog;
use App\Models\Comments\Comment;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentResourceTest extends TestCase
{
    protected Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->blog = $this->create(Blog::class);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itListsTheNewestCommentsFirst(): void
    {
        $comments = $this->build(Comment::class)->on($this->blog)->count(3)->create();

        $this->assertSame(
            $comments->reverse()->pluck('id')->all(),
            CommentResource::getEloquentQuery()->pluck('id')->all()
        );
    }

    #[Test]
    public function itIncludesCommentsThatArentApproved(): void
    {
        $this->build(Comment::class)->on($this->blog)->approved()->create();
        $this->build(Comment::class)->on($this->blog)->create(['approved' => false]);

        $this->assertCount(2, CommentResource::getEloquentQuery()->get());
    }

    #[Test]
    public function itEagerLoadsTheCommentableAndReply(): void
    {
        $this->assertSame(
            ['commentable', 'reply'],
            array_keys(CommentResource::getEloquentQuery()->getEagerLoads())
        );
    }

    #[Test]
    public function itIsNotGloballySearchable(): void
    {
        $this->assertFalse(CommentResource::canGloballySearch());
    }

    #[Test]
    public function itRegistersOnlyTheListPage(): void
    {
        $this->assertSame(['index'], array_keys(CommentResource::getPages()));
    }

    #[Test]
    public function itBadgesTheNumberOfCommentsAwaitingApproval(): void
    {
        $this->build(Comment::class)->on($this->blog)->count(2)->create(['approved' => false]);
        $this->build(Comment::class)->on($this->blog)->approved()->create();

        $this->assertSame('2', CommentResource::getNavigationBadge());
    }

    #[Test]
    public function itDoesNotBadgeWhenNothingIsAwaitingApproval(): void
    {
        $this->build(Comment::class)->on($this->blog)->approved()->create();

        $this->assertNull(CommentResource::getNavigationBadge());
    }

    #[Test]
    public function itBadgesInDanger(): void
    {
        $this->assertSame('danger', CommentResource::getNavigationBadgeColor());
    }
}
