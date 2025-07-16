<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Response;

use PHPUnit\Framework\Attributes\Test;
use App\Http\Response\Inertia;
use Tests\TestCase;

class InertiaTest extends TestCase
{
    protected Inertia $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new Inertia();
    }

    #[Test]
    public function itReturnsTheDefaultPageTitleIfOneIsntSpecified(): void
    {
        $this->assertEquals(config('metas.title'), $this->factory->getShared('meta.title'));
    }

    #[Test]
    public function itReturnsASpecifiedPageTitleIfOneIsSpecified(): void
    {
        $this->factory->title('Hello World');

        $this->assertEquals('Hello World', $this->factory->getShared('meta.title'));
    }

    #[Test]
    public function itReturnsTheDefaultMetaDescriptionIfOneIsntSpecified(): void
    {
        $this->assertEquals(config('metas.description'), $this->factory->getShared('meta.description'));
    }

    #[Test]
    public function itReturnsASpecifiedMetaDescriptionIfOneIsSpecified(): void
    {
        $this->factory->metaDescription('Hello World');

        $this->assertEquals('Hello World', $this->factory->getShared('meta.description'));
    }

    #[Test]
    public function itReturnsTheDefaultMetaTagsIfOnesArentSpecified(): void
    {
        $this->assertEquals(config('metas.tags'), $this->factory->getShared('meta.tags'));
    }

    #[Test]
    public function itReturnsCustomMetaTagsWithTheDefaultOnesIfCustomsOnesAreSpecified(): void
    {
        $this->factory->metaTags(['Foo', 'Bar']);

        $tags = $this->factory->getShared('meta.tags');

        $this->assertContains('Foo', $tags);
        $this->assertContains('Bar', $tags);
        $this->assertContains(config('metas.tags.0'), $tags);
    }

    #[Test]
    public function itReturnsOnlyCustomTagsIfTheMergeFlagIsDisabled(): void
    {
        $this->factory->metaTags(['Foo', 'Bar'], false);

        $tags = $this->factory->getShared('meta.tags');

        $this->assertContains('Foo', $tags);
        $this->assertContains('Bar', $tags);
        $this->assertNotContains(config('metas.tags.0'), $tags);
    }

    #[Test]
    public function itReturnsTheDefaultMetaImageIfOneIsSpecified(): void
    {
        $this->assertEquals(config('metas.image'), $this->factory->getShared('meta.image'));
    }

    #[Test]
    public function itReturnsTheGivenMetaImageIfOneIsSpecified(): void
    {
        $this->factory->metaImage('foobar.jpg');

        $this->assertEquals('foobar.jpg', $this->factory->getShared('meta.image'));
    }

    #[Test]
    public function itCanBeSetToNotTrack(): void
    {
        $this->factory->doNotTrack();

        $this->assertTrue($this->factory->getShared('meta.doNotTrack'));
    }
}
