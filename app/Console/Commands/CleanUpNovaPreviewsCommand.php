<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Signature;
use App\Models\NovaPreview;
use Illuminate\Console\Command;

#[Signature('coeliac:clean-up-nova-previews')]
class CleanUpNovaPreviewsCommand extends Command
{
    public function handle(): void
    {
        NovaPreview::query()
            ->where('created_at', '<', now()->subDay())
            ->delete();
    }
}
