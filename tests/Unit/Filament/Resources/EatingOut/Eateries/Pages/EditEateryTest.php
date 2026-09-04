<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Pages;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\Eateries\Pages\CreateEatery;
use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Models\EatingOut\Eatery;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditEateryTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->create(Eatery::class, ['name' => 'The Old Cafe', 'slug' => 'the-old-cafe']);
    }

    #[Test]
    public function itUpdatesTheEatery(): void
    {
        $this->editEatery(['name' => 'The New Cafe', 'info' => 'Under new management.'])->assertNotified();

        $this->eatery->refresh();

        $this->assertSame('The New Cafe', $this->eatery->name);
        $this->assertSame('Under new management.', $this->eatery->info);
    }

    #[Test]
    public function itUpdatesTheSnippet(): void
    {
        $this->editEatery(['snippet' => 'A cracking little cafe.']);

        $this->assertSame('A cracking little cafe.', $this->eatery->refresh()->snippet);
    }

    #[Test]
    public function itMarksAnEateryAsClosedDown(): void
    {
        $this->editEatery(['closed_down' => true]);

        $this->assertTrue($this->eatery->refresh()->closed_down);
    }

    #[Test]
    public function itShowsTheSlugOnTheEditPage(): void
    {
        Livewire::test(EditEatery::class, ['record' => $this->eatery->id])
            ->assertSchemaComponentExists('slug');
    }

    #[Test]
    public function itHidesTheSlugOnTheCreatePage(): void
    {
        Livewire::test(CreateEatery::class)->assertSchemaComponentHidden('slug');
    }

    #[Test]
    public function itKeepsTheSlugLockedUntilItIsUnlocked(): void
    {
        Livewire::test(EditEatery::class, ['record' => $this->eatery->id])
            ->assertSchemaComponentStateSet('unlock_slug', false)
            ->callAction(TestAction::make('unlock_slug_action')->schemaComponent('slug'))
            ->assertSchemaComponentStateSet('unlock_slug', true);
    }

    #[Test]
    public function itUpdatesTheSlug(): void
    {
        $this->editEatery(['slug' => 'the-brand-new-cafe']);

        $this->assertSame('the-brand-new-cafe', $this->eatery->refresh()->slug);
    }

    #[Test]
    public function itRejectsASlugAlreadyUsedInTheSameTown(): void
    {
        $this->create(Eatery::class, ['slug' => 'taken', 'town_id' => $this->eatery->town_id]);

        Livewire::test(EditEatery::class, ['record' => $this->eatery->id])
            ->fillForm(['slug' => 'taken'])
            ->call('save')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    #[Test]
    public function itSendsTheUserBackToTheListAfterSaving(): void
    {
        $this->editEatery()->assertRedirect(EateryResource::getUrl('index'));
    }

    #[Test]
    public function itKeepsTheUserOnTheEditPageWhenSavingAndContinuing(): void
    {
        Livewire::test(EditEatery::class, ['record' => $this->eatery->id])
            ->call('saveAndContinueEditing')
            ->assertHasNoFormErrors()
            ->assertRedirect(EateryResource::getUrl('edit', ['record' => $this->eatery]));
    }

    protected function editEatery(array $overrides = []): Testable
    {
        $page = Livewire::test(EditEatery::class, ['record' => $this->eatery->id]);

        if ($overrides !== []) {
            $page->fillForm($overrides);
        }

        return $page->call('save')->assertHasNoFormErrors();
    }
}
