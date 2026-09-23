@php
    use App\Models\Shop\ShopOrderItem;
    use Illuminate\Support\Collection;
    use App\Support\Helpers;
    use Money\Money;
 @endphp
@props(['loop', 'item', 'resend', 'overrides'])

@php
    /** @var ShopOrderItem $item */
    /** @var bool $resend */
    /** @var Collection $overrides */
@endphp

<mj-section margin-bottom="{{ $loop->last || $item->digitalBundles->isNotEmpty() ? '0' : '10px' }}">
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
                    <span
                        @if($resend && $overrides->get($item->id, 0) > 0 && $overrides->get($item->id, 0) < $item->quantity) style="text-decoration: line-through" @endif>
                        {{ $item->quantity }}X
                    </span>
                @if($resend && $overrides->get($item->id, 0) > 0 && $overrides->get($item->id, 0) < $item->quantity)
                    <span style="font-weight: bold; color: #29719f">{{ $overrides->get($item->id ) }}X</span>
                @endif
                <span>
                        {{ $item->product_title }}
                    @if($item->variant->title !== '')
                        - {{ $item->variant->title }}
                    @endif
                    </span>
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

@if($item->digitalBundles->isNotEmpty())
    <mj-section padding="0" margin-bottom="5px">
        <mj-group>
            <mj-column width="25%" padding="0"></mj-column>
            <mj-column width="75%" padding="0 4px 5px" border-bottom="1px solid #29719f">
                <mj-text font-weight="bold" color="#29719f">Digital Bundles</mj-text>
            </mj-column>
        </mj-group>
    </mj-section>

    @foreach($item->digitalBundles as $digitalItem)
        <mj-section margin-bottom="10px" padding="0">
            <mj-group padding="0">
                <mj-column width="25%" padding="0"></mj-column>
                <mj-column width="75%" padding="0 4px">
                    <x-shop-order-table.digital-line
                        :loop="$loop"
                        :item="$digitalItem"
                        compact
                        :skip-bundle-rows="false"
                    />
                </mj-column>
            </mj-group>
        </mj-section>
    @endforeach
@endif
