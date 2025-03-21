<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\GetPopupCtaAction;
use App\Models\Popup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class GetPopupCtaActionTest extends TestCase
{
    protected Popup $popup;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        TestTime::freeze();

        $this->popup = $this->create(Popup::class);
        $this->popup->addMedia(UploadedFile::fake()->image('popup.jpg'))->toMediaCollection('primary');

        TestTime::addDay();
    }

    #[Test]
    public function itReturnsNullIfThereIsNoLivePopup(): void
    {
        $this->popup->update(['live' => false]);

        $this->assertNull(app(GetPopupCtaAction::class)->handle());
    }

    #[Test]
    public function itReturnsThePopup(): void
    {
        $returnedPopup = app(GetPopupCtaAction::class)->handle();

        $this->assertTrue($this->popup->is($returnedPopup));
    }

    #[Test]
    public function itDoesntReturnThePopupIfItHasBeenSeen(): void
    {
        Request::instance()->cookies->add(["CS_SEEN_POPUP_{$this->popup->id}" => (now()->timestamp - 1)]);

        $this->assertNull(app(GetPopupCtaAction::class)->handle());
    }

    #[Test]
    public function itReturnsThePopupIfItHasBeenSeenButItWasMoreThatTheTimeLimitAgo(): void
    {
        $this->popup->update(['display_every' => 1]);

        Request::instance()->cookies->add(["CS_SEEN_POPUP_{$this->popup->id}" => now()->timestamp]);

        $this->assertNull(app(GetPopupCtaAction::class)->handle());

        TestTime::addDay()->addSecond();

        $returnedPopup = app(GetPopupCtaAction::class)->handle();

        $this->assertTrue($this->popup->is($returnedPopup));
    }
}
