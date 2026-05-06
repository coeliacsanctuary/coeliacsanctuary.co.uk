<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use App\Jobs\Metrics\Blogs\BlogMetricOrchestratorJob;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PrepareMetricUpdatesCommandTest extends TestCase
{
    #[Test]
    public function itDispatchesBlogMetricOrchestratorJob(): void
    {
        Bus::fake();

        $this->artisan('coeliac:prepare-metric-updates');

        Bus::assertDispatched(BlogMetricOrchestratorJob::class);
    }
}
