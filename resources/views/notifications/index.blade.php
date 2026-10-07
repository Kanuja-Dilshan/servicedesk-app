@php
  $unreadCount = auth()->user()->unreadNotifications()->count();
  $groups = $notifications->getCollection()->groupBy(
      fn ($n) => $n->created_at->isToday() ? 'Today' : ($n->created_at->isYesterday() ? 'Yesterday' : 'Earlier')
  );
  $bell = 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0';
@endphp
<x-app-layout>
<div class="mx-auto max-w-3xl">
  {{-- Header --}}
  <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold">Notifications</h1>
      <p class="text-slate-500">
        @if ($unreadCount)
          You have <span class="font-semibold text-indigo-600">{{ $unreadCount }}</span> unread {{ \Illuminate\Support\Str::plural('notification', $unreadCount) }}.
        @else
          You're all caught up.
        @endif
      </p>
    </div>

    @if ($unreadCount && Route::has('notifications.read-all'))
      <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-[0_3px_0_0_#cbd5e1,0_6px_12px_-4px_rgba(15,23,42,0.2)] ring-1 ring-slate-200 transition hover:brightness-95 active:translate-y-0.5 active:shadow-none">
          Mark all as read
        </button>
      </form>
    @endif
  </div>

  {{-- List --}}
  @forelse ($groups as $title => $items)
    <h2 class="mb-2 mt-6 text-xs font-semibold uppercase tracking-wide text-slate-500 first:mt-0">{{ $title }}</h2>
    <div class="space-y-3">
      @foreach ($items as $n)
        <a href="{{ route('notifications.open', $n->id) }}"
           class="group flex items-start gap-4 rounded-2xl bg-white p-4 ring-1 transition duration-200 hover:-translate-y-0.5
                  {{ $n->read_at
                      ? 'opacity-75 ring-slate-900/5 shadow-sm hover:opacity-100'
                      : 'ring-indigo-500/30 shadow-[inset_4px_0_0_0_#6366f1,0_16px_32px_-12px_rgba(79,70,229,0.35)]' }}">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                       {{ $n->read_at ? 'bg-slate-200 text-slate-500' : 'bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] text-white shadow-[0_4px_10px_rgba(79,70,229,0.5)]' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bell }}"/></svg>
          </span>

          <div class="min-w-0 flex-1">
            <p class="text-sm {{ $n->read_at ? '' : 'font-semibold' }}">{{ $n->data['message'] ?? 'Ticket update' }}</p>
            <p class="mt-0.5 text-xs text-slate-500">{{ $n->created_at->diffForHumans() }}</p>
          </div>

          @unless ($n->read_at)
            <span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.9)]" title="Unread"></span>
          @endunless
        </a>
      @endforeach
    </div>
  @empty
    <div class="rounded-2xl bg-white px-6 py-14 text-center ring-1 ring-slate-900/5 shadow-sm">
      <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bell }}"/></svg>
      </div>
      <p class="font-semibold text-slate-700">No notifications yet</p>
      <p class="mt-1 text-sm text-slate-500">When a ticket is assigned or updated, you'll see it here.</p>
    </div>
  @endforelse

  <div class="mt-6">{{ $notifications->links() }}</div>
</div>
</x-app-layout>