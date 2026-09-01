<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\MainSite\Popups\PopupResource;
use App\Models\Popup;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PopupPanelAccessTest extends TestCase
{
    protected Popup $popup;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->popup = $this->create(Popup::class);
    }

    #[Test]
    #[DataProvider('popupPages')]
    public function guestsAreSentToTheLoginPage(string $page): void
    {
        $this->get($this->url($page))->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    #[DataProvider('popupPages')]
    public function signedInUsersCanOpenEveryPopupPage(string $page): void
    {
        $this->actingAs($this->create(User::class))
            ->get($this->url($page))
            ->assertOk();
    }

    public static function popupPages(): array
    {
        return [
            'the popup list' => ['index'],
            'the create page' => ['create'],
            'the edit page' => ['edit'],
        ];
    }

    protected function url(string $page): string
    {
        return in_array($page, ['index', 'create'], true)
            ? PopupResource::getUrl($page)
            : PopupResource::getUrl($page, ['record' => $this->popup]);
    }
}
