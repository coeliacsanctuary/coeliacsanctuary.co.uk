<?php

declare(strict_types=1);

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Http\Requests\NovaRequest;

Route::get('render/{ids?}', function (NovaRequest $request, Dompdf $pdf, $ids = 'all') {
    $orders = ShopOrder::query()
        ->when(
            $ids === 'all',
            fn (Builder $builder) => $builder->where('state_id', OrderState::PAID),
            fn (Builder $builder) => $builder->whereIn('id', explode(',', $ids))
        )
        ->with(['items', 'payment', 'address', 'discountCode', 'refunds'])
        ->get();

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
            view('nova.shop-dispatch-slip', ['orders' => $orders])->render()
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
    ]);
});
