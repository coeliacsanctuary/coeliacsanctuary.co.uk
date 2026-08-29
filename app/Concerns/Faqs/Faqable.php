<?php

declare(strict_types=1);

namespace App\Concerns\Faqs;

use App\Models\Faqs\Faq;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @template T of Model
 *
 * @mixin Model
 */
trait Faqable
{
    /** @return MorphMany<Faq, T> */
    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable');
    }
}
