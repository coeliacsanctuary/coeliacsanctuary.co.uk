<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\ShopCta;

use App\Models\Popup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $popup = $this->create(Popup::class);
        $popup->addMedia(UploadedFile::fake()->image('popup.jpg'))->toMediaCollection('primary');
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.shop-cta.get'))->assertForbidden();
    }

    #[Test]
    public function itReturnsADataProperty(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => []]);
    }

    #[Test]
    public function itReturnsEachItemInTheExpectedFormat(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'text',
                    'link',
                    'image',
                ],
            ]);
    }

    #[Test]
    public function itReturnsTheExpectedValues(): void
    {
        $popup = Popup::query()->first();

        $this->makeRequest()
            ->assertOk()
            ->assertJsonPath('data.text', $popup->text)
            ->assertJsonPath('data.link', config('app.url') . $popup->link)
            ->assertJsonPath('data.image', $popup->getMedia('primary')->random()?->getUrl());
    }

    protected function makeRequest(string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.shop-cta.get'),
            ['x-coeliac-source' => $source],
        );
    }
}
