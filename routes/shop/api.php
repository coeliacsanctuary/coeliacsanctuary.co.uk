<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Shop\AddressSearch\ShowController as AddressSearchShowController;
use App\Http\Controllers\Api\Shop\AddressSearch\StoreController as AddressSearchStoreController;
use App\Http\Controllers\Api\Shop\Products\GetController as ShopProductGetController;
use App\Http\Controllers\Api\Shop\Products\IndexController as ShopProductIndexController;
use App\Http\Controllers\Api\Shop\Products\SealiacOverview\GetController as ProductSealiacOverviewGetController;
use App\Http\Controllers\Api\Shop\TravelCardSearch\GetController as TravelCardSearchGetController;
use App\Http\Controllers\Api\Shop\TravelCardSearch\StoreController as TravelCardSearchStoreController;
use Illuminate\Support\Facades\Route;

Route::post('address-search', AddressSearchStoreController::class)->name('api.shop.address-search');
Route::get('address-search/{id}', AddressSearchShowController::class)->name('api.shop.address-search.get');

Route::post('travel-card-search', TravelCardSearchStoreController::class)->name('api.shop.travel-card-search.store');
Route::get('travel-card-search/{travelCardSearchTerm}', TravelCardSearchGetController::class)->name('api.shop.travel-card-search.get');

Route::get('products', ShopProductIndexController::class)->name('api.shop.products.index');
Route::get('products/{product}', ShopProductGetController::class)->name('api.shop.products.show');

Route::get('products/{product:id}/sealiac', ProductSealiacOverviewGetController::class)->name('api.shop.products.sealiac.get');
