<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\EatingOut\EateryCounties\EateryCountyResource;
use App\Models\EatingOut\EateryCounty;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryCountyPanelAccessTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = $this->create(EateryCounty::class);
    }

    #[Test]
    #[DataProvider('countyPages')]
    public function guestsAreSentToTheLoginPage(string $page): void
    {
        $this->get($this->url($page))->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    #[DataProvider('countyPages')]
    public function signedInUsersCanOpenEveryCountyPage(string $page): void
    {
        $this->actingAs($this->create(User::class))
            ->get($this->url($page))
            ->assertOk();
    }

    public static function countyPages(): array
    {
        return [
            'the county list' => ['index'],
            'the create page' => ['create'],
            'the view page' => ['view'],
            'the edit page' => ['edit'],
        ];
    }

    protected function url(string $page): string
    {
        return in_array($page, ['index', 'create'], true)
            ? EateryCountyResource::getUrl($page)
            : EateryCountyResource::getUrl($page, ['record' => $this->county]);
    }
}
