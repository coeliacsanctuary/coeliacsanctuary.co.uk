<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Contracts\Faqs\HasFaqs;
use App\Models\Faqs\Faq;

class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'question' => $this->faker->sentence,
            'answer' => $this->faker->paragraph,
        ];
    }

    public function on(HasFaqs $faqable): self
    {
        return $this->state(fn (array $attributes) => [
            'faqable_type' => $faqable::class,
            'faqable_id' => $faqable->id,
        ]);
    }
}
