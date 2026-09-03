@props([
    'chat' => null,
    'turns' => [],
])

<div class="space-y-4">
    <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
        @if (filled($chat->summary))
            <p class="text-sm text-gray-950 dark:text-white">{{ $chat->summary }}</p>
        @else
            <p class="text-sm italic text-gray-400 dark:text-gray-500">Not yet summarised</p>
        @endif

        <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
            <div class="flex gap-1">
                <dt>Messages</dt>
                <dd class="font-medium text-gray-700 dark:text-gray-300">{{ count($turns) }}</dd>
            </div>

            <div class="flex gap-1">
                <dt>Started</dt>
                <dd class="font-medium text-gray-700 dark:text-gray-300">{{ $chat->created_at->format('jS M Y, H:i') }}</dd>
            </div>

            <div class="flex gap-1">
                <dt>Session</dt>
                <dd class="font-mono font-medium text-gray-700 dark:text-gray-300">{{ $chat->session_id }}</dd>
            </div>
        </dl>
    </div>

    <ol class="space-y-4">
        @forelse ($turns as $turn)
            <li class="space-y-3 rounded-lg border border-gray-200 p-4 dark:border-white/10">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Visitor</p>
                    <p class="mt-1 text-sm break-words text-gray-950 dark:text-white">{!! $turn['prompt'] !!}</p>
                </div>

                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-primary-600 dark:text-primary-400">Sealiac</p>
                    <div class="prose prose-sm mt-1 max-w-none break-words dark:prose-invert">{!! $turn['response'] !!}</div>
                </div>

                @if ($turn['toolUses'] !== [])
                    <details class="text-xs">
                        <summary class="flex cursor-pointer flex-wrap items-center gap-1 text-gray-500 dark:text-gray-400">
                            <span>Tools</span>

                            @foreach ($turn['toolUses'] as $toolUse)
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-gray-700 dark:bg-white/10 dark:text-gray-300">
                                    {{ $toolUse['tool'] }}
                                </span>
                            @endforeach
                        </summary>

                        <div class="mt-2 space-y-2">
                            @foreach ($turn['toolUses'] as $toolUse)
                                <div>
                                    <p class="font-mono text-gray-500 dark:text-gray-400">{{ $toolUse['tool'] }}</p>

                                    <pre class="mt-1 overflow-x-auto rounded bg-gray-50 p-2 text-gray-600 dark:bg-white/5 dark:text-gray-400">{{ json_encode($toolUse['data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif

                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $turn['at'] }}</p>
            </li>
        @empty
            <li class="text-sm text-gray-500 dark:text-gray-400">This chat has no messages.</li>
        @endforelse
    </ol>
</div>
