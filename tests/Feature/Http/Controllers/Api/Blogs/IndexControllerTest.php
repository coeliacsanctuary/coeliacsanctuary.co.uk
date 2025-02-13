<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Blogs;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Blogs\GetBlogsForBlogIndexAction;
use App\Resources\Blogs\BlogApiCollection;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    #[Test]
    public function itCallsTheGetBlogsForBlogIndexAction(): void
    {
        $this->expectAction(GetBlogsForBlogIndexAction::class, return: BlogApiCollection::make(collect()));

        $this->get(route('api.blogs.index'))->assertOk();
    }
}
