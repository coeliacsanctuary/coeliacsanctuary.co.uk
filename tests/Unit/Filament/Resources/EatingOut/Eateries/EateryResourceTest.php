<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itIncludesEateriesThatArentLive(): void
    {
        $this->create(Eatery::class, ['live' => true]);
        $this->build(Eatery::class)->notLive()->create();

        $this->assertCount(2, EateryResource::getEloquentQuery()->get());
    }

    #[Test]
    public function itIncludesEateriesThatHaveClosedDown(): void
    {
        $this->create(Eatery::class);
        $this->build(Eatery::class)->closedDown()->create();

        $this->assertCount(2, EateryResource::getEloquentQuery()->get());
    }

    #[Test]
    public function itCountsTheReviews(): void
    {
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, 3, ['wheretoeat_id' => $eatery->id]);

        $this->assertSame(3, EateryResource::getEloquentQuery()->firstOrFail()->reviews_count);
    }

    #[Test]
    public function itCountsTheNationwideBranches(): void
    {
        $this->create(Eatery::class);

        $this->assertSame(0, EateryResource::getEloquentQuery()->firstOrFail()->nationwide_branches_count);
    }

    #[Test]
    public function itEagerLoadsTheLocationRelationships(): void
    {
        $this->create(Eatery::class);

        $eatery = EateryResource::getEloquentQuery()->firstOrFail();

        foreach (['town', 'county', 'country', 'reviews'] as $relation) {
            $this->assertTrue($eatery->relationLoaded($relation));
        }
    }

    #[Test]
    public function itTitlesAnEateryByItsName(): void
    {
        $this->assertSame('name', EateryResource::getRecordTitleAttribute());
    }

    #[Test]
    public function itRegistersTheListCreateAndEditPages(): void
    {
        $this->assertSame(['index', 'create', 'edit'], array_keys(EateryResource::getPages()));
    }

    #[Test]
    public function itSearchesGloballyByNameAndLocation(): void
    {
        $this->assertSame(
            ['name', 'town.town', 'county.county', 'area.area'],
            EateryResource::getGloballySearchableAttributes(),
        );
    }
}
