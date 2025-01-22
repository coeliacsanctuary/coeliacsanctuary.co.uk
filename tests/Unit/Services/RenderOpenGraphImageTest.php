<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Services\RenderOpenGraphImage;
use Spatie\Browsershot\Browsershot;
use Tests\TestCase;

class RenderOpenGraphImageTest extends TestCase
{
    #[Test]
    public function itCanGenerateABase64ImageStringUsingBrowsershot(): void
    {
        $this->mock(Browsershot::class)
            ->shouldReceive('setHtml')->once()->andReturnSelf()->getMock()
            ->shouldReceive('setIncludePath')->with('$PATH')->once()->andReturnSelf()->getMock()
            ->shouldReceive('setNodeBinary')->with(config('browsershot.node_path'))->once()->andReturnSelf()->getMock()
            ->shouldReceive('setNpmBinary')->with(config('browsershot.npm_path'))->once()->andReturnSelf()->getMock()
            ->shouldReceive('windowSize')->with(1200, 630)->andReturnSelf()->once()->getMock()
            ->shouldReceive('base64Screenshot')->andReturn('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAb0lEQVR4nGK567aCAQZOXAuEs/cf3QZnO3xaDGczMZAIaK+BJVjTFs5RLAyBs1UuS8DZaj8m0tFJJGtgzORshHMELtfA2TMC58LZ7i3MdHQS6fHw6VkPnKMWcRHOlq/JgbOPFO+ko5NI1gAIAAD//3qSF5xOx6hcAAAAAElFTkSuQmCC')->once();

        app(RenderOpenGraphImage::class)->handle('foo');
    }
}
