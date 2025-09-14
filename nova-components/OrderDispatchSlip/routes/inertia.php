<?php

declare(strict_types=1);

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\NovaRequest;

Route::get('render/{ids?}', function (NovaRequest $request, Dompdf $pdf, $ids = 'all') {
    $orders = ShopOrder::query()
        ->when(
            $ids === 'all',
            fn (Builder $builder) => $builder->where('state_id', OrderState::PAID),
            fn (Builder $builder) => $builder->whereIn('id', explode(',', $ids))
        )
        ->with([
            'items' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
            'items.variant' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
            'payment', 'address', 'discountCode', 'refunds',
        ])
        ->get();

    $overrides = [];
    $resend = false;

    if ($request->boolean('resend') === true) {
        $resend = true;
        $overrides = collect(json_decode($request->get('options')))->mapWithKeys(fn ($quantity, $key) => [
            (int) Str::before($key, '-') => $quantity,
        ]);
    }

    $pdf->setOptions(new Options(['isRemoteEnabled' => true]))
        ->setHttpContext(
            stream_context_create([
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ])
        )
        ->loadHtml(
            view(
                'nova.shop-dispatch-slip',
                [
                    'orders' => $orders,
                    'resend' => $resend,
                    'overrides' => $overrides,
                ],
            )->render()
        );

    $pdf->setPaper('A4')
        ->render();

    return new Response(
        $pdf->stream('slips.pdf', ['Attachment' => false]),
        200,
        ['Content-type' => 'application/pdf']
    );
});

Route::get('/{ids?}', function (NovaRequest $request, $ids = 'all') {
    $orders = ShopOrder::query()
        ->when(
            $ids === 'all',
            fn (Builder $builder) => $builder->where('state_id', OrderState::PAID),
            fn (Builder $builder) => $builder->whereIn('id', explode(',', $ids))
        )
        ->with(['items', 'payment', 'address'])
        ->get();

    return inertia('OrderDispatchSlip', [
        'orders' => $orders,
        'id' => $ids,
        'resend' => $request->boolean('resend'),
        'options' => $request->get('options') ? collect(json_decode($request->get('options'))) : null,
    ]);
});
