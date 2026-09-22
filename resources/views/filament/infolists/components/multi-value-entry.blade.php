@php
    $state = $getState();
    $items = collect(is_array($state) ? $state : [])
        ->filter(fn (mixed $item): bool => is_array($item) && filled($item['display'] ?? null))
        ->values();
    $hiddenCount = max(0, $items->count() - 1);
    $first = $items->first();
@endphp

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @if ($first === null)
        <p class="fi-in-placeholder">{{ $getPlaceholder() }}</p>
    @else
        <div
            x-data="{
                copiedIndex: null,
                copiedMessage: @js(__('filament/inline-edit.copied_value')),
                copyToClipboard(text, index, event) {
                    event.preventDefault();
                    event.stopPropagation();
                    window.navigator.clipboard.writeText(text);
                    this.copiedIndex = index;
                    this.announceToScreenReader(this.copiedMessage.replaceAll(':value', text));
                    setTimeout(() => {
                        this.copiedIndex = null;
                    }, 2000);
                },
                announceToScreenReader(message) {
                    if (this.$refs.announcer) {
                        this.$refs.announcer.textContent = message;
                    }
                },
            }"
            class="fi-multi-value-entry flex w-full min-w-0 items-center gap-1"
        >
            <div x-ref="announcer" aria-live="polite" aria-atomic="true" class="sr-only"></div>

            @include('filament.infolists.components.partials.multi-value-item', ['item' => $first, 'index' => 0])

            @if ($hiddenCount > 0)
                <span class="fi-multi-value-more shrink-0 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                    {{ __('filament/inline-edit.show_n_more', ['count' => $hiddenCount]) }}
                </span>
            @endif
        </div>
    @endif
</x-dynamic-component>
