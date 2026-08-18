<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-amber-500 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#8B5A2B] focus:bg-[#8B5A2B] active:bg-[#6B4423] focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
