<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\FindLinkForCountyTool;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FindLinkForCountyToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itReturnsTheCountyAbsoluteLinkWhenFound(): void
    {
        $county = $this->create(EateryCounty::class, ['county' => 'Cheshire East']);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);
        $this->create(Eatery::class, ['county_id' => $county->id, 'town_id' => $town->id]);

        $result = (new FindLinkForCountyTool())->handle(new Request(['county' => 'Cheshire East']));

        $this->assertEquals($county->absoluteLink(), (string) $result);
    }

    #[Test]
    public function itMatchesOnAPartialCountyName(): void
    {
        $county = $this->create(EateryCounty::class, ['county' => 'Cheshire East']);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);
        $this->create(Eatery::class, ['county_id' => $county->id, 'town_id' => $town->id]);

        $result = (new FindLinkForCountyTool())->handle(new Request(['county' => 'Cheshire']));

        $this->assertEquals($county->absoluteLink(), (string) $result);
    }

    #[Test]
    public function itReturnsNotFoundWhenCountyDoesNotExist(): void
    {
        $result = (new FindLinkForCountyTool())->handle(new Request(['county' => 'Nonexistent County']));

        $this->assertEquals('- county not found -', (string) $result);
    }
}
