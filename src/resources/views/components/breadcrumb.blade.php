@props(['items' => []])

<nav class="mb-6 text-sm text-muted" aria-label="Breadcrumb">
    <ol class="flex items-center flex-wrap gap-1">
        <li>
            <a href="/dashboard" class="hover:text-text hover:underline transition-colors">{{ __('messages.nav.dashboard') }}</a>
        </li>
        @foreach($items as $item)
            <li class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-border" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                @if(!empty($item['href']))
                    <a href="{{ $item['href'] }}" class="hover:text-text hover:underline transition-colors">{{ $item['label'] }}</a>
                @else
                    <span class="text-text font-medium">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
