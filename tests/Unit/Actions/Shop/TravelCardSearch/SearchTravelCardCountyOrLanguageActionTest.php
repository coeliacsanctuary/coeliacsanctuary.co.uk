<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\SearchTravelCardCountyOrLanguageAction;
use App\Models\Shop\TravelCardSearchTerm;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SearchTravelCardCountyOrLanguageActionTest extends TestCase
{
    #[Test]
    public function itReturnsMatchingResults(): void
    {
        $check = $this->create(TravelCardSearchTerm::class, [
            'term' => 'foobar',
        ]);

        $results = [
            app(SearchTravelCardCountyOrLanguageAction::class)->handle('foo'),
            app(SearchTravelCardCountyOrLanguageAction::class)->handle('bar'),
            app(SearchTravelCardCountyOrLanguageAction::class)->handle('foobar'),
            app(SearchTravelCardCountyOrLanguageAction::class)->handle('fOoBaR'),
        ];

        foreach ($results as $result) {
            $this->assertCount(1, $result);
            $this->assertEquals($check->id, $result[0]['id']);
        }
    }

    #[Test]
    public function itReturnsAFormattedResponse(): void
    {
        $this->create(TravelCardSearchTerm::class, [
            'term' => 'foobar',
        ]);

        $result = app(SearchTravelCardCountyOrLanguageAction::class)->handle('foo');

        $this->assertArrayHasKeys(['id', 'term', 'type'], $result[0]);
        $this->assertEquals('<strong>foo</strong>bar', $result[0]['term']);

        $result = app(SearchTravelCardCountyOrLanguageAction::class)->handle('bar');
        $this->assertEquals('foo<strong>bar</strong>', $result[0]['term']);

        $result = app(SearchTravelCardCountyOrLanguageAction::class)->handle('foobar');
        $this->assertEquals('<strong>foobar</strong>', $result[0]['term']);
    }
}
