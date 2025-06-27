<?php

declare(strict_types=1);

namespace App\Schema;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryReviewImage;
use App\Models\EatingOut\EateryTown;
use Illuminate\Support\Str;
use Spatie\SchemaOrg\Organization;
use Spatie\SchemaOrg\Restaurant;
use Spatie\SchemaOrg\Review;
use Spatie\SchemaOrg\Schema;

class EaterySchema
{
    protected Restaurant $schema;

    public static function make(Eatery $eatery): Restaurant
    {
        return (new self($eatery))->toSchema();
    }

    public function __construct(protected Eatery $eatery)
    {
        $this->schema = Schema::restaurant();
    }

    public function toSchema(): Restaurant
    {
        $this->configureBaseSchema();
        $this->addOptionalEateryInformation();
        $this->addAdminReview();
        $this->addCustomerReviews();

        return $this->schema;
    }

    protected function configureBaseSchema(): void
    {
        /** @var EateryTown $town */
        $town = $this->eatery->town;

        /** @var EateryCounty $county */
        $county = $this->eatery->county;

        $this->schema
            ->name($this->eatery->name)
            ->description($this->eatery->info)
            ->address(
                Schema::postalAddress()
                    ->addressLocality($town->town)
                    ->addressRegion($county->county)
            )
            ->servesCuisine('Gluten Free')
            ->latitude($this->eatery->lat)
            ->longitude($this->eatery->lng)
            ->url($this->eatery->absoluteLink())
            ->mainEntityOfPage(Schema::webPage()->identifier($this->eatery->absoluteLink()))
            ->publishingPrinciples(Schema::creativeWork()->name('Coeliac Sanctuary')->url(config('app.url')));
    }

    protected function addOptionalEateryInformation(): void
    {
        if ($this->eatery->average_rating && $this->eatery->reviews_count) {
            $this->schema->aggregateRating(
                Schema::aggregateRating()
                    ->bestRating(5)
                    ->worstRating(1)
                    ->ratingValue($this->eatery->average_rating)
                    ->ratingCount($this->eatery->reviews_count)
            );
        }

        if ($this->eatery->average_expense) {
            $this->schema->priceRange(Str::of('£')->repeat((int) $this->eatery->average_expense['value'])->toString());
        }

        if ($this->eatery->phone) {
            $this->schema->telephone($this->eatery->phone);
        }

        if ($this->eatery->website) {
            $this->schema->url([$this->eatery->website, $this->eatery->absoluteLink()]);
        }

        if ($this->eatery->gf_menu_link) {
            /** @phpstan-ignore-next-line  */
            $this->schema->haMenu($this->eatery->gf_menu_link);
        }
    }

    protected function addAdminReview(): void
    {
        if ( ! $this->eatery->adminReview) {
            return;
        }

        $this->schema->review($this->makeReview($this->eatery->adminReview, true));
    }

    protected function addCustomerReviews(): void
    {
        /** @phpstan-ignore-next-line  */
        if ( ! $this->eatery->reviews || $this->eatery->reviews->isEmpty()) {
            return;
        }

        $validReviews = $this->eatery->reviews->where('admin_review', false)->where('review', '!=', '')->values();

        if ($validReviews->isEmpty()) {
            return;
        }

        $this->schema->reviews($validReviews->map(fn (EateryReview $review) => $this->makeReview($review))->toArray());
    }

    protected function makeReview(EateryReview $review, bool $isAdmin = false): Review
    {
        $reviewSchema = Schema::review()
            ->reviewBody($review->review)
            ->reviewRating(
                Schema::rating()
                    ->bestRating(5)
                    ->worstRating(1)
                    ->ratingValue($review->rating)
                    ->author($isAdmin ? $this->getCoeliacOrganisation() : Schema::person()->name((string)$review->name))
            );

        /** @phpstan-ignore-next-line  */
        if ($review->images && $review->images->isNotEmpty()) {
            $reviewSchema->image(
                $review->images->map(fn (EateryReviewImage $image) => Schema::imageObject()->url($image->path))->toArray()
            );
        }

        return $reviewSchema;
    }

    protected function getCoeliacOrganisation(): Organization
    {
        return Schema::organization()->name('Coeliac Sanctuary')->url(config('app.url'));
    }
}
