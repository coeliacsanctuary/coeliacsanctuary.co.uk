@php use App\Enums\EatingOut\EateryType;use Illuminate\Support\Str; @endphp
@vite('resources/js/app.ts')
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">

<div class="w-[1200px] h-[630px] bg-linear-to-b from-primary to-primary-light relative overflow-hidden flex flex-col object-cover">
    <div class="absolute w-full h-full object-cover opacity-15">
        <img src="{{ $town->image ?? $town->county->image ?? $town->county->country->image }}" class="object-cover"/>
    </div>

    <div class="flex-1">
        <div class="flex justify-between z-20 w-full p-6 relative">
            <div class="flex flex-col items-center w-1/2">
                <img src="{{ asset('images/logo.svg') }}" />
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2">
        <div class="text-6xl font-coeliac p-8 text-center">
            @if($town->county?->county === 'London')
                Eating out Gluten Free in the London borough of {{ $town->town }}
            @else
            Eating out Gluten Free in {{ $town->town }}
            @endif
        </div>
        <div class="grid gap-4 font-sans p-6 z-20 @if($width === 4) grid-cols-4 @elseif($width===3) grid-cols-3 @else grid-cols-2 @endif">
            @if($eateries > 0)
                <div class="bg-secondary rounded-lg p-4 flex flex-col items-center space-y-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-12">
                            <path
                                d="M5.223 2.25c-.497 0-.974.198-1.325.55l-1.3 1.298A3.75 3.75 0 0 0 7.5 9.75c.627.47 1.406.75 2.25.75.844 0 1.624-.28 2.25-.75.626.47 1.406.75 2.25.75.844 0 1.623-.28 2.25-.75a3.75 3.75 0 0 0 4.902-5.652l-1.3-1.299a1.875 1.875 0 0 0-1.325-.549H5.223Z"/>
                            <path fill-rule="evenodd"
                                  d="M3 20.25v-8.755c1.42.674 3.08.673 4.5 0A5.234 5.234 0 0 0 9.75 12c.804 0 1.568-.182 2.25-.506a5.234 5.234 0 0 0 2.25.506c.804 0 1.567-.182 2.25-.506 1.42.674 3.08.675 4.5.001v8.755h.75a.75.75 0 0 1 0 1.5H2.25a.75.75 0 0 1 0-1.5H3Zm3-6a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-.75.75h-3a.75.75 0 0 1-.75-.75v-3Zm8.25-.75a.75.75 0 0 0-.75.75v5.25c0 .414.336.75.75.75h3a.75.75 0 0 0 .75-.75v-5.25a.75.75 0 0 0-.75-.75h-3Z"
                                  clip-rule="evenodd"/>
                        </svg>
                        <span class="text-3xl font-semibold text-center">
                            {{ number_format($eateries) }}
                        </span>
                        <span class="text-xl text-center">
                            {{ Str::plural('Place', $eateries) }} to eat
                        </span>
                </div>
            @endif
            @if($attractions > 0)
                <div class="bg-secondary rounded-lg p-4 flex flex-col items-center space-y-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-12">
                            <path fill-rule="evenodd"
                                  d="M8.161 2.58a1.875 1.875 0 0 1 1.678 0l4.993 2.498c.106.052.23.052.336 0l3.869-1.935A1.875 1.875 0 0 1 21.75 4.82v12.485c0 .71-.401 1.36-1.037 1.677l-4.875 2.437a1.875 1.875 0 0 1-1.676 0l-4.994-2.497a.375.375 0 0 0-.336 0l-3.868 1.935A1.875 1.875 0 0 1 2.25 19.18V6.695c0-.71.401-1.36 1.036-1.677l4.875-2.437ZM9 6a.75.75 0 0 1 .75.75V15a.75.75 0 0 1-1.5 0V6.75A.75.75 0 0 1 9 6Zm6.75 3a.75.75 0 0 0-1.5 0v8.25a.75.75 0 0 0 1.5 0V9Z"
                                  clip-rule="evenodd"/>
                        </svg>
                        <span class="text-3xl font-semibold text-center">
                            {{ number_format($attractions) }}
                        </span>
                        <span class="text-xl text-center">
                            {{ Str::plural('Attraction', $attractions) }}
                        </span>
                </div>
            @endif
            @if($hotels > 0)
                <div class="bg-secondary rounded-lg p-4 flex flex-col items-center space-y-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-12">
                            <path
                                d="M19.006 3.705a.75.75 0 1 0-.512-1.41L6 6.838V3a.75.75 0 0 0-.75-.75h-1.5A.75.75 0 0 0 3 3v4.93l-1.006.365a.75.75 0 0 0 .512 1.41l16.5-6Z"/>
                            <path fill-rule="evenodd"
                                  d="M3.019 11.114 18 5.667v3.421l4.006 1.457a.75.75 0 1 1-.512 1.41l-.494-.18v8.475h.75a.75.75 0 0 1 0 1.5H2.25a.75.75 0 0 1 0-1.5H3v-9.129l.019-.007ZM18 20.25v-9.566l1.5.546v9.02H18Zm-9-6a.75.75 0 0 0-.75.75v4.5c0 .414.336.75.75.75h3a.75.75 0 0 0 .75-.75V15a.75.75 0 0 0-.75-.75H9Z"
                                  clip-rule="evenodd"/>
                        </svg>
                        <span class="text-3xl font-semibold text-center">
                            {{ number_format($hotels) }}
                        </span>
                        <span class="text-xl text-center">
                            {{ Str::plural('Hotel', $hotels) }} {{ Str::plural('B&B', $hotels) }}
                        </span>
                </div>
            @endif
            @if($reviews > 0)
                <div class="bg-secondary rounded-lg p-4 flex flex-col items-center space-y-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-12">
                            <path fill-rule="evenodd"
                                  d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"
                                  clip-rule="evenodd"/>
                        </svg>
                        <span class="text-3xl font-semibold text-center">
                            {{ number_format($reviews) }}
                        </span>
                        <span class="text-xl text-center">
                            {{ Str::plural('Review', $reviews) }}
                        </span>
                </div>
            @endif
        </div>
    </div>
</div>
