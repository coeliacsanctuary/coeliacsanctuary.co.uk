<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AskSealiacChatMessage extends Model
{
    /** @return BelongsTo<AskSealiacChat, $this> */
    public function askSealiacChat(): BelongsTo
    {
        return $this->belongsTo(AskSealiacChat::class);
    }

    protected function casts(): array
    {
        return [
            'tool_uses' => 'array',
        ];
    }
}
