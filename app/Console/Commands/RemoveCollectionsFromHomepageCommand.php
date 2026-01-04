<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Collections\Collection;
use Illuminate\Console\Command;

class RemoveCollectionsFromHomepageCommand extends Command
{
    protected $signature = 'coeliac:remove-collections-from-homepage';

    public function handle(): void
    {
        Collection::query()
            ->where('display_on_homepage', true)
            ->where('remove_from_homepage', '<', now())
            ->update([
                'display_on_homepage' => false,
                'remove_from_homepage' => null,
            ]);
    }
}
