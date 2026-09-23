@php
    use App\Models\Shop\ShopOrderDigitalItem;
    use App\Support\Helpers;
    use Money\Money;
@endphp
@props(['loop', 'item','compact' => false, 'skipBundleRows' => true])

@php
    /** @var ShopOrderDigitalItem $item */

$country = $item->card->countries->first()->term;
$countryCode = Helpers::countryCode($country);
$inBasket = $item->orderItem->exists();

$size = $compact ? '40x30' : '96x72';
@endphp

@if(($inBasket === false && $skipBundleRows === true) || $skipBundleRows === false)
<mj-section margin-bottom="{{ $loop->last || $compact ? '0' : '10px' }}" @if($compact) padding="0" @endif>
    <mj-group>
        <mj-column width="{{ $compact ? '15%' : '25%' }}" padding="4px 0" padding-right="{{ $compact ? '12px' : '0' }}">
            <mj-image
                src="https://flagcdn.com/96x72/{{ $countryCode }}.png"
                class="prod-image"
                width="96px"
            />
        </mj-column>

        <mj-column width="{{ $compact ? '65%' : '60%' }}" padding="4px 0">
            <mj-text font-size="18px" line-height="1.2">
                {{ $item->digital_card_title }}
            </mj-text>
        </mj-column>

        <mj-column width="{{ $compact ? '20%' : '15%' }}" padding="4px 0">
            <mj-text align="right">
                {{ Helpers::formatMoney(Money::GBP($item->digital_card_price)) }}
            </mj-text>
        </mj-column>
    </mj-group>
</mj-section>
@endif
