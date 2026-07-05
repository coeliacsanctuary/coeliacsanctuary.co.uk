<?php

declare(strict_types=1);

namespace App\Contracts\Faqs;

use App\Models\Faqs\Faq;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/** @template T of Model */
interface HasFaqs
{
    /** @return MorphMany<Faq, T> */
    public function faqs(): MorphMany;
}
