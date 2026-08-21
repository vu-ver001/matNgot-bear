@props([
    'items' => [],
    'separator' => '>'
])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'flex items-center gap-2 text-xs font-semibold text-[#7E4A28]']) }}>
    @if(empty($items))
        {{ $slot }}
    @else
        @foreach($items as $index => $item)
            @php
                $isLast = $loop->last;
                $label = is_array($item) ? ($item['label'] ?? '') : $item;
                $url = is_array($item) ? ($item['url'] ?? null) : null;
            @endphp

            @if(!$isLast && $url)
                <a href="{{ $url }}" class="hover:underline hover:text-[#2C160B] transition">
                    {{ $label }}
                </a>
                <span class="text-[#DDA760] select-none text-[11px] font-bold">{{ $separator }}</span>
            @elseif(!$isLast)
                <span class="text-[#5C3219]">{{ $label }}</span>
                <span class="text-[#DDA760] select-none text-[11px] font-bold">{{ $separator }}</span>
            @else
                <span class="text-[#8E8076] truncate" aria-current="page">{{ $label }}</span>
            @endif
        @endforeach
    @endif
</nav>
