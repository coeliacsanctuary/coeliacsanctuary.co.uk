<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Eatery\Report;

use App\Actions\EatingOut\CreateEateryReportAction;
use App\Models\EatingOut\Eatery;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreControllerTest extends TestCase
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
        $this->postJson(route('api.v1.eating-out.details.report', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itErrorsWithAMissingOrInvalidReportDetails(): void
    {
        $this->makeRequest(['details' => null])->assertJsonValidationErrorFor('details');
        $this->makeRequest(['details' => true])->assertJsonValidationErrorFor('details');
        $this->makeRequest(['details' => 123])->assertJsonValidationErrorFor('details');
    }

    #[Test]
    public function itCallsTheCreateEateryReportAction(): void
    {
        $this->mock(CreateEateryReportAction::class)
            ->shouldReceive('handle')
            ->once();

        $this->makeRequest()->assertCreated();
    }

    protected function makeRequest(mixed $report = 'foo', string $source = 'bar'): TestResponse
    {
        return $this->postJson(
            route('api.v1.eating-out.details.report', $this->eatery),
            ['details' => $report],
            ['x-coeliac-source' => $source],
        );
    }
}
