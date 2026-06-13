<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Hotel HMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body class="h-full" style="background: #f1f5f9;">

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ──────────────────────────────────────────── --}}
    <aside class="w-64 flex flex-col flex-shrink-0"
           style="background: linear-gradient(180deg, #0f172a 0%, #1a2744 100%);">

        {{-- Hotel branding --}}
        <div class="px-5 py-5 border-b border-white/5">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2
                                 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5
                                 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="overflow-hidden">
                    <p class="text-white font-bold text-sm leading-tight truncate">
                        {{ auth('employee')->user()->hotel->name ?? 'Hotel HMS' }}
                    </p>
                    <p class="text-slate-400 text-xs capitalize mt-0.5">
                        {{ auth('employee')->user()->role }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            {{-- Main operations --}}
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest
                       px-3 mb-2 mt-1">
                Operations
            </p>

            <x-nav-link href="{{ route('front-desk.index') }}" icon="front-desk">
                Front Desk
            </x-nav-link>

            <x-nav-link href="{{ route('bookings.index') }}" icon="calendar">
                Bookings
            </x-nav-link>

            <x-nav-link href="{{ route('rooms.index') }}" icon="office-building">
                Rooms
            </x-nav-link>

            <x-nav-link href="{{ route('guests.index') }}" icon="users">
                Guests
            </x-nav-link>

            <x-nav-link href="{{ route('housekeeping.index') }}" icon="sparkles">
                Housekeeping
            </x-nav-link>

            @if(auth('employee')->user()->hasRole(['admin','manager','receptionist']))
            <x-nav-link href="{{ route('invoices.index') }}" icon="document-text">
                Invoices
            </x-nav-link>
            @endif

            {{-- Analytics (manager+) --}}
            @if(auth('employee')->user()->hasRole(['admin','manager']))

            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest
                       px-3 mb-2 mt-5">
                Analytics
            </p>

            <x-nav-link href="{{ route('dashboard') }}" icon="chart-bar">
                Dashboard
            </x-nav-link>

            <x-nav-link href="{{ route('reports.revenue') }}" icon="currency">
                Revenue
            </x-nav-link>

            <x-nav-link href="{{ route('reports.occupancy') }}" icon="trending">
                Occupancy
            </x-nav-link>

            <x-nav-link href="{{ route('reports.bookings') }}" icon="calendar">
                Booking Stats
            </x-nav-link>

            {{-- Admin --}}
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest
                       px-3 mb-2 mt-5">
                Admin
            </p>

            <x-nav-link href="{{ route('employees.index') }}" icon="user-group">
                Employees
            </x-nav-link>

            @endif

        </nav>

        {{-- User profile + logout --}}
        <div class="px-3 py-4 border-t border-white/5">

            {{-- Quick stats bar --}}
            @php
                $today = \App\Models\Booking::where('hotel_id', auth('employee')->user()->hotel_id)
                    ->where('status', 'confirmed')
                    ->whereDate('checkin_date', today())
                    ->count();
            @endphp
            @if($today > 0)
            <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl px-3 py-2.5 mb-3">
                <p class="text-amber-400 text-xs font-semibold">
                    🏃 {{ $today }} arrival{{ $today > 1 ? 's' : '' }} today
                </p>
            </div>
            @endif

            {{-- User card --}}
            <div class="flex items-center gap-3 px-2 py-2 rounded-xl
                        hover:bg-white/5 transition-colors group">
                <div class="w-8 h-8 rounded-full flex items-center justify-center
                            text-xs font-bold flex-shrink-0 text-white"
                     style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                    {{ strtoupper(substr(auth('employee')->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-semibold truncate">
                        {{ auth('employee')->user()->name }}
                    </p>
                    <p class="text-slate-400 text-[10px] truncate">
                        {{ auth('employee')->user()->email }}
                    </p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-slate-500 hover:text-red-400 transition-colors p-1"
                            title="Sign out">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3
                                     3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- ── Main area ────────────────────────────────────────── --}}
    <main class="flex-1 flex flex-col overflow-hidden">

        {{-- Top header --}}
        <header class="bg-white border-b border-slate-200/80 px-8 py-4
                        flex items-center justify-between flex-shrink-0"
                style="box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

            <div class="flex items-center gap-3">
                {{-- Breadcrumb --}}
                <div>
                    <h1 class="text-lg font-bold text-slate-900">
                        @yield('page-title', 'Dashboard')
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ now()->format('l, d F Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success') || session('error') || session('warning'))
        <div class="px-8 pt-4 flex-shrink-0 space-y-2">
            @if(session('success'))
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200
                        text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium
                        shadow-sm shadow-emerald-100">
                <div class="w-5 h-5 bg-emerald-500 rounded-full flex items-center
                            justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="flex items-center gap-3 bg-red-50 border border-red-200
                        text-red-800 px-4 py-3 rounded-xl text-sm font-medium
                        shadow-sm shadow-red-100">
                <div class="w-5 h-5 bg-red-500 rounded-full flex items-center
                            justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                {{ session('error') }}
            </div>
            @endif
        </div>
        @endif

        {{-- Page content --}}
        <div class="flex-1 overflow-y-auto px-8 py-6">
            @yield('content')
        </div>

    </main>

</div>

@stack('scripts')
</body>
</html>