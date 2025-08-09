@php use App\Enums\EatingOut\EateryType;use Illuminate\Support\Str; @endphp
@vite('resources/js/app.ts')
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">

<div class="w-[1200px] h-[630px] bg-linear-to-b from-primary to-primary-light relative overflow-hidden flex flex-col object-cover">
    <div class="flex-1">
        <div class="flex flex-col justify-center space-x-6 items-center z-20 w-full p-6 relative">
            <img src="{{ asset('images/logo.svg') }}" class="w-1/2"/>
        </div>
    </div>

    <div class="border-t-4 border-secondary">
        <span class="font-coeliac text-3xl text-center block w-full mt-6">
            Check out our online shop
        </span>

        <div class="grid gap-4 font-sans p-6 z-20 grid-cols-8">
            <div></div>
            <div class="flex flex-col space-y-2 col-span-3">
                <img class="rounded-lg h-[185px] object-cover" src="{{ $spanishCard->main_image }}" />

                <span class=" text-center text-xl font-semibold">Coeliac Travel Cards</span>
            </div>
{{--            <div class="flex flex-col space-y-2 col-span-2">--}}
{{--                <img class="rounded-lg h-[185px] object-cover" src="{{ $stickers->main_image }}" />--}}

{{--                <span class=" text-center text-xl font-semibold">Gluten Free Stickers</span>--}}
{{--            </div>--}}
            <div class="flex flex-col space-y-2 col-span-3">
                <img class="rounded-lg h-[185px] object-cover" src="{{ $otherAllergyCard->main_image }}" />

                <span class=" text-center text-xl font-semibold">Coeliac+ Other Allergen Cards</span>
            </div>
            <div></div>
        </div>
    </div>
</div>
