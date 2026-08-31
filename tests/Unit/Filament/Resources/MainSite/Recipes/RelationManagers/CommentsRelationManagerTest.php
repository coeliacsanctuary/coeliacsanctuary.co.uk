<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Recipes\RelationManagers;

use App\Filament\Resources\MainSite\Recipes\Pages\EditRecipe;
use App\Filament\Resources\MainSite\Recipes\RelationManagers\CommentsRelationManager;
use App\Models\Comments\Comment;
use App\Models\Recipes\Recipe;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentsRelationManagerTest extends TestCase
{
    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->recipe = $this->create(Recipe::class);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsForARecipeWithNoComments(): void
    {
        $this->commentsPanel()->assertOk();
    }

    #[Test]
    public function itShowsTheCommentsOnTheRecipe(): void
    {
        $comment = $this->build(Comment::class)->on($this->recipe)->approved()->create();

        $this->commentsPanel()->assertCanSeeTableRecords([$comment]);
    }

    #[Test]
    public function itDoesNotShowCommentsFromAnotherRecipe(): void
    {
        $mine = $this->build(Comment::class)->on($this->recipe)->approved()->create();
        $theirs = $this->build(Comment::class)->on($this->create(Recipe::class))->approved()->create();

        $this->commentsPanel()
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    #[Test]
    public function itOnlyShowsApprovedComments(): void
    {
        $approved = $this->build(Comment::class)->on($this->recipe)->approved()->create();
        $pending = $this->build(Comment::class)->on($this->recipe)->create(['approved' => false]);

        $this->commentsPanel()
            ->assertCanSeeTableRecords([$approved])
            ->assertCanNotSeeTableRecords([$pending]);
    }

    protected function commentsPanel(): Testable
    {
        return Livewire::test(CommentsRelationManager::class, [
            'ownerRecord' => $this->recipe,
            'pageClass' => EditRecipe::class,
        ]);
    }
}
