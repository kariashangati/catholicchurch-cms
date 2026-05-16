<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'ChurchMS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen">
        <!-- Mobile overlay -->
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
            style="display: none;"
            @click="sidebarOpen = false"
        ></div>

        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-slate-200 bg-white shadow-2xl transition-transform duration-300 lg:static lg:z-auto"
            >
                <!-- Brand -->
                <div class="flex h-20 items-center justify-between border-b border-slate-200 px-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 via-indigo-600 to-violet-600 text-lg font-black text-white shadow-lg">
                            C
                        </div>
                        <div>
                            <div class="text-lg font-extrabold tracking-tight text-slate-900">
                                {{ config('app.name', 'ChurchMS') }}
                            </div>
                            <div class="text-xs font-medium text-slate-500">
                                {{ db_trans('church_management_system') }}
                            </div>
                        </div>
                    </a>

                    <button
                        @click="sidebarOpen = false"
                        class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 lg:hidden"
                        type="button"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Sidebar content -->
                <div class="flex-1 overflow-y-auto px-4 py-6">
                    <!-- Main -->
                    <div class="mb-7">
                        <div class="px-3 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">
                            {{ db_trans('main_navigation') }}
                        </div>

                        <div class="mt-3 space-y-1.5">
                            <a href="{{ route('dashboard') }}"
                               class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-200
                               {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl
                                    {{ request()->routeIs('dashboard') ? 'bg-white/15 text-white' : 'bg-blue-50 text-blue-600 group-hover:bg-white' }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10.5L12 3l9 7.5V21a1 1 0 0 1-1 1h-5.5v-6h-5v6H4a1 1 0 0 1-1-1v-10.5z"/>
                                    </svg>
                                </span>
                                <span>{{ db_trans('dashboard') }}</span>
                            </a>

                            @can('members.view')
                                <a href="{{ route('members.index') }}"
                                   class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-200
                                   {{ request()->routeIs('members.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl
                                        {{ request()->routeIs('members.*') ? 'bg-white/15 text-white' : 'bg-emerald-50 text-emerald-600 group-hover:bg-white' }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4" stroke-width="1.8"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                        </svg>
                                    </span>
                                    <span>{{ db_trans('members') }}</span>
                                </a>
                            @endcan

                            <a href="{{ route('profile.edit') }}"
                               class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-200
                               {{ request()->routeIs('profile.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl
                                    {{ request()->routeIs('profile.*') ? 'bg-white/15 text-white' : 'bg-violet-50 text-violet-600 group-hover:bg-white' }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="8" r="4" stroke-width="1.8"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 20a8 8 0 0 1 16 0"/>
                                    </svg>
                                </span>
                                <span>{{ db_trans('profile') }}</span>
                            </a>

                            <a href="#"
                               class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 group-hover:bg-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317a1 1 0 0 1 1.35-.936l.789.456a1 1 0 0 0 1 0l.79-.456a1 1 0 0 1 1.35.936v.911a1 1 0 0 0 .5.866l.789.456a1 1 0 0 1 0 1.732l-.79.456a1 1 0 0 0-.499.866v.912a1 1 0 0 1-1.35.935l-.79-.456a1 1 0 0 0-1 0l-.789.456a1 1 0 0 1-1.35-.935v-.912a1 1 0 0 0-.5-.866l-.789-.456a1 1 0 0 1 0-1.732l.79-.456a1 1 0 0 0 .499-.866v-.911z"/>
                                        <circle cx="12" cy="8" r="2.5" stroke-width="1.8"/>
                                    </svg>
                                </span>
                                <span>{{ db_trans('settings') }}</span>
                            </a>

                            @can('finance.view')
                                <a href="#"
                                   class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 text-green-600 group-hover:bg-white">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7v10m-2.5-7.5H13a2 2 0 1 1 0 4h-2a2 2 0 1 0 0 4H15"/>
                                        </svg>
                                    </span>
                                    <span>{{ db_trans('finance') }}</span>
                                </a>
                            @endcan

                            @can('reports.view')
                                <a href="#"
                                   class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-pink-50 text-pink-600 group-hover:bg-white">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19h16"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16V9"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V5"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16v-4"/>
                                        </svg>
                                    </span>
                                    <span>{{ db_trans('reports') }}</span>
                                </a>
                            @endcan
                        </div>
                    </div>

                    <!-- System -->
                    @role('Super Admin')
                        <div class="mb-7">
                            <div class="px-3 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                {{ db_trans('system') }}
                            </div>

                            <div class="mt-3 space-y-1.5">
                                <a href="#"
                                   class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 group-hover:bg-white">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4" stroke-width="1.8"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 8v6m3-3h-6"/>
                                        </svg>
                                    </span>
                                    <span>{{ db_trans('users') }}</span>
                                </a>

                                <a href="{{ route('translations.index') }}"
                                   class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-200
                                   {{ request()->routeIs('translations.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl
                                        {{ request()->routeIs('translations.*') ? 'bg-white/15 text-white' : 'bg-indigo-50 text-indigo-600 group-hover:bg-white' }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h6"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 12h4"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16h4"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 8h6"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12h5"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h7"/>
                                        </svg>
                                    </span>
                                    <span>{{ db_trans('translations') }}</span>
                                </a>
                            </div>
                        </div>
                    @endrole
                </div>

                <!-- User card -->
                <div class="border-t border-slate-200 p-4">
                    <div class="rounded-3xl bg-gradient-to-r from-slate-900 to-slate-800 p-4 text-white shadow-lg">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 font-bold">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs uppercase tracking-wider text-slate-400">
                                    {{ db_trans('logged_in_as') }}
                                </p>
                                <p class="truncate text-sm font-semibold text-white">
                                    {{ Auth::user()->name }}
                                </p>
                                <p class="truncate text-xs text-slate-300">
                                    {{ Auth::user()->email }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main content -->
            <div class="flex min-h-screen flex-1 flex-col lg:ml-0">
                <!-- Topbar -->
                <header class="sticky top-0 z-30 border-b border-blue-500/20 bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 shadow-lg">
                    <div class="flex h-20 items-center justify-between px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-4">
                            <button
                                @click="sidebarOpen = true"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-white backdrop-blur hover:bg-white/20 lg:hidden"
                                type="button"
                            >
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>

                            <div>
                                <h1 class="text-xl font-extrabold tracking-tight text-white sm:text-2xl">
                                    {{ $title ?? config('app.name', 'ChurchMS') }}
                                </h1>
                                <p class="text-sm text-blue-100">
                                    {{ db_trans('welcome_back') }}, {{ Auth::user()->name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Search -->
                            <div class="hidden md:flex">
                                <div class="flex items-center gap-2 rounded-2xl bg-white/10 px-4 py-2.5 text-white backdrop-blur">
                                    <svg class="h-5 w-5 text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="7" stroke-width="1.8"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 20l-3.5-3.5"/>
                                    </svg>
                                    <input
                                        type="text"
                                        placeholder="{{ db_trans('search') }}"
                                        class="w-44 border-0 bg-transparent p-0 text-sm text-white placeholder:text-blue-100 focus:outline-none focus:ring-0"
                                    >
                                </div>
                            </div>

                            <!-- Language -->
                            <div class="hidden sm:flex items-center gap-1 rounded-2xl bg-white/10 p-1 backdrop-blur">
                                <a href="{{ route('lang.switch', 'en') }}"
                                   class="rounded-xl px-3 py-2 text-sm font-semibold transition
                                   {{ app()->getLocale() === 'en' ? 'bg-white text-slate-900 shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                                    EN
                                </a>
                                <a href="{{ route('lang.switch', 'sw') }}"
                                   class="rounded-xl px-3 py-2 text-sm font-semibold transition
                                   {{ app()->getLocale() === 'sw' ? 'bg-emerald-400 text-slate-900 shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                                    SW
                                </a>
                            </div>

                            <!-- Profile -->
                            <a href="{{ route('profile.edit') }}"
                               class="hidden sm:inline-flex items-center rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                {{ db_trans('profile') }}
                            </a>

                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="inline-flex items-center rounded-2xl bg-red-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-600">
                                    {{ db_trans('logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <!-- Page content -->
                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    @if(session('success'))
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm">
                            <div class="font-semibold">{{ db_trans('please_fix_the_following_errors') }}</div>
                            <ul class="mt-2 list-disc ps-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot }}
                </main>

                <!-- Footer -->
                <footer class="border-t border-slate-200 bg-white px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col gap-2 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            © {{ date('Y') }} {{ config('app.name', 'ChurchMS') }}. {{ db_trans('all_rights_reserved') }}
                        </div>
                        <div>
                            {{ db_trans('built_with_laravel') }}
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</body>
</html>