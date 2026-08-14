<?php

declare(strict_types=1);

namespace App\Models\Faqs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Faq extends Model
{
    /** @return MorphTo<Model, $this> */
    public function faqable(): MorphTo
    {
        return $this->morphTo();
    }
}
