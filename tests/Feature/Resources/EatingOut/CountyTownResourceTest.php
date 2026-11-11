<?php

declare(strict_types=1);

namespace Tests\Feature\Resources\EatingOut;

use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Resources\EatingOut\CountyTownResource;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CountyTownResourceTest extends TestCase
{
    protected Eatery $eatery;

    protected EateryTown $town;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->town = EateryTown::query()->withoutGlobalScopes()->first();

        $this->eatery = $this->create(Eatery::class);

        Storage::fake('media');
    }

    #[Test]
    public function itHasANameAttribute(): void
    {
        $resource = new CountyTownResource($this->town)->toArray(request());

        $this->assertArrayHasKey('name', $resource);
        $this->assertEquals($this->town->town, $resource['name']);
    }

    #[Test]
    public function itHasALinkAttribute(): void
    {
        $resource = new CountyTownResource($this->town)->toArray(request());

        $this->assertArrayHasKey('link', $resource);
        $this->assertEquals($this->town->link(), $resource['link']);
    }

    #[Test]
    public function itHasTheNumberOfLiveEateries(): void
    {
        $this->build(Eatery::class)->count(4)->create();
        $this->build(Eatery::class)->notLive()->create();

        $resource = new CountyTownResource($this->town)->toArray(request());

        $this->assertArrayHasKey('eateries', $resource);
        $this->assertEquals(5, $resource['eateries']);
    }

    #[Test]
    public function itIncludesNationwideBranchesWithTheNumberOfLiveEateries(): void
    {
        $eateries = $this->build(Eatery::class)->count(4)->create();
        $this->build(Eatery::class)->notLive()->create();

        $this->build(NationwideBranch::class)
            ->count(5)
            ->sequence(fn (Sequence $sequence): array => [
                'wheretoeat_id' => $eateries[$sequence->index]->id ?? $this->eatery->id,
            ])
            ->create();

        $this->build(NationwideBranch::class)->notLive()->create();

        $resource = new CountyTownResource($this->town)->toArray(request());

        $this->assertArrayHasKey('eateries', $resource);
        $this->assertEquals(10, $resource['eateries']);
    }

    #[Test]
    public function itHasTheNumberOfLiveAttractions(): void
    {
        $this->build(Eatery::class)->attraction()->count(4)->create();
        $this->build(Eatery::class)->attraction()->notLive()->create();

        $resource = new CountyTownResource($this->town)->toArray(request());

        $this->assertArrayHasKey('attractions', $resource);
        $this->assertEquals(4, $resource['attractions']);
    }

    #[Test]
    public function itHasTheNumberOfLiveHotels(): void
    {
        $this->build(Eatery::class)->hotel()->count(4)->create();
        $this->build(Eatery::class)->hotel()->notLive()->create();

        $resource = new CountyTownResource($this->town)->toArray(request());

        $this->assertArrayHasKey('hotels', $resource);
        $this->assertEquals(4, $resource['hotels']);
    }
}
