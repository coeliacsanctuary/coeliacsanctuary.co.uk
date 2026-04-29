<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\NovaPreview;
use Illuminate\Console\Command;

class CleanUpNovaPreviewsCommand extends Command
{
    protected $signature = 'coeliac:clean-up-nova-previews';

    public function handle(): void
    {
        NovaPreview::query()
            ->where('created_at', '<', now()->subDay())
            ->delete();
    }
}
