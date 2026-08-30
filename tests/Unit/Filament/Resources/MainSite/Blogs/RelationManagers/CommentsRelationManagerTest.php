<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Blogs\RelationManagers;

use App\Filament\Resources\MainSite\Blogs\Pages\EditBlog;
use App\Filament\Resources\MainSite\Blogs\RelationManagers\CommentsRelationManager;
use App\Models\Blogs\Blog;
use App\Models\Comments\Comment;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentsRelationManagerTest extends TestCase
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
    public function itLoadsForABlogWithNoComments(): void
    {
        $this->commentsPanel()->assertOk();
    }

    #[Test]
    public function itShowsTheCommentsOnTheBlog(): void
    {
        $comment = $this->build(Comment::class)->on($this->blog)->approved()->create();

        $this->commentsPanel()->assertCanSeeTableRecords([$comment]);
    }

    #[Test]
    public function itDoesNotShowCommentsFromAnotherBlog(): void
    {
        $mine = $this->build(Comment::class)->on($this->blog)->approved()->create();
        $theirs = $this->build(Comment::class)->on($this->create(Blog::class))->approved()->create();

        $this->commentsPanel()
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    #[Test]
    public function itOnlyShowsApprovedComments(): void
    {
        $approved = $this->build(Comment::class)->on($this->blog)->approved()->create();
        $pending = $this->build(Comment::class)->on($this->blog)->create(['approved' => false]);

        $this->commentsPanel()
            ->assertCanSeeTableRecords([$approved])
            ->assertCanNotSeeTableRecords([$pending]);
    }

    protected function commentsPanel(): Testable
    {
        return Livewire::test(CommentsRelationManager::class, [
            'ownerRecord' => $this->blog,
            'pageClass' => EditBlog::class,
        ]);
    }
}
