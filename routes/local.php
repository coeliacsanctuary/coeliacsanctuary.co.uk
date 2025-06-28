<?php

declare(strict_types=1);

use App\Actions\OpenGraphImages\GenerateCountyOpenGraphImageAction;
use App\Actions\OpenGraphImages\GenerateEateryOpenGraphImageAction;
use App\Actions\OpenGraphImages\GenerateNationwideBranchOpenGraphImageAction;
use App\Actions\OpenGraphImages\GenerateTownOpenGraphImageAction;
use App\DataObjects\NotificationRelatedObject;
use App\Enums\EatingOut\EateryType;
use App\Enums\Shop\OrderState;
use App\Models\Blogs\Blog;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentReply;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPaymentRefund;
use App\Models\Shop\ShopProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Spatie\Mjml\Mjml;

Route::get('/mail/shop/order-confirmed/{orderId?}', function (?int $orderId = null): string {
    $order = ShopOrder::query()
        ->where('state_id', OrderState::PAID)
        ->with(['items', 'items.product.media', 'payment', 'customer', 'address'])
        ->when(
            $orderId,
            fn (Builder $builder) => $builder->findOrFail($orderId),
            fn (Builder $builder) => $builder->latest()->first(),
        );

    $content = view('mailables.mjml.shop.order-complete', [
        'key' => 'foo',
        'date' => now(),
        'order' => $order,
        'reason' => 'as confirmation to an order placed in the Coeliac Sanctuary Shop.',
        'notifiable' => $order->customer,
        'relatedTitle' => 'products',
        'relatedItems' => ShopProduct::query()->take(3)->inRandomOrder()->get()->map(fn (ShopProduct $product) => new NotificationRelatedObject(
            title: $product->title,
            image: $product->main_image,
            link: $product->link,
        )),
    ])->render();

    return Mjml::new()->toHtml($content);
});

Route::get('/mail/shop/order-refund/{orderId?}', function (?int $orderId = null): string {
    $order = ShopOrder::query()
        ->where('state_id', OrderState::PAID)
        ->with(['items', 'items.product.media', 'payment', 'customer', 'address'])
        ->when(
            $orderId,
            fn (Builder $builder) => $builder->findOrFail($orderId),
            fn (Builder $builder) => $builder->latest()->first(),
        );

    $refunds = collect([
        ShopPaymentRefund::query()->make([
            'payment_id' => $order->payment->id,
            'amount' => 50,
            'note' => 'first',
            'created_at' => $order->created_at->addHours(2),
        ]),
        ShopPaymentRefund::query()->make([
            'payment_id' => $order->payment->id,
            'amount' => 100,
            'note' => 'second',
            'created_at' => $order->created_at->addHours(3),
        ])
    ]);

    $order->setRelation('refunds', $refunds);

    $content = view('mailables.mjml.shop.order-refund', [
        'key' => 'foo',
        'date' => now(),
        'order' => $order,
        'refund' => $refunds->first(),
        'refundReason' => 'To test the refund email',
        'reason' => 'as confirmation to an order placed in the Coeliac Sanctuary Shop.',
        'notifiable' => $order->customer,
        'relatedTitle' => 'products',
        'relatedItems' => ShopProduct::query()->take(3)->inRandomOrder()->get()->map(fn (ShopProduct $product) => new NotificationRelatedObject(
            title: $product->title,
            image: $product->main_image,
            link: $product->link,
        )),
    ])->render();

    return Mjml::new()->toHtml($content);
});

Route::get('/mail/shop/abandoned-cart/{orderId?}', function (?int $basketId = null): string {
    $basket = ShopOrder::query()
        ->whereIn('state_id', [OrderState::BASKET, OrderState::EXPIRED])
        ->whereHas('customer')
        ->with(['items', 'items.product.media', 'customer'])
        ->when(
            $basketId,
            fn (Builder $builder) => $builder->findOrFail($basketId),
            fn (Builder $builder) => $builder->latest()->first(),
        );

    $content = view('mailables.mjml.shop.abandoned-basket', [
        'key' => 'foo',
        'date' => now(),
        'link' => URL::temporarySignedRoute('shop.basket.reopen', now()->addDay(), ['basket' => $basket]),
        'basket' => $basket,
        'reason' => 'as a reminder to complete your purchase',
        'notifiable' => $basket->customer,
        'relatedTitle' => 'products',
        'relatedItems' => ShopProduct::query()->take(3)->inRandomOrder()->get()->map(fn (ShopProduct $product) => new NotificationRelatedObject(
            title: $product->title,
            image: $product->main_image,
            link: $product->link,
        )),
    ])->render();

    return Mjml::new()->toHtml($content);
});

Route::get('/mail/shop/review-invitation/{orderId?}', function (?int $orderId = null): string {
    $order = ShopOrder::query()
        ->where('state_id', OrderState::SHIPPED)
        ->with(['items', 'items.product.media', 'payment', 'customer', 'address', 'reviewInvitation'])
        ->whereHas('reviewInvitation')
        ->when(
            $orderId,
            fn (Builder $builder) => $builder->findOrFail($orderId),
            fn (Builder $builder) => $builder->latest()->first(),
        );

    $content = view('mailables.mjml.shop.review-invitation', [
        'key' => 'foo',
        'date' => now(),
        'order' => $order,
        'reason' => 'mailables.mjml.shop.review-invitation',
        'delayText' => '10 days',
        'reviewLink' => URL::temporarySignedRoute(
            'shop.review-order',
            Carbon::now()->addMonths(6),
            ['invitation' => $order->reviewInvitation, 'hash' => sha1($order->customer->email)]
        ),
        'notifiable' => $order->customer,
        'relatedTitle' => 'products',
        'relatedItems' => ShopProduct::query()->take(3)->inRandomOrder()->get()->map(fn (ShopProduct $product) => new NotificationRelatedObject(
            title: $product->title,
            image: $product->main_image,
            link: $product->link,
        )),
    ])->render();

    return Mjml::new()->toHtml($content);
});

Route::get('/mail/eating-out/review-approved/{id?}', function (?int $id = null): string {
    $eateryReview = EateryReview::query()
        ->where('approved', true)
        ->whereNotNull('name')
        ->whereNotNull('email')
        ->with(['eatery'])
        ->when(
            $id,
            fn (Builder $builder) => $builder->findOrFail($id),
            fn (Builder $builder) => $builder->latest()->first(),
        );

    $content = view('mailables.mjml.eating-out.review-approved', [
        'key' => 'foo',
        'date' => now(),
        'eateryReview' => $eateryReview,
        'reason' => 'as confirmation to an order placed in the Coeliac Sanctuary Shop.',
        'email' => $eateryReview->email,
        'relatedTitle' => 'products',
        'relatedItems' => ShopProduct::query()->take(3)->inRandomOrder()->get()->map(fn (ShopProduct $product) => new NotificationRelatedObject(
            title: $product->title,
            image: $product->main_image,
            link: $product->link,
        )),
    ])->render();

    return Mjml::new()->toHtml($content);
});

Route::get('/mail/comment-approved/{id?}', function (?int $id = null): string {
    $comment = Comment::query()
        ->where('approved', true)
        ->with(['commentable'])
        ->when(
            $id,
            fn (Builder $builder) => $builder->findOrFail($id),
            fn (Builder $builder) => $builder->latest()->first(),
        );

    $content = view('mailables.mjml.comment-approved', [
        'key' => 'foo',
        'date' => now(),
        'comment' => $comment,
        'reason' => 'to let you know your comment on Coeliac Sanctuary has been approved.',
        'email' => $comment->email,
        'relatedTitle' => 'products',
        'relatedItems' => ShopProduct::query()->take(3)->inRandomOrder()->get()->map(fn (ShopProduct $product) => new NotificationRelatedObject(
            title: $product->title,
            image: $product->main_image,
            link: $product->link,
        )),
    ])->render();

    return Mjml::new()->toHtml($content);
});

Route::get('/mail/comment-reply/{id?}', function (?int $id = null): string {
    $reply = CommentReply::query()
        ->with(['comment', 'comment.commentable'])
        ->when(
            $id,
            fn (Builder $builder) => $builder->findOrFail($id),
            fn (Builder $builder) => $builder->latest()->first(),
        );

    $content = view('mailables.mjml.comment-replied', [
        'key' => 'foo',
        'date' => now(),
        'reply' => $reply,
        'reason' => 'to let you know your comment on Coeliac Sanctuary has been replied to.',
        'email' => $reply->comment->email,
        'relatedTitle' => 'products',
        'relatedItems' => ShopProduct::query()->take(3)->inRandomOrder()->get()->map(fn (ShopProduct $product) => new NotificationRelatedObject(
            title: $product->title,
            image: $product->main_image,
            link: $product->link,
        )),
    ])->render();

    return Mjml::new()->toHtml($content);
});

Route::get('/og/eating-out/town/{slug?}', function (GenerateTownOpenGraphImageAction $generateTownOpenGraphImageAction, ?string $slug = null): View {
    $town = EateryTown::query()
        ->where('slug', $slug ?: 'crewe')
        ->with(['media', 'county', 'county.media', 'county.country'])
        ->firstOrFail();

    return $generateTownOpenGraphImageAction->handle($town);
});

Route::get('/og/eating-out/county/{slug?}', function (GenerateCountyOpenGraphImageAction $generateCountyOpenGraphImageAction, ?string $slug = null): View {
    $county = EateryCounty::query()
        ->where('slug', $slug ?: 'cheshire')
        ->with(['media', 'country'])
        ->firstOrFail();

    return $generateCountyOpenGraphImageAction->handle($county);
});

Route::get('/og/eating-out/eatery/{id?}', function (GenerateEateryOpenGraphImageAction $generateEateryOpenGraphImageAction, ?int $id = null): View {
    $eatery = Eatery::query()
        ->where('id', $id ?: 2645)
        ->with(['town', 'county', 'country', 'reviews', 'reviewImages' => fn (Relation $builder) => $builder->latest()])
        ->withCount(['nationwideBranches'])
        ->firstOrFail();

    return $generateEateryOpenGraphImageAction->handle($eatery);
});

Route::get('/og/eating-out/branch/{id?}', function (GenerateNationwideBranchOpenGraphImageAction $generateNationwideBranchOpenGraphImageAction, ?string $id = null): View {
    $branch = NationwideBranch::query()
        ->when($id, fn (Builder $builder) => $builder->where('id', $id), fn (Builder $builder) => $builder->inRandomOrder())
        ->with(['town', 'county', 'country', 'reviews'])
        ->firstOrFail();

    return $generateNationwideBranchOpenGraphImageAction->handle($branch);
});

Route::get('og/generic/home', function () {
    $blogs = Blog::query()->with(['media'])->latest()->take(4)->get();
    $recipes = Recipe::query()->with(['media'])->latest()->take(4)->get();

    $items = $blogs->concat($recipes)->sortByDesc('updated_at')->take(4);

    return view('og-images.home', [
        'items' => $items,
    ]);
});

Route::get('og/generic/shop', function () {
    $spanishCard = ShopProduct::query()
        ->with(['media'])
        ->where('title', 'like', 'spanish and italian%')
        ->firstOrFail();

    $stickers = ShopProduct::query()
        ->with(['media'])
        ->where('title', 'like', '%stickers%')
        ->firstOrFail();

    $otherAllergyCard = ShopProduct::query()
        ->with(['media'])
        ->where('title', 'like', '%coeliac+%')
        ->firstOrFail();

    return view('og-images.shop', [
        'spanishCard' => $spanishCard,
        'stickers' => $stickers,
        'otherAllergyCard' => $otherAllergyCard,
    ]);
});

Route::get('og/generic/wte', function () {
    $eateries = Eatery::query()
        ->where('type_id', EateryType::EATERY)
        ->count();

    $attractions = Eatery::query()
        ->where('type_id', EateryType::ATTRACTION)
        ->count();

    $hotels = Eatery::query()
        ->where('type_id', EateryType::HOTEL)
        ->count();

    $branches = NationwideBranch::query()->count();

    $reviews = EateryReview::query()->count();

    return view('og-images.eatery', [
        'eateries' => $eateries + $branches,
        'attractions' => $attractions,
        'hotels' => $hotels,
        'branches' => $branches,
        'reviews' => $reviews,
    ]);
});

Route::get('og/generic/wte-app', function () {
    $eateries = Eatery::query()
        ->where('type_id', EateryType::EATERY)
        ->count();

    $attractions = Eatery::query()
        ->where('type_id', EateryType::ATTRACTION)
        ->count();

    $hotels = Eatery::query()
        ->where('type_id', EateryType::HOTEL)
        ->count();

    $branches = NationwideBranch::query()->count();

    $reviews = EateryReview::query()->count();

    return view('og-images.eatery-app', [
        'eateries' => $eateries + $branches,
        'attractions' => $attractions,
        'hotels' => $hotels,
        'branches' => $branches,
        'reviews' => $reviews,
    ]);
});

Route::get('og/generic/wte-map', function () {
    $eateries = Eatery::query()
        ->where('type_id', EateryType::EATERY)
        ->count();

    $attractions = Eatery::query()
        ->where('type_id', EateryType::ATTRACTION)
        ->count();

    $hotels = Eatery::query()
        ->where('type_id', EateryType::HOTEL)
        ->count();

    $branches = NationwideBranch::query()->count();

    $reviews = EateryReview::query()->count();

    return view('og-images.eatery-map', [
        'eateries' => $eateries + $branches,
        'attractions' => $attractions,
        'hotels' => $hotels,
        'branches' => $branches,
        'reviews' => $reviews,
    ]);
});
