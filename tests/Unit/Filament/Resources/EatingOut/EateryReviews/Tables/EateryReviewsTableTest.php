<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryReviews\Tables;

use App\Filament\Resources\EatingOut\EateryReviews\Pages\ListEateryReviews;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryReviewsTableTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->create(Eatery::class, ['name' => 'Test Eatery', 'town_id' => 1, 'county_id' => 1, 'country_id' => 1]);
    }

    #[Test]
    public function itShowsApprovedUnapprovedAndAdminReviews(): void
    {
        $approved = $this->build(EateryReview::class)->on($this->eatery)->approved()->create();
        $pending = $this->build(EateryReview::class)->on($this->eatery)->create();
        $admin = $this->build(EateryReview::class)->on($this->eatery)->adminReview()->create();

        Livewire::test(ListEateryReviews::class)->assertCanSeeTableRecords([$approved, $pending, $admin]);
    }

    #[Test]
    public function itShowsTheNewestReviewsFirst(): void
    {
        $oldest = $this->build(EateryReview::class)->on($this->eatery)->create(['created_at' => now()->subDays(2)]);
        $middle = $this->build(EateryReview::class)->on($this->eatery)->create(['created_at' => now()->subDay()]);
        $newest = $this->build(EateryReview::class)->on($this->eatery)->create(['created_at' => now()]);

        Livewire::test(ListEateryReviews::class)->assertCanSeeTableRecords([$newest, $middle, $oldest], inOrder: true);
    }

    #[Test]
    public function itShowsTheEateryFullName(): void
    {
        $review = $this->build(EateryReview::class)->on($this->eatery)->create();

        Livewire::test(ListEateryReviews::class)
            ->assertTableColumnStateSet('eatery_name', $this->eatery->load(['area', 'town', 'county', 'country'])->full_name, $review);
    }

    #[Test]
    public function itShowsTheBranchFullName(): void
    {
        $branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create();
        $review = $this->build(EateryReview::class)->on($this->eatery)->branch($branch)->create();

        Livewire::test(ListEateryReviews::class)
            ->assertTableColumnStateSet('branch_full_name', $branch->load(['eatery', 'area', 'town', 'county', 'country'])->full_name, $review);
    }

    #[Test]
    public function itShowsTheReviewerDetails(): void
    {
        $review = $this->build(EateryReview::class)->on($this->eatery)->create([
            'name' => 'Jane Reviewer',
            'email' => 'jane@example.com',
            'ip' => '127.0.0.1',
            'branch_name' => 'High Street',
        ]);

        Livewire::test(ListEateryReviews::class)
            ->assertTableColumnStateSet('name', 'Jane Reviewer', $review)
            ->assertTableColumnStateSet('email', 'jane@example.com', $review)
            ->assertTableColumnStateSet('ip', '127.0.0.1', $review)
            ->assertTableColumnStateSet('branch_name', 'High Street', $review);
    }

    #[Test]
    public function itFiltersByApproved(): void
    {
        $approved = $this->build(EateryReview::class)->on($this->eatery)->approved()->create();
        $pending = $this->build(EateryReview::class)->on($this->eatery)->create();

        Livewire::test(ListEateryReviews::class)
            ->filterTable('approved', false)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$approved]);
    }

    #[Test]
    public function itFiltersByMethod(): void
    {
        $website = $this->build(EateryReview::class)->on($this->eatery)->create(['method' => 'website']);
        $app = $this->build(EateryReview::class)->on($this->eatery)->create(['method' => 'app']);

        Livewire::test(ListEateryReviews::class)
            ->filterTable('method', 'app')
            ->assertCanSeeTableRecords([$app])
            ->assertCanNotSeeTableRecords([$website]);
    }

    #[Test]
    public function itSearchesById(): void
    {
        $wanted = $this->build(EateryReview::class)->on($this->eatery)->create();
        $other = $this->build(EateryReview::class)->on($this->eatery)->create();

        Livewire::test(ListEateryReviews::class)
            ->searchTable((string) $wanted->id)
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itDeletesAReview(): void
    {
        $review = $this->build(EateryReview::class)->on($this->eatery)->create();

        Livewire::test(ListEateryReviews::class)->callAction(TestAction::make(DeleteAction::class)->table($review));

        $this->assertDatabaseEmpty(EateryReview::class);
    }
}
