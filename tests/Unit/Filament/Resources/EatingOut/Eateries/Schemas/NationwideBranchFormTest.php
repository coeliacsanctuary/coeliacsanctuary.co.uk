<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Schemas;

use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\BranchesRelationManager;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NationwideBranchFormTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itSavesABranchWithNoName(): void
    {
        $this->createBranch(['name' => null])->assertHasNoFormErrors();

        $this->assertSame('', NationwideBranch::query()->sole()->name);
    }

    #[Test]
    #[DataProvider('invalidBranches')]
    public function itValidatesTheBranch(array $overrides, array $errors): void
    {
        $this->createBranch($overrides)->assertHasFormErrors($errors);

        $this->assertDatabaseEmpty(NationwideBranch::class);
    }

    public static function invalidBranches(): array
    {
        return [
            '`name` is max 200 characters' => [['name' => Str::repeat('a', 201)], ['name' => 'max']],
            '`county_id` is required' => [['county_id' => null], ['county_id' => 'required']],
            '`town_id` is required' => [['town_id' => null], ['town_id' => 'required']],
            '`address` is required' => [['address' => null], ['address' => 'required']],
        ];
    }

    #[Test]
    public function itHidesTheSlugWhenCreating(): void
    {
        $this->branchesPanel()
            ->mountAction(TestAction::make('create')->table())
            ->assertFormFieldHidden('slug');
    }

    #[Test]
    public function itUnlocksTheSlug(): void
    {
        $this->branchesPanel()
            ->mountAction(TestAction::make('edit')->table($this->branch()))
            ->assertFormFieldExists('slug', checkFieldUsing: fn (TextInput $field): bool => $field->isReadOnly())
            ->set('mountedActions.0.data.unlock_slug', true)
            ->assertFormFieldExists('slug', checkFieldUsing: fn (TextInput $field): bool => ! $field->isReadOnly());
    }

    #[Test]
    public function itUpdatesTheSlug(): void
    {
        $branch = $this->branch(['slug' => 'old']);

        $this->editSlug($branch, 'new')->assertHasNoFormErrors();

        $this->assertSame('new', $branch->refresh()->slug);
    }

    #[Test]
    public function itRejectsASlugUsedByAnotherBranchOfTheChain(): void
    {
        $this->branch(['slug' => 'taken']);

        $this->editSlug($this->branch(['slug' => 'mine']), 'taken')->assertHasFormErrors(['slug' => 'unique']);
    }

    #[Test]
    public function itAllowsASlugUsedByAnotherChain(): void
    {
        $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->create(Eatery::class)->id, 'slug' => 'taken']);

        $this->editSlug($this->branch(['slug' => 'mine']), 'taken')->assertHasNoFormErrors();
    }

    protected function editSlug(NationwideBranch $branch, string $slug): Testable
    {
        return $this->branchesPanel()->callAction(TestAction::make('edit')->table($branch), ['unlock_slug' => true, 'slug' => $slug]);
    }

    protected function createBranch(array $overrides = []): Testable
    {
        return $this->branchesPanel()->callAction(TestAction::make('create')->table(), [
            'name' => 'My Branch',
            'live' => true,
            'country_id' => 1,
            'county_id' => 1,
            'town_id' => 1,
            'address' => '1 High Street',
            'lat' => 1,
            'lng' => 2,
            ...$overrides,
        ]);
    }

    protected function branch(array $attributes = []): NationwideBranch
    {
        return $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id, ...$attributes]);
    }

    protected function branchesPanel(): Testable
    {
        return Livewire::test(BranchesRelationManager::class, [
            'ownerRecord' => $this->eatery,
            'pageClass' => EditEatery::class,
        ]);
    }
}
