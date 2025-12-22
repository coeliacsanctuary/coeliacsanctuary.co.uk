<?php

declare(strict_types=1);

namespace App\Mailables\EatingOut;

use App\Infrastructure\MjmlMessage;
use App\Mailables\BaseMailable;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Support\Collection;

class EateryRecommendationAlreadyExistsMailable extends BaseMailable
{
    public function __construct(protected Eatery $eatery, protected ?NationwideBranch $branch, protected EateryRecommendation $recommendation, ?string $emailKey = null)
    {
        parent::__construct($emailKey);
    }

    /** @return Collection<int, Eatery> */
    protected function nearbyEateries(): Collection
    {
        /** @var EateryTown | null $town */
        $town = $this->eatery->town;

        if ($this->branch) {
            $town = $this->branch->town;
        }

        if ( ! $town) {
            return collect();
        }

        return $town
            ->eateries()
            ->chaperone('town')
            ->with(['area', 'county', 'country'])
            ->whereNot('id', $this->eatery->id)
            ->inRandomOrder()
            ->take(5)
            ->get();
    }

    public function toMail(): MjmlMessage
    {
        $subject = match (true) {
            $this->branch && $this->branch->name => "Your recommendation of {$this->branch->name} as part of the {$this->eatery->name} chain already exists in the Coeliac Sanctuary eating out guide!",
            (bool) $this->branch => "Your recommendation of the {$this->branch->town?->town} branch of {$this->eatery->name} already exists in the Coeliac Sanctuary eating out guide!",
            default => "Your recommendation of {$this->eatery->name} already exists in the Coeliac Sanctuary eating out guide!",
        };

        return MjmlMessage::make()
            ->subject($subject)
            ->mjml('mailables.mjml.eating-out.recommended-eatery-already-exists', $this->baseData([
                'eatery' => $this->eatery,
                'branch' => $this->branch,
                'recommendation' => $this->recommendation,
                'nearbyEateries' => $this->nearbyEateries(),
                'reason' => 'to let you know that the place you suggested already exists in the Coeliac Sanctuary eating out guide.',
                'email' => $this->recommendation->email,
            ]));
    }
}
