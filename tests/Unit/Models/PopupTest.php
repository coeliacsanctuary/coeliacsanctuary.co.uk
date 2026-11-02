<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Popup;
use App\Scopes\LiveScope;
use Tests\Concerns\DisplaysMediaTestTrait;
use Tests\TestCase;

class PopupTest extends TestCase
{
    use DisplaysMediaTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDisplaysMediaTest(fn () => $this->create(Popup::class));
    }

    #[Test]
    public function itHasTheLiveScopeApplied(): void
    {
        $this->assertTrue(Popup::hasGlobalScope(LiveScope::class));
    }
}
