<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Hotel HMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body class="h-full bg-white">

<div class="min-h-screen flex">

    {{-- ── Left panel: Branding ────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-[58%] relative overflow-hidden flex-col"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);">

        {{-- Grid pattern background --}}
        <div class="absolute inset-0 opacity-[0.04]"
             style="background-image:
                linear-gradient(rgba(255,255,255,0.8) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.8) 1px, transparent 1px);
                background-size: 50px 50px;">
        </div>

        {{-- Glow orbs --}}
        <div class="absolute top-20 -left-16 w-72 h-72 rounded-full"
             style="background: radial-gradient(circle, rgba(245,158,11,0.15), transparent 70%);">
        </div>
        <div class="absolute bottom-32 right-10 w-96 h-96 rounded-full"
             style="background: radial-gradient(circle, rgba(59,130,246,0.1), transparent 70%);">
        </div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full"
             style="background: radial-gradient(circle, rgba(245,158,11,0.05), transparent 70%);">
        </div>

        {{-- Content --}}
        <div class="relative z-10 flex flex-col h-full p-12 lg:p-16">

            {{-- Logo --}}
            <div class="flex items-center gap-3 mb-16">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9
                                 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1
                                 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-bold text-lg leading-none">Hotel HMS</p>
                    <p class="text-slate-400 text-xs mt-0.5">Management System</p>
                </div>
            </div>

            {{-- Main headline --}}
            <div class="flex-1 flex flex-col justify-center">
                <div class="inline-flex items-center gap-2 bg-amber-500/10 border border-amber-500/20
                            rounded-full px-4 py-1.5 mb-6 w-fit">
                    <div class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-pulse"></div>
                    <span class="text-amber-400 text-xs font-semibold tracking-wide uppercase">
                        Professional Edition
                    </span>
                </div>

                <h1 class="text-5xl font-bold text-white leading-[1.15] mb-5">
                    Run your hotel<br>
                    <span style="background: linear-gradient(90deg, #f59e0b, #fbbf24);
                                 -webkit-background-clip: text; -webkit-text-fill-color: transparent;
                                 background-clip: text;">
                        smarter.
                    </span>
                </h1>

                <p class="text-slate-400 text-lg leading-relaxed mb-12 max-w-md">
                    Everything your team needs — bookings, guests,
                    invoices, housekeeping, and real-time analytics —
                    all in one place.
                </p>

                {{-- Feature list --}}
                <div class="space-y-4">
                    @foreach([
                        ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                         'text' => 'Smart booking with double-booking prevention'],
                        ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                         'text' => 'Automated invoices, tax & payment receipts'],
                        ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                         'text' => 'Live dashboard with revenue & occupancy charts'],
                        ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0',
                         'text' => 'Role-based access for your entire team'],
                    ] as $feature)
                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.2);">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="{{ $feature['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-slate-300 text-sm">{{ $feature['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Stats row --}}
            <div class="grid grid-cols-3 gap-4 mt-12 pt-8 border-t border-slate-700/50">
                @foreach([
                    ['value' => '122', 'label' => 'Tests Passing'],
                    ['value' => '8',   'label' => 'Core Modules'],
                    ['value' => '24/7','label' => 'Operational'],
                ] as $stat)
                <div class="text-center">
                    <p class="text-2xl font-bold text-white">{{ $stat['value'] }}</p>
                    <p class="text-slate-500 text-xs mt-0.5">{{ $stat['label'] }}</p>
                </div>
                @endforeach
            </div>

        </div>
    </div>

    {{-- ── Right panel: Login form ──────────────────────────── --}}
    <div class="w-full lg:w-[42%] flex items-center justify-center
                px-6 sm:px-12 py-12 bg-white">
        <div class="w-full max-w-sm">

            {{-- Mobile logo --}}
            <div class="flex items-center gap-3 mb-10 lg:hidden">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                     style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9
                                 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2
                                 a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <span class="text-slate-900 font-bold text-xl">Hotel HMS</span>
            </div>

            {{-- Heading --}}
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-slate-900 mb-2">Welcome back 👋</h2>
                <p class="text-slate-500 text-sm">Sign in to your hotel dashboard</p>
            </div>

            {{-- Error message --}}
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6
                        flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor"
                     viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0
                             00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414
                             1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414
                             10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707
                             7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-red-800">Incorrect credentials</p>
                    <p class="text-xs text-red-600 mt-0.5">
                        Please check your email and password.
                    </p>
                </div>
            </div>
            @endif

            @if(session('status'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-emerald-700">{{ session('status') }}</p>
            </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email"
                           class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center
                                    pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2
                                         2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2
                                         2 0 002 2z"/>
                            </svg>
                        </div>
                        <input id="email" type="email" name="email"
                               value="{{ old('email') }}"
                               placeholder="you@hotel.com"
                               class="w-full pl-11 pr-4 py-3 rounded-xl text-sm
                                      text-slate-800 placeholder-slate-400
                                      border border-slate-200 bg-slate-50
                                      focus:bg-white focus:outline-none
                                      focus:ring-2 focus:ring-amber-400
                                      focus:border-transparent transition-all"
                               autofocus required>
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password"
                               class="text-sm font-semibold text-slate-700">
                            Password
                        </label>
                        @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-xs text-amber-600 hover:text-amber-700
                                  font-medium transition-colors">
                            Forgot password?
                        </a>
                        @endif
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center
                                    pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0
                                         00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10
                                         V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="passwordInput" type="password" name="password"
                               placeholder="••••••••"
                               class="w-full pl-11 pr-12 py-3 rounded-xl text-sm
                                      text-slate-800 placeholder-slate-400
                                      border border-slate-200 bg-slate-50
                                      focus:bg-white focus:outline-none
                                      focus:ring-2 focus:ring-amber-400
                                      focus:border-transparent transition-all"
                               required>
                        <button type="button" onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center
                                       text-slate-400 hover:text-slate-600 transition-colors">
                            <svg id="eyeShow" class="w-4 h-4" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0
                                         8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542
                                         7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eyeHide" class="w-4 h-4 hidden" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478
                                         0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029
                                         m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242
                                         4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29
                                         M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0
                                         8.268 2.943 9.543 7a10.025 10.025 0 01-4.132
                                         5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center gap-2.5">
                    <input type="checkbox" name="remember" id="remember"
                           class="w-4 h-4 rounded border-slate-300
                                  text-amber-500 focus:ring-amber-400
                                  focus:ring-offset-0 cursor-pointer">
                    <label for="remember"
                           class="text-sm text-slate-600 cursor-pointer select-none">
                        Keep me signed in for 30 days
                    </label>
                </div>

                {{-- Submit button --}}
                <button type="submit"
                        class="w-full py-3.5 px-4 rounded-xl text-sm font-bold
                               text-white tracking-wide transition-all duration-150
                               hover:opacity-90 active:scale-[0.99]
                               shadow-lg shadow-amber-200/50"
                        style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    Sign In to Dashboard →
                </button>

            </form>

            {{-- Demo credentials box --}}
            <div class="mt-8 rounded-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-4 py-3 border-b border-slate-200">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                        🔑 Demo Credentials
                    </p>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach([
                        ['role' => 'Admin',        'email' => 'admin@hotel.com',       'color' => '#7c3aed', 'bg' => '#ede9fe'],
                        ['role' => 'Manager',      'email' => 'manager@hotel.com',     'color' => '#1d4ed8', 'bg' => '#dbeafe'],
                        ['role' => 'Receptionist', 'email' => 'reception@hotel.com',   'color' => '#065f46', 'bg' => '#d1fae5'],
                        ['role' => 'Housekeeper',  'email' => 'housekeeper@hotel.com', 'color' => '#9a3412', 'bg' => '#fed7aa'],
                    ] as $demo)
                    <button type="button"
                            onclick="fillCredentials('{{ $demo['email'] }}')"
                            class="w-full flex items-center gap-3 px-4 py-2.5
                                   hover:bg-amber-50 transition-colors text-left
                                   group">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center
                                    text-xs font-bold flex-shrink-0"
                             style="background: {{ $demo['bg'] }}; color: {{ $demo['color'] }};">
                            {{ substr($demo['role'], 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-semibold text-slate-700">
                                {{ $demo['role'] }}
                            </span>
                            <span class="text-xs text-slate-400 ml-1.5 truncate">
                                {{ $demo['email'] }}
                            </span>
                        </div>
                        <span class="text-xs text-amber-500 opacity-0 group-hover:opacity-100
                                     transition-opacity font-medium">
                            Use →
                        </span>
                    </button>
                    @endforeach
                </div>
                <div class="bg-slate-50 px-4 py-2 border-t border-slate-200">
                    <p class="text-xs text-slate-400">
                        Password for all accounts:
                        <code class="bg-white border border-slate-200 rounded px-1.5 py-0.5
                                     font-mono text-slate-600">
                            password
                        </code>
                    </p>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const show  = document.getElementById('eyeShow');
    const hide  = document.getElementById('eyeHide');

    if (input.type === 'password') {
        input.type = 'text';
        show.classList.add('hidden');
        hide.classList.remove('hidden');
    } else {
        input.type = 'password';
        show.classList.remove('hidden');
        hide.classList.add('hidden');
    }
}

function fillCredentials(email) {
    document.getElementById('email').value         = email;
    document.getElementById('passwordInput').value = 'password';
    document.getElementById('email').focus();
}
</script>

</body>
</html>