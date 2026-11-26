<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryMagicRoutes;

use App\Filament\Resources\EatingOut\EateryMagicRoutes\EateryMagicRouteResource;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryMagicRouteResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itTitlesAMagicRouteByItsId(): void
    {
        $this->assertSame('id', EateryMagicRouteResource::getRecordTitleAttribute());
    }

    #[Test]
    public function itHasALinkIconInTheNavigation(): void
    {
        $this->assertSame(Heroicon::OutlinedLink, EateryMagicRouteResource::getNavigationIcon());
    }

    #[Test]
    public function itRegistersTheListCreateAndViewPagesOnly(): void
    {
        $this->assertSame(['index', 'create', 'view'], array_keys(EateryMagicRouteResource::getPages()));
    }
}
