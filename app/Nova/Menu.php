<?php

declare(strict_types=1);

namespace App\Nova;

use App\Enums\Shop\OrderState;
use App\Models\Comments\Comment;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryReport;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EaterySuggestedEdit;
use App\Models\Shop\ShopOrder;
use App\Nova\Dashboards\Main;
use App\Nova\Resources\EatingOut\Counties;
use App\Nova\Resources\EatingOut\Eateries;
use App\Nova\Resources\EatingOut\EaterySearch;
use App\Nova\Resources\EatingOut\MyPlaces;
use App\Nova\Resources\EatingOut\NationwideEateries;
use App\Nova\Resources\EatingOut\PlaceRecommendations;
use App\Nova\Resources\EatingOut\PlaceReports;
use App\Nova\Resources\EatingOut\Reviews;
use App\Nova\Resources\EatingOut\SuggestedEdits;
use App\Nova\Resources\EatingOut\Towns;
use App\Nova\Resources\Main\Blog;
use App\Nova\Resources\Main\Collection;
use App\Nova\Resources\Main\Comments;
use App\Nova\Resources\Main\Recipe;
use App\Nova\Resources\Search\SearchResource;
use App\Nova\Resources\Shop\Baskets;
use App\Nova\Resources\Shop\Categories;
use App\Nova\Resources\Shop\DiscountCode;
use App\Nova\Resources\Shop\MassDiscount;
use App\Nova\Resources\Shop\OrderReviews;
use App\Nova\Resources\Shop\Orders;
use App\Nova\Resources\Shop\PostagePrice;
use App\Nova\Resources\Shop\Products;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuGroup;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;

/**
 * @codeCoverageIgnore
 */
class Menu
{
    public static function build(): void
    {
        $commentsCount = Comment::withoutGlobalScopes()->where('approved', false)->count();
        $reviewCount = EateryReview::withoutGlobalScopes()->where('approved', false)->count();
        $reportsCount = EateryReport::query()->where('completed', false)->count();
        $myPlacesCount = EateryRecommendation::query()->where('email', 'contact@coeliacsanctuary.co.uk')->where('completed', false)->count();
        $recommendationsCount = EateryRecommendation::query()->where('email', '!=', 'contact@coeliacsanctuary.co.uk')->where('completed', false)->count();
        $suggestedEditsCount = EaterySuggestedEdit::query()->where('rejected', false)->where('accepted', false)->count();

        $basketsCount = ShopOrder::query()->where('state_id', OrderState::BASKET)->count();
        $ordersCount = ShopOrder::query()->where('state_id', OrderState::PAID)->count();

        Nova::mainMenu(fn (Request $request) => [
            MenuSection::make('Dashboards', [
                MenuItem::dashboard(Main::class),
            ])->icon('chart-bar'),

            MenuSection::make('Main Site', [
                MenuItem::resource(Blog::class),
                MenuItem::resource(Recipe::class),
                MenuItem::resource(Collection::class),
                MenuItem::resource(Comments::class)->withBadgeIf(fn () => (string) $commentsCount, 'danger', fn () => $commentsCount > 0),
            ])->icon('home'),

            MenuSection::make('Eating Out', [
                MenuGroup::make('Locations', [
                    MenuItem::resource(Eateries::class),
                    MenuItem::resource(NationwideEateries::class),
                    MenuItem::resource(Counties::class),
                    MenuItem::resource(Towns::class),
                ]),

                MenuGroup::make('Feedback', [
                    MenuItem::resource(Reviews::class)->withBadgeIf(fn () => (string) $reviewCount, 'danger', fn () => $reviewCount > 0),
                    MenuItem::resource(PlaceReports::class)->withBadgeIf(fn () => (string) $reportsCount, 'danger', fn () => $reportsCount > 0),
                    MenuItem::resource(SuggestedEdits::class)->withBadgeIf(fn () => (string) $suggestedEditsCount, 'danger', fn () => $suggestedEditsCount > 0),
                ]),

                MenuGroup::make('Recommendations', [
                    MenuItem::resource(MyPlaces::class)->withBadgeIf(fn () => (string) $myPlacesCount, 'danger', fn () => $myPlacesCount > 0),
                    MenuItem::resource(PlaceRecommendations::class)->withBadgeIf(fn () => (string) $recommendationsCount, 'danger', fn () => $recommendationsCount > 0),
                ]),

                MenuGroup::make('Search', [
                    MenuItem::resource(EaterySearch::class),
                ]),
            ])->icon('map'),

            MenuSection::make('Search', [
                MenuItem::resource(SearchResource::class),
            ])->icon('search'),

            MenuSection::make('Shop', [
                MenuGroup::make('Sales', [
                    MenuItem::resource(Baskets::class)->withBadgeIf(fn () => (string) $basketsCount, 'danger', fn () => $basketsCount > 0),
                    MenuItem::resource(Orders::class)->withBadgeIf(fn () => (string) $ordersCount, 'danger', fn () => $ordersCount > 0),
                    MenuItem::make('Daily Stock')->path('/shop-daily-stock'),
                    MenuItem::resource(OrderReviews::class),
                ]),

                MenuGroup::make('Inventory', [
                    MenuItem::resource(Categories::class),
                    MenuItem::resource(Products::class),
                ]),

                MenuGroup::make('Admin', [
                    MenuItem::resource(DiscountCode::class),
                    MenuItem::resource(PostagePrice::class),
                    MenuItem::resource(MassDiscount::class),
                ]),
            ])->icon('shopping-bag'),
        ]);
    }
}
