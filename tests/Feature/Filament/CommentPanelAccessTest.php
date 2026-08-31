<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\MainSite\Comments\CommentResource;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentPanelAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
    }

    #[Test]
    public function guestsAreSentToTheLoginPage(): void
    {
        $this->get(CommentResource::getUrl('index'))->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    public function signedInUsersCanOpenTheCommentList(): void
    {
        $this->actingAs($this->create(User::class))
            ->get(CommentResource::getUrl('index'))
            ->assertOk();
    }
}
