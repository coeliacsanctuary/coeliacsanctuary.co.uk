@props(['item'])

<span @class(['flex items-center gap-2' => $item->imageUrl, 'block' => ! $item->imageUrl])>
    @if ($item->imageUrl)
        <img src="{{ $item->imageUrl }}" alt="" class="h-8 w-8 shrink-0 rounded object-cover" />
    @endif

    <span class="block">
        <span class="font-medium">{{ $item->title }}</span>

        @if ($item->subtitle())
            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $item->subtitle() }}</span>
        @endif
    </span>
</span>
