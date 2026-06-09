<?php

declare(strict_types=1);

namespace App\Console\Commands\OneTime;

use App\Models\EatingOut\Eatery;
use Illuminate\Console\Command;

use function Laravel\Prompts\progress;

class MigrateEateriesSocialUrlsCommand extends Command
{
    protected $signature = 'one-time:migrate-eateries-social-urls';

    protected int $facebookMigrated = 0;

    protected int $instagramMigrated = 0;

    public function handle(): void
    {
        $eateries = Eatery::withoutGlobalScopes()
            ->whereNotNull('website')
            ->where('website', '!=', '')
            ->get();

        if ($eateries->isEmpty()) {
            $this->line('No eateries with website URLs found.');
            $this->line('Facebook URLs migrated: 0');
            $this->line('Instagram URLs migrated: 0');

            return;
        }

        progress(
            label: 'Migrating social URLs',
            steps: $eateries,
            callback: $this->processEatery(...),
        );

        $this->line("Facebook URLs migrated: {$this->facebookMigrated}");
        $this->line("Instagram URLs migrated: {$this->instagramMigrated}");
    }

    protected function processEatery(Eatery $eatery): void
    {
        $website = $eatery->website;

        if ($this->isFacebookUrl($website)) {
            $eatery->updateQuietly(['facebook_url' => $website, 'website' => null]);

            ++$this->facebookMigrated;

            return;
        }

        if ($this->isInstagramUrl($website)) {
            $eatery->updateQuietly(['instagram_url' => $website, 'website' => null]);

            ++$this->instagramMigrated;
        }
    }

    protected function isFacebookUrl(string $url): bool
    {
        return str_contains($url, 'facebook.com') || str_contains($url, 'fb.com');
    }

    protected function isInstagramUrl(string $url): bool
    {
        return str_contains($url, 'instagram.com');
    }
}
