<?php

declare(strict_types=1);

namespace Feature\Http\Api\V1\Controllers\EatingOut\Eatery\Branches\Summary;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
        $this->build(NationwideBranch::class)->forEatery($this->eatery)->count(20)->create();
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.details.branches.summary.index', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itReturnsTheExpectedFormat(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure([
                'data' => [],
            ]);
    }

    protected function makeRequest(array $params = [], string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.details.branches.summary.index', ['eatery' => $this->eatery, ...$params]),
            ['x-coeliac-source' => $source],
        );
    }
}
