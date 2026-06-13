@props(['href', 'icon' => 'home'])

@php
    $current = request()->url();
    $base    = url($href);
    $active  = $current === $base
        || str_starts_with($current, rtrim($base, '/') . '/');
@endphp

<a href="{{ $href }}"
   class="{{ $active
       ? 'text-white'
       : 'text-slate-400 hover:text-white hover:bg-white/5' }}
          group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm
          font-medium transition-all duration-150 relative"
   @if($active)
   style="background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.2);"
   @endif
>
    {{-- Active indicator --}}
    @if($active)
    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-4 rounded-r-full"
         style="background: #f59e0b;"></div>
    @endif

    {{-- Icon --}}
    <svg class="w-4 h-4 flex-shrink-0 {{ $active ? 'text-amber-400' : '' }}"
         fill="none" stroke="currentColor" viewBox="0 0 24 24">

        @switch($icon)
        @case('front-desk')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0
                     00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2
                     a2 2 0 012 2m-6 9l2 2 4-4"/>@break
        @case('calendar')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5
                     a2 2 0 00-2 2v12a2 2 0 002 2z"/>@break
        @case('office-building')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9
                     0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2
                     a1 1 0 011 1v5m-4 0h4"/>@break
        @case('users')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6
                     v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>@break
        @case('sparkles')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714
                     2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>@break
        @case('document-text')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1
                     0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>@break
        @case('chart-bar')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0
                     002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2
                     a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2
                     2h-2a2 2 0 01-2-2z"/>@break
        @case('currency')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343
                     2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1
                     c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>@break
        @case('trending')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>@break
        @case('user-group')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656
                     -.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7
                     20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288
                     0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>@break
        @default
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"/>
        @endswitch
    </svg>

    <span class="flex-1">{{ $slot }}</span>

    {{-- Notification dot for arrivals --}}
    @if($icon === 'front-desk' && $active === false)
    @php
        $pendingArrivals = \App\Models\Booking::where('hotel_id', auth('employee')->user()->hotel_id)
            ->where('status', 'confirmed')
            ->whereDate('checkin_date', today())
            ->count();
    @endphp
    @if($pendingArrivals > 0)
    <span class="ml-auto bg-amber-500 text-white text-[10px] font-bold
                 px-1.5 py-0.5 rounded-full leading-none">
        {{ $pendingArrivals }}
    </span>
    @endif
    @endif

</a>