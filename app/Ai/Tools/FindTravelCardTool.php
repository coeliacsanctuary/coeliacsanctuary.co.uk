<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Ai\Concerns\FormatTravelCard;
use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class FindTravelCardTool extends BaseTool
{
    use FormatTravelCard;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return <<<'text'
        Use this tool to find travel cards in our online shop, it requires either a country, or a language to search for

        Always link to the product page so the user can add to basket and checkout.

        If a language the user wants is only available on a standard card, don't make any suggestions that they could get a plus card version, as it doesnt exist.
        text;
    }

    /**
     * Execute the tool's logic.
     */
    protected function execute(Request $request): Stringable|string
    {
        return TravelCardSearchTerm::query()
            ->where('term', 'like', "%{$request->string('country_or_language')->toString()}%")
            ->with(['products', 'products.categories', 'products.reviews', 'products.prices', 'products.variants'])
            ->get()
            ->map(fn (TravelCardSearchTerm $searchTerm) => [
                'id' => $searchTerm->id,
                'term' => $searchTerm->term,
                'type' => $searchTerm->type,
                'products' => $searchTerm->products->map($this->formatTravelCard(...)),
            ])
            ->toJson();
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'country_or_language' => $schema->string()->required()->description('The country or language to search for, eg spain, or french'),
        ];
    }
}
