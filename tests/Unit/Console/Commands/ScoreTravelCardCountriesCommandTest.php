<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Ai\Agents\TravelCardCountryScoringAgent;
use App\Console\Commands\OneTime\ScoreTravelCardCountriesCommand;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ScoreTravelCardCountriesCommandTest extends TestCase
{
    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->create(ShopProduct::class, ['title' => 'Greek and Turkish Coeliac Gluten Free Travel Translation Card']);

        $this->assignToCategory($this->product, 1);
    }

    #[Test]
    public function itWritesTheScoresReturnedByTheAgent(): void
    {
        $greece = $this->attachTerm($this->product, 'greece', 'Greek', 95);
        $crete = $this->attachTerm($this->product, 'crete', 'Greek', 100);

        $this->fakeAgent([
            $this->score($greece, 100, 'Greek'),
            $this->score($crete, 90, 'Greek'),
        ]);

        $this->artisan(ScoreTravelCardCountriesCommand::class)->run();

        $this->assertSame('100', $this->pivotFor($greece)->card_score);
        $this->assertSame('90', $this->pivotFor($crete)->card_score);
    }

    #[Test]
    public function itHidesAnAliasTermWithoutAScoreAndKeepsTheTermRow(): void
    {
        $greece = $this->attachTerm($this->product, 'greece', 'Greek', 95);
        $alias = $this->attachTerm($this->product, 'hellas', 'Greek', 90);

        $this->fakeAgent([
            $this->score($greece, 100, 'Greek'),
            ['term_id' => $alias->id, 'show_on_product_page' => false, 'score' => null, 'language' => 'Greek', 'reason' => 'Alias of Greece'],
        ]);

        $this->artisan(ScoreTravelCardCountriesCommand::class)->run();

        $pivot = $this->pivotFor($alias);

        $this->assertSame(0, (int) $pivot->card_show_on_product_page);
        $this->assertNull($pivot->card_score);
        $this->assertNotNull(TravelCardSearchTerm::query()->find($alias->id));
    }

    #[Test]
    public function itWritesTheLanguageReturnedByTheAgentForATwoLanguageCard(): void
    {
        $term = $this->attachTerm($this->product, 'cyprus', 'both', 80);

        $this->fakeAgent([$this->score($term, 100, 'Greek')]);

        $this->artisan(ScoreTravelCardCountriesCommand::class)->run();

        $this->assertSame('Greek', $this->pivotFor($term)->card_language);
    }

    #[Test]
    public function itLeavesTheCardLanguageNullForASingleLanguageCard(): void
    {
        $product = $this->create(ShopProduct::class, ['title' => 'Greek Coeliac+ Gluten Free and Other Dietary Needs Travel Translation Card']);

        $this->assignToCategory($product, 11);

        $term = $this->attachTerm($product, 'greece', null, null);

        $this->fakeAgent([['term_id' => $term->id, 'show_on_product_page' => true, 'score' => 100, 'reason' => 'Primary country']]);

        $this->artisan(ScoreTravelCardCountriesCommand::class)->run();

        $pivot = $this->pivotFor($term, $product);

        $this->assertSame('100', $pivot->card_score);
        $this->assertNull($pivot->card_language);
    }

    #[Test]
    public function itDoesntWriteScoresWhenALanguageListHasARepeatedScore(): void
    {
        $greece = $this->attachTerm($this->product, 'greece', 'Greek', 95);
        $crete = $this->attachTerm($this->product, 'crete', 'Greek', 100);

        $this->fakeAgent([
            $this->score($greece, 100, 'Greek'),
            $this->score($crete, 100, 'Greek'),
        ], times: 2);

        $this->artisan(ScoreTravelCardCountriesCommand::class)->run();

        $this->assertSame('95', $this->pivotFor($greece)->card_score);
        $this->assertSame('100', $this->pivotFor($crete)->card_score);
    }

    #[Test]
    public function itDoesntWriteScoresWhenALanguageListDoesntTopOutAtOneHundred(): void
    {
        $greece = $this->attachTerm($this->product, 'greece', 'Greek', 95);

        $this->fakeAgent([$this->score($greece, 80, 'Greek')], times: 2);

        $this->artisan(ScoreTravelCardCountriesCommand::class)->run();

        $this->assertSame('95', $this->pivotFor($greece)->card_score);
    }

    #[Test]
    public function itMergesDuplicateSearchTermsIntoTheOneWithTheMostHits(): void
    {
        $survivor = $this->attachTerm($this->product, 'Crete', 'Greek', 95, hits: 64);
        $loser = $this->create(TravelCardSearchTerm::class, ['term' => 'crete', 'type' => 'country', 'hits' => 38]);

        $this->fakeAgent([$this->score($survivor, 100, 'Greek')]);

        $this->artisan(ScoreTravelCardCountriesCommand::class)->run();

        $this->assertNull(TravelCardSearchTerm::query()->find($loser->id));
        $this->assertSame(102, TravelCardSearchTerm::query()->findOrFail($survivor->id)->hits);
    }

    #[Test]
    public function itMovesAnAssignmentFromTheMergedTermOntoTheSurvivor(): void
    {
        $survivor = $this->attachTerm($this->product, 'Crete', 'Greek', 95, hits: 64);

        $other = $this->create(ShopProduct::class, ['title' => 'Greek Coeliac+ Gluten Free and Other Dietary Needs Travel Translation Card']);
        $this->assignToCategory($other, 11);

        $loser = $this->attachTerm($other, 'crete', null, null, hits: 38);

        $this->fakeAgent([$this->score($survivor, 100, 'Greek')]);

        $this->artisan(ScoreTravelCardCountriesCommand::class)->run();

        $this->assertNull(TravelCardSearchTerm::query()->find($loser->id));

        $this->assertDatabaseHas('shop_product_assigned_travel_card_search_terms', [
            'search_term_id' => $survivor->id,
            'product_id' => $other->id,
        ]);

        $this->assertDatabaseMissing('shop_product_assigned_travel_card_search_terms', [
            'search_term_id' => $loser->id,
        ]);
    }

    #[Test]
    public function itKeepsTheShownFlagWhenMergingAHiddenAssignmentOntoAShownOne(): void
    {
        $survivor = $this->attachTerm($this->product, 'Crete', 'Greek', 95, hits: 64);

        $loser = $this->create(TravelCardSearchTerm::class, ['term' => 'crete', 'type' => 'country', 'hits' => 38]);

        $this->product->travelCardSearchTerms()->attach($loser, [
            'card_show_on_product_page' => false,
            'card_score' => null,
            'card_language' => null,
        ]);

        $this->fakeAgent([$this->score($survivor, 100, 'Greek')]);

        $this->artisan(ScoreTravelCardCountriesCommand::class)->run();

        $this->assertSame(1, (int) $this->pivotFor($survivor)->card_show_on_product_page);
    }

    /**
     * @param  array<int, array<string, mixed>> $terms
     */
    protected function fakeAgent(array $terms, int $times = 1): void
    {
        TravelCardCountryScoringAgent::fake(array_fill(0, $times, ['terms' => $terms]));
    }

    /** @return array<string, mixed> */
    protected function score(TravelCardSearchTerm $term, int $score, string $language): array
    {
        return [
            'term_id' => $term->id,
            'show_on_product_page' => true,
            'score' => $score,
            'language' => $language,
            'reason' => 'Because',
        ];
    }

    protected function attachTerm(ShopProduct $product, string $term, ?string $language, ?int $score, bool $show = true, int $hits = 0): TravelCardSearchTerm
    {
        $searchTerm = $this->create(TravelCardSearchTerm::class, ['term' => $term, 'type' => 'country', 'hits' => $hits]);

        $product->travelCardSearchTerms()->attach($searchTerm, [
            'card_show_on_product_page' => $show,
            'card_score' => $score,
            'card_language' => $language,
        ]);

        return $searchTerm;
    }

    protected function assignToCategory(ShopProduct $product, int $categoryId): void
    {
        $category = ShopCategory::query()->withoutGlobalScopes()->find($categoryId)
            ?? $this->create(ShopCategory::class, ['id' => $categoryId, 'title' => "Category {$categoryId}"]);

        $product->categories()->sync([$category->id]);

        $product->unsetRelation('categories');
    }

    protected function pivotFor(TravelCardSearchTerm $term, ?ShopProduct $product = null): object
    {
        return DB::table('shop_product_assigned_travel_card_search_terms')
            ->where('search_term_id', $term->id)
            ->where('product_id', ($product ?? $this->product)->id)
            ->firstOrFail();
    }
}
