<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryMagicRoutes\Pages;

use App\Actions\EatingOut\MagicRouting\GenerateNewMagicRouteAction;
use App\Enums\EatingOut\EateryMagicRouteType;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\Pages\CreateEateryMagicRoute;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Filament\Forms\Components\MorphToSelect;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateEateryMagicRouteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->create(Eatery::class, ['county_id' => 1, 'town_id' => 1, 'live' => true]);
    }

    #[Test]
    public function itGeneratesTheMagicRouteForACounty(): void
    {
        $county = EateryCounty::query()->first();

        $this->expectAction(GenerateNewMagicRouteAction::class, [
            fn (EateryMagicRouteType $type, EateryCounty|EateryTown|EateryArea $location): bool => $type === EateryMagicRouteType::HundredPercentGlutenFree && $location->is($county),
        ], return: $this->routeRecordFor($county));

        $this->createPage()
            ->fillForm($this->formData(EateryCounty::class, $county->id))
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();
    }

    #[Test]
    public function itGeneratesTheMagicRouteForATown(): void
    {
        $town = EateryTown::query()->first();

        $this->expectAction(GenerateNewMagicRouteAction::class, [
            fn (EateryMagicRouteType $type, EateryCounty|EateryTown|EateryArea $location): bool => $type === EateryMagicRouteType::HundredPercentGlutenFree && $location->is($town),
        ], return: $this->routeRecordFor($town));

        $this->createPage()
            ->fillForm($this->formData(EateryTown::class, $town->id))
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();
    }

    #[Test]
    public function itOnlyOffersCountiesAndTownsForHundredPercentGlutenFree(): void
    {
        $component = $this->createPage()
            ->fillForm(['resolver_type' => EateryMagicRouteType::HundredPercentGlutenFree->value])
            ->instance()
            ->form
            ->getComponent(fn ($component): bool => $component instanceof MorphToSelect);

        $this->assertSame([EateryCounty::class, EateryTown::class], array_keys($component->getTypes()));
    }

    #[Test]
    public function itPredictsTheNumberOfEateries(): void
    {
        $this->expectPipelineToRun(GetEateriesForMagicRoutePipeline::class, return: collect([new Eatery(), new Eatery()]));

        $this->createPage()
            ->fillForm($this->formData(EateryCounty::class, EateryCounty::query()->value('id')))
            ->assertSchemaStateSet(['predicted_eateries' => 2]);
    }

    #[Test]
    public function itPredictsTheNumberOfEateriesOnceTheLocationIsPicked(): void
    {
        $this->expectPipelineToRun(GetEateriesForMagicRoutePipeline::class, return: collect([new Eatery(), new Eatery()]));

        $this->createPage()
            ->set('data.resolver_type', EateryMagicRouteType::HundredPercentGlutenFree->value)
            ->set('data.location_type', EateryCounty::class)
            ->set('data.location_id', EateryCounty::query()->value('id'))
            ->assertSchemaStateSet(['predicted_eateries' => 2]);
    }

    #[Test]
    public function itRequiresATypeAndALocation(): void
    {
        $this->dontExpectAction(GenerateNewMagicRouteAction::class);

        $this->createPage()
            ->call('create')
            ->assertHasFormErrors(['resolver_type' => 'required', 'location_type' => 'required']);
    }

    protected function formData(string $locationType, int $locationId): array
    {
        return [
            'resolver_type' => EateryMagicRouteType::HundredPercentGlutenFree->value,
            'location_type' => $locationType,
            'location_id' => $locationId,
        ];
    }

    protected function routeRecordFor(EateryCounty|EateryTown $location): EateryMagicRouteRecord
    {
        return $this->create(EateryMagicRouteRecord::class, [
            'raw_location' => $location->slug,
            'location_type' => $location::class,
            'location_id' => $location->id,
        ]);
    }

    protected function createPage(): Testable
    {
        return Livewire::test(CreateEateryMagicRoute::class);
    }
}
