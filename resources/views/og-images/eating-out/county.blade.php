@php use App\Enums\EatingOut\EateryType;use Illuminate\Support\Str; @endphp
<html>
<head>
@vite('resources/js/app.ts')
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="w-[1200px] h-[630px] bg-linear-to-b from-primary to-primary-light relative overflow-hidden flex flex-col object-cover">
    <div class="absolute w-full h-full object-cover opacity-15">
        <img src="{{ $county->image ?? $county->country?->image }}" class="object-cover"/>
    </div>

    <div class="flex-1">
        <div class="flex justify-between z-20 w-full p-6 relative">
            <div class="flex flex-col items-center w-1/2">
                <img src="{{ asset('images/logo.svg') }}"/>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2">
        <div class="text-6xl font-coeliac p-8 text-center">
            @unless($county->county === 'Nationwide')
                Eating out Gluten Free in {{ $county->county }}
            @else
                Gluten Free Nationwide Chains
            @endif
        </div>
        <div class="grid gap-4 font-sans p-6 z-20 grid-cols-3">
            @unless($county->county === 'Nationwide')
                <div class="bg-secondary rounded-lg p-4 flex flex-col items-center space-y-3 justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-12">
                        <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-3xl font-semibold text-center">
                            {{ number_format($towns) }}
                        </span>
                    <span class="text-xl text-center">
                            {{ Str::plural('Town', $towns) }}
                        </span>
                </div>
            @else
                <div></div>
            @endunless
            <div class="bg-secondary rounded-lg p-4 flex flex-col items-center space-y-3 justify-center">
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
                        {{ Str::plural($county->county === 'Nationwide' ? 'Chain' : 'Location', $eateries) }}
                    </span>
            </div>
            <div class="bg-secondary rounded-lg p-4 flex flex-col items-center space-y-3 justify-center">
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
        </div>
    </div>
</div>
</body>
</html>
