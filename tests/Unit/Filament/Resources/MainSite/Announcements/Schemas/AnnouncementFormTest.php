<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Announcements\Schemas;

use App\Filament\Resources\MainSite\Announcements\Pages\CreateAnnouncement;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnnouncementFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itAcceptsACompleteAnnouncement(): void
    {
        $this->fillCreateForm()->call('create')->assertHasNoFormErrors();
    }

    #[Test]
    #[DataProvider('invalidAnnouncements')]
    public function itValidatesTheAnnouncement(array $overrides, array $errors): void
    {
        $this->fillCreateForm($overrides)
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    }

    public static function invalidAnnouncements(): array
    {
        return [
            '`title` is required' => [['title' => null], ['title' => 'required']],
            '`text` is required' => [['text' => null], ['text' => 'required']],
            '`expires_at` is required' => [['expires_at' => null], ['expires_at' => 'required']],
            '`expires_at` cannot be in the past' => [
                ['expires_at' => Carbon::now()->subWeek()],
                ['expires_at' => 'after'],
            ],
            '`expires_at` cannot be today' => [
                ['expires_at' => Carbon::today()],
                ['expires_at' => 'after'],
            ],
        ];
    }

    #[Test]
    public function itDefaultsTheExpiryDateToAWeekAway(): void
    {
        Livewire::test(CreateAnnouncement::class)
            ->assertSchemaStateSet(['expires_at' => Carbon::now()->addWeek()]);
    }

    protected function fillCreateForm(array $overrides = []): Testable
    {
        return Livewire::test(CreateAnnouncement::class)->fillForm([
            'title' => 'The shop is closed',
            'text' => 'Orders are paused until Monday',
            'live' => true,
            'expires_at' => Carbon::now()->addWeek(),
            ...$overrides,
        ]);
    }
}
