<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Journeys;

use App\Actions\Journey\ResolveJourneyAction;
use App\Models\Journeys\Journey;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResolveJourneyActionTest extends TestCase
{
    #[Test]
    public function itCreatesAJourneyForTheGivenSessionIdButDoesntPersistItInTheDatabase(): void
    {
        $this->assertDatabaseEmpty(Journey::class);

        $journey = app(ResolveJourneyAction::class)->handle('foo');

        $this->assertDatabaseEmpty(Journey::class);
        $this->assertEquals('foo', $journey->session_id);
    }

    #[Test]
    public function itReturnsAnExistingJourneyIfThereIsOneForTheGivenSessionId(): void
    {
        $this->build(Journey::class)->forSession('foo')->create();

        app(ResolveJourneyAction::class)->handle('foo');

        $this->assertDatabaseCount(Journey::class, 1);
    }
}
