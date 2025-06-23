<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\OpenGraphImages;

use App\Actions\OpenGraphImages\GenerateAreaOpenGraphImageAction;
use App\Models\EatingOut\EateryArea;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\OpenGraphImages\GenerateTownOpenGraphImageAction;
use App\Models\EatingOut\EateryTown;
use Illuminate\View\View;
use Tests\TestCase;

class GenerateAreaOpenGraphImageActionTest extends TestCase
{
    #[Test]
    public function itReturnsTheView(): void
    {
        $area = $this->create(EateryArea::class);

        $action = app(GenerateAreaOpenGraphImageAction::class)->handle($area);

        $this->assertInstanceOf(View::class, $action);
        $this->assertEquals('og-images.eating-out.area', $action->name());
        $this->assertArrayHasKeys(['area', 'eateries', 'attractions', 'hotels', 'reviews', 'width'], $action->getData());
        $this->assertTrue($area->is($action->getData()['area']));
    }
}
