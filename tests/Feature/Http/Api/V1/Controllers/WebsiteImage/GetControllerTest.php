<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\WebsiteImage;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.website-img.get'))->assertForbidden();
    }

    #[Test]
    public function itReturnsADataProperty(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => []]);
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageForRouteAction(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class);

        $this->makeRequest()->assertOk();
    }

    protected function makeRequest(string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.website-img.get'),
            ['x-coeliac-source' => $source],
        );
    }
}
