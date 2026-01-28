<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\AdsTxt;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    #[Test]
    public function itReturnsTheAdsTxtContentFromS3(): void
    {
        Storage::fake('system');
        Storage::disk('system')->put('ads.txt', 'mediavine ads content');

        $response = $this->get(route('ads.txt'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->assertSee('mediavine ads content');
    }

    #[Test]
    public function itReturns404WhenAdsTxtDoesNotExist(): void
    {
        Storage::fake('system');

        $response = $this->get(route('ads.txt'));

        $response->assertNotFound();
    }
}
