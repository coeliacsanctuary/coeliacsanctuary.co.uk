<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Blogs\Pages;

use App\Filament\Resources\MainSite\Blogs\Pages\ListBlogs;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListBlogsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsTheBlogList(): void
    {
        Livewire::test(ListBlogs::class)->assertOk();
    }

    #[Test]
    public function itOffersAButtonToCreateABlog(): void
    {
        Livewire::test(ListBlogs::class)->assertActionExists(CreateAction::class);
    }
}
