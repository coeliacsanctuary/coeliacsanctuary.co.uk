<?php

declare(strict_types=1);

namespace App\Support\Ai\Prompts;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Support\Collection;

class EatingOutSealiacOverviewPrompt
{
    protected Eatery $eatery;

    protected ?NationwideBranch $branch = null;

    /** @var Collection<int, string> */
    protected Collection $promptSections;

    public function handle(Eatery $eatery, ?NationwideBranch $branch = null): string
    {
        $this->eatery = $eatery;
        $this->branch = $branch;
        $this->promptSections = collect();

        $this->preparePromptIntroduction();
        $this->addBaseEateryDetails();
        $this->addAdminReviewIfAvailable();
        $this->addVisitorReviews();
        $this->addEateryFeatures();

        return $this->promptSections->join("\n\n");
    }

    protected function preparePromptIntroduction(): void
    {
        $this->promptSections->push(<<<'PROMPT'
        Your role is "Sealiac the Seal", the mascot of a website called Coeliac Sanctuary.

        Your job is to give your thoughts and feelings on visiting this gluten free eatery, and whether others should visit too,
        using the below information and reviews.

        Please use a friendly, fun tone.

        Please return nothing else except your thoughts and feelings in 2 - 3 paragraphs.
        PROMPT);
    }

    protected function addBaseEateryDetails(): void
    {
        $sections = [
            '## Eatery Details',
            "Eatery Name: {$this->eateryName()}",
            "Eatery Location: {$this->eateryLocation()}",
        ];

        if ($this->averageExpense()) {
            $sections[] = "Average Value for Money Rating: {$this->averageExpense()['label']}";
        }

        if ($this->eatery->average_rating) {
            $sections[] = "Average Rating: {$this->eatery->average_rating} out of 5 stars";
        }

        $this->promptSections->push(collect($sections)->join("\n"));
    }

    protected function addAdminReviewIfAvailable(): void
    {
        if ( ! $this->eatery->adminReview || ($this->branch && $this->eatery->adminReview->nationwide_branch_id !== $this->branch->id)) {
            return;
        }

        $adminReview = [
            '## Coeliac Sanctuary Team Reviews',
            '',
            ...$this->formatReview($this->eatery->adminReview),
        ];

        $this->promptSections->push(collect($adminReview)->join("\n"));
    }

    protected function addVisitorReviews(): void
    {
        if ($this->reviews()->isEmpty()) {
            return;
        }

        $reviews = $this->reviews()
            ->map($this->formatReview(...))
            ->map(fn (array $review) => [
                ...$review,
                '------',
            ])
            ->flatten()
            ->toArray();

        $sections = [
            '## Website Visitor Reviews',
            '',
            ...$reviews,
        ];

        $this->promptSections->push(collect($sections)->join("\n"));
    }

    protected function addEateryFeatures(): void
    {
        if ($this->eatery->features->isEmpty()) {
            return;
        }

        $features = $this->eatery->features->map(fn (EateryFeature $feature) => "- {$feature->feature}")->toArray();

        $sections = [
            '## Features of this eatery listed on our website:',
            '',
            ...$features,
        ];

        $this->promptSections->push(collect($sections)->join("\n"));
    }

    protected function formatReview(EateryReview $review): array
    {
        $sections = [];

        if ($review->service_rating) {
            $sections[] = "Service Rating: {$review->service_rating}";
        }

        if ($review->food_rating) {
            $sections[] = "Food Rating: {$review->food_rating}";
        }

        if ($review->price) {
            $sections[] = "Value for Money: {$review->price['label']}";
        }

        return [
            ...$sections,
            "Overall Rating: {$review->rating} out of 5 stars.",
            '',
            $review->review,
            '',
            "Published: {$review->created_at}",
        ];
    }

    protected function eateryName(): string
    {
        if ($this->branch && $this->branch->name) {
            return $this->branch->name;
        }

        return $this->eatery->name;
    }

    protected function eateryLocation(): string
    {
        if ($this->branch) {
            return $this->branch->full_location;
        }

        return $this->eatery->full_location;
    }

    /** @return null | array{label: string} */
    protected function averageExpense(): ?array
    {
        return $this->eatery->average_expense;
    }

    /** @return Collection<int, EateryReview> */
    protected function reviews(): Collection
    {
        return once(fn () => $this->eatery
            ->reviews
            ->filter(fn (EateryReview $review) => $review->admin_review === false && $review->review)
            ->when($this->branch, fn (Collection $reviews) => $reviews->filter(fn (EateryReview $review) => $review->nationwide_branch_id === $this->branch?->id)));
    }
}
