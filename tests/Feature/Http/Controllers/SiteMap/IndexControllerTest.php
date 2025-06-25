<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\SiteMap;

use App\Models\Blogs\Blog;
use App\Models\Recipes\Recipe;
use App\Support\SiteMap\SiteMapGenerator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->build(Blog::class)->count(10)->state(['live' => true])->create();
        $this->build(Recipe::class)->count(10)->state(['live' => true])->create();
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->get(route('sitemap'))->assertOk();
    }

    #[Test]
    public function itCallsTheCombinedFeedClass(): void
    {
        $this->mock(SiteMapGenerator::class)
            ->shouldReceive('blogs')
            ->andReturn(collect())
            ->once()
            ->shouldReceive('recipes')
            ->andReturn(collect())
            ->once()
            ->shouldReceive('counties')
            ->andReturn(collect())
            ->once()
            ->shouldReceive('towns')
            ->andReturn(collect())
            ->once()
            ->shouldReceive('areas')
            ->andReturn(collect())
            ->once()
            ->shouldReceive('eateries')
            ->andReturn(collect())
            ->once()
            ->shouldReceive('nationwideChains')
            ->andReturn(collect())
            ->once()
            ->shouldReceive('nationwideBranches')
            ->andReturn(collect())
            ->once()
            ->shouldReceive('categories')
            ->andReturn(collect())
            ->once()
            ->shouldReceive('products')
            ->andReturn(collect())
            ->once();

        $this->get(route('sitemap'));
    }

    #[Test]
    public function itReturnsAnXmlHeader(): void
    {
        $this->get(route('sitemap'))->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    #[Test]
    public function itReturnsTheSiteMapView(): void
    {
        $this->get(route('sitemap'))->assertViewIs('static.sitemap');
    }
}
