<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Schemas;

use App\Enums\EatingOut\EateryType;
use App\Filament\Resources\EatingOut\Eateries\Pages\CreateEatery;
use App\Models\EatingOut\Eatery;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Str;
use Tests\TestCase;

class EateryFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->create(Eatery::class);
    }

    #[Test]
    public function itAcceptsACompleteEatery(): void
    {
        $this->fillCreateForm()->call('create')->assertHasNoFormErrors();
    }

    #[Test]
    #[DataProvider('invalidEateries')]
    public function itValidatesTheEatery(array $overrides, array $errors): void
    {
        $this->fillCreateForm($overrides)
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    }

    public static function invalidEateries(): array
    {
        return [
            '`name` is required' => [['name' => null], ['name' => 'required']],
            '`name` is max 200 characters' => [['name' => Str::repeat('a', 201)], ['name' => 'max']],
            '`phone` is max 50 characters' => [['phone' => Str::repeat('1', 51)], ['phone' => 'max']],
            '`website` must be a url' => [['website' => 'not a url'], ['website' => 'url']],
            '`gf_menu_link` must be a url' => [['gf_menu_link' => 'not a url'], ['gf_menu_link' => 'url']],
            '`facebook_url` must be a url' => [['facebook_url' => 'not a url'], ['facebook_url' => 'url']],
            '`instagram_url` must be a url' => [['instagram_url' => 'not a url'], ['instagram_url' => 'url']],
            '`snippet` is max 150 characters' => [['snippet' => Str::repeat('a', 151)], ['snippet' => 'max']],
            '`type_id` is required' => [['type_id' => null], ['type_id' => 'required']],
            '`venue_type_id` is required' => [['venue_type_id' => null], ['venue_type_id' => 'required']],
            '`cuisine_id` is required' => [['cuisine_id' => null], ['cuisine_id' => 'required']],
            '`info` is required' => [['info' => null], ['info' => 'required']],
            '`address` is required' => [['address' => null], ['address' => 'required']],
        ];
    }

    #[Test]
    public function itShowsTheEateryFieldsForAnEatery(): void
    {
        $this->formForType(EateryType::EATERY)
            ->assertSchemaComponentVisible('venue_type_id')
            ->assertSchemaComponentVisible('cuisine_id')
            ->assertSchemaComponentVisible('info')
            ->assertSchemaComponentHidden('restaurants');
    }

    #[Test]
    public function itHidesTheCuisineAndInfoForAnAttraction(): void
    {
        $this->formForType(EateryType::ATTRACTION)
            ->assertSchemaComponentHidden('cuisine_id')
            ->assertSchemaComponentHidden('info')
            ->assertSchemaComponentVisible('restaurants');
    }

    #[Test]
    public function itHidesTheCuisineButKeepsTheInfoForAHotel(): void
    {
        $this->formForType(EateryType::HOTEL)
            ->assertSchemaComponentHidden('cuisine_id')
            ->assertSchemaComponentVisible('info')
            ->assertSchemaComponentHidden('restaurants');
    }

    #[Test]
    public function itDefaultsTheVenueTypeForAHotel(): void
    {
        $this->formForType(EateryType::HOTEL)->assertSchemaComponentStateSet('venue_type_id', 26);
    }

    #[Test]
    public function itDefaultsTheCuisineForAnAttraction(): void
    {
        $this->formForType(EateryType::ATTRACTION)->assertSchemaComponentStateSet('cuisine_id', 29);
    }

    #[Test]
    public function itDefaultsTheCuisineForAHotel(): void
    {
        $this->formForType(EateryType::HOTEL)->assertSchemaComponentStateSet('cuisine_id', 29);
    }

    #[Test]
    public function itClearsTheInfoWhenSwitchingToAnAttraction(): void
    {
        $this->formForType(EateryType::ATTRACTION)->assertSchemaComponentStateSet('info', null);
    }

    protected function formForType(EateryType $type): Testable
    {
        return $this->fillCreateForm()->set('data.type_id', $type->value);
    }

    protected function fillCreateForm(array $overrides = []): Testable
    {
        return Livewire::test(CreateEatery::class)
            ->fillForm([
                'name' => 'The Gluten Free Cafe',
                'live' => true,
                'location' => '1|1|1|',
                'address' => "1 High Street\nCrewe\nCW1 2AB",
                'lat' => 53.0977,
                'lng' => -2.4426,
                'type_id' => 1,
                'venue_type_id' => 1,
                'cuisine_id' => 1,
                'info' => 'A lovely little cafe.',
                ...$overrides,
            ]);
    }
}
