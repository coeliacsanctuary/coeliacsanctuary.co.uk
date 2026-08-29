<?php

declare(strict_types=1);

namespace App\Schema;

use App\Models\Faqs\Faq;
use Illuminate\Support\Collection;
use Spatie\SchemaOrg\Answer;
use Spatie\SchemaOrg\FAQPage;
use Spatie\SchemaOrg\Question;

class FaqSchema
{
    /** @param Collection<int, Faq> $faqs */
    public static function make(Collection $faqs): FAQPage
    {
        return (new FAQPage())->mainEntity($faqs->map(fn (Faq $faq): Question => (new Question())
            ->name($faq->question)
            ->acceptedAnswer((new Answer())->text($faq->answer)))->all());
    }
}
