<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\EatingOut\EateryAreas\EateryAreaResource;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryAreaPanelAccessTest extends TestCase
{
    protected EateryArea $area;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london']);
        $borough = $this->create(EateryTown::class, ['county_id' => $london->id]);

        $this->area = $this->create(EateryArea::class, ['town_id' => $borough->id]);
    }

    #[Test]
    #[DataProvider('areaPages')]
    public function guestsAreSentToTheLoginPage(string $page): void
    {
        $this->get($this->url($page))->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    #[DataProvider('areaPages')]
    public function signedInUsersCanOpenEveryAreaPage(string $page): void
    {
        $this->actingAs($this->create(User::class))
            ->get($this->url($page))
            ->assertOk();
    }

    public static function areaPages(): array
    {
        return [
            'the area list' => ['index'],
            'the create page' => ['create'],
            'the edit page' => ['edit'],
        ];
    }

    protected function url(string $page): string
    {
        return in_array($page, ['index', 'create'], true)
            ? EateryAreaResource::getUrl($page)
            : EateryAreaResource::getUrl($page, ['record' => $this->area]);
    }
}
