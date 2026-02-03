<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\RemoveCollectionsFromHomepageCommand;
use App\Models\Collections\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RemoveCollectionsFromHomepageCommandTest extends TestCase
{
    #[Test]
    public function itDoesNothingIfTheCollectionIsntSetAsToBeDisplayedOnHomepage(): void
    {
        /** @var Collection $collection */
        $collection = $this->build(Collection::class)->notOnHomepage()->create();

        $this->artisan(RemoveCollectionsFromHomepageCommand::class);

        $this->assertFalse($collection->isDirty());
    }

    #[Test]
    public function itDoesNothingIfTheRemoveFromHomepageDateIsInTheFuture(): void
    {
        /** @var Collection $collection */
        $collection = $this->build(Collection::class)->displayedOnHomepage(now()->addDay())->create();

        $this->artisan(RemoveCollectionsFromHomepageCommand::class);

        $this->assertTrue($collection->refresh()->display_on_homepage);
    }

    #[Test]
    public function itRemovesTheCollectionFromTheHomepage(): void
    {
        /** @var Collection $collection */
        $collection = $this->build(Collection::class)->displayedOnHomepage(now()->subDay())->create();

        $this->artisan(RemoveCollectionsFromHomepageCommand::class);

        $collection->refresh();

        $this->assertFalse($collection->display_on_homepage);
        $this->assertNull($collection->remove_from_homepage);
    }
}
