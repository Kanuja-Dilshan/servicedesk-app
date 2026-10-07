<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script>
  try {
    var t = localStorage.getItem('theme');
    if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    }
  } catch (e) {}
</script>
  <title>{{ config('app.name', 'ServiceDesk') }}</title>
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800">
@php
  $u = auth()->user();
  $unread = $u->unreadNotifications()->count();
  $icons = [
    'home' => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
    'ticket' => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z',
    'bell' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
    'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    'tag' => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z',
    'building' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
        'chart' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
  ];
  $main = [
    ['Dashboard', 'dashboard', 'dashboard', 'home', 0],
    ['Tickets', 'tickets.index', 'tickets.*', 'ticket', 0],
    ['Notifications', 'notifications.index', 'notifications.*', 'bell', $unread],
  ];
  $admin = [
    ['Users', 'admin.users.index', 'admin.users.*', 'users', 0],
    ['Categories', 'admin.categories.index', 'admin.categories.*', 'tag', 0],
    ['Departments', 'admin.departments.index', 'admin.departments.*', 'building', 0],
        ['Reports', 'admin.reports.index', 'admin.reports.*', 'chart', 0],
  ];
  $linkBase = 'group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition duration-150';
  $linkOn = 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-950/40';
  $linkOff = 'text-slate-300 hover:bg-slate-800 hover:text-white';
@endphp

<div x-data="{ open: false }" @keydown.escape.window="open = false" class="min-h-screen lg:flex">
  <div @click="open = false" :class="open ? 'block' : 'hidden'" class="hidden fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-sm lg:hidden"></div>

  <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
         class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-gradient-to-b from-slate-900 to-slate-950 text-slate-300 shadow-2xl transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">

    {{-- Brand --}}
    <a href="{{ route('dashboard') }}" class="flex h-16 items-center gap-3 border-b border-slate-800 px-5">
      <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-400 to-violet-500 text-white shadow-[0_4px_10px_rgba(99,102,241,0.5)] ring-1 ring-white/20">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
      </div>
      <span class="text-lg font-semibold tracking-tight text-white">ServiceDesk</span>
    </a>

    {{-- Navigation --}}
    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
      <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Menu</p>
      @foreach ($main as [$label, $route, $pattern, $icon, $badge])
        @php $active = request()->routeIs($pattern); @endphp
        <a href="{{ route($route) }}" class="{{ $linkBase }} {{ $active ? $linkOn : $linkOff }}">
          @if ($active)<span class="absolute -left-3 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full bg-indigo-300"></span>@endif
          <svg class="h-5 w-5 shrink-0 {{ $active ? '' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$icon] }}"/></svg>
          <span class="flex-1">{{ $label }}</span>
          @if ($badge)
            <span class="min-w-[1.35rem] rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[11px] font-bold leading-none text-white shadow-[0_2px_6px_rgba(239,68,68,0.5)]">{{ $badge > 9 ? '9+' : $badge }}</span>
          @endif
        </a>
      @endforeach

      @can('admin')
        <p class="px-3 pb-1 pt-6 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Administration</p>
        @foreach ($admin as [$label, $route, $pattern, $icon, $badge])
          @php $active = request()->routeIs($pattern); @endphp
          <a href="{{ route($route) }}" class="{{ $linkBase }} {{ $active ? $linkOn : $linkOff }}">
            @if ($active)<span class="absolute -left-3 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full bg-indigo-300"></span>@endif
            <svg class="h-5 w-5 shrink-0 {{ $active ? '' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$icon] }}"/></svg>
            <span class="flex-1">{{ $label }}</span>
          </a>
        @endforeach
      @endcan
    </nav>

    {{-- Theme toggle --}}
    <div x-data="{ dark: document.documentElement.classList.contains('dark'),
                   toggle() { this.dark = ! this.dark; document.documentElement.classList.toggle('dark', this.dark);
                              try { localStorage.setItem('theme', this.dark ? 'dark' : 'light') } catch (e) {} } }"
         class="px-4 pb-3">
      <button type="button" @click="toggle()" class="flex w-full items-center justify-between rounded-xl bg-slate-800/80 px-3 py-2.5 text-sm text-slate-300 ring-1 ring-white/5 transition hover:bg-slate-700 hover:text-white">
        <span class="flex items-center gap-2.5">
          <svg x-show="dark" class="h-5 w-5 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
          <svg x-show="!dark" x-cloak class="h-5 w-5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
          <span x-text="dark ? 'Dark mode' : 'Light mode'"></span>
        </span>
        <span class="relative h-5 w-9 rounded-full transition" :class="dark ? 'bg-indigo-500' : 'bg-slate-600'">
          <span class="absolute top-0.5 h-4 w-4 rounded-full bg-[#fff] shadow transition-all" :class="dark ? 'left-[18px]' : 'left-0.5'"></span>
        </span>
      </button>
    </div>

    {{-- User --}}
    <div class="border-t border-slate-800 p-4">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-[0_2px_8px_rgba(99,102,241,0.5)] ring-2 ring-slate-800">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
        <div class="min-w-0">
          <p class="truncate text-sm font-semibold text-white">{{ $u->name }}</p>
          <span class="mt-0.5 inline-block rounded-full bg-slate-800 px-2 py-0.5 text-[11px] font-medium capitalize text-slate-300 ring-1 ring-white/5">{{ $u->role }}</span>
        </div>
      </div>
      <div class="mt-3 flex gap-2 text-xs font-medium">
        <a href="{{ route('profile.edit') }}" class="flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-slate-800 py-2 text-slate-300 ring-1 ring-white/5 transition hover:bg-slate-700 hover:text-white">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
          Profile
        </a>
        <form method="POST" action="{{ route('logout') }}" class="flex-1">
          @csrf
          <button class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-slate-800 py-2 text-slate-300 ring-1 ring-white/5 transition hover:bg-red-500/20 hover:text-red-300">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
            Log out
          </button>
        </form>
      </div>
    </div>
  </aside>

  <div class="min-w-0 flex-1">
    {{-- Mobile header --}}
    <header class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b bg-white px-4 shadow-sm lg:hidden">
      <button @click="open = true" class="rounded-lg p-2 text-slate-600 transition hover:bg-slate-100" aria-label="Open menu">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <span class="font-semibold">ServiceDesk</span>
      @if ($unread)
        <a href="{{ route('notifications.index') }}" class="ms-auto rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white">{{ $unread > 9 ? '9+' : $unread }}</a>
      @endif
    </header>

    @isset($header)
      <div class="border-b bg-white px-6 py-4">{{ $header }}</div>
    @endisset

    <main class="mx-auto w-full max-w-6xl p-4 sm:p-8">
      @if (session('status'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.opacity
             class="mb-6 flex items-start justify-between gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 shadow-sm">
          <span class="flex items-start gap-2">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
          </span>
          <button type="button" @click="show = false" class="text-lg leading-none text-green-700 hover:text-green-900" aria-label="Dismiss">&times;</button>
        </div>
      @endif

      @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition.opacity
             class="mb-6 flex items-start justify-between gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
          <span>{{ session('error') }}</span>
          <button type="button" @click="show = false" class="text-lg leading-none text-red-700 hover:text-red-900" aria-label="Dismiss">&times;</button>
        </div>
      @endif

      @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
          <p class="font-semibold">Please fix the following:</p>
          <ul class="mt-1 list-inside list-disc">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
          </ul>
        </div>
      @endif

      {{ $slot }}
    </main>
  </div>
</div>
</body>
</html>