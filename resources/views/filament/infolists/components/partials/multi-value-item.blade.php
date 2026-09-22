@php
    /** @var array{display: string, href: string, copy: string, external?: bool} $item */
    $external = (bool) ($item['external'] ?? false);
@endphp

<div class="fi-multi-value-item group flex w-full min-w-0 items-center gap-1">
    <a
        href="{{ $item['href'] }}"
        @if ($external)
            target="_blank"
            rel="noopener noreferrer"
        @endif
        class="min-w-0 truncate text-primary-600 dark:text-primary-400 underline decoration-gray-300 dark:decoration-gray-600 decoration-1 underline-offset-2"
        title="{{ $item['display'] }}"
    >{{ $item['display'] }}</a>
    <button
        type="button"
        class="fi-multi-value-copy shrink-0 rounded p-0.5 text-primary-500 opacity-70 hover:opacity-100 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1"
        x-on:click.stop="copyToClipboard(@js($item['copy']), {{ $index }}, $event)"
        x-on:keydown.enter.stop
        x-on:keydown.space.stop
        aria-label="{{ __('filament/inline-edit.copy_value', ['value' => $item['display']]) }}"
    >
        <x-filament::icon
            icon="heroicon-m-clipboard-document"
            x-show="copiedIndex !== {{ $index }}"
            class="size-3.5"
            aria-hidden="true"
        />
        <x-filament::icon
            icon="heroicon-m-check"
            x-show="copiedIndex === {{ $index }}"
            x-cloak
            class="size-3.5 text-green-500"
            aria-hidden="true"
        />
    </button>
</div>
