<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Redirects\Tables;

use App\Filament\Resources\MainSite\Redirects\Pages\ListRedirects;
use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use App\Models\Redirect;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Http\Response;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RedirectsTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itShowsEveryRedirect(): void
    {
        $redirects = $this->create(Redirect::class, 3);

        Livewire::test(ListRedirects::class)->assertCanSeeTableRecords($redirects);
    }

    #[Test]
    public function itShowsTheNewestRedirectsFirst(): void
    {
        $redirects = $this->create(Redirect::class, 3);

        Livewire::test(ListRedirects::class)
            ->assertCanSeeTableRecords($redirects->reverse()->values(), inOrder: true);
    }

    #[Test]
    #[DataProvider('columns')]
    public function itShowsTheRedirectColumns(string $column): void
    {
        $this->create(Redirect::class);

        Livewire::test(ListRedirects::class)->assertTableColumnExists($column);
    }

    public static function columns(): array
    {
        return [
            'id' => ['id'],
            'from' => ['from'],
            'to' => ['to'],
            'status' => ['status'],
            'hits' => ['hits'],
        ];
    }

    #[Test]
    public function itLabelsTheIdColumn(): void
    {
        Livewire::test(ListRedirects::class)
            ->assertTableColumnExists('id', fn (TextColumn $column): bool => $column->getLabel() === 'ID');
    }

    #[Test]
    #[DataProvider('searchableColumns')]
    public function itSearchesTheRedirectColumns(string $column): void
    {
        Livewire::test(ListRedirects::class)
            ->assertTableColumnExists($column, fn (TextColumn $c): bool => $c->isSearchable());
    }

    public static function searchableColumns(): array
    {
        return [
            'id' => ['id'],
            'from' => ['from'],
            'to' => ['to'],
        ];
    }

    #[Test]
    public function itSortsByTheHitCount(): void
    {
        Livewire::test(ListRedirects::class)
            ->assertTableColumnExists('hits', fn (TextColumn $column): bool => $column->isSortable());
    }

    #[Test]
    public function itFindsARedirectByItsFromPath(): void
    {
        $wanted = $this->create(Redirect::class, ['from' => '/blog/old-post']);
        $other = $this->create(Redirect::class, ['from' => '/recipe/old-recipe']);

        Livewire::test(ListRedirects::class)
            ->searchTable('/blog/old-post')
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itFindsARedirectByItsToPath(): void
    {
        $wanted = $this->create(Redirect::class, ['to' => '/blog/new-post']);
        $other = $this->create(Redirect::class, ['to' => '/recipe/new-recipe']);

        Livewire::test(ListRedirects::class)
            ->searchTable('/blog/new-post')
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itFindsARedirectById(): void
    {
        $redirects = $this->create(Redirect::class, 2);

        Livewire::test(ListRedirects::class)
            ->searchTable((string) $redirects->last()->id)
            ->assertCanSeeTableRecords([$redirects->last()])
            ->assertCanNotSeeTableRecords([$redirects->first()]);
    }

    #[Test]
    #[DataProvider('storedStatuses')]
    public function itLabelsTheStatusOfARedirect(int $stored, string $label): void
    {
        $redirect = $this->create(Redirect::class, ['status' => $stored]);

        Livewire::test(ListRedirects::class)->assertTableColumnStateSet('status', $label, $redirect);
    }

    public static function storedStatuses(): array
    {
        return [
            '301 reads as permanent' => [Response::HTTP_MOVED_PERMANENTLY, 'Permanent'],
            '302 reads as temporary' => [Response::HTTP_FOUND, 'Temporary'],
            '307 reads as temporary' => [Response::HTTP_TEMPORARY_REDIRECT, 'Temporary'],
            '308 reads as permanent' => [Response::HTTP_PERMANENTLY_REDIRECT, 'Permanent'],
        ];
    }

    #[Test]
    public function itLinksEachRowToTheEditPage(): void
    {
        $redirect = $this->create(Redirect::class);

        $this->assertSame(
            RedirectResource::getUrl('edit', ['record' => $redirect]),
            Livewire::test(ListRedirects::class)->instance()->getTable()->getRecordUrl($redirect)
        );
    }

    #[Test]
    public function itOffersAnEditActionForEveryRedirect(): void
    {
        $redirect = $this->create(Redirect::class);

        Livewire::test(ListRedirects::class)->assertActionExists(TestAction::make(EditAction::class)->table($redirect));
    }

    #[Test]
    public function itDoesNotLetYouDeleteARedirect(): void
    {
        $redirect = $this->create(Redirect::class);

        Livewire::test(ListRedirects::class)->assertActionDoesNotExist(TestAction::make(DeleteAction::class)->table($redirect));
    }

    #[Test]
    public function itDoesNotLetYouBulkDeleteRedirects(): void
    {
        $this->create(Redirect::class);

        Livewire::test(ListRedirects::class)->assertActionDoesNotExist(DeleteBulkAction::class);
    }
}
