<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\ComputeEateryBackLinkAction;
use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EaterySearchTerm;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ComputeEateryBackLinkActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itReturnsTheCountyNameAndPreviousUrlWhenComingFromACountyPage(): void
    {
        $eatery = $this->create(Eatery::class);

        $previousRoute = route('eating-out.county', $eatery->county);

        $this->setPreviousRoute($previousRoute);

        [$name, $previous] = app(ComputeEateryBackLinkAction::class)->handle($eatery);

        $this->assertEquals("Back to {$eatery->county->county}", $name);
        $this->assertEquals($previousRoute, $previous);
    }

    #[Test]
    public function itReturnsTheMapPageAndUrlWhenComingFromTheMainMapPage(): void
    {
        $eatery = $this->create(Eatery::class);

        $previousRoute = route('eating-out.browse');

        $this->setPreviousRoute($previousRoute);

        [$name, $previous] = app(ComputeEateryBackLinkAction::class)->handle($eatery);

        $this->assertEquals('Back to map', $name);
        $this->assertEquals($previousRoute, $previous);
    }

    #[Test]
    public function itReturnsTheMapPageAndUrlWhenComingFromTheMainLatLngPage(): void
    {
        $eatery = $this->create(Eatery::class);

        $previousRoute = route('eating-out.browse.any', ['51.1/-1.2/17']);

        $this->setPreviousRoute($previousRoute);

        [$name, $previous] = app(ComputeEateryBackLinkAction::class)->handle($eatery);

        $this->assertEquals('Back to map', $name);
        $this->assertEquals($previousRoute, $previous);
    }

    #[Test]
    public function itReturnsTheSearchResultsNameAndUrlWhenComingFromTheEaterySearchResultsPage(): void
    {
        $eatery = $this->create(Eatery::class);

        $previousRoute = route('eating-out.search.show', $this->create(EaterySearchTerm::class));

        $this->setPreviousRoute($previousRoute);

        [$name, $previous] = app(ComputeEateryBackLinkAction::class)->handle($eatery);

        $this->assertEquals('Back to search results', $name);
        $this->assertEquals($previousRoute, $previous);
    }

    #[Test]
    public function itReturnsTheSearchResultsNameAndUrlWhenComingFromTheGlobalSiteSearchResultsPage(): void
    {
        $eatery = $this->create(Eatery::class);

        $previousRoute = route('search.index');

        $this->setPreviousRoute($previousRoute);

        [$name, $previous] = app(ComputeEateryBackLinkAction::class)->handle($eatery);

        $this->assertEquals('Back to search results', $name);
        $this->assertEquals($previousRoute, $previous);
    }

    #[Test]
    public function itReturnsTheEatingOutIndexPageWhenComingFromTheIndexPage(): void
    {
        $eatery = $this->create(Eatery::class);

        $previousRoute = route('eating-out.index');

        $this->setPreviousRoute($previousRoute);

        [$name, $previous] = app(ComputeEateryBackLinkAction::class)->handle($eatery);

        $this->assertEquals('Back to eating out guide', $name);
        $this->assertEquals($previousRoute, $previous);
    }

    #[Test]
    public function itReturnsTheEateryTownPageWhenComingFromTheTownPage(): void
    {
        $eatery = $this->create(Eatery::class);

        $previousRoute = route('eating-out.town', ['county' => $eatery->county, 'town' => $eatery->town]);

        $this->setPreviousRoute($previousRoute);

        [$name, $previous] = app(ComputeEateryBackLinkAction::class)->handle($eatery);

        $this->assertEquals("Back to {$eatery->town->town}", $name);
        $this->assertEquals($previousRoute, $previous);
    }

    #[Test]
    public function itFallsBackToTheTownPageWhenComingFromElsewhereOnTheSite(): void
    {
        $eatery = $this->create(Eatery::class);

        $route = route('eating-out.town', ['county' => $eatery->county, 'town' => $eatery->town]);

        $this->setPreviousRoute(route('blog.show', $this->create(Blog::class)));

        [$name, $previous] = app(ComputeEateryBackLinkAction::class)->handle($eatery);

        $this->assertEquals("Back to {$eatery->town->town}", $name);
        $this->assertEquals($route, $previous);
    }

    #[Test]
    public function itFallsBackToTheTownPageWhenComingFromExternal(): void
    {
        $eatery = $this->create(Eatery::class);

        $route = route('eating-out.town', ['county' => $eatery->county, 'town' => $eatery->town]);

        $this->setPreviousRoute('https://google.com');

        [$name, $previous] = app(ComputeEateryBackLinkAction::class)->handle($eatery);

        $this->assertEquals("Back to {$eatery->town->town}", $name);
        $this->assertEquals($route, $previous);
    }

    protected function setPreviousRoute(string $previousRoute): void
    {
        URL::shouldReceive('previous')
            ->andReturn($previousRoute)
            ->once()
            ->getMock()
            ->allows('route');
    }
}
