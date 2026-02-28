<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\ConvertEateriesToNationwideCommand;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Testing\PendingCommand;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class ConvertEateriesToNationwideCommandTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->build(EateryTown::class)->create([
            'id' => 529,
            'town' => 'Nationwide',
            'slug' => 'nationwide',
            'county_id' => 1,
        ]);

        $this->county = $this->create(EateryCounty::class);
        $this->town = $this->build(EateryTown::class)->create(['county_id' => $this->county->id]);
    }

    /**
     * @param  Collection<int, Eatery>|Eatery  $eateries
     * @param  array<string, mixed>  $options
     */
    protected function runCommand(Collection|Eatery $eateries, array $options = []): PendingCommand
    {
        if ($eateries instanceof Eatery) {
            $eateries = collect([$eateries]);
        }

        $eateryIds = $eateries->pluck('id')->toArray();

        $eateries = Eatery::withoutGlobalScopes()
            ->with(['town', 'county'])
            ->whereIn('id', $eateryIds)
            ->get();

        /** @var Eatery $first */
        $first = $eateries->first();

        $defaults = [
            'name' => $first->name,
            'phone' => $first->phone,
            'website' => $first->website,
            'venueTypeId' => 1,
            'cuisineId' => 1,
            'info' => $first->info,
            'featureIds' => [],
            'confirm' => true,
        ];

        $params = array_merge($defaults, $options);

        $eateryOptions = $eateries
            ->mapWithKeys(fn (Eatery $e) => [$e->id => "{$e->name} — {$e->town?->town}, {$e->county?->county}"])
            ->toArray();

        $venueTypes = EateryVenueType::all()->pluck('venue_type', 'id')->toArray();
        $cuisines = EateryCuisine::all()->pluck('cuisine', 'id')->toArray();
        $features = EateryFeature::all()->pluck('feature', 'id')->toArray();

        return $this->artisan(ConvertEateriesToNationwideCommand::class)
            ->expectsSearch('Search and select eateries to convert', $eateryIds, '', $eateryOptions)
            ->expectsQuestion('Name', $params['name'])
            ->expectsQuestion('Phone', $params['phone'])
            ->expectsQuestion('Website', $params['website'])
            ->expectsChoice('Venue type', $params['venueTypeId'], $venueTypes)
            ->expectsChoice('Cuisine', $params['cuisineId'], $cuisines)
            ->expectsQuestion('Info', $params['info'])
            ->expectsChoice('Select features', $params['featureIds'], $features)
            ->expectsConfirmation('Proceed to create the nationwide eatery and branches?', $params['confirm'] ? 'yes' : 'no');
    }

    #[Test]
    public function itCreatesTheBaseEateryWithCorrectDetails(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
            'name' => 'Test Eatery',
            'phone' => '01234 567890',
            'website' => 'https://example.com',
            'info' => 'Great gluten-free food',
            'type_id' => 1,
            'venue_type_id' => 1,
            'cuisine_id' => 1,
        ]);

        $this->runCommand($eatery, [
            'name' => 'Test Eatery',
            'phone' => '01234 567890',
            'website' => 'https://example.com',
            'venueTypeId' => 1,
            'cuisineId' => 1,
            'info' => 'Great gluten-free food',
        ])->run();

        $this->assertDatabaseHas('wheretoeat', [
            'name' => 'Test Eatery',
            'phone' => '01234 567890',
            'website' => 'https://example.com',
            'venue_type_id' => 1,
            'cuisine_id' => 1,
            'info' => 'Great gluten-free food',
            'type_id' => 1,
            'country_id' => 1,
            'county_id' => 1,
            'town_id' => 529,
            'live' => 1,
        ]);
    }

    #[Test]
    public function itAttachesFeaturesToTheBaseEatery(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
        ]);

        $featureIds = EateryFeature::all()->take(2)->pluck('id')->toArray();

        $this->runCommand($eatery, ['featureIds' => $featureIds])->run();

        $baseEatery = Eatery::withoutGlobalScopes()->where('town_id', 529)->first();

        $this->assertNotNull($baseEatery);
        $this->assertEqualsCanonicalizing($featureIds, $baseEatery->features->pluck('id')->toArray());
    }

    #[Test]
    public function itCreatesBranchesWithEmptyNameWhenItMatchesParent(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
            'name' => 'Matching Name',
        ]);

        $this->runCommand($eatery, ['name' => 'Matching Name'])->run();

        $this->assertDatabaseHas('wheretoeat_nationwide_branches', ['name' => '']);
    }

    #[Test]
    public function itCreatesBranchesWithOriginalNameWhenItDiffersFromParent(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
            'name' => 'Branch Name',
        ]);

        $this->runCommand($eatery, ['name' => 'Different Parent Name'])->run();

        $this->assertDatabaseHas('wheretoeat_nationwide_branches', ['name' => 'Branch Name']);
    }

    #[Test]
    public function itUsesTownSlugForBranchSlugWhenNameMatchesParent(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
            'name' => 'Same Name',
        ]);

        $this->runCommand($eatery, ['name' => 'Same Name'])->run();

        $this->assertDatabaseHas('wheretoeat_nationwide_branches', [
            'slug' => $this->town->slug,
        ]);
    }

    #[Test]
    public function itAppendPostcodeToSlugWhenTownSlugIsAlreadyUsed(): void
    {
        $sharedName = 'Chain Eatery';

        $eatery1 = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
            'name' => $sharedName,
            'address' => "1 Main St\n{$this->town->town}\nSW1 1AA",
        ]);

        $eatery2 = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
            'name' => $sharedName,
            'address' => "2 Other St\n{$this->town->town}\nSW2 2BB",
        ]);

        $this->runCommand(collect([$eatery1, $eatery2]), ['name' => $sharedName])->run();

        $branches = NationwideBranch::withoutGlobalScopes()->get();

        $this->assertCount(2, $branches);

        $this->assertTrue($branches->contains('slug', $this->town->slug));
        $this->assertTrue($branches->filter(fn ($b) => str_starts_with($b->slug, $this->town->slug . '-'))->isNotEmpty());
    }

    #[Test]
    public function itUsesExistingEaterySlugWhenBranchHasUniqueName(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
            'name' => 'Branch Specific Name',
            'slug' => 'branch-specific-name',
        ]);

        $this->runCommand($eatery, ['name' => 'Different Parent Name'])->run();

        $this->assertDatabaseHas('wheretoeat_nationwide_branches', [
            'slug' => 'branch-specific-name',
        ]);
    }

    #[Test]
    public function itMigratesReviewsToTheNewBaseEateryAndBranch(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
        ]);

        $this->build(EateryReview::class)->create(['wheretoeat_id' => $eatery->id]);

        $this->runCommand($eatery)->run();

        $baseEatery = Eatery::withoutGlobalScopes()->where('town_id', 529)->first();
        $branch = NationwideBranch::withoutGlobalScopes()->where('wheretoeat_id', $baseEatery->id)->first();

        $this->assertDatabaseHas('wheretoeat_reviews', [
            'wheretoeat_id' => $baseEatery->id,
            'nationwide_branch_id' => $branch->id,
        ]);
    }

    #[Test]
    public function itSetsOriginalEateriesToLiveZero(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
        ]);

        $this->runCommand($eatery)->run();

        $this->assertDatabaseHas('wheretoeat', [
            'id' => $eatery->id,
            'live' => 0,
        ]);
    }

    #[Test]
    public function itCreatesMultipleBranchesFromMultipleEateries(): void
    {
        $town2 = $this->build(EateryTown::class)->create(['county_id' => $this->county->id]);

        $eatery1 = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
        ]);

        $eatery2 = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $town2->id,
        ]);

        $this->runCommand(collect([$eatery1, $eatery2]))->run();

        $baseEatery = Eatery::withoutGlobalScopes()->where('town_id', 529)->first();

        $this->assertNotNull($baseEatery);
        $this->assertDatabaseCount('wheretoeat_nationwide_branches', 2);
        $this->assertDatabaseHas('wheretoeat_nationwide_branches', [
            'wheretoeat_id' => $baseEatery->id,
            'town_id' => $this->town->id,
        ]);
        $this->assertDatabaseHas('wheretoeat_nationwide_branches', [
            'wheretoeat_id' => $baseEatery->id,
            'town_id' => $town2->id,
        ]);
    }

    #[Test]
    public function itReusesAnExistingNationwideEateryOnRerun(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
            'name' => 'Chain Eatery',
        ]);

        $this->runCommand($eatery, ['name' => 'Chain Eatery'])->run();

        $eatery2 = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
            'name' => 'Chain Eatery',
        ]);

        $this->runCommand($eatery2, ['name' => 'Chain Eatery'])->run();

        $this->assertSame(
            1,
            Eatery::withoutGlobalScopes()
                ->where(['name' => 'Chain Eatery', 'county_id' => 1, 'town_id' => 529])
                ->count(),
        );

        $this->assertDatabaseCount('wheretoeat_nationwide_branches', 2);
    }

    #[Test]
    public function itWrapsEverythingInADatabaseTransaction(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
        ]);

        NationwideBranch::creating(static fn () => throw new RuntimeException('Simulated failure'));

        try {
            $this->runCommand($eatery)->run();
        } catch (Exception) {
            // The exception may propagate depending on the Artisan runner
        }

        $this->assertDatabaseMissing('wheretoeat', [
            'county_id' => 1,
            'town_id' => 529,
        ]);
        $this->assertDatabaseCount('wheretoeat_nationwide_branches', 0);
    }
}
