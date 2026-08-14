<?php

declare(strict_types=1);

namespace Tests\Unit\Support\NovaPreview;

use App\Support\NovaPreview\BlogRenderer;
use App\Support\NovaPreview\NovaPreviewResolver;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NovaPreviewResolverTest extends TestCase
{
    protected NovaPreviewResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new NovaPreviewResolver();
    }

    #[Test]
    public function itReturnsBlogRendererForTheBlogModel(): void
    {
        $renderer = $this->resolver->handle('blog');

        $this->assertInstanceOf(BlogRenderer::class, $renderer);
    }

    #[Test]
    public function itThrowsForAnUnknownModel(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->resolver->handle('unknown');
    }
}
