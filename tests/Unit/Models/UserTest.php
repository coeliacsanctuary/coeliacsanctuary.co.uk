<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    #[Test]
    public function everyUserCanAccessTheAdminPanel(): void
    {
        Filament::setCurrentPanel('admin');

        $this->assertTrue($this->create(User::class)->canAccessPanel(Filament::getCurrentPanel()));
    }
}
