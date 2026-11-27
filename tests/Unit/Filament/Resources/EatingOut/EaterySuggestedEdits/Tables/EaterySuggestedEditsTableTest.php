<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EaterySuggestedEdits\Tables;

use App\Filament\Resources\EatingOut\EaterySuggestedEdits\Pages\ListEaterySuggestedEdits;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryOpeningTimes;
use App\Models\EatingOut\EaterySuggestedEdit;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EaterySuggestedEditsTableTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->create(Eatery::class, ['name' => 'Test Eatery', 'town_id' => 1, 'county_id' => 1, 'country_id' => 1]);
    }

    #[Test]
    public function itShowsTheNewestEditsFirst(): void
    {
        $oldPending = $this->edit(['created_at' => now()->subDays(3)]);
        $newAccepted = $this->edit(['accepted' => true, 'created_at' => now()]);
        $middleRejected = $this->edit(['rejected' => true, 'created_at' => now()->subDay()]);

        Livewire::test(ListEaterySuggestedEdits::class)
            ->assertCanSeeTableRecords([$newAccepted, $middleRejected, $oldPending], inOrder: true);
    }

    #[Test]
    public function itShowsTheEateryFullName(): void
    {
        $edit = $this->edit();

        Livewire::test(ListEaterySuggestedEdits::class)
            ->assertTableColumnStateSet('eatery_name', $this->eatery->load(['area', 'town', 'county', 'country'])->full_name, $edit);
    }

    #[Test]
    public function itShowsEditsForEateriesThatAreNotLive(): void
    {
        $notLive = $this->build(Eatery::class)->notLive()->create();

        $edit = $this->edit(['wheretoeat_id' => $notLive->id]);

        Livewire::test(ListEaterySuggestedEdits::class)->assertCanSeeTableRecords([$edit]);
    }

    #[Test]
    #[DataProvider('fields')]
    public function itRendersACardForEachField(string $field, string $value): void
    {
        $this->create(EateryOpeningTimes::class, ['wheretoeat_id' => $this->eatery->id]);
        $this->eatery->features()->attach(EateryFeature::query()->first());

        $edit = $this->edit(['field' => $field, 'value' => $value]);

        Livewire::test(ListEaterySuggestedEdits::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$edit]);
    }

    /** @return array<string, array{string, string}> */
    public static function fields(): array
    {
        return [
            'address' => ['address', "1 Test Street\nCrewe"],
            'cuisine' => ['cuisine', '2'],
            'features' => ['features', (string) json_encode([['key' => 1, 'label' => 'Wifi', 'selected' => true]])],
            'gf_menu_link' => ['gf_menu_link', 'https://example.com/menu'],
            'info' => ['info', 'They have a new gluten free menu'],
            'opening_times' => ['opening_times', (string) json_encode([
                ['key' => 'monday', 'label' => 'Monday', 'closed' => false, 'start' => [9, 0], 'end' => [17, 30]],
                ['key' => 'tuesday', 'label' => 'Tuesday', 'closed' => true, 'start' => [null, null], 'end' => [null, null]],
            ])],
            'phone' => ['phone', '01234 567890'],
            'venue_type' => ['venue_type', '2'],
            'website' => ['website', 'https://example.com'],
        ];
    }

    /** @param array<string, mixed> $attributes */
    protected function edit(array $attributes = []): EaterySuggestedEdit
    {
        return $this->create(EaterySuggestedEdit::class, ['wheretoeat_id' => $this->eatery->id, ...$attributes]);
    }
}
