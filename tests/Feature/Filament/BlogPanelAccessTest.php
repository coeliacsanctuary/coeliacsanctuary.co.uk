<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\MainSite\Blogs\BlogResource;
use App\Models\Blogs\Blog;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogPanelAccessTest extends TestCase
{
    protected Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->blog = $this->create(Blog::class);
    }

    #[Test]
    #[DataProvider('blogPages')]
    public function guestsAreSentToTheLoginPage(string $page): void
    {
        $this->get($this->url($page))->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    #[DataProvider('blogPages')]
    public function signedInUsersCanOpenEveryBlogPage(string $page): void
    {
        $this->actingAs($this->create(User::class))
            ->get($this->url($page))
            ->assertOk();
    }

    public static function blogPages(): array
    {
        return [
            'the blog list' => ['index'],
            'the create page' => ['create'],
            'the edit page' => ['edit'],
            'the metrics page' => ['metrics'],
        ];
    }

    protected function url(string $page): string
    {
        return in_array($page, ['index', 'create'], true)
            ? BlogResource::getUrl($page)
            : BlogResource::getUrl($page, ['record' => $this->blog]);
    }
}
