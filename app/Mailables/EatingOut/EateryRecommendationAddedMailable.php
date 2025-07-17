<?php

declare(strict_types=1);

namespace App\Mailables\EatingOut;

use App\Infrastructure\MjmlMessage;
use App\Mailables\BaseMailable;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryTown;
use Illuminate\Support\Collection;

class EateryRecommendationAddedMailable extends BaseMailable
{
    public function __construct(protected Eatery $eatery, protected EateryRecommendation $recommendation, ?string $emailKey = null)
    {
        parent::__construct($emailKey);
    }

    /** @return Collection<int, Eatery> */
    protected function nearbyEateries(): Collection
    {
        /** @var EateryTown | null $town */
        $town = $this->eatery->town;

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
        return MjmlMessage::make()
            ->subject("We’ve added {$this->eatery->name} to the Coeliac Sanctuary eating out guide!")
            ->mjml('mailables.mjml.eating-out.recommended-eatery-added', $this->baseData([
                'eatery' => $this->eatery,
                'recommendation' => $this->recommendation,
                'nearbyEateries' => $this->nearbyEateries(),
                'reason' => 'to let you know that we\'ve added your place recommendation to the Coeliac Sanctuary eating out guide.',
                'email' => $this->recommendation->email,
            ]));
    }
}
