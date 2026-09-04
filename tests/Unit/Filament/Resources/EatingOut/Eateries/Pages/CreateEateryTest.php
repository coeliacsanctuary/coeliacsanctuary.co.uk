<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Pages;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\Eateries\Pages\CreateEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateEateryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        // the location lookup resolves through the `hasPlaces` scopes, so the town needs an eatery already
        $this->create(Eatery::class);
    }

    #[Test]
    public function itCreatesTheEatery(): void
    {
        $this->assertDatabaseCount(Eatery::class, 1);

        $this->createEatery()->assertNotified();

        $this->assertDatabaseCount(Eatery::class, 2);

        $eatery = $this->createdEatery();

        $this->assertSame('The Gluten Free Cafe', $eatery->name);
        $this->assertSame('A lovely little cafe.', $eatery->info);
        $this->assertTrue($eatery->live);
    }

    #[Test]
    public function itGeneratesTheSlug(): void
    {
        $this->createEatery();

        $this->assertSame('the-gluten-free-cafe', $this->createdEatery()->slug);
    }

    #[Test]
    public function itStoresTheSnippet(): void
    {
        $this->createEatery(['snippet' => 'A lovely little cafe in Crewe.']);

        $this->assertSame('A lovely little cafe in Crewe.', $this->createdEatery()->snippet);
    }

    #[Test]
    public function itResolvesTheLocationFromTheLookup(): void
    {
        $this->createEatery();

        $eatery = $this->createdEatery();

        $this->assertSame(1, $eatery->country_id);
        $this->assertSame(1, $eatery->county_id);
        $this->assertSame(1, $eatery->town_id);
    }

    #[Test]
    public function itStoresTheContactDetails(): void
    {
        $this->createEatery([
            'phone' => '01270 123456',
            'website' => 'https://example.com',
            'gf_menu_link' => 'https://example.com/gf',
            'facebook_url' => 'https://facebook.com/example',
            'instagram_url' => 'https://instagram.com/example',
        ]);

        $eatery = $this->createdEatery();

        $this->assertSame('01270 123456', $eatery->phone);
        $this->assertSame('https://example.com', $eatery->website);
        $this->assertSame('https://example.com/gf', $eatery->gf_menu_link);
        $this->assertSame('https://facebook.com/example', $eatery->facebook_url);
        $this->assertSame('https://instagram.com/example', $eatery->instagram_url);
    }

    #[Test]
    public function itAttachesTheSelectedFeatures(): void
    {
        $features = EateryFeature::query()->take(2)->pluck('id');

        $this->createEatery(['features' => $features->all()]);

        $this->assertSame(2, $this->createdEatery()->features()->count());
    }

    #[Test]
    public function itSendsTheUserBackToTheEateryListAfterCreating(): void
    {
        $this->createEatery()->assertRedirect(EateryResource::getUrl('index'));
    }

    protected function createEatery(array $overrides = []): Testable
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
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    protected function createdEatery(): Eatery
    {
        return Eatery::query()->withoutGlobalScopes()->latest('id')->firstOrFail();
    }
}
