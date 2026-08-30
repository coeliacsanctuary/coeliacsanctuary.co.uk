@props([
    'url' => null,
    'previewErrors' => [],
])

@if ($url)
    <iframe
        src="{{ $url }}"
        class="w-full rounded-lg border-0 bg-white"
        style="height: 75vh"
        title="Blog preview"
    ></iframe>
@else
    <ul class="space-y-1 text-sm text-danger-600 dark:text-danger-400">
        @forelse ($previewErrors as $previewError)
            <li>{{ $previewError }}</li>
        @empty
            <li>Preview could not be generated. Please try again.</li>
        @endforelse
    </ul>
@endif
