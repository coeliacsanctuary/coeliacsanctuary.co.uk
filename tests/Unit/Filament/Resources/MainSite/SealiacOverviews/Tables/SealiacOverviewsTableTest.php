<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\SealiacOverviews\Tables;

use App\Filament\Resources\MainSite\SealiacOverviews\Pages\ListSealiacOverviews;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use App\Models\Shop\ShopProduct;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SealiacOverviewsTableTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    #[DataProvider('columns')]
    public function itShowsTheSealiacOverviewColumns(string $column): void
    {
        Livewire::test(ListSealiacOverviews::class)->assertTableColumnExists($column);
    }

    public static function columns(): array
    {
        return [
            'type' => ['model_type'],
            'model' => ['model_name'],
            'status' => ['status'],
            'thumbs up' => ['thumbs_up'],
            'thumbs down' => ['thumbs_down'],
            'rating' => ['rating'],
            'overview' => ['overview'],
        ];
    }

    #[Test]
    public function itDoesNotShowAnIdColumn(): void
    {
        Livewire::test(ListSealiacOverviews::class)->assertTableColumnDoesNotExist('id');
    }

    #[Test]
    public function itShowsTheNewestOverviewsFirst(): void
    {
        $overviews = $this->build(SealiacOverview::class)->forEatery($this->eatery)->count(3)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->assertCanSeeTableRecords($overviews->reverse()->values(), inOrder: true);
    }

    #[Test]
    public function itLabelsTheTypeOfAnEateryOverview(): void
    {
        $overview = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->assertTableColumnFormattedStateSet('model_type', 'Eatery', $overview);
    }

    #[Test]
    public function itLabelsTheTypeOfANationwideBranchOverview(): void
    {
        $branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create();
        $overview = $this->build(SealiacOverview::class)->forNationwideBranch($branch)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->assertTableColumnFormattedStateSet('model_type', 'Nationwide Branch', $overview);
    }

    #[Test]
    public function itLabelsTheTypeOfAProductOverview(): void
    {
        $product = $this->create(ShopProduct::class);
        $overview = $this->build(SealiacOverview::class)->forProduct($product)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->assertTableColumnFormattedStateSet('model_type', 'Product', $overview);
    }

    #[Test]
    public function itShowsTheNameOfTheEateryAnOverviewIsFor(): void
    {
        $eatery = $this->create(Eatery::class, ['name' => 'The Gluten Free Kitchen']);
        $overview = $this->build(SealiacOverview::class)->forEatery($eatery)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->assertTableColumnStateSet('model_name', 'The Gluten Free Kitchen', $overview);
    }

    #[Test]
    public function itShowsTheNameOfTheNationwideBranchAnOverviewIsFor(): void
    {
        $branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create(['name' => 'Crewe Branch']);
        $overview = $this->build(SealiacOverview::class)->forNationwideBranch($branch)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->assertTableColumnStateSet('model_name', 'Crewe Branch', $overview);
    }

    #[Test]
    public function itFallsBackToTheEateryNameForABranchWithoutItsOwnName(): void
    {
        $eatery = $this->create(Eatery::class, ['name' => 'Coeliac Cafe']);
        $branch = $this->build(NationwideBranch::class)->forEatery($eatery)->create(['name' => null]);
        $overview = $this->build(SealiacOverview::class)->forNationwideBranch($branch)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->assertTableColumnStateSet('model_name', 'Coeliac Cafe', $overview);
    }

    #[Test]
    public function itShowsTheTitleOfTheProductAnOverviewIsFor(): void
    {
        $product = $this->create(ShopProduct::class, ['title' => 'Coeliac Sanctuary Mug']);
        $overview = $this->build(SealiacOverview::class)->forProduct($product)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->assertTableColumnStateSet('model_name', 'Coeliac Sanctuary Mug', $overview);
    }

    #[Test]
    public function itDoesntBreakWhenTheModelAnOverviewIsForNoLongerExists(): void
    {
        $overview = $this->create(SealiacOverview::class, [
            'model_type' => Eatery::class,
            'model_id' => 99999,
        ]);

        Livewire::test(ListSealiacOverviews::class)
            ->assertOk()
            ->assertTableColumnStateSet('model_name', null, $overview);
    }

    #[Test]
    public function itSearchesByEateryName(): void
    {
        $eatery = $this->create(Eatery::class, ['name' => 'The Gluten Free Kitchen']);
        $wanted = $this->build(SealiacOverview::class)->forEatery($eatery)->create();
        $other = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->searchTable('Gluten Free Kitchen')
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itSearchesByNationwideBranchName(): void
    {
        $branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create(['name' => 'Crewe Branch']);
        $wanted = $this->build(SealiacOverview::class)->forNationwideBranch($branch)->create();
        $other = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->searchTable('Crewe Branch')
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itSearchesByProductTitle(): void
    {
        $product = $this->create(ShopProduct::class, ['title' => 'Coeliac Sanctuary Mug']);
        $wanted = $this->build(SealiacOverview::class)->forProduct($product)->create();
        $other = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->searchTable('Sanctuary Mug')
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itSearchesEateriesThatArentLive(): void
    {
        $eatery = $this->build(Eatery::class)->notLive()->create(['name' => 'The Gluten Free Kitchen']);
        $overview = $this->build(SealiacOverview::class)->forEatery($eatery)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->searchTable('Gluten Free Kitchen')
            ->assertCanSeeTableRecords([$overview]);
    }

    #[Test]
    public function itRatesAnOverviewByItsThumbsUpMinusItsThumbsDown(): void
    {
        $overview = $this->build(SealiacOverview::class)
            ->forEatery($this->eatery)
            ->create(['thumbs_up' => 7, 'thumbs_down' => 2]);

        Livewire::test(ListSealiacOverviews::class)->assertTableColumnStateSet('rating', 5, $overview);
    }

    #[Test]
    public function itSortsByRating(): void
    {
        $worst = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create(['thumbs_up' => 1, 'thumbs_down' => 6]);
        $middle = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create(['thumbs_up' => 3, 'thumbs_down' => 3]);
        $best = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create(['thumbs_up' => 9, 'thumbs_down' => 1]);

        Livewire::test(ListSealiacOverviews::class)
            ->sortTable('rating')
            ->assertCanSeeTableRecords([$worst, $middle, $best], inOrder: true)
            ->sortTable('rating', 'desc')
            ->assertCanSeeTableRecords([$best, $middle, $worst], inOrder: true);
    }

    #[Test]
    public function itMarksAnActiveOverviewAsActive(): void
    {
        $overview = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();

        Livewire::test(ListSealiacOverviews::class)->assertTableColumnStateSet('status', 'Active', $overview);
    }

    #[Test]
    public function itMarksAnInvalidatedOverviewAsInvalidated(): void
    {
        $overview = $this->build(SealiacOverview::class)->forEatery($this->eatery)->invalidated()->create();

        Livewire::test(ListSealiacOverviews::class)->assertTableColumnStateSet('status', 'Invalidated', $overview);
    }

    #[Test]
    public function itOpensTheFullOverviewInAReadOnlyModal(): void
    {
        $overview = $this->build(SealiacOverview::class)
            ->forEatery($this->eatery)
            ->create(['overview' => 'Sealiac reckons this one is worth a visit.']);

        Livewire::test(ListSealiacOverviews::class)
            ->assertActionExists(
                TestAction::make('view')->table($overview),
                fn (Action $action): bool => $action->getModalHeading() === 'Sealiac Overview' && $action->getModalSubmitAction() === null,
            )
            ->mountAction(TestAction::make('view')->table($overview))
            ->assertSee('Sealiac reckons this one is worth a visit.');
    }

    #[Test]
    public function itOffersTheInvalidateActionOnlyForAnActiveOverview(): void
    {
        $active = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();
        $invalidated = $this->build(SealiacOverview::class)->forEatery($this->eatery)->invalidated()->create();

        Livewire::test(ListSealiacOverviews::class)
            ->assertActionVisible(TestAction::make('invalidate')->table($active))
            ->assertActionHidden(TestAction::make('invalidate')->table($invalidated));
    }

    #[Test]
    public function itConfirmsBeforeInvalidating(): void
    {
        $overview = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();

        Livewire::test(ListSealiacOverviews::class)->assertActionExists(
            TestAction::make('invalidate')->table($overview),
            fn (Action $action): bool => $action->isConfirmationRequired(),
        );
    }

    #[Test]
    public function itInvalidatesAnOverview(): void
    {
        $overview = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();

        Livewire::test(ListSealiacOverviews::class)
            ->callAction(TestAction::make('invalidate')->table($overview));

        $this->assertTrue($overview->refresh()->invalidated);
    }
}
