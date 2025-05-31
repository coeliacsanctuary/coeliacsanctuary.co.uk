<?php

declare(strict_types=1);

use App\Http\Controllers\About\IndexController as AboutIndexController;
use App\Http\Controllers\Comments\GetController;
use App\Http\Controllers\Contact\IndexController as ContactIndexController;
use App\Http\Controllers\Contact\StoreController as ContactStoreController;
use App\Http\Controllers\CookiePolicy\IndexController as CookiePolicyIndexController;
use App\Http\Controllers\Feed\IndexController as FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Newsletter\StoreController as NewsletterStoreController;
use App\Http\Controllers\Popup\Activity\StoreController as PopupActivityStoreController;
use App\Http\Controllers\PrivacyPolicy\IndexController as PrivacyPolicyIndexController;
use App\Http\Controllers\Shop\TravelCards\IndexController as ShopTravelCardsLandingPageIndexController;
use App\Http\Controllers\SiteMap\IndexController as SiteMapController;
use App\Http\Controllers\Static\Map\GetController as StaticMapGetController;
use App\Http\Controllers\TermsOfUse\IndexController as TermsOfUseIndexController;
use App\Http\Controllers\WorkWithUs\IndexController as WorkWithUsIndexController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::prefix('blog')->group(base_path('routes/blogs/web.php'));
Route::prefix('collection')->group(base_path('routes/collections/web.php'));
Route::prefix('recipe')->group(base_path('routes/recipes/web.php'));
Route::prefix('search')->group(base_path('routes/search/web.php'));
Route::prefix('shop')->group(base_path('routes/shop/web.php'));
Route::prefix('')->group(base_path('routes/eating-out/web.php'));

Route::get('/', HomeController::class)->name('home');

Route::get('about', AboutIndexController::class)->name('about');

Route::post('comments', GetController::class)->name('comments.create');

Route::get('contact', ContactIndexController::class)->name('contact.index');
Route::post('contact', ContactStoreController::class)
    ->middleware(HandlePrecognitiveRequests::class)
    ->name('contact.store');

Route::get('cookie-policy', CookiePolicyIndexController::class)->name('cookie-policy');
Route::get('privacy-policy', PrivacyPolicyIndexController::class)->name('privacy-policy');
Route::get('terms-of-use', TermsOfUseIndexController::class)->name('terms-of-use');
Route::get('work-with-us', WorkWithUsIndexController::class)->name('work-with-us');

Route::get('gluten-free-travel-translation-cards', ShopTravelCardsLandingPageIndexController::class)->name('shop.travel-cards.landing-page');

Route::post('popup/{popup}', PopupActivityStoreController::class)->name('popup.activity.store');

Route::post('newsletter', NewsletterStoreController::class)
    ->middleware(HandlePrecognitiveRequests::class)
    ->name('newsletter.store');

Route::get('feed', FeedController::class)->name('feed');
Route::get('sitemap.xml', SiteMapController::class)->name('sitemap');

Route::get('static/map/{latlng}', StaticMapGetController::class)
    ->name('static.map')
    ->where('latlng', '^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$');
