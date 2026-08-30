<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Transformers;

use App\Filament\Transformers\StatusTransformer;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatusTransformerTest extends TestCase
{
    #[Test]
    public function itMarksALiveStatusAsLive(): void
    {
        $this->assertTrue(StatusTransformer::transform(['status' => 'Live'])['live']);
    }

    #[Test]
    #[DataProvider('notLiveStatuses')]
    public function itMarksEveryOtherStatusAsNotLive(string $status): void
    {
        $this->assertFalse(StatusTransformer::transform(['status' => $status])['live']);
    }

    public static function notLiveStatuses(): array
    {
        return [
            'draft' => ['Draft'],
            'scheduled' => ['Scheduled'],
        ];
    }

    #[Test]
    public function itKeepsThePublishAtDateForAScheduledStatus(): void
    {
        $publishAt = Carbon::parse('2026-09-01 09:00:00');

        $data = StatusTransformer::transform(['status' => 'Scheduled', 'publish_at' => $publishAt]);

        $this->assertSame($publishAt, $data['publish_at']);
    }

    #[Test]
    #[DataProvider('unscheduledStatuses')]
    public function itClearsThePublishAtDateForEveryOtherStatus(string $status): void
    {
        $data = StatusTransformer::transform([
            'status' => $status,
            'publish_at' => Carbon::parse('2026-09-01 09:00:00'),
        ]);

        $this->assertNull($data['publish_at']);
    }

    public static function unscheduledStatuses(): array
    {
        return [
            'live' => ['Live'],
            'draft' => ['Draft'],
        ];
    }

    #[Test]
    public function itRemovesTheStatusKey(): void
    {
        $this->assertArrayNotHasKey('status', StatusTransformer::transform(['status' => 'Live']));
    }

    #[Test]
    public function itLeavesEveryOtherKeyUntouched(): void
    {
        $data = StatusTransformer::transform([
            'status' => 'Live',
            'title' => 'How To Make Gluten Free Bread',
            'slug' => 'how-to-make-gluten-free-bread',
            'show_author' => false,
        ]);

        $this->assertSame('How To Make Gluten Free Bread', $data['title']);
        $this->assertSame('how-to-make-gluten-free-bread', $data['slug']);
        $this->assertFalse($data['show_author']);
    }
}
