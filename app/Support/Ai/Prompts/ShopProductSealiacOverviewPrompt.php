<?php

declare(strict_types=1);

namespace App\Support\Ai\Prompts;

use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use Illuminate\Support\Collection;

class ShopProductSealiacOverviewPrompt
{
    protected ShopProduct $product;

    /** @var Collection<int, string> */
    protected Collection $promptSections;

    public function handle(ShopProduct $product): string
    {
        $this->product = $product;
        $this->promptSections = collect();

        $this->preparePromptIntroduction();
        $this->addBaseProductInformation();
        $this->addCustomerReviews();

        return $this->promptSections->join("\n\n");
    }

    protected function preparePromptIntroduction(): void
    {
        $this->promptSections->push(<<<'PROMPT'
        Your role is "Sealiac the Seal", the mascot of a website called Coeliac Sanctuary.

        The website sells various products through its online store such as travel cards for various countries that explain
        coeliac disease in the native language, gluten free sticker labels, and more.

        Your job is to give your thoughts and feelings on this product in the online shop, based on previous customer reviews,
        and to encourage others to purchase.

        Please use a friendly, fun tone, but not a tone as though you have purchased the card your self.

        If you response includes the phrase gluten free, please spell it without an hyphen, just 'gluten free'

        Please return nothing else except your thoughts and feelings in 1 or 2 SHORT paragraphs or no more than 50 words each.

        To emphasise, **one or two paragraphs, of no more than 50 words each** is enough.
        PROMPT);
    }

    protected function addBaseProductInformation(): void
    {
        $sections = [
            '## Product Details',
            "Product Title: {$this->product->title}",
            "Product Description: {$this->product->long_description}",
        ];

        if ($this->product->average_rating) {
            $sections[] = "Average Rating: {$this->product->average_rating} out of 5 stars";
            $sections[] = "Total reviews: {$this->product->reviews->count()}";
        }

        $this->promptSections->push(collect($sections)->join("\n"));
    }

    protected function addCustomerReviews(): void
    {
        if ($this->product->reviews->isEmpty()) {
            return;
        }

        $reviews = $this->product->reviews
            ->map($this->formatReview(...))
            ->map(fn (array $review) => [
                ...$review,
                '------',
            ])
            ->flatten()
            ->toArray();

        $sections = [
            '## Previous Purchaser Reviews',
            '',
            ...$reviews,
        ];

        $this->promptSections->push(collect($sections)->join("\n"));
    }

    protected function formatReview(ShopOrderReviewItem $review): array
    {
        return [
            "Overall Rating: {$review->rating} out of 5 stars.",
            '',
            $review->review,
            '',
            "Published: {$review->created_at}",
        ];
    }
}
