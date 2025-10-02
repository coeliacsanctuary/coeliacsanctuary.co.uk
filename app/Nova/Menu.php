<?php

declare(strict_types=1);

namespace App\Nova;

use App\Enums\Shop\OrderState;
use App\Models\Comments\Comment;
use App\Models\EateryAiDescription;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryReport;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EaterySuggestedEdit;
use App\Models\Shop\ShopOrder;
use App\Nova\Dashboards\Main;
use App\Nova\Dashboards\Shop;
use App\Nova\Resources\EatingOut\Areas;
use App\Nova\Resources\EatingOut\Counties;
use App\Nova\Resources\EatingOut\Eateries;
use App\Nova\Resources\EatingOut\EateryAiDescriptionResource;
use App\Nova\Resources\EatingOut\EaterySearch;
use App\Nova\Resources\EatingOut\MyPlaces;
use App\Nova\Resources\EatingOut\NationwideEateries;
use App\Nova\Resources\EatingOut\PlaceRecommendations;
use App\Nova\Resources\EatingOut\PlaceReports;
use App\Nova\Resources\EatingOut\Reviews;
use App\Nova\Resources\EatingOut\SuggestedEdits;
use App\Nova\Resources\EatingOut\Towns;
use App\Nova\Resources\Main\AnnouncementResource;
use App\Nova\Resources\Main\Blog;
use App\Nova\Resources\Main\Collection;
use App\Nova\Resources\Main\Comments;
use App\Nova\Resources\Main\PopupResource;
use App\Nova\Resources\Main\Recipe;
use App\Nova\Resources\Main\RedirectResource;
use App\Nova\Resources\Main\SealiacOverviews;
use App\Nova\Resources\Search\SearchResource;
use App\Nova\Resources\Shop\Baskets;
use App\Nova\Resources\Shop\Categories;
use App\Nova\Resources\Shop\DiscountCode;
use App\Nova\Resources\Shop\MassDiscount;
use App\Nova\Resources\Shop\OrderReviews;
use App\Nova\Resources\Shop\Orders;
use App\Nova\Resources\Shop\OrderSourcesResource;
use App\Nova\Resources\Shop\PostagePrice;
use App\Nova\Resources\Shop\Products;
use App\Nova\Resources\Shop\TravelCardSearchHistory;
use App\Nova\Resources\Shop\TravelCardSearchTerms;
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
        $reportsCount = EateryReport::query()->where('completed', false)->where('ignored', false)->count();
        $myPlacesCount = EateryRecommendation::query()->where('email', 'alisondwheatley@gmail.com')->where('completed', false)->where('ignored', false)->count();
        $recommendationsCount = EateryRecommendation::query()->where('email', '!=', 'alisondwheatley@gmail.com')->where('completed', false)->where('ignored', false)->count();
        $suggestedEditsCount = EaterySuggestedEdit::query()->where('rejected', false)->where('accepted', false)->count();
        $eateryAiDescriptionsCount = EateryAiDescription::query()->count();

        $basketsCount = ShopOrder::query()->where('state_id', OrderState::BASKET)->count();
        $ordersCount = ShopOrder::query()->whereIn('state_id', [OrderState::PAID, OrderState::READY])->count();

        Nova::mainMenu(fn (Request $request) => [
            MenuSection::make('Dashboards', [
                MenuItem::dashboard(Main::class),
                MenuItem::dashboard(Shop::class),
            ])->icon('chart-bar'),

            MenuItem::externalLink('Mailcoach', config('mail.mailers.mailcoach.url'))->openInNewTab(),

            MenuSection::make('Main Site', [
                MenuItem::resource(Blog::class),
                MenuItem::resource(Recipe::class),
                MenuItem::resource(Collection::class),
                MenuItem::resource(Comments::class)->withBadgeIf(fn () => (string) $commentsCount, 'danger', fn () => $commentsCount > 0),
                MenuItem::resource(PopupResource::class),
                MenuItem::resource(AnnouncementResource::class),
                MenuItem::resource(RedirectResource::class),
                MenuItem::resource(SealiacOverviews::class),
            ])->icon('home'),

            MenuSection::make('Eating Out', [
                MenuGroup::make('Locations', [
                    MenuItem::resource(Eateries::class),
                    MenuItem::resource(NationwideEateries::class),
                    MenuItem::resource(Counties::class),
                    MenuItem::resource(Towns::class),
                    MenuItem::resource(Areas::class),
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

                MenuGroup::make('Misc', [
                    MenuItem::resource(EateryAiDescriptionResource::class)->withBadgeIf(fn () => (string) $eateryAiDescriptionsCount, 'danger', fn () => $eateryAiDescriptionsCount > 0),
                ]),

                MenuGroup::make('Imports', [
                    MenuItem::make('Nationwide Branch Import')->path('/wte-nationwide-branch-import'),
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
                    MenuItem::resource(OrderSourcesResource::class),
                ]),

                MenuGroup::make('Search', [
                    MenuItem::resource(TravelCardSearchHistory::class),
                    MenuItem::resource(TravelCardSearchTerms::class),
                ]),
            ])->icon('shopping-bag'),
        ]);
    }
}
