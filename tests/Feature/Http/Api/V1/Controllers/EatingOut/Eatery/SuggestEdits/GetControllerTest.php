<?php

declare(strict_types=1);

namespace Feature\Http\Api\V1\Controllers\EatingOut\Eatery\SuggestEdits;

use App\Models\EatingOut\Eatery;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.details.suggest-edit.get', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itReturnsTheExpectedFormat(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'address',
                    'website',
                    'gf_menu_link',
                    'phone',
                    'type_id',
                    'venue_type',
                    'cuisine',
                    'opening_times' => [
                        'today' => [],
                        'monday' => [],
                        'tuesday' => [],
                        'wednesday' => [],
                        'thursday' => [],
                        'friday' => [],
                        'saturday' => [],
                        'sunday' => [],
                    ],
                    'features',
                    'is_nationwide',
                ],
            ]);
    }

    protected function makeRequest(array $params = [], string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.details.suggest-edit.get', ['eatery' => $this->eatery, ...$params]),
            ['x-coeliac-source' => $source],
        );
    }
}
