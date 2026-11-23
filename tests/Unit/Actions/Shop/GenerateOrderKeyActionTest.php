<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Shop\GenerateOrderKeyAction;
use App\Models\Shop\ShopOrder;
use Tests\TestCase;

class GenerateOrderKeyActionTest extends TestCase
{
    #[Test]
    public function itReturnsAnEightDigitNumericKey(): void
    {
        $key = $this->callAction(GenerateOrderKeyAction::class);

        $this->assertMatchesRegularExpression('/^\d{8}$/', $key);
    }

    #[Test]
    public function itReturnsTheFirstKeyItGeneratesIfItIsntInUse(): void
    {
        $this->create(ShopOrder::class, ['order_key' => '12345678']);

        $this->partialMock(GenerateOrderKeyAction::class)
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('generateKey')
            ->once()
            ->andReturn('87654321');

        $this->assertSame('87654321', $this->callAction(GenerateOrderKeyAction::class));
    }

    #[Test]
    public function itGeneratesAnotherKeyIfTheFirstOneIsAlreadyInUse(): void
    {
        $this->create(ShopOrder::class, ['order_key' => '12345678']);

        $this->partialMock(GenerateOrderKeyAction::class)
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('generateKey')
            ->twice()
            ->andReturn('12345678', '87654321');

        $this->assertSame('87654321', $this->callAction(GenerateOrderKeyAction::class));
    }
}
