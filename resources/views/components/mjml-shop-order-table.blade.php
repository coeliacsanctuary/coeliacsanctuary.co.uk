@php
    use App\Models\Shop\ShopOrder;
    use App\Support\Helpers;
    use Illuminate\Support\Str;
    use Money\Money;
    use App\Enums\Shop\ProductVariantType;
    /** @var ShopOrder $order */
@endphp

@props(['order', 'resend' => false, 'overrides' => collect()])

<!-- BEGIN PRODUCTS -->
@foreach($order->items as $item)
    <mj-section margin-bottom="{{ $loop->last ? '0' : '10px' }}">
        <mj-column width="25%" padding="4px 0">
            <mj-image
                src="{{ $item->product->main_image }}"
                fluid-on-mobile="true"
                css-class="prod-image"
            />
        </mj-column>

        <mj-column width="60%" padding="4px 0">
            <mj-text font-size="18px" line-height="1.2">
                <a
                    href="{{ $item->product->link }}" @if($overrides->get($item->id, null) === 0)
                    style="text-decoration: line-through" @endif
                >
                    @unless($item->variant->variant_type === ProductVariantType::DIGITAL)
                        <span
                            @if($resend && $overrides->get($item->id, 0) > 0 && $overrides->get($item->id, 0) < $item->quantity) style="text-decoration: line-through" @endif>
                            {{ $item->quantity }}X
                        </span>
                        @if($resend && $overrides->get($item->id, 0) > 0 && $overrides->get($item->id, 0) < $item->quantity)
                            <span style="font-weight: bold; color: #29719f">{{ $overrides->get($item->id ) }}X</span>
                        @endif
                    @endif

                    <span>
                        {{ $item->product_title }}
                        @if($item->variant->title !== '')
                            - {{ $item->variant->title }}
                        @elseif($item->product->variants->count() > 1)
                            - {{ $item->variant->variant_type->label() }}
                        @endif
                    </span>
                    @if($item->variant->short_description)
                        <br /><span style="font-weight: normal; font-size: 12px">
                            {{ $item->variant->short_description }}
                        </span>
                    @endif
                </a>
            </mj-text>
        </mj-column>

        <mj-column width="15%" padding="4px 0">
            <mj-text
                align="right"
                @if($resend && $overrides->get($item->id, null) === 0) style="text-decoration: line-through" @endif
                @if($resend && $overrides->get($item->id, 0) > 0 && $overrides->get($item->id, 0) < $item->quantity) style="text-decoration: line-through" @endif
            >
                {{ Helpers::formatMoney(Money::GBP($item->product_price * $item->quantity)) }}
            </mj-text>
        </mj-column>
    </mj-section>
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
    @if($order->is_digital_only === false || ($order->is_digital_only && $order->payment->discount))
        <mj-column css-class="force-half-width" width="50%">
            <mj-text line-height="1.5">Subtotal</mj-text>
        </mj-column>
        <mj-column css-class="force-half-width" width="50%">
            <mj-text line-height="1.5"
                     align="right">{{ Helpers::formatMoney(Money::GBP($order->payment->subtotal)) }}</mj-text>
        </mj-column>
    @endif

    @if($order->payment->discount)
        <mj-column css-class="force-half-width" width="50%">
            <mj-text line-height="1.5">Discount</mj-text>
        </mj-column>
        <mj-column css-class="force-half-width" width="50%">
            <mj-text line-height="1.5" align="right">
                -{{ Helpers::formatMoney(Money::GBP($order->payment->discount)) }}</mj-text>
        </mj-column>
    @endif

    @unless($order->is_digital_only)
        <mj-column css-class="force-half-width" width="50%">
            <mj-text line-height="1.5">Postage</mj-text>
        </mj-column>
        <mj-column css-class="force-half-width" width="50%">
            <mj-text line-height="1.5"
                     align="right">{{ Helpers::formatMoney(Money::GBP($order->payment->postage)) }}</mj-text>
        </mj-column>
    @endunless

    <mj-column css-class="force-half-width" width="50%">
        <mj-text
            line-height="1.5"
            @if($order->is_digital_only === false || ($order->is_digital_only && $order->payment->discount))
                padding-top="10px"
            @endif
        >
            <h2 @if($order->refunds->isNotEmpty()) style="text-decoration: line-through;" @endif>
                Total
            </h2>
        </mj-text>
    </mj-column>
    <mj-column css-class="force-half-width" width="50%">
        <mj-text
            line-height="1.5"
            align="right"
            @if($order->is_digital_only === false || ($order->is_digital_only && $order->payment->discount))
                padding-top="10px"
            @endif
        >
            <h2 @if($order->refunds->isNotEmpty()) style="text-decoration: line-through;" @endif>
                {{ Helpers::formatMoney(Money::GBP($order->payment->total)) }}
            </h2>
        </mj-text>
    </mj-column>

    @if($order->refunds->isNotEmpty())
        @foreach($order->refunds as $refund)
            <mj-column css-class="force-half-width" width="50%">
                <mj-text line-height="1.5" padding-top="10px" css-class="red-text">
                    <h4 class="red-text">@if($loop->first)
                            {{ Str::plural('Refund', $order->refunds->count()) }}
                        @else
                            &nbsp;
                        @endif</h4>
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
