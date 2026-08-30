<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Schemas\Components;

use App\Filament\Schemas\Components\VisibilitySection;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\BuildsFilamentSchemas;
use Tests\TestCase;

class VisibilitySectionTest extends TestCase
{
    use BuildsFilamentSchemas;

    #[Test]
    public function itHoldsTheStatusSelect(): void
    {
        $this->assertInstanceOf(Select::class, $this->mountedComponent('status', [VisibilitySection::make()]));
    }

    #[Test]
    public function itHoldsThePublishAtPicker(): void
    {
        $this->assertInstanceOf(DateTimePicker::class, $this->mountedComponent('publish_at', [VisibilitySection::make()]));
    }

    #[Test]
    public function thePublishAtDateDefaultsToTomorrow(): void
    {
        $this->travelTo(Carbon::parse('2026-08-30 12:00:00'));

        $this->mountSchema([VisibilitySection::make()])
            ->assertSchemaComponentStateSet('publish_at', '2026-08-31 12:00:00');
    }

    #[Test]
    public function thePublishAtDateIsOnlyShownWhenScheduling(): void
    {
        $this->mountSchema([VisibilitySection::make()])
            ->assertSchemaComponentHidden('publish_at')
            ->set('data.status', 'scheduled')
            ->assertSchemaComponentVisible('publish_at');
    }

    #[Test]
    public function thePublishAtDateIsRequiredWhenScheduling(): void
    {
        $section = $this->mountSchema([VisibilitySection::make()])->set('data.status', 'scheduled');

        $this->assertTrue($section->instance()->getSchema('form')->getFlatComponents(withHidden: true)['publish_at']->isRequired());
    }

    #[Test]
    #[DataProvider('unscheduledStatuses')]
    public function thePublishAtDateIsNotRequiredOtherwise(string $status): void
    {
        $section = $this->mountSchema([VisibilitySection::make()])->set('data.status', $status);

        $this->assertFalse($section->instance()->getSchema('form')->getFlatComponents(withHidden: true)['publish_at']->isRequired());
    }

    public static function unscheduledStatuses(): array
    {
        return [
            'draft' => ['draft'],
            'live' => ['live'],
        ];
    }
}
