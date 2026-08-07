<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\Search;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\EatingOut\CreateSearchAction;
use App\Models\EatingOut\EaterySearchTerm;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    #[Test]
    public function itErrorsWithAnInvalidSearchTermDetails(): void
    {
        $this->submitSearch(term: null)->assertSessionHasErrors('term');
        $this->submitSearch(term: 123)->assertSessionHasErrors('term');
        $this->submitSearch(term: true)->assertSessionHasErrors('term');
        $this->submitSearch(term: 'aa')->assertSessionHasErrors('term'); // to short
        $this->submitSearch(term: Str::random(101))->assertSessionHasErrors('term'); // to long
    }

    #[Test]
    public function itErrorsWithAnInvalidLatLng(): void
    {
        $this->submitSearch(term: null, latlng: 'foo')->assertSessionHasErrors('latlng');
        $this->submitSearch(term: null, latlng: '53.0873')->assertSessionHasErrors('latlng');
        $this->submitSearch(term: null, latlng: '53.0873,')->assertSessionHasErrors('latlng');
    }

    #[Test]
    public function itAcceptsALatLngInsteadOfATerm(): void
    {
        $this->submitSearch(term: null, latlng: '53.0873,-2.4419')->assertSessionHasNoErrors();
    }

    #[Test]
    public function itCreatesASearchFromTheUsersLocation(): void
    {
        $this->submitSearch(term: null, latlng: '53.0873,-2.4419');

        $searchTerm = EaterySearchTerm::query()->sole();

        $this->assertEquals('53.0873,-2.4419', $searchTerm->term);
        $this->assertTrue($searchTerm->from_user_location);
    }

    #[Test]
    public function aTermSearchIsNotFlaggedAsComingFromTheUsersLocation(): void
    {
        $this->submitSearch('crewe');

        $searchTerm = EaterySearchTerm::query()->sole();

        $this->assertEquals('crewe', $searchTerm->term);
        $this->assertFalse($searchTerm->from_user_location);
    }

    #[Test]
    public function itErrorsWithAnInvalidSearchRangeDetails(): void
    {
        $this->submitSearch(range: null)->assertSessionHasErrors('range');
        $this->submitSearch(range: 'foo')->assertSessionHasErrors('range');
        $this->submitSearch(range: true)->assertSessionHasErrors('range');
        $this->submitSearch(range: 7)->assertSessionHasErrors('range'); // not valid entry
    }

    #[Test]
    public function itCallsTheCreateSearchAction(): void
    {
        $this->expectAction(CreateSearchAction::class);

        $this->submitSearch('foo', 5);

    }

    #[Test]
    public function itRedirectsToTheResultsPage(): void
    {
        $searchTerm = $this->create(EaterySearchTerm::class, [
            'term' => 'foo',
            'range' => 2,
        ]);

        $this->submitSearch('foo')->assertRedirectToRoute('eating-out.search.show', [
            'eaterySearchTerm' => $searchTerm,
        ]);
    }

    protected function submitSearch(mixed $term = null, mixed $range = 2, mixed $latlng = null): TestResponse
    {
        return $this->post(route('eating-out.search.create'), [
            'term' => $term,
            'range' => $range,
            'latlng' => $latlng,
        ]);
    }
}
