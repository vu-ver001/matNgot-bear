@props([
    'items' => [],
    'separator' => '>'
])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'flex items-center gap-2 text-xs sm:text-sm font-semibold text-[#786B61]']) }}>
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
                <a href="{{ $url }}" class="text-[#786B61] hover:text-[#2C1408] transition">
                    {{ $label }}
                </a>
                <span class="text-[#A89889] select-none text-[11px] font-bold mx-0.5">{{ $separator }}</span>
            @elseif(!$isLast)
                <span class="text-[#786B61]">{{ $label }}</span>
                <span class="text-[#A89889] select-none text-[11px] font-bold mx-0.5">{{ $separator }}</span>
            @else
                <span class="text-[#2C1408] font-bold truncate" aria-current="page">{{ $label }}</span>
            @endif
        @endforeach
    @endif
</nav>
