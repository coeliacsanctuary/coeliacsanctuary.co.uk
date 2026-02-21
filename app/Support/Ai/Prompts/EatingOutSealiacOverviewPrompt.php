<?php

declare(strict_types=1);

namespace App\Support\Ai\Prompts;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Support\Collection;

class EatingOutSealiacOverviewPrompt
{
    protected Eatery $eatery;

    protected ?NationwideBranch $branch = null;

    public function handle(Eatery $eatery, ?NationwideBranch $branch = null): string
    {
        $this->eatery = $eatery;
        $this->branch = $branch;

        $this->eatery->loadMissing(['area', 'town', 'county', 'country', 'adminReview', 'features', 'reviews']);
        $this->branch?->loadMissing(['area', 'town', 'county', 'country']);

        return view('prompts.eating-out-sealiac-overview', [
            'eatery' => $this->eatery,
            'branch' => $this->branch,
            'eateryName' => $this->eateryName(),
            'eateryLocation' => $this->eateryLocation(),
            'averageExpense' => $this->averageExpense(),
            'adminReview' => $this->adminReview(),
            'visitorReviews' => $this->reviews(),
        ])->render();
    }

    protected function adminReview(): ?EateryReview
    {
        if ( ! $this->eatery->adminReview) {
            return null;
        }

        if ($this->branch && $this->eatery->adminReview->nationwide_branch_id !== $this->branch->id) {
            return null;
        }

        return $this->eatery->adminReview;
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

        if ($this->eatery->county?->slug === 'nationwide') {
            return 'Nationwide Chain';
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
