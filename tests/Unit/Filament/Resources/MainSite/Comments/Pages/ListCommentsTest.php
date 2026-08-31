<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Comments\Pages;

use App\Filament\Resources\MainSite\Comments\Pages\ListComments;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListCommentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsTheCommentList(): void
    {
        Livewire::test(ListComments::class)->assertOk();
    }

    #[Test]
    public function itDoesNotOfferAButtonToCreateAComment(): void
    {
        Livewire::test(ListComments::class)->assertActionDoesNotExist(CreateAction::class);
    }
}
