<svg class="password-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
    @switch($name)
        @case('lock')
            <rect x="5" y="10" width="14" height="10" rx="2" />
            <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v2" />
            @break
        @case('shield')
            <path d="M12 3 5 6v5c0 4.7 2.8 8 7 10 4.2-2 7-5.3 7-10V6l-7-3Z" />
            <path d="m9 12 2 2 4-4" />
            @break
        @case('key')
            <circle cx="8" cy="15" r="4" />
            <path d="m11 12 8-8m-3 3 3 3m-6 0 2 2" />
            @break
        @case('check')
            <path d="m5 12 4 4L19 6" />
            @break
        @case('info')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 11v5m0-8h.01" />
            @break
    @endswitch
</svg>
