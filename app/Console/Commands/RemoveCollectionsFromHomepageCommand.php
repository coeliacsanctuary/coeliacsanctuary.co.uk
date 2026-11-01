<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Signature;
use App\Models\Collections\Collection;
use Illuminate\Console\Command;

#[Signature('coeliac:remove-collections-from-homepage')]
class RemoveCollectionsFromHomepageCommand extends Command
{
    public function handle(): void
    {
        Collection::query()
            ->where('display_on_homepage', true)
            ->where('remove_from_homepage', '<', now())
            ->get()
            ->each(fn (Collection $collection) => $collection->update([
                'display_on_homepage' => false,
                'remove_from_homepage' => null,
            ]));
    }
}
