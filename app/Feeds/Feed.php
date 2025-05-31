<?php

declare(strict_types=1);

namespace App\Feeds;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/** @template T of Model */
abstract class Feed
{
    /** @var Collection<int, T> */
    protected Collection $items;

    /** @param T $item */
    abstract protected function formatItem(Model $item): array;

    abstract protected function feedTitle(): string;

    abstract protected function linkRoot(): string;

    abstract protected function feedDescription(): string;

    protected function makeData(): array
    {
        $lastUpdated = $this->items->first()->created_at ?? Carbon::now();

        return [
            'title' => $this->feedTitle(),
            'link' => $this->linkRoot(),
            'description' => $this->feedDescription(),
            'date' => $lastUpdated->toRfc822String(),
            'items' => $this->items->map(fn (Model $item) => $this->formatItem($item)),
        ];
    }

    /** @param Collection<int, T> $items */
    public function render(Collection $items): View
    {
        $this->items = $items;

        return view('static.feed', $this->makeData());
    }
}
