<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\Search;

use App\Actions\EatingOut\CreateSearchAction;
use App\Http\Requests\EatingOut\SearchCreateRequest;
use Illuminate\Http\RedirectResponse;

class StoreController
{
    public function __invoke(SearchCreateRequest $request, CreateSearchAction $createSearchAction): RedirectResponse
    {
        $fromUserLocation = $request->filled('latlng');

        $searchTerm = $createSearchAction->handle(
            $fromUserLocation ? $request->string('latlng')->toString() : $request->string('term')->toString(),
            $request->integer('range'),
            $fromUserLocation,
        );

        return new RedirectResponse(route('eating-out.search.show', ['eaterySearchTerm' => $searchTerm]));
    }
}
