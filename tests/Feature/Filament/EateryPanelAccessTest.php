<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Models\EatingOut\Eatery;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryPanelAccessTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    #[DataProvider('eateryPages')]
    public function guestsAreSentToTheLoginPage(string $page): void
    {
        $this->get($this->url($page))->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    #[DataProvider('eateryPages')]
    public function signedInUsersCanOpenEveryEateryPage(string $page): void
    {
        $this->actingAs($this->create(User::class))
            ->get($this->url($page))
            ->assertOk();
    }

    public static function eateryPages(): array
    {
        return [
            'the eatery list' => ['index'],
            'the create page' => ['create'],
            'the edit page' => ['edit'],
        ];
    }

    protected function url(string $page): string
    {
        return in_array($page, ['index', 'create'], true)
            ? EateryResource::getUrl($page)
            : EateryResource::getUrl($page, ['record' => $this->eatery]);
    }
}
