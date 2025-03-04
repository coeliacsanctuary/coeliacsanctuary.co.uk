<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailcoachSchedule extends Model
{
    protected $table = 'mailcoach_schedule';

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }
}
