<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Shop;

use App\Actions\Shop\SyncShippingToGoogleMerchantAction;
use App\Jobs\Shop\SyncShippingToGoogleMerchantJob;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncShippingToGoogleMerchantJobTest extends TestCase
{
    #[Test]
    public function itCallsTheSyncAction(): void
    {
        $this->mock(SyncShippingToGoogleMerchantAction::class)
            ->shouldReceive('handle')
            ->once();

        (new SyncShippingToGoogleMerchantJob())->handle(app(SyncShippingToGoogleMerchantAction::class));
    }
}
