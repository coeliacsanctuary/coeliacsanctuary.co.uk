<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\EatingOut\Features;

use App\Services\EatingOut\Filters\GetFilters;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itCallsTheGetFiltersService(): void
    {
        $this->expectAction(GetFilters::class);

        $this->get(route('api.wheretoeat.features'));
    }
}
