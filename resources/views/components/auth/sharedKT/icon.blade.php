@props(['name'])

<svg {{ $attributes->merge(['class' => 'auth-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="m3 7 9 6 9-6" />
            @break
        @case('lock')
            <rect x="5" y="10" width="14" height="11" rx="2" />
            <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" />
            @break
        @case('eye')
            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
            <circle cx="12" cy="12" r="2.5" />
            @break
        @case('eye-off')
            <path d="m3 3 18 18M10.7 6.1A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-2 2.7M6.6 6.7C4 8.3 2.5 12 2.5 12s3.5 6 9.5 6c1.4 0 2.6-.3 3.7-.8M10.2 10.2a2.5 2.5 0 0 0 3.6 3.6" />
            @break
        @case('user')
            <circle cx="12" cy="8" r="4" />
            <path d="M4 21a8 8 0 0 1 16 0" />
            @break
        @case('user-plus')
            <circle cx="10" cy="8" r="4" />
            <path d="M3 21a7 7 0 0 1 14 0M19 8v6M16 11h6" />
            @break
        @case('phone')
            <path d="M7.2 3H4.6A1.6 1.6 0 0 0 3 4.7C3.5 13 11 20.5 19.3 21a1.6 1.6 0 0 0 1.7-1.6v-2.6l-4.3-1-1.2 2a14 14 0 0 1-9.3-9.3l2-1.2L7.2 3Z" />
            @break
        @case('gift')
            <path d="M3 9h18v12H3zM2 5h20v4H2zM12 5v16M12 5H8.5a2.5 2.5 0 1 1 2.2-3.7L12 5Zm0 0h3.5a2.5 2.5 0 1 0-2.2-3.7L12 5Z" />
            @break
        @case('truck')
            <path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z" />
            <circle cx="7" cy="18" r="2" /><circle cx="18" cy="18" r="2" />
            @break
        @case('shield')
            <path d="M12 2 4 5v6c0 5.2 3.4 9.2 8 11 4.6-1.8 8-5.8 8-11V5l-8-3Z" />
            <path d="m8.5 12 2.2 2.2 4.8-5" />
            @break
        @case('send')
            <path d="m22 2-7 20-4-9-9-4 20-7Z" />
            <path d="M22 2 11 13" />
            @break
        @case('check')
            <path d="M20 6 9 17l-5-5" />
            @break
        @case('paw')
            <circle cx="7" cy="7" r="2" fill="currentColor" stroke="none" />
            <circle cx="17" cy="7" r="2" fill="currentColor" stroke="none" />
            <circle cx="4.5" cy="12" r="1.8" fill="currentColor" stroke="none" />
            <circle cx="19.5" cy="12" r="1.8" fill="currentColor" stroke="none" />
            <path d="M12 10c-3 0-5 3-5 5.5S9.2 20 12 18.5c2.8 1.5 5-.5 5-3S15 10 12 10Z" fill="currentColor" stroke="none" />
            @break
    @endswitch
</svg>
