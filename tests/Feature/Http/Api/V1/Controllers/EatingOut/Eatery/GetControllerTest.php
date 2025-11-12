<?php

declare(strict_types=1);

namespace Feature\Http\Api\V1\Controllers\EatingOut\Eatery;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected NationwideBranch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
        $this->branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create();
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeaderOnTheEateryRoute(): void
    {
        $this->getJson(route('api.v1.eating-out.details.get', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeaderOnTheBranchRoute(): void
    {
        $this->getJson(route('api.v1.eating-out.details.get.branch', [$this->eatery, $this->branch]))->assertForbidden();
    }

    #[Test]
    public function itReturnsTheExpectedFormatForAnEatery(): void
    {
        $this->makeEateryRequest()
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'county',
                    'town',
                    'area',
                    'venue_type',
                    'type',
                    'cuisine',
                    'website',
                    'menu',
                    'restaurants' => [],
                    'is_fully_gf',
                    'info',
                    'location' => ['address', 'lat', 'lng'],
                    'phone',
                    'reviews' => ['number', 'average', 'expense'],
                    'features' => [],
                    'opening_times',
                    'branch',
                    'is_nationwide',
                    'last_updated',
                    'last_updated_human',
                    'qualifies_for_ai',
                    'number_of_branches',
                ],
            ]);
    }

    #[Test]
    public function itReturnsNotFoundIfTheBranchBelongsToAnotherEatery(): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->create(Eatery::class)->id]);

        $this->makeBranchRequest($branch)->assertNotFound();
    }

    #[Test]
    public function itReturnsTheExpectedFormatForABranch(): void
    {
        $this->makeBranchRequest()
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'county',
                    'town',
                    'area',
                    'venue_type',
                    'type',
                    'cuisine',
                    'website',
                    'menu',
                    'restaurants' => [],
                    'is_fully_gf',
                    'info',
                    'location' => ['address', 'lat', 'lng'],
                    'phone',
                    'reviews' => ['number', 'average', 'expense'],
                    'features' => [],
                    'opening_times',
                    'branch',
                    'is_nationwide',
                    'last_updated',
                    'last_updated_human',
                    'qualifies_for_ai',
                    'number_of_branches',
                ],
            ]);
    }

    protected function makeEateryRequest(string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.details.get', $this->eatery),
            ['x-coeliac-source' => $source],
        );
    }

    protected function makeBranchRequest(?NationwideBranch $branch = null, string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.details.get.branch', [$this->eatery, $branch ?: $this->branch]),
            ['x-coeliac-source' => $source],
        );
    }
}
