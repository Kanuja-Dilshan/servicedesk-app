<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
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
  ];
@endphp

<div x-data="{ open: false }" class="min-h-screen lg:flex">
  <div @click="open = false" :class="open ? 'block' : 'hidden'" class="hidden fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

  <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
         class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-slate-900 text-slate-300 transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
    <div class="flex h-16 items-center gap-3 px-5">
      <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500 font-bold text-white">S</div>
      <span class="text-lg font-semibold text-white">ServiceDesk</span>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
      @foreach ($main as [$label, $route, $pattern, $icon, $badge])
        <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs($pattern) ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$icon] }}"/></svg>
          <span class="flex-1">{{ $label }}</span>
          @if ($badge)<span class="rounded-full bg-red-500 px-2 text-xs text-white">{{ $badge }}</span>@endif
        </a>
      @endforeach

      @can('admin')
        <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Administration</p>
        @foreach ($admin as [$label, $route, $pattern, $icon, $badge])
          <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs($pattern) ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$icon] }}"/></svg>
            {{ $label }}
          </a>
        @endforeach
      @endcan
    </nav>

    <div class="border-t border-slate-800 p-4">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-700 text-sm font-semibold text-white">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
        <div class="min-w-0">
          <p class="truncate text-sm font-medium text-white">{{ $u->name }}</p>
          <p class="text-xs capitalize text-slate-400">{{ $u->role }}</p>
        </div>
      </div>
      <div class="mt-3 flex gap-2 text-xs">
        <a href="{{ route('profile.edit') }}" class="flex-1 rounded-md bg-slate-800 py-1.5 text-center hover:bg-slate-700">Profile</a>
        <form method="POST" action="{{ route('logout') }}" class="flex-1">
          @csrf
          <button class="w-full rounded-md bg-slate-800 py-1.5 hover:bg-slate-700">Log out</button>
        </form>
      </div>
    </div>
  </aside>

  <div class="min-w-0 flex-1">
    <header class="flex h-14 items-center gap-3 border-b bg-white px-4 lg:hidden">
      <button @click="open = true" class="rounded-md p-2 text-slate-600 hover:bg-slate-100" aria-label="Open menu">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <span class="font-semibold">ServiceDesk</span>
    </header>

    @isset($header)
      <div class="border-b bg-white px-6 py-4">{{ $header }}</div>
    @endisset

    <main class="mx-auto w-full max-w-6xl p-4 sm:p-8">
      @if (session('status'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
      @endif
      {{ $slot }}
    </main>
  </div>
</div>
</body>
</html>
