@php
    use App\Enums\EatingOut\EateryType;
    use Illuminate\Support\Str;
@endphp
@vite('resources/js/app.ts')
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet" />

<div class="from-primary to-primary-light relative flex h-[630px] w-[1200px] flex-col overflow-hidden bg-linear-to-b object-cover">
    <div class="flex-1">
        <div class="relative z-20 flex w-full flex-col items-center justify-center space-x-6 p-6">
            <img src="{{ asset('images/logo.svg') }}" class="w-1/2" />
        </div>
    </div>

    <div class="border-secondary border-t-4">
        <span class="font-coeliac mt-6 block w-full text-center text-3xl"> Check out our online shop </span>

        <div class="z-20 grid grid-cols-8 gap-4 p-6 font-sans">
            <div></div>
            <div class="col-span-3 flex flex-col space-y-2">
                <img class="h-[185px] rounded-lg object-cover" src="{{ $spanishCard->main_image }}" />

                <span class="text-center text-xl font-semibold">Coeliac Travel Cards</span>
            </div>
            {{--            <div class="flex flex-col space-y-2 col-span-2">--}}
            {{--                <img class="rounded-lg h-[185px] object-cover" src="{{ $stickers->main_image }}" />--}}

            {{--                <span class=" text-center text-xl font-semibold">Gluten Free Stickers</span>--}}
            {{--            </div>--}}
            <div class="col-span-3 flex flex-col space-y-2">
                <img class="h-[185px] rounded-lg object-cover" src="{{ $otherAllergyCard->main_image }}" />

                <span class="text-center text-xl font-semibold">Coeliac+ Other Allergen Cards</span>
            </div>
            <div></div>
        </div>
    </div>
</div>
