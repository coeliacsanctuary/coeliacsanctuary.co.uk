<?php

declare(strict_types=1);

namespace App\Mailables;

use App\DataObjects\NotificationRelatedObject;
use App\Infrastructure\MjmlMessage;
use App\Models\Blogs\Blog;
use App\Models\Recipes\Recipe;
use App\Models\Shop\ShopProduct;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class BaseMailable
{
    protected string $emailKey = '';

    public function __construct(?string $emailKey = null)
    {
        $this->emailKey = $emailKey ?? Str::uuid()->toString();
    }

    public static function make(mixed ...$args): MjmlMessage
    {
        /** @phpstan-ignore-next-line  */
        return (new static(...$args))->toMail();
    }

    protected function baseData(array $data = []): array
    {
        return array_merge([
            'date' => Carbon::now(),
            'key' => $this->emailKey,
            ...$this->relatedItems(),
        ], $data);
    }

    abstract public function toMail(): MjmlMessage;

    /** @return array<string, mixed> */
    protected function relatedItems(): array
    {
        /** @var array{array<string, mixed>} $randomItems */
        $randomItems = Arr::random([
            [
                'relatedTitle' => 'Blogs',
                'relatedItems' => Blog::query()
                    ->take(3)
                    ->inRandomOrder()
                    ->with(['media'])
                    ->get()
                    ->map(fn (Blog $blog) => new NotificationRelatedObject(
                        title: $blog->title,
                        image: $blog->main_image,
                        link: $blog->link,
                    )),
            ],
            [
                'relatedTitle' => 'Recipes',
                'relatedItems' => Recipe::query()
                    ->take(3)
                    ->inRandomOrder()
                    ->with(['media'])
                    ->get()
                    ->map(fn (Recipe $recipe) => new NotificationRelatedObject(
                        title: $recipe->title,
                        image: $recipe->square_image ?? $recipe->main_image,
                        link: $recipe->link,
                    )),
            ],
            [
                'relatedTitle' => 'Shop Products',
                'relatedItems' => ShopProduct::query()
                    ->take(3)
                    ->inRandomOrder()
                    ->with(['media'])
                    ->get()
                    ->map(fn (ShopProduct $product) => new NotificationRelatedObject(
                        title: $product->title,
                        image: $product->main_image,
                        link: $product->link,
                    )),
            ],
        ], 1);

        /** @var array<string, mixed> $result */
        $result = Arr::first($randomItems);

        return $result;
    }
}
