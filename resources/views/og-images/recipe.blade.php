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
        <span class="font-coeliac mt-6 block w-full text-center text-3xl"> Our latest recipes... </span>

        <div class="z-20 grid grid-cols-4 gap-4 p-6 font-sans">
            @foreach ($recipes as $recipe)
                <div class="flex flex-col space-y-2">
                    <img class="rounded-lg" src="{{ $recipe->main_image }}" />

                    <span class="text-center text-lg font-semibold">{{ $recipe->title }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
