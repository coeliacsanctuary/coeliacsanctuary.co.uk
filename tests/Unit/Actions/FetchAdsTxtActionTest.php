<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\FetchAdsTxtAction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FetchAdsTxtActionTest extends TestCase
{
    #[Test]
    public function itFetchesTheAdsTxtFromMediavineAndStoresToS3(): void
    {
        Http::fake([
            'adstxt.mediavine.com/*' => Http::response('mediavine ads content', 200),
        ]);

        Storage::fake('system');

        $result = $this->callAction(FetchAdsTxtAction::class);

        $this->assertTrue($result);

        Storage::disk('system')->assertExists('ads.txt');
        $this->assertEquals('mediavine ads content', Storage::disk('system')->get('ads.txt'));
    }

    #[Test]
    public function itReturnsFalseWhenTheHttpRequestFails(): void
    {
        Http::fake([
            'adstxt.mediavine.com/*' => Http::response('Not Found', 404),
        ]);

        Storage::fake('system');

        $result = $this->callAction(FetchAdsTxtAction::class);

        $this->assertFalse($result);

        Storage::disk('system')->assertMissing('ads.txt');
    }
}
