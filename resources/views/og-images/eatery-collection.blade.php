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
            Our latest curated collections of places to eat out...
        </span>

        <div class="grid gap-4 font-sans p-6 z-20 grid-cols-4">
            @foreach($collections as $collection)
                <div class="flex flex-col space-y-2">
                    <img class="rounded-lg" src="{{ $collection->main_image }}" />

                    <span class=" text-center text-lg font-semibold">{{ $collection->title }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
