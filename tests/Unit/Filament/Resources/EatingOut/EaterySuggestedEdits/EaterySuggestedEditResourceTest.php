<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EaterySuggestedEdits;

use App\Filament\Resources\EatingOut\EaterySuggestedEdits\EaterySuggestedEditResource;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EaterySuggestedEdit;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EaterySuggestedEditResourceTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itShowsTheNumberOfPendingEditsInTheNavigationBadge(): void
    {
        $this->create(EaterySuggestedEdit::class, 2, ['wheretoeat_id' => $this->eatery->id]);
        $this->create(EaterySuggestedEdit::class, ['wheretoeat_id' => $this->eatery->id, 'accepted' => true]);
        $this->create(EaterySuggestedEdit::class, ['wheretoeat_id' => $this->eatery->id, 'rejected' => true]);

        $this->assertSame('2', EaterySuggestedEditResource::getNavigationBadge());
    }

    #[Test]
    public function itHasNoNavigationBadgeWhenEveryEditIsAcceptedOrRejected(): void
    {
        $this->create(EaterySuggestedEdit::class, ['wheretoeat_id' => $this->eatery->id, 'accepted' => true]);
        $this->create(EaterySuggestedEdit::class, ['wheretoeat_id' => $this->eatery->id, 'rejected' => true]);

        $this->assertNull(EaterySuggestedEditResource::getNavigationBadge());
    }

    #[Test]
    public function itShowsTheNavigationBadgeInRed(): void
    {
        $this->assertSame('danger', EaterySuggestedEditResource::getNavigationBadgeColor());
    }

    #[Test]
    public function itRegistersTheListPageOnly(): void
    {
        $this->assertSame(['index'], array_keys(EaterySuggestedEditResource::getPages()));
    }
}
