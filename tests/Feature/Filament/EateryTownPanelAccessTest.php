<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\EatingOut\EateryTowns\EateryTownResource;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryTownPanelAccessTest extends TestCase
{
    protected EateryTown $town;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->town = $this->create(EateryTown::class, ['county_id' => $this->create(EateryCounty::class)->id]);
    }

    #[Test]
    #[DataProvider('townPages')]
    public function guestsAreSentToTheLoginPage(string $page): void
    {
        $this->get($this->url($page))->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    #[DataProvider('townPages')]
    public function signedInUsersCanOpenEveryTownPage(string $page): void
    {
        $this->actingAs($this->create(User::class))
            ->get($this->url($page))
            ->assertOk();
    }

    public static function townPages(): array
    {
        return [
            'the town list' => ['index'],
            'the create page' => ['create'],
            'the edit page' => ['edit'],
        ];
    }

    protected function url(string $page): string
    {
        return in_array($page, ['index', 'create'], true)
            ? EateryTownResource::getUrl($page)
            : EateryTownResource::getUrl($page, ['record' => $this->town]);
    }
}
