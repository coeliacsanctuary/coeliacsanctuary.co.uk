<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\TripPlanner;

use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected function visitPage(?string $to = null): TestResponse
    {
        return $this->get(route('eating-out.trip-planner', $to !== null ? ['to' => $to] : []));
    }

    protected function encode(mixed $destination): string
    {
        return rtrim(strtr(base64_encode((string) json_encode($destination)), '+/', '-_'), '=');
    }

    public static function invalidDestinations(): array
    {
        return [
            'not base64' => ['%%%not-base64%%%'],
            'not json' => [rtrim(strtr(base64_encode('not json'), '+/', '-_'), '=')],
            'missing a label' => [rtrim(strtr(base64_encode('{"lat":54.48,"lng":-0.61}'), '+/', '-_'), '=')],
            'non numeric lat' => [rtrim(strtr(base64_encode('{"label":"Whitby","lat":"north","lng":-0.61}'), '+/', '-_'), '=')],
            'lat out of range' => [rtrim(strtr(base64_encode('{"label":"Whitby","lat":154.48,"lng":-0.61}'), '+/', '-_'), '=')],
        ];
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->visitPage()->assertOk();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitPage()->assertInertia(fn (Assert $page): Assert => $page->component('EatingOut/TripPlanner'));
    }

    #[Test]
    public function itHasNoDestinationWithoutOne(): void
    {
        $this->visitPage()->assertInertia(fn (Assert $page): Assert => $page->where('destination', null));
    }

    #[Test]
    public function itPassesTheDecodedDestinationToThePage(): void
    {
        $this->visitPage($this->encode(['label' => 'Whitby, UK', 'lat' => 54.4858, 'lng' => -0.6206]))
            ->assertInertia(fn (Assert $page): Assert => $page
                ->where('destination.label', 'Whitby, UK')
                ->where('destination.lat', 54.4858)
                ->where('destination.lng', -0.6206));
    }

    #[Test]
    public function itDecodesADestinationWithAccentedCharacters(): void
    {
        $this->visitPage($this->encode(['label' => 'Dún Laoghaire, Ireland', 'lat' => 53.29, 'lng' => -6.13]))
            ->assertInertia(fn (Assert $page): Assert => $page->where('destination.label', 'Dún Laoghaire, Ireland'));
    }

    #[Test]
    #[DataProvider('invalidDestinations')]
    public function itIgnoresAnInvalidDestination(string $to): void
    {
        $this->visitPage($to)
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page->where('destination', null));
    }
}
