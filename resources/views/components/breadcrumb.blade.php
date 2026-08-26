@props([
    'items' => [],
    'separator' => '›'
])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'flex items-center gap-2.5 text-sm font-semibold text-[#786B61]']) }}>
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
                <span class="text-[#D1C4B5] select-none text-xs font-bold">{{ $separator }}</span>
            @elseif(!$isLast)
                <span class="text-[#786B61]">{{ $label }}</span>
                <span class="text-[#D1C4B5] select-none text-xs font-bold">{{ $separator }}</span>
            @else
                <span class="text-[#E08A1E] font-bold truncate" aria-current="page">{{ $label }}</span>
            @endif
        @endforeach
    @endif
</nav>
