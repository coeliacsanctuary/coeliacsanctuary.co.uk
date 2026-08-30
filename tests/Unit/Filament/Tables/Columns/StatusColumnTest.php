<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Tables\Columns;

use App\Filament\Resources\MainSite\Blogs\Pages\ListBlogs;
use App\Filament\Tables\Columns\StatusColumn;
use App\Models\Blogs\Blog;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatusColumnTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itRendersAsABadge(): void
    {
        $this->assertTrue(StatusColumn::make()->isBadge());
    }

    #[Test]
    public function itLabelsALiveBlogAsLive(): void
    {
        $blog = $this->create(Blog::class, ['live' => true]);

        Livewire::test(ListBlogs::class)->assertTableColumnStateSet('status', 'Live', $blog);
    }

    #[Test]
    public function itLabelsAScheduledBlogAsPending(): void
    {
        $blog = $this->create(Blog::class, ['live' => false, 'publish_at' => Carbon::now()->addDay()]);

        Livewire::test(ListBlogs::class)->assertTableColumnStateSet('status', 'Pending', $blog);
    }

    #[Test]
    public function itLabelsAnUnscheduledBlogAsDraft(): void
    {
        $blog = $this->create(Blog::class, ['live' => false, 'publish_at' => null]);

        Livewire::test(ListBlogs::class)->assertTableColumnStateSet('status', 'Draft', $blog);
    }

    #[Test]
    #[DataProvider('stateColours')]
    public function itColoursEachState(string $state, string $colour): void
    {
        $this->assertSame($colour, StatusColumn::make()->getColor($state));
    }

    public static function stateColours(): array
    {
        return [
            'live is green' => ['Live', 'success'],
            'pending is amber' => ['Pending', 'warning'],
            'draft is grey' => ['Draft', 'gray'],
        ];
    }
}
