<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Resources\EatingOut\Areas;
use App\Nova\Resources\EatingOut\Counties;
use App\Nova\Resources\EatingOut\Countries;
use App\Nova\Resources\EatingOut\Eateries;
use App\Nova\Resources\EatingOut\EaterySearch;
use App\Nova\Resources\EatingOut\EaterySearchHistory;
use App\Nova\Resources\EatingOut\MyPlaces;
use App\Nova\Resources\EatingOut\NationwideBranches;
use App\Nova\Resources\EatingOut\NationwideEateries;
use App\Nova\Resources\EatingOut\PlaceRecommendations;
use App\Nova\Resources\EatingOut\PlaceReports;
use App\Nova\Resources\EatingOut\ReviewImage;
use App\Nova\Resources\EatingOut\Reviews;
use App\Nova\Resources\EatingOut\SuggestedEdits;
use App\Nova\Resources\EatingOut\Towns;
use App\Nova\Resources\Main\AnnouncementResource;
use App\Nova\Resources\Main\Blog;
use App\Nova\Resources\Main\BlogTag;
use App\Nova\Resources\Main\Collection;
use App\Nova\Resources\Main\CollectionItem;
use App\Nova\Resources\Main\CommentReply;
use App\Nova\Resources\Main\Comments;
use App\Nova\Resources\Main\PopupResource;
use App\Nova\Resources\Main\Recipe;
use App\Nova\Resources\Main\RecipeAllergens;
use App\Nova\Resources\Main\RecipeNutritionalInformation;
use App\Nova\Resources\Main\RedirectResource;
use App\Nova\Resources\Main\SealiacOverviews;
use App\Nova\Resources\Search\SearchAiResponseResource;
use App\Nova\Resources\Search\SearchHistoryResource;
use App\Nova\Resources\Search\SearchResource;
use App\Nova\Resources\Shop\Baskets;
use App\Nova\Resources\Shop\Categories;
use App\Nova\Resources\Shop\Customer;
use App\Nova\Resources\Shop\DiscountCode;
use App\Nova\Resources\Shop\MassDiscount;
use App\Nova\Resources\Shop\OrderItem;
use App\Nova\Resources\Shop\OrderReviewItem;
use App\Nova\Resources\Shop\OrderReviews;
use App\Nova\Resources\Shop\Orders;
use App\Nova\Resources\Shop\OrderSourcesResource;
use App\Nova\Resources\Shop\Payment;
use App\Nova\Resources\Shop\PaymentRefund;
use App\Nova\Resources\Shop\PostageArea;
use App\Nova\Resources\Shop\PostagePrice;
use App\Nova\Resources\Shop\ProductPrice;
use App\Nova\Resources\Shop\Products;
use App\Nova\Resources\Shop\ProductVariant;
use App\Nova\Resources\Shop\ShippingAddress;
use App\Nova\Resources\Shop\ShippingMethod;
use App\Nova\Resources\Shop\TravelCardSearchHistory;
use App\Nova\Resources\Shop\TravelCardSearchTerms;
use Laravel\Nova\Nova;

/**
 * @codeCoverageIgnore
 */
class ResourceRegistrar
{
    public static function handle(): void
    {
        Nova::resources([
            // Main
            Blog::class,
            BlogTag::class,
            Collection::class,
            CollectionItem::class,
            Recipe::class,
            RecipeAllergens::class,
            RecipeNutritionalInformation::class,
            Comments::class,
            CommentReply::class,
            PopupResource::class,
            AnnouncementResource::class,
            RedirectResource::class,

            // Eating Out
            Eateries::class,
            NationwideEateries::class,
            NationwideBranches::class,
            Countries::class,
            Counties::class,
            Towns::class,
            Areas::class,
            Reviews::class,
            ReviewImage::class,
            MyPlaces::class,
            PlaceRecommendations::class,
            PlaceReports::class,
            SuggestedEdits::class,
            EaterySearch::class,
            EaterySearchHistory::class,
            SealiacOverviews::class,

            // Search
            SearchResource::class,
            SearchAiResponseResource::class,
            SearchHistoryResource::class,

            // Shop Sales
            Baskets::class,
            Orders::class,
            Payment::class,
            PaymentRefund::class,
            OrderItem::class,

            // Shop Inventory
            Categories::class,
            Products::class,
            ShippingMethod::class,
            ProductPrice::class,
            ProductVariant::class,

            // Shop Customers
            Customer::class,
            ShippingAddress::class,

            // Shop Admin
            DiscountCode::class,
            PostagePrice::class,
            PostageArea::class,
            MassDiscount::class,
            OrderReviews::class,
            OrderReviewItem::class,
            OrderSourcesResource::class,

            // Shop Search
            TravelCardSearchHistory::class,
            TravelCardSearchTerms::class,
        ]);
    }
}
