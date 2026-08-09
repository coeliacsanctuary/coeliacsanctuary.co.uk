<?php

declare(strict_types=1);

namespace Tests\Fixtures\MagicRouting;

use Illuminate\Contracts\Support\Responsable;

class StubController
{
    public ?StubDependency $receivedDep = null;

    public function __construct(protected readonly StubResponse $response = new StubResponse())
    {
    }

    public function __invoke(StubDependency $dep): Responsable
    {
        $this->receivedDep = $dep;

        return $this->response;
    }
}
