<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryCuisines\Pages;

use App\Filament\Resources\EatingOut\EateryCuisines\Pages\EditEateryCuisine;
use App\Models\EatingOut\EateryCuisine;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditEateryCuisineTest extends TestCase
{
    protected EateryCuisine $cuisine;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));

        $this->cuisine = $this->create(EateryCuisine::class, ['cuisine' => 'Peruvian']);
    }

    #[Test]
    public function itFillsTheForm(): void
    {
        $this->editPage()->assertSchemaStateSet(['cuisine' => 'Peruvian']);
    }

    #[Test]
    public function itSaves(): void
    {
        $this->editPage()
            ->set('data.cuisine', 'Ethiopian')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Ethiopian', $this->cuisine->refresh()->cuisine);
    }

    #[Test]
    public function itCannotBeDeleted(): void
    {
        $this->editPage()->assertActionDoesNotExist('delete');
    }

    protected function editPage(): Testable
    {
        return Livewire::test(EditEateryCuisine::class, ['record' => $this->cuisine->id]);
    }
}
