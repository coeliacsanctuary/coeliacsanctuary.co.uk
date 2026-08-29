<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Response;

use PHPUnit\Framework\Attributes\Test;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopHoliday;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

class InertiaTest extends TestCase
{
    protected Inertia $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new Inertia();
    }

    #[Test]
    public function itReturnsTheDefaultPageTitleIfOneIsntSpecified(): void
    {
        $this->assertEquals(config('metas.title'), $this->factory->getShared('meta.title'));
    }

    #[Test]
    public function itReturnsASpecifiedPageTitleIfOneIsSpecified(): void
    {
        $this->factory->title('Hello World');

        $this->assertEquals('Hello World', $this->factory->getShared('meta.title'));
    }

    #[Test]
    public function itReturnsTheDefaultMetaDescriptionIfOneIsntSpecified(): void
    {
        $this->assertEquals(config('metas.description'), $this->factory->getShared('meta.description'));
    }

    #[Test]
    public function itReturnsASpecifiedMetaDescriptionIfOneIsSpecified(): void
    {
        $this->factory->metaDescription('Hello World');

        $this->assertEquals('Hello World', $this->factory->getShared('meta.description'));
    }

    #[Test]
    public function itReturnsTheDefaultMetaTagsIfOnesArentSpecified(): void
    {
        $this->assertEquals(config('metas.tags'), $this->factory->getShared('meta.tags'));
    }

    #[Test]
    public function itReturnsCustomMetaTagsWithTheDefaultOnesIfCustomsOnesAreSpecified(): void
    {
        $this->factory->metaTags(['Foo', 'Bar']);

        $tags = $this->factory->getShared('meta.tags');

        $this->assertContains('Foo', $tags);
        $this->assertContains('Bar', $tags);
        $this->assertContains(config('metas.tags.0'), $tags);
    }

    #[Test]
    public function itReturnsOnlyCustomTagsIfTheMergeFlagIsDisabled(): void
    {
        $this->factory->metaTags(['Foo', 'Bar'], false);

        $tags = $this->factory->getShared('meta.tags');

        $this->assertContains('Foo', $tags);
        $this->assertContains('Bar', $tags);
        $this->assertNotContains(config('metas.tags.0'), $tags);
    }

    #[Test]
    public function itReturnsTheDefaultMetaImageIfOneIsSpecified(): void
    {
        $this->assertEquals(config('metas.image'), $this->factory->getShared('meta.image'));
    }

    #[Test]
    public function itReturnsTheGivenMetaImageIfOneIsSpecified(): void
    {
        $this->factory->metaImage('foobar.jpg');

        $this->assertEquals('foobar.jpg', $this->factory->getShared('meta.image'));
    }

    #[Test]
    public function itCanBeSetToNotTrack(): void
    {
        $this->factory->doNotTrack();

        $this->assertTrue($this->factory->getShared('meta.doNotTrack'));
    }

    #[Test]
    public function itSharesTheActiveShopHolidayOnShopRoutes(): void
    {
        $holiday = $this->create(ShopHoliday::class);

        $this->assertEquals(
            ['id' => $holiday->id, 'notice' => $holiday->notice],
            $this->inertiaForRoute('shop.index')->getShared('shopHoliday'),
        );
    }

    #[Test]
    public function itSharesTheActiveShopHolidayOnTheCheckoutRoute(): void
    {
        $holiday = $this->create(ShopHoliday::class);

        $this->assertEquals(
            ['id' => $holiday->id, 'notice' => $holiday->notice],
            $this->inertiaForRoute('shop.basket.checkout')->getShared('shopHoliday'),
        );
    }

    #[Test]
    public function itDoesntShareAShopHolidayOnNonShopRoutes(): void
    {
        $this->create(ShopHoliday::class);

        $this->assertNull($this->inertiaForRoute('blog.index')->getShared('shopHoliday'));
    }

    #[Test]
    public function itDoesntShareAShopHolidayWhenThereIsntAnActiveOne(): void
    {
        $this->build(ShopHoliday::class)->upcoming()->create();

        $this->assertNull($this->inertiaForRoute('shop.index')->getShared('shopHoliday'));
    }

    #[Test]
    public function itAppendsThePageNumberToTheTitleWhenPastTheFirstPage(): void
    {
        $factory = $this->inertiaForUrl('/recipe?page=3');

        $factory->title('Hello World');

        $this->assertEquals('Hello World - Page 3', $factory->getShared('meta.title'));
    }

    #[Test]
    public function itDoesntAppendThePageNumberToTheTitleOnTheFirstPage(): void
    {
        $factory = $this->inertiaForUrl('/recipe?page=1');

        $factory->title('Hello World');

        $this->assertEquals('Hello World', $factory->getShared('meta.title'));
    }

    #[Test]
    public function itSetsTheCanonicalUrlWithoutAQueryString(): void
    {
        $this->assertEquals('http://localhost/recipe', $this->canonicalUrlFor('/recipe'));
    }

    #[Test]
    public function itKeepsThePageNumberInTheCanonicalUrl(): void
    {
        $this->assertEquals('http://localhost/recipe?page=2', $this->canonicalUrlFor('/recipe?page=2'));
    }

    #[Test]
    public function itDoesntKeepTheFirstPageInTheCanonicalUrl(): void
    {
        $this->assertEquals('http://localhost/recipe', $this->canonicalUrlFor('/recipe?page=1'));
    }

    #[Test]
    public function itDoesntKeepUndeclaredQueryParamsInTheCanonicalUrl(): void
    {
        $this->assertEquals(
            'http://localhost/recipe',
            $this->canonicalUrlFor('/recipe?utm_source=facebook&fbclid=abc123&features=vegan'),
        );
    }

    #[Test]
    public function itKeepsDeclaredQueryParamsInTheCanonicalUrl(): void
    {
        $this->assertEquals(
            'http://localhost/recipe?features=vegan&page=2',
            $this->canonicalUrlFor('/recipe?features=vegan&fbclid=abc123&page=2', ['features', 'meals', 'freeFrom']),
        );
    }

    #[Test]
    public function itOrdersTheCanonicalQueryParamsConsistently(): void
    {
        $this->assertEquals(
            'http://localhost/recipe?features=vegan&meals=breakfast&page=2',
            $this->canonicalUrlFor('/recipe?page=2&meals=breakfast&features=vegan', ['features', 'meals', 'freeFrom']),
        );
    }

    /** @param string[] $canonicalParams */
    protected function canonicalUrlFor(string $url, array $canonicalParams = []): string
    {
        $factory = $this->inertiaForUrl($url);

        if ($canonicalParams !== []) {
            $factory->canonicalParams($canonicalParams);
        }

        /** @var callable $currentUrl */
        $currentUrl = $factory->getShared('meta.currentUrl');

        return $currentUrl();
    }

    protected function inertiaForUrl(string $url): Inertia
    {
        $this->app->instance('request', Request::create($url));

        return new Inertia();
    }

    protected function inertiaForRoute(string $name): Inertia
    {
        $route = (new Route('GET', '/foo', []))->name($name);

        request()->setRouteResolver(fn () => $route);

        return new Inertia();
    }
}
