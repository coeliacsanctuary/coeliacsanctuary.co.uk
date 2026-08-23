<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\Shop\ShopProduct;
use App\Models\Shop\TravelCardSearchTerm;
use App\Support\Helpers;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[Model('gpt-5.2')]
class TravelCardCountryScoringAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    protected bool $singleLanguage;

    public function __construct(protected ShopProduct $product)
    {
        $this->singleLanguage = $product->categories()
            ->withoutGlobalScopes()
            ->where('shop_categories.id', 1)
            ->doesntExist();
    }

    public function instructions(): Stringable|string
    {
        return view('prompts.travel-card-country-scoring', [
            'product' => $this->product,
            'languages' => $this->languages(),
            'terms' => $this->terms(),
        ])->render();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'terms' => $schema->array()
                ->items($schema->object($this->termSchema(...)))
                ->required(),
        ];
    }

    /** @return array<int, string> */
    public function languages(): array
    {
        if ($this->isSingleLanguage()) {
            return [];
        }

        return Str::of($this->product->title)
            ->before(' Coeliac Gluten Free Travel Translation Card')
            ->explode(' and ')
            ->toArray();
    }

    public function isSingleLanguage(): bool
    {
        return $this->singleLanguage;
    }

    /** @return array<string, mixed> */
    protected function termSchema(JsonSchema $schema): array
    {
        $term = [
            'term_id' => $schema->integer()->required(),
            'show_on_product_page' => $schema->boolean()->required(),
            'score' => $schema->integer()->nullable()->required(),
            'reason' => $schema->string()->required(),
        ];

        if ( ! $this->isSingleLanguage()) {
            $term['language'] = $schema->string()->enum([...$this->languages(), 'both'])->required();
        }

        return $term;
    }

    /** @return Collection<int, array{id: int, term: string, current_language: string|null, current_score: string|null, flag_code: string|null}> */
    protected function terms(): Collection
    {
        return $this->product
            ->travelCardSearchTerms
            ->map(fn (TravelCardSearchTerm $term) => [
                'id' => $term->id,
                'term' => $term->term,
                'current_language' => $this->pivotValue($term, 'card_language'),
                'current_score' => $this->pivotValue($term, 'card_score'),
                'flag_code' => Helpers::countryCode($term->term),
            ]);
    }

    protected function pivotValue(TravelCardSearchTerm $term, string $key): ?string
    {
        $value = $term->pivot->getAttribute($key); /** @phpstan-ignore-line */

        return is_scalar($value) ? (string) $value : null;
    }
}
