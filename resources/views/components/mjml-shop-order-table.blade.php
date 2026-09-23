@php
    use App\Models\Shop\ShopOrder;
    use App\Support\Helpers;
    use Illuminate\Support\Str;
    use Money\Money;
    /** @var ShopOrder $order */
@endphp

@props(['order', 'resend' => false, 'overrides' => collect()])

<!-- BEGIN PRODUCTS -->
@foreach($order->items as $item)
    <x-shop-order-table.product-line
        :loop="$loop"
        :item="$item"
        :resend="$resend"
        :overrides="$overrides"
    />
@endforeach
<!-- END: PRODUCTS -->

<!-- BEGIN DIGITAL PRODUCTS -->
@foreach($order->digitalItems as $item)
    <x-shop-order-table.digital-line
        :loop="$loop"
        :item="$item"
    />
@endforeach
<!-- END: PRODUCTS -->

<mj-section>
    <mj-column>
        <mj-text>
            <hr color="#addaf9"></hr>
        </mj-text>
    </mj-column>
</mj-section>

<!-- BEGIN: TOTALS -->
<mj-section>
    <mj-column css-class="force-half-width" width="50%">
        <mj-text line-height="1.5">Subtotal</mj-text>
    </mj-column>
    <mj-column css-class="force-half-width" width="50%">
        <mj-text line-height="1.5" align="right">{{ Helpers::formatMoney(Money::GBP($order->payment->subtotal)) }}</mj-text>
    </mj-column>

    @if($order->payment->discount)
        <mj-column css-class="force-half-width" width="50%">
            <mj-text line-height="1.5">Discount</mj-text>
        </mj-column>
        <mj-column css-class="force-half-width" width="50%">
            <mj-text line-height="1.5" align="right">-{{ Helpers::formatMoney(Money::GBP($order->payment->discount)) }}</mj-text>
        </mj-column>
    @endif

    <mj-column css-class="force-half-width" width="50%">
        <mj-text line-height="1.5">Postage</mj-text>
    </mj-column>
    <mj-column css-class="force-half-width" width="50%">
        <mj-text line-height="1.5" align="right">{{ Helpers::formatMoney(Money::GBP($order->payment->postage)) }}</mj-text>
    </mj-column>

    @if(count($order->payment->fees_breakdown ?? []) > 0)
        @foreach($order->payment->fees_breakdown as $fee)
            <mj-column css-class="force-half-width" width="50%">
                <mj-text line-height="1.5">{{ $fee['description'] ?? 'Customs Fee' }}</mj-text>
            </mj-column>
            <mj-column css-class="force-half-width" width="50%">
                <mj-text line-height="1.5" align="right">{{ Helpers::formatMoney(Money::GBP($fee['fee'])) }}</mj-text>
            </mj-column>
        @endforeach
        @if(count($order->payment->fees_breakdown) > 1)
            <mj-column css-class="force-half-width" width="50%">
                <mj-text line-height="1.5">Total Fees</mj-text>
            </mj-column>
            <mj-column css-class="force-half-width" width="50%">
                <mj-text line-height="1.5" align="right">{{ Helpers::formatMoney(Money::GBP($order->payment->custom_fees)) }}</mj-text>
            </mj-column>
        @endif
    @endif

    <mj-column css-class="force-half-width" width="50%">
        <mj-text line-height="1.5" padding-top="10px">
            <h2 @if($order->refunds->isNotEmpty()) style="text-decoration: line-through;" @endif>
                Total
            </h2>
        </mj-text>
    </mj-column>
    <mj-column css-class="force-half-width" width="50%">
        <mj-text line-height="1.5" align="right" padding-top="10px">
            <h2 @if($order->refunds->isNotEmpty()) style="text-decoration: line-through;" @endif>
                {{ Helpers::formatMoney(Money::GBP($order->payment->total)) }}
            </h2>
        </mj-text>
    </mj-column>

    @if($order->refunds->isNotEmpty())
        @foreach($order->refunds as $refund)
            <mj-column css-class="force-half-width" width="50%">
                <mj-text line-height="1.5" padding-top="10px" css-class="red-text">
                    <h4 class="red-text">@if($loop->first){{ Str::plural('Refund', $order->refunds->count()) }} @else &nbsp; @endif</h4>
                </mj-text>
            </mj-column>
            <mj-column css-class="force-half-width" width="50%">
                <mj-text line-height="1.5" align="right" padding-top="10px" css-class="red-text">
                    <h4 class="red-text">-{{ Helpers::formatMoney(Money::GBP($refund->amount)) }}</h4>
                </mj-text>
            </mj-column>
        @endforeach

        <mj-column css-class="force-half-width" width="50%">
            <mj-text line-height="1.5" padding-top="10px"><h2>Total</h2></mj-text>
        </mj-column>
        <mj-column css-class="force-half-width" width="50%">
            <mj-text line-height="1.5" align="right" padding-top="10px">
                <h2>{{ Helpers::formatMoney(Money::GBP($order->payment->total - $order->refunds->sum('amount'))) }}</h2>
            </mj-text>
        </mj-column>
    @endif

    @if($resend)
        <mj-column css-class="force-half-width" width="50%">
            <mj-text line-height="1.5" padding-top="10px">
                <h2 style="color:#29719f">RESEND - {{ now()->format('jS F Y') }}</h2>
            </mj-text>
        </mj-column>
        <mj-column css-class="force-half-width" width="50%">
            <mj-text line-height="1.5" align="right" padding-top="10px">
                <h2 style="color:#29719f">{{ Helpers::formatMoney(Money::GBP(0)) }}</h2>
            </mj-text>
        </mj-column>
    @endif
</mj-section>
<!-- END: TOTALS -->
