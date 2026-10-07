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
    <body class="font-sans antialiased text-slate-900 dark:text-slate-100">
        <div class="grid min-h-screen lg:grid-cols-2">

            {{-- Brand panel (desktop only) --}}
            <div class="relative hidden overflow-hidden bg-[linear-gradient(135deg,#4338ca,#6366f1_55%,#8b5cf6)] p-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-black/10"></div>

                <a href="/" class="relative flex items-center gap-3 text-xl font-bold">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 ring-1 ring-white/30">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </span>
                    {{ config('app.name', 'ServiceDesk') }}
                </a>

                <div class="relative max-w-md">
                    <h2 class="text-4xl font-bold leading-tight">IT support, without the back-and-forth.</h2>
                    <p class="mt-4 text-lg text-indigo-100">Report an issue, track its progress and get it resolved faster. Every ticket reaches the right technician automatically.</p>
                </div>

                <p class="relative text-sm text-indigo-200">&copy; {{ date('Y') }} {{ config('app.name', 'ServiceDesk') }}</p>
            </div>

            {{-- Form side --}}
            <div class="flex items-center justify-center bg-slate-50 px-6 py-12 dark:bg-slate-900">
                <div class="w-full max-w-md">
                    <a href="/" class="mb-8 flex items-center justify-center gap-3 text-xl font-bold text-indigo-600 dark:text-indigo-400 lg:hidden">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] text-white shadow-[0_4px_10px_rgba(79,70,229,0.45)]">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </span>
                        {{ config('app.name', 'ServiceDesk') }}
                    </a>

                    <div class="rounded-2xl bg-white p-8 ring-1 ring-slate-900/5 shadow-[0_25px_50px_-12px_rgba(79,70,229,0.22),0_8px_16px_-8px_rgba(15,23,42,0.1)] dark:bg-slate-800 dark:ring-white/10">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>