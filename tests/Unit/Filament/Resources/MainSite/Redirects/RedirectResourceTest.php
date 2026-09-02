<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Redirects;

use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RedirectResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itTitlesARedirectByItsId(): void
    {
        $this->assertSame('id', RedirectResource::getRecordTitleAttribute());
    }

    #[Test]
    public function itIsNotGloballySearchable(): void
    {
        $this->assertFalse(RedirectResource::canGloballySearch());
    }

    #[Test]
    public function itRegistersTheListCreateAndEditPages(): void
    {
        $this->assertSame(['index', 'create', 'edit'], array_keys(RedirectResource::getPages()));
    }
}
