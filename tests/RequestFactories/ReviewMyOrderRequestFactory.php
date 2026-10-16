<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Worksome\RequestFactories\RequestFactory;

class ReviewMyOrderRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'whereHeard' => ['facebook', 'instagram'],
        ];
    }

    /** @param Collection<int, ShopOrderItem | ShopOrderDigitalItem> $items */
    public function forItems(Collection $items): self
    {
        [$digitalItems, $physicalItems] = $items->partition(fn (Model $item) => $item instanceof ShopOrderDigitalItem);

        return $this->state([
            'products' => $physicalItems
                ->unique('product_id')
                ->map(fn (ShopOrderItem $item) => $this->reviewFor($item->product_id, 'physical'))
                ->concat(
                    $digitalItems
                        ->unique('digital_card_id')
                        ->map(fn (ShopOrderDigitalItem $item) => $this->reviewFor($item->digital_card_id, 'digital'))
                )
                ->values()
                ->toArray(),
        ]);
    }

    /** @return array{id: int, rating: int, review: string, type: string} */
    protected function reviewFor(int $id, string $type): array
    {
        return [
            'id' => $id,
            'rating' => $this->faker->numberBetween(1, 5),
            'review' => $this->faker->paragraph,
            'type' => $type,
        ];
    }
}
