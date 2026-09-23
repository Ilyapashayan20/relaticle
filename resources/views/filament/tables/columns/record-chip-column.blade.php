@php
    $chips = $getChips();
    $visibleChips = array_slice($chips, 0, 2);
    $hiddenChips = array_slice($chips, 2);
    $hiddenNames = implode(', ', array_map(
        fn (\App\Filament\Components\RecordChip $chip): string => $chip->name,
        $hiddenChips,
    ));
    $url = $getUrl();
@endphp

<div class="fi-ta-record-chips flex min-w-0 flex-nowrap items-center gap-x-2 overflow-hidden px-3 py-2 text-sm text-gray-950 dark:text-white">
    @forelse ($visibleChips as $chip)
        @if (filled($url))
            <a
                href="{{ $url }}"
                @if ($shouldOpenUrlInNewTab()) target="_blank" @endif
                class="min-w-0 text-primary-600 hover:underline dark:text-primary-400"
            >
                {{ $chip }}
            </a>
        @else
            {{ $chip }}
        @endif
    @empty
        <span class="text-gray-400 dark:text-gray-500">{{ $getPlaceholder() }}</span>
    @endforelse

    @if ($hiddenChips !== [])
        <span class="fi-ta-record-chips-more shrink-0 text-gray-500 dark:text-gray-400" title="{{ $hiddenNames }}">{{ count($hiddenChips) }}+</span>
    @endif
</div>
