<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Forms\Components;

use App\Filament\Forms\Components\StatusField;
use App\Filament\Transformers\StatusTransformer;
use App\Models\Blogs\Blog;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\BuildsFilamentSchemas;
use Tests\TestCase;

class StatusFieldTest extends TestCase
{
    use BuildsFilamentSchemas;

    #[Test]
    public function itOffersDraftLiveAndScheduled(): void
    {
        $this->assertSame(
            ['draft' => 'Draft', 'live' => 'Live', 'scheduled' => 'Scheduled'],
            $this->mountedComponent('status', [StatusField::make()])->getOptions()
        );
    }

    #[Test]
    public function itDefaultsToDraft(): void
    {
        $this->mountSchema([StatusField::make()])->assertSchemaComponentStateSet('status', 'draft');
    }

    #[Test]
    public function itIsLive(): void
    {
        $this->assertTrue($this->mountedComponent('status', [StatusField::make()])->isLive());
    }

    #[Test]
    public function itShowsALiveRecordAsLive(): void
    {
        $blog = $this->create(Blog::class, ['live' => true]);

        $this->mountSchema([StatusField::make()], 'edit', $blog)
            ->assertSchemaComponentStateSet('status', 'live');
    }

    #[Test]
    public function itShowsAnUnpublishedRecordWithAPublishDateAsScheduled(): void
    {
        $blog = $this->create(Blog::class, ['live' => false, 'publish_at' => Carbon::now()->addDay()]);

        $this->mountSchema([StatusField::make()], 'edit', $blog)
            ->assertSchemaComponentStateSet('status', 'scheduled');
    }

    #[Test]
    public function itShowsAnUnpublishedRecordWithoutAPublishDateAsDraft(): void
    {
        $blog = $this->create(Blog::class, ['live' => false, 'publish_at' => null]);

        $this->mountSchema([StatusField::make()], 'edit', $blog)
            ->assertSchemaComponentStateSet('status', 'draft');
    }

    #[Test]
    public function itShowsDraftWhenThereIsNoRecord(): void
    {
        $this->mountSchema([StatusField::make()], 'create')
            ->assertSchemaComponentStateSet('status', 'draft');
    }

    #[Test]
    public function itClearsThePublishDateWhenSwitchingToLive(): void
    {
        $this->mountSchema([StatusField::make()])
            ->set('data.publish_at', '2026-09-01 09:00:00')
            ->set('data.status', 'live')
            ->assertSchemaStateSet(['publish_at' => null]);
    }

    #[Test]
    public function itKeepsThePublishDateWhenSwitchingToScheduled(): void
    {
        $this->mountSchema([StatusField::make()])
            ->set('data.publish_at', '2026-09-01 09:00:00')
            ->set('data.status', 'scheduled')
            ->assertSchemaStateSet(['publish_at' => '2026-09-01 09:00:00']);
    }

    #[Test]
    #[DataProvider('dehydratedStatuses')]
    public function itDehydratesTheStatusInTitleCaseForTheTransformer(string $state, string $dehydrated): void
    {
        $field = $this->mountedComponent('status', [StatusField::make()]);

        $this->assertSame($dehydrated, $field->getStateToDehydrate($state)['data.status']);

        $this->assertArrayNotHasKey('status', StatusTransformer::transform(['status' => $dehydrated]));
    }

    public static function dehydratedStatuses(): array
    {
        return [
            'draft' => ['draft', 'Draft'],
            'live' => ['live', 'Live'],
            'scheduled' => ['scheduled', 'Scheduled'],
        ];
    }
}
