<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\Blogs;

use App\Models\Blogs\Blog;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withBlogs(15);
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.blogs.index'))->assertForbidden();
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
                    [
                        'title',
                        'link',
                        'image',
                        'date',
                        'description',
                    ],
                ],
            ]);
    }

    #[Test]
    public function itReturns12Items(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonCount(12, 'data');
    }

    #[Test]
    public function itDoesntReturnTheOldestBlog(): void
    {
        $blog = Blog::query()->oldest()->first();

        $request = $this->makeRequest();

        $blogs = $request->collect('data');

        $this->assertNotContains($blog->title, $blogs->pluck('title'));
    }

    #[Test]
    public function itReturnsTheNewestBlogFirst(): void
    {
        $blog = Blog::query()->latest()->first();

        $this->makeRequest()
            ->assertOk()
            ->assertJsonPath('data.0.title', $blog->title);
    }

    protected function makeRequest(string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.blogs.index'),
            ['x-coeliac-source' => $source],
        );
    }
}
