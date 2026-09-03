<svg class="account-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
    @switch($name)
        @case('user')
            <circle cx="12" cy="8" r="3.5" />
            <path d="M5 20c.6-4 3-6 7-6s6.4 2 7 6" />
            @break
        @case('package')
            <path d="m4 7 8-4 8 4v10l-8 4-8-4V7Z" />
            <path d="m4 7 8 4 8-4M12 11v10" />
            @break
        @case('heart')
            <path d="M20.8 5.3a5.2 5.2 0 0 0-7.4 0L12 6.7l-1.4-1.4a5.2 5.2 0 0 0-7.4 7.4L12 21l8.8-8.3a5.2 5.2 0 0 0 0-7.4Z" />
            @break
        @case('star')
            <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z" />
            @break
        @case('message')
            <path d="M21 12a8 8 0 0 1-8 8H5l-3 2 1-5.2A9 9 0 1 1 21 12Z" />
            <path d="M7.5 11.5h.01m4.49 0h.01m4.49 0h.01" stroke-linecap="round" stroke-width="2.5" />
            @break
        @case('lock')
            <rect x="5" y="10" width="14" height="11" rx="2" />
            <path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3" />
            @break
        @case('logout')
            <path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5m5-4 4-4-4-4m4 4H9" />
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
            @break
    @endswitch
</svg>
