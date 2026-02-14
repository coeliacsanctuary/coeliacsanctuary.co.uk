<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Ai\Concerns\FormatTravelCard;
use App\Models\Shop\ShopProduct;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListAllTravelCardsTool extends BaseTool
{
    use FormatTravelCard;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return <<<'text'
        List all travel cards for sale in our shop.

        Note, If the user asks for language combinations that aren't available (eg english and spanish on one card for example) inform them that cards are preprinted, can't be customised.

        If a language the user wants is only available on a standard card, don't make any suggestions that they could get a plus card version, as it doesnt exist.
        text;
    }

    /**
     * Execute the tool's logic.
     */
    protected function execute(Request $request): Stringable|string
    {
        $standardCards = ShopProduct::query()
            ->whereRelation('categories', 'title', 'Coeliac Gluten Free Travel Cards')
            ->with(['categories', 'reviews', 'prices', 'variants'])
            ->orderByDesc('pinned')
            ->orderBy('title')
            ->get()
            ->map($this->formatTravelCard(...));

        $plusCards = ShopProduct::query()
            ->whereRelation('categories', 'title', 'Coeliac+ Other Allergen Travel Cards')
            ->with(['categories', 'reviews', 'prices', 'variants'])
            ->orderByDesc('pinned')
            ->orderBy('title')
            ->get()
            ->map($this->formatTravelCard(...));

        $data = [
            'standard_cards' => $standardCards,
            'coeliac_plus_cards' => $plusCards,
        ];

        return (string) json_encode($data);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
