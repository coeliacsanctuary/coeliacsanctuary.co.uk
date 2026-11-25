<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryCuisines\Pages;

use App\Filament\Resources\EatingOut\EateryCuisines\Pages\ListEateryCuisines;
use App\Models\EatingOut\EateryCuisine;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListEateryCuisinesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itCreatesTheCuisineInAModal(): void
    {
        Livewire::test(ListEateryCuisines::class)
            ->assertActionVisible('create')
            ->mountAction('create')
            ->assertActionMounted('create');

        $this->createCuisine('Ethiopian')->assertHasNoFormErrors()->assertNotified()->assertNoRedirect();

        $this->assertSame('Ethiopian', $this->createdCuisine()->cuisine);
    }

    #[Test]
    public function itErrorsWithoutAName(): void
    {
        $this->createCuisine(null)->assertHasFormErrors(['cuisine' => 'required']);
    }

    #[Test]
    public function itAllowsANameThatFillsTheColumn(): void
    {
        $this->createCuisine(str_repeat('a', 50))->assertHasNoFormErrors();

        $this->assertSame(str_repeat('a', 50), $this->createdCuisine()->cuisine);
    }

    #[Test]
    public function itErrorsIfTheNameIsTooLong(): void
    {
        $this->createCuisine(str_repeat('a', 51))->assertHasFormErrors(['cuisine' => 'max']);
    }

    protected function createCuisine(?string $name): Testable
    {
        return Livewire::test(ListEateryCuisines::class)->callAction('create', ['cuisine' => $name]);
    }

    protected function createdCuisine(): EateryCuisine
    {
        return EateryCuisine::query()->latest('id')->firstOrFail();
    }
}
