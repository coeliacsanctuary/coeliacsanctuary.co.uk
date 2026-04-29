<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use App\Models\NovaPreview;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CleanUpNovaPreviewsCommandTest extends TestCase
{
    #[Test]
    public function itDeletesPreviewRecordsOlderThan24Hours(): void
    {
        $old = $this->create(NovaPreview::class, ['created_at' => now()->subDay()->subMinute()]);
        $recent = $this->create(NovaPreview::class, ['created_at' => now()->subHour()]);

        $this->artisan('coeliac:clean-up-nova-previews');

        $this->assertDatabaseCount(NovaPreview::class, 1);
        $this->assertModelExists($recent);
        $this->assertModelMissing($old);
    }

    #[Test]
    public function itDoesNothingWhenNoOldRecordsExist(): void
    {
        $this->create(NovaPreview::class, ['created_at' => now()->subHour()]);

        $this->artisan('coeliac:clean-up-nova-previews');

        $this->assertDatabaseCount(NovaPreview::class, 1);
    }

    #[Test]
    public function itDoesNothingWhenTableIsEmpty(): void
    {
        $this->assertDatabaseEmpty(NovaPreview::class);

        $this->artisan('coeliac:clean-up-nova-previews');

        $this->assertDatabaseEmpty(NovaPreview::class);
    }
}
