<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('db:wipe {--force}')]
class FakeWipeCommand extends Command
{
    public bool $ran = false;

    public function handle(): int
    {
        $this->ran = true;

        return self::SUCCESS;
    }
}
