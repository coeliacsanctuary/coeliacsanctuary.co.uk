<?php

declare(strict_types=1);

namespace Tests\Fixtures\MagicRouting;

use Illuminate\Contracts\Support\Responsable;

class StubMultiParamController
{
    public ?StubDependency $receivedDep = null;

    public ?AnotherStubDependency $receivedOther = null;

    public function __invoke(StubDependency $dep, AnotherStubDependency $other): Responsable
    {
        $this->receivedDep = $dep;
        $this->receivedOther = $other;

        return new StubResponse();
    }
}
