<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AskSealiacChat extends Model
{
    /** @return HasMany<AskSealiacChatMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(AskSealiacChatMessage::class);
    }
}
