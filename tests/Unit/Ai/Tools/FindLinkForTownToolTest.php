<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\FindLinkForTownTool;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FindLinkForTownToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itReturnsTheTownAbsoluteLinkWhenFound(): void
    {
        $county = $this->create(EateryCounty::class);
        $town = $this->create(EateryTown::class, ['town' => 'Chester', 'county_id' => $county->id]);
        $this->create(Eatery::class, ['county_id' => $county->id, 'town_id' => $town->id]);

        $result = (new FindLinkForTownTool())->handle(new Request(['town' => 'Chester']));

        $this->assertEquals($town->absoluteLink(), (string) $result);
    }

    #[Test]
    public function itMatchesOnAPartialTownName(): void
    {
        $county = $this->create(EateryCounty::class);
        $town = $this->create(EateryTown::class, ['town' => 'Chester-le-Street', 'county_id' => $county->id]);
        $this->create(Eatery::class, ['county_id' => $county->id, 'town_id' => $town->id]);

        $result = (new FindLinkForTownTool())->handle(new Request(['town' => 'Chester']));

        $this->assertEquals($town->absoluteLink(), (string) $result);
    }

    #[Test]
    public function itReturnsNotFoundWhenTownDoesNotExist(): void
    {
        $result = (new FindLinkForTownTool())->handle(new Request(['town' => 'Nonexistent Town']));

        $this->assertEquals('- town not found -', (string) $result);
    }
}
