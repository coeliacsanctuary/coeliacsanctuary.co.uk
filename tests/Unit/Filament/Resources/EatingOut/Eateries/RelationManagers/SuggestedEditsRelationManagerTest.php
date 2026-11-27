<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\RelationManagers;

use App\Filament\Resources\EatingOut\Eateries\Pages\ViewEatery;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\SuggestedEditsRelationManager;
use App\Filament\Resources\EatingOut\EaterySuggestedEdits\Tables\EaterySuggestedEditsTable;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryOpeningTimes;
use App\Models\EatingOut\EaterySuggestedEdit;
use App\Models\EatingOut\EateryVenueType;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Filament\Actions\Testing\TestAction;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SuggestedEditsRelationManagerTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->build(Eatery::class)->notLive()->create(['website' => 'https://old.com']);
    }

    #[Test]
    public function itShowsTheSuggestedEditsForTheEatery(): void
    {
        $mine = $this->edit();
        $theirs = $this->create(EaterySuggestedEdit::class, ['wheretoeat_id' => $this->create(Eatery::class)->id]);

        $this->panel()->assertCanSeeTableRecords([$mine])->assertCanNotSeeTableRecords([$theirs]);
    }

    #[Test]
    public function itAcceptsAnEditWithoutChangingTheEatery(): void
    {
        $edit = $this->edit();

        $this->panel()->callAction(TestAction::make('accept')->table($edit));

        $this->assertTrue($edit->refresh()->accepted);
        $this->assertSame('https://old.com', $this->eatery()->website);
    }

    #[Test]
    public function itAcceptsAndImplementsAnEdit(): void
    {
        $edit = $this->edit();

        $this->panel()->callAction(TestAction::make('acceptAndImplement')->table($edit));

        $this->assertTrue($edit->refresh()->accepted);
        $this->assertSame('https://new.com', $this->eatery()->website);
    }

    #[Test]
    public function itRejectsAnEdit(): void
    {
        $edit = $this->edit();

        $this->panel()->callAction(TestAction::make('reject')->table($edit));

        $this->assertTrue($edit->refresh()->rejected);
        $this->assertFalse($edit->accepted);
    }

    #[Test]
    public function itHidesTheActionsOnceAnEditIsDealtWith(): void
    {
        $edit = $this->edit(['accepted' => true]);

        $panel = $this->panel();

        foreach (['accept', 'acceptAndImplement', 'reject'] as $action) {
            $panel->assertActionHidden(TestAction::make($action)->table($edit));
        }
    }

    #[Test]
    public function itCannotImplementAnInfoEdit(): void
    {
        $edit = $this->edit(['field' => 'info', 'value' => 'New info']);

        $this->panel()
            ->assertActionVisible(TestAction::make('accept')->table($edit))
            ->assertActionHidden(TestAction::make('acceptAndImplement')->table($edit));
    }

    #[Test]
    public function itHidesTheEateryName(): void
    {
        $this->edit();

        $this->panel()->assertTableColumnHidden('eatery_name');
    }

    #[Test]
    public function itComparesTheVenueType(): void
    {
        $edit = $this->edit(['field' => 'venue_type', 'value' => '2']);

        $this->assertSame([EateryVenueType::query()->findOrFail(1)->venue_type], EaterySuggestedEditsTable::currentValue($this->loaded($edit)));
        $this->assertSame([EateryVenueType::query()->findOrFail(2)->venue_type], EaterySuggestedEditsTable::suggestedValue($edit));
    }

    #[Test]
    public function itComparesTheFeatures(): void
    {
        $features = EateryFeature::query()->take(2)->get();
        $this->eatery->features()->attach($features[0]);

        $edit = $this->edit(['field' => 'features', 'value' => json_encode([
            ['key' => 1, 'label' => $features[0]->feature, 'selected' => false],
            ['key' => 2, 'label' => $features[1]->feature, 'selected' => true],
        ])]);

        $allFeatures = EateryFeature::query()->orderBy('feature')->pluck('feature')->all();

        $this->assertSame($allFeatures, EaterySuggestedEditsTable::currentValue($this->loaded($edit)));
        $this->assertSame($allFeatures, EaterySuggestedEditsTable::suggestedValue($edit));

        $this->assertTrue(EaterySuggestedEditsTable::featureIsSelected($this->loaded($edit), $features[0]->feature, suggested: false));
        $this->assertFalse(EaterySuggestedEditsTable::featureIsSelected($this->loaded($edit), $features[1]->feature, suggested: false));
        $this->assertFalse(EaterySuggestedEditsTable::featureIsSelected($edit, $features[0]->feature, suggested: true));
        $this->assertTrue(EaterySuggestedEditsTable::featureIsSelected($edit, $features[1]->feature, suggested: true));
    }

    #[Test]
    public function itDoesNotMarkFeaturesOnOtherFields(): void
    {
        $edit = $this->edit();

        $this->assertNull(EaterySuggestedEditsTable::featureIsSelected($this->loaded($edit), 'https://new.com', suggested: true));
    }

    #[Test]
    public function itComparesTheOpeningTimes(): void
    {
        $this->create(EateryOpeningTimes::class, ['wheretoeat_id' => $this->eatery->id, 'monday_start' => null, 'monday_end' => null]);

        $edit = $this->edit(['field' => 'opening_times', 'value' => json_encode([
            ['key' => 'monday', 'label' => 'Monday', 'closed' => false, 'start' => [9, 0], 'end' => [17, 30]],
            ['key' => 'tuesday', 'label' => 'Tuesday', 'closed' => true, 'start' => [null, null], 'end' => [null, null]],
        ])]);

        $this->assertContains('Monday: Closed', EaterySuggestedEditsTable::currentValue($this->loaded($edit)));
        $this->assertSame(['Monday: 09:00 - 17:30', 'Tuesday: Closed'], EaterySuggestedEditsTable::suggestedValue($edit));
    }

    protected function edit(array $attributes = []): EaterySuggestedEdit
    {
        return $this->create(EaterySuggestedEdit::class, [
            'wheretoeat_id' => $this->eatery->id,
            'field' => 'website',
            'value' => 'https://new.com',
            ...$attributes,
        ]);
    }

    protected function loaded(EaterySuggestedEdit $edit): EaterySuggestedEdit
    {
        return $edit->setRelation('eatery', $this->eatery());
    }

    protected function eatery(): Eatery
    {
        return Eatery::query()->withoutGlobalScopes()->findOrFail($this->eatery->id);
    }

    protected function panel(): Testable
    {
        return Livewire::test(SuggestedEditsRelationManager::class, [
            'ownerRecord' => $this->eatery,
            'pageClass' => ViewEatery::class,
        ]);
    }
}
