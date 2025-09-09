<?php

declare(strict_types=1);

use App\Http\Controllers\Shop\Basket\DestroyController as BasketDestroyController;
use App\Http\Controllers\Shop\Basket\Discount\DestroyController as DiscountDestroyController;
use App\Http\Controllers\Shop\Basket\Reopen\IndexController as ReopenBasketIndexController;
use App\Http\Controllers\Shop\Basket\ShowController as BasketShowController;
use App\Http\Controllers\Shop\Basket\StoreController as BasketStoreController;
use App\Http\Controllers\Shop\Basket\UpdateController as BasketUpdateController;
use App\Http\Controllers\Shop\Category\ShowController as CategoryShowController;
use App\Http\Controllers\Shop\DownloadMyProducts\GetController as DownloadMyProductsController;
use App\Http\Controllers\Shop\IndexController;
use App\Http\Controllers\Shop\Order\DestroyController as OrderDestroyController;
use App\Http\Controllers\Shop\Order\Done\ShowController as OrderDoneShowController;
use App\Http\Controllers\Shop\Order\StoreController as OrderStoreController;
use App\Http\Controllers\Shop\Product\ShowController as ProductShowController;
use App\Http\Controllers\Shop\ReviewMyOrder\ShowController as ReviewMyOrderShowController;
use App\Http\Controllers\Shop\ReviewMyOrder\StoreController as ReviewMyOrderStoreController;
use App\Http\Controllers\Shop\ReviewMyOrder\Thanks\ShowController as ReviewMyOrderThanksShowController;
use App\Http\Middleware\ShopBasketTokenMiddleware;
use App\Http\Middleware\ShopHasBasketMiddleware;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::middleware(ShopBasketTokenMiddleware::class)->group(function (): void {
    Route::get('/', IndexController::class)->name('shop.index');

    Route::prefix('/basket')->group(function (): void {
        Route::get('/', BasketShowController::class)->name('shop.basket.checkout');

        Route::post('/', OrderStoreController::class)
            ->middleware(ShopHasBasketMiddleware::class)
            ->name('shop.order.complete');

        Route::put('/', BasketStoreController::class)
            ->middleware(HandlePrecognitiveRequests::class)
            ->name('shop.basket.add');

        Route::patch('/', BasketUpdateController::class)->name('shop.basket.patch');
        Route::delete('/', OrderDestroyController::class)->name('shop.basket.revert');

        Route::get('/done', OrderDoneShowController::class)->name('shop.basket.done');

        Route::delete('/discount', DiscountDestroyController::class)->name('shop.basket.discount.remove');
        Route::delete('/{item}', BasketDestroyController::class)->name('shop.basket.remove');

        Route::get('/{basket:token}/reopen', ReopenBasketIndexController::class)
            ->name('shop.basket.reopen');
    });

    Route::get('/product/{product}', ProductShowController::class)->name('shop.product');

    Route::get('review-my-order/{invitation}', ReviewMyOrderShowController::class)
        ->middleware(['signed'])
        ->name('shop.review-order');

    Route::get('review-my-order/{invitation}/thanks', ReviewMyOrderThanksShowController::class)
        ->name('shop.review-order.thanks');

    Route::post('review-my-order/{invitation}', ReviewMyOrderStoreController::class)
        ->middleware(HandlePrecognitiveRequests::class)
        ->name('shop.review-order.store');

    Route::redirect('terms', '/terms-of-use#shop');

    Route::get('/{category}', CategoryShowController::class)->name('shop.category');

    Route::get('/download-my-products/{downloadLink}', DownloadMyProductsController::class)
        ->name('shop.download-my-products')
        ->middleware(['signed']);
});
