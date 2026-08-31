<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Comments\Tables;

use App\Filament\Resources\MainSite\Blogs\BlogResource;
use App\Filament\Resources\MainSite\Blogs\Pages\EditBlog;
use App\Filament\Resources\MainSite\Blogs\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\MainSite\Comments\Pages\ListComments;
use App\Filament\Resources\MainSite\Recipes\RecipeResource;
use App\Models\Blogs\Blog;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentReply;
use App\Models\Recipes\Recipe;
use App\Models\User;
use App\Notifications\CommentApprovedNotification;
use App\Notifications\CommentRepliedNotification;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentsTableTest extends TestCase
{
    protected Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->blog = $this->create(Blog::class);

        $this->actingAs($this->create(User::class));

        Notification::fake();
    }

    #[Test]
    public function itShowsApprovedAndPendingComments(): void
    {
        $approved = $this->build(Comment::class)->on($this->blog)->approved()->create();
        $pending = $this->build(Comment::class)->on($this->blog)->create(['approved' => false]);

        Livewire::test(ListComments::class)->assertCanSeeTableRecords([$approved, $pending]);
    }

    #[Test]
    public function itShowsTheNewestCommentsFirst(): void
    {
        $comments = $this->build(Comment::class)->on($this->blog)->count(3)->create();

        Livewire::test(ListComments::class)
            ->assertCanSeeTableRecords($comments->reverse()->values(), inOrder: true);
    }

    #[Test]
    #[DataProvider('columns')]
    public function itShowsTheCommentColumns(string $column): void
    {
        $this->build(Comment::class)->on($this->blog)->create();

        Livewire::test(ListComments::class)->assertTableColumnExists($column);
    }

    public static function columns(): array
    {
        return [
            'resource' => ['commentable.title'],
            'name' => ['name'],
            'comment' => ['comment'],
            'has reply' => ['has_reply'],
            'approved' => ['approved'],
            'added' => ['created_at'],
        ];
    }

    #[Test]
    public function itDoesNotShowAnIdColumn(): void
    {
        Livewire::test(ListComments::class)->assertTableColumnDoesNotExist('id');
    }

    #[Test]
    public function itLabelsTheResourceAndAddedColumns(): void
    {
        Livewire::test(ListComments::class)
            ->assertTableColumnExists('commentable.title', fn (TextColumn $column): bool => $column->getLabel() === 'Resource')
            ->assertTableColumnExists('created_at', fn (TextColumn $column): bool => $column->getLabel() === 'Added');
    }

    #[Test]
    public function itPrefixesTheResourceWithItsType(): void
    {
        $comment = $this->build(Comment::class)->on($this->blog)->create();

        Livewire::test(ListComments::class)->assertTableColumnExists(
            'commentable.title',
            fn (TextColumn $column): bool => $column->getPrefix() === 'Blog - ',
            $comment,
        );
    }

    #[Test]
    public function itLinksACommentToTheBlogItWasLeftOn(): void
    {
        $comment = $this->build(Comment::class)->on($this->blog)->create();

        Livewire::test(ListComments::class)->assertTableColumnExists(
            'commentable.title',
            fn (TextColumn $column): bool => $column->getUrl() === BlogResource::getUrl('edit', ['record' => $this->blog->id]),
            $comment,
        );
    }

    #[Test]
    public function itLinksACommentToTheRecipeItWasLeftOn(): void
    {
        $recipe = $this->create(Recipe::class);
        $comment = $this->build(Comment::class)->on($recipe)->create();

        Livewire::test(ListComments::class)->assertTableColumnExists(
            'commentable.title',
            fn (TextColumn $column): bool => $column->getUrl() === RecipeResource::getUrl('edit', ['record' => $recipe->id]),
            $comment,
        );
    }

    #[Test]
    public function itHidesTheResourceColumnInsideARelationManager(): void
    {
        $this->build(Comment::class)->on($this->blog)->approved()->create();

        Livewire::test(CommentsRelationManager::class, [
            'ownerRecord' => $this->blog,
            'pageClass' => EditBlog::class,
        ])->assertTableColumnHidden('commentable.title');
    }

    #[Test]
    public function itFlagsACommentThatHasAReply(): void
    {
        $comment = $this->build(Comment::class)->on($this->blog)->create();
        $this->build(CommentReply::class)->create(['comment_id' => $comment->id]);

        Livewire::test(ListComments::class)->assertTableColumnStateSet('has_reply', true, $comment);
    }

    #[Test]
    public function itDoesNotFlagACommentWithoutAReply(): void
    {
        $comment = $this->build(Comment::class)->on($this->blog)->create();

        Livewire::test(ListComments::class)->assertTableColumnStateSet('has_reply', false, $comment);
    }

    #[Test]
    public function itOffersApproveAndReplyForAPendingComment(): void
    {
        $comment = $this->build(Comment::class)->on($this->blog)->create(['approved' => false]);

        Livewire::test(ListComments::class)
            ->assertActionVisible(TestAction::make('approve')->table($comment))
            ->assertActionVisible(TestAction::make('reply')->table($comment));
    }

    #[Test]
    public function itHidesApproveAndReplyForAnApprovedComment(): void
    {
        $comment = $this->build(Comment::class)->on($this->blog)->approved()->create();

        Livewire::test(ListComments::class)
            ->assertActionHidden(TestAction::make('approve')->table($comment))
            ->assertActionHidden(TestAction::make('reply')->table($comment));
    }

    #[Test]
    public function itConfirmsBeforeApproving(): void
    {
        $comment = $this->build(Comment::class)->on($this->blog)->create(['approved' => false]);

        Livewire::test(ListComments::class)->assertActionExists(
            TestAction::make('approve')->table($comment),
            fn (Action $action): bool => $action->isConfirmationRequired(),
        );
    }

    #[Test]
    public function itApprovesACommentAndEmailsTheAuthor(): void
    {
        $comment = $this->build(Comment::class)->on($this->blog)->create(['approved' => false]);

        Livewire::test(ListComments::class)->callAction(TestAction::make('approve')->table($comment));

        $this->assertTrue($comment->refresh()->approved);

        Notification::assertSentTo(new AnonymousNotifiable(), CommentApprovedNotification::class);
    }

    #[Test]
    public function itRepliesToACommentApprovesItAndEmailsTheAuthor(): void
    {
        $comment = $this->build(Comment::class)->on($this->blog)->create(['approved' => false]);

        Livewire::test(ListComments::class)->callAction(
            TestAction::make('reply')->table($comment),
            ['reply' => 'Thanks for the kind words!'],
        );

        $this->assertTrue($comment->refresh()->approved);
        $this->assertSame('Thanks for the kind words!', $comment->reply->comment_reply);

        Notification::assertSentTo(new AnonymousNotifiable(), CommentRepliedNotification::class);
    }

    #[Test]
    public function itOffersTheViewReplyActionOnlyWhenAReplyExists(): void
    {
        $replied = $this->build(Comment::class)->on($this->blog)->approved()->create();
        $this->build(CommentReply::class)->create(['comment_id' => $replied->id]);

        $unanswered = $this->build(Comment::class)->on($this->blog)->approved()->create();

        Livewire::test(ListComments::class)
            ->assertActionVisible(TestAction::make('view-reply')->table($replied))
            ->assertActionHidden(TestAction::make('view-reply')->table($unanswered));
    }

    #[Test]
    public function itOffersADeleteActionForEveryComment(): void
    {
        $comment = $this->build(Comment::class)->on($this->blog)->create();

        Livewire::test(ListComments::class)->assertActionExists(TestAction::make(DeleteAction::class)->table($comment));
    }
}
