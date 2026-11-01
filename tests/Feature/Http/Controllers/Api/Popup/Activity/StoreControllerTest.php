<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Popup\Activity;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Popup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected Popup $popup;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        TestTime::freeze();

        $this->popup = $this->create(Popup::class);
        $this->popup->addMedia(UploadedFile::fake()->image('popup.jpg'))->toMediaCollection('primary');
    }

    #[Test]
    public function itReturnsNotFoundIfThePopupDoesntExist(): void
    {
        $this->post(route('popup.activity.store', ['popup' => 123]))->assertNotFound();
    }

    #[Test]
    public function itCreatesACookieThatThePopupHasBeenSeen(): void
    {
        $this->post(route('popup.activity.store', $this->popup))
            ->assertCookie("CS_SEEN_POPUP_{$this->popup->id}", (string)now()->timestamp);
    }
}
