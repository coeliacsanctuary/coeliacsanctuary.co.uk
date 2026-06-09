<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
