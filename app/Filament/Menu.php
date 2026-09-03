<?php

declare(strict_types=1);

namespace App\Filament;

use App\Filament\Pages\RefreshAdsTxt;
use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\EateryAreas\EateryAreaResource;
use App\Filament\Resources\EatingOut\EateryCounties\EateryCountyResource;
use App\Filament\Resources\EatingOut\EateryReports\EateryReportResource;
use App\Filament\Resources\EatingOut\EateryReviews\EateryReviewResource;
use App\Filament\Resources\EatingOut\EateryTowns\EateryTownResource;
use App\Filament\Resources\EatingOut\NationwideBranches\NationwideBranchResource;
use App\Filament\Resources\MainSite\Announcements\AnnouncementResource;
use App\Filament\Resources\MainSite\Blogs\BlogResource;
use App\Filament\Resources\MainSite\Collections\CollectionResource;
use App\Filament\Resources\MainSite\Comments\CommentResource;
use App\Filament\Resources\MainSite\Popups\PopupResource;
use App\Filament\Resources\MainSite\Recipes\RecipeResource;
use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use App\Filament\Resources\MainSite\SealiacOverviews\SealiacOverviewResource;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;

class Menu
{
    public static function make(NavigationBuilder $builder): NavigationBuilder
    {
        return $builder->groups([
            NavigationGroup::make('Main Site')
                ->items([
                    ...BlogResource::getNavigationItems(),
                    ...RecipeResource::getNavigationItems(),
                    ...CollectionResource::getNavigationItems(),
                    ...CommentResource::getNavigationItems(),
                    ...PopupResource::getNavigationItems(),
                    ...AnnouncementResource::getNavigationItems(),
                    ...RedirectResource::getNavigationItems(),
                    ...SealiacOverviewResource::getNavigationItems(),
                ]),

            NavigationGroup::make('Tools')
                ->items([
                    ...RefreshAdsTxt::getNavigationItems(),
                ]),

            NavigationGroup::make('Eating Out Locations')
                ->items([
                    ...EateryResource::getNavigationItems(),
                    ...NationwideBranchResource::getNavigationItems(),
                    ...EateryCountyResource::getNavigationItems(),
                    ...EateryTownResource::getNavigationItems(),
                    ...EateryAreaResource::getNavigationItems(),
                ]),

            NavigationGroup::make('Eating Out Feedback')
                ->items([
                    ...EateryReviewResource::getNavigationItems(),
                    ...EateryReportResource::getNavigationItems(),
                ]),
        ]);
    }
}
