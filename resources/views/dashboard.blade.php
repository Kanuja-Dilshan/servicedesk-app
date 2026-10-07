@php
  $user = auth()->user();
  $first = explode(' ', trim($user->name))[0];
  $subtitle = match ($user->role) {
      'admin' => 'Overview of every ticket in the system.',
      'support' => 'Tickets assigned to you.',
      default => 'Tickets you have submitted.',
  };
  $listTitle = match ($user->role) {
      'admin' => 'Latest tickets',
      'support' => 'Your latest assigned tickets',
      default => 'Your latest tickets',
  };
  $allLink = $user->role === 'support' ? route('tickets.index', ['mine' => 1]) : route('tickets.index');

  $bar = ['Open' => 'from-blue-400 to-blue-600', 'Assigned' => 'from-indigo-400 to-indigo-600', 'In Progress' => 'from-yellow-400 to-yellow-500',
          'Waiting for User' => 'from-orange-400 to-orange-500', 'Resolved' => 'from-green-400 to-green-600', 'Closed' => 'from-slate-300 to-slate-500',
          'Low' => 'from-slate-300 to-slate-500', 'Medium' => 'from-blue-400 to-blue-600', 'High' => 'from-orange-400 to-orange-500', 'Critical' => 'from-red-400 to-red-600'];
  $card = 'rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_20px_40px_-12px_rgba(15,23,42,0.18),0_6px_12px_-6px_rgba(15,23,42,0.1)]';
  $btn3d = 'rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition hover:brightness-110 active:translate-y-1';

  $ticketIcon = 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z';
  $clockIcon = 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z';
  $usersIcon = 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z';

  $stats = [
    ['Total tickets', $total, 'from-indigo-500 to-violet-500', 'shadow-[0_6px_14px_rgba(99,102,241,0.55)]', $ticketIcon, ''],
    ['Overdue (past SLA)', $overdue, $overdue ? 'from-red-500 to-rose-500' : 'from-green-500 to-emerald-500', $overdue ? 'shadow-[0_6px_14px_rgba(239,68,68,0.55)]' : 'shadow-[0_6px_14px_rgba(34,197,94,0.5)]', $clockIcon, $overdue ? 'text-red-600' : ''],
  ];
  if ($unassigned !== null) {
    $stats[] = ['Unassigned and open', $unassigned, 'from-yellow-400 to-orange-500', 'shadow-[0_6px_14px_rgba(245,158,11,0.55)]', $usersIcon, ''];
  }
@endphp
<x-app-layout>
  {{-- Header --}}
  <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold">Welcome back, {{ $first }}</h1>
      <p class="text-slate-500">{{ $subtitle }}</p>
    </div>
    <a href="{{ route('tickets.create') }}"
       class="{{ $btn3d }} bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] text-center shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] active:shadow-[0_0_0_0_#3730a3,0_4px_8px_-2px_rgba(79,70,229,0.5)]">
      + New ticket
    </a>
  </div>

  {{-- Stat cards --}}
  <div class="mb-8 grid grid-cols-1 gap-5 {{ count($stats) === 3 ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }}">
    @foreach ($stats as [$label, $value, $grad, $glow, $icon, $valueClass])
      <div class="{{ $card }} flex items-center gap-4 p-5 transition duration-200 hover:-translate-y-1 hover:shadow-[0_28px_50px_-12px_rgba(79,70,229,0.28)]">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} {{ $glow }} ring-1 ring-white/40">
          <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
        </div>
        <div>
          <p class="text-sm text-slate-500">{{ $label }}</p>
          <p class="text-3xl font-bold {{ $valueClass }}">{{ $value }}</p>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Charts --}}
  <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
    @foreach (['By status' => $byStatus, 'By priority' => $byPriority] as $title => $rows)
      @php $max = max(1, $rows->max()); @endphp
      <div class="{{ $card }} p-6">
        <h2 class="mb-5 font-semibold">{{ $title }}</h2>
        @foreach ($rows as $name => $count)
          <div class="mb-4 last:mb-0">
            <div class="mb-1.5 flex justify-between text-sm"><span>{{ $name }}</span><span class="font-semibold">{{ $count }}</span></div>
            <div class="h-3 rounded-full bg-slate-100 shadow-inner">
              <div class="h-3 rounded-full bg-gradient-to-r {{ $bar[$name] ?? 'from-indigo-400 to-indigo-600' }} shadow-[0_2px_6px_rgba(15,23,42,0.25)] ring-1 ring-white/30"
                   style="width: {{ $count ? max(($count / $max) * 100, 6) : 0 }}%"></div>
            </div>
          </div>
        @endforeach
      </div>
    @endforeach
  </div>

  {{-- Recent tickets --}}
  <div class="{{ $card }} overflow-hidden">
    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
      <h2 class="font-semibold">{{ $listTitle }}</h2>
      <a href="{{ $allLink }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View all &rarr;</a>
    </div>

    @forelse ($recent as $t)
      <a href="{{ route('tickets.show', $t) }}"
         class="group flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-4 transition last:border-b-0 hover:bg-indigo-50/50 hover:shadow-[inset_4px_0_0_0_#6366f1]">
        <div class="min-w-0">
          <p class="truncate font-semibold text-slate-900 transition group-hover:text-indigo-600">{{ $t->title }}</p>
          <p class="text-xs text-slate-500">
            #{{ $t->id }} &middot; {{ $t->created_at->diffForHumans() }}
            @if ($t->technician) &middot; {{ $t->technician->name }}@endif
          </p>
        </div>
        <div class="flex shrink-0 items-center gap-2">
          <span class="hidden sm:inline"><x-priority-badge :priority="$t->priority->name" /></span>
          <x-status-badge :status="$t->status->name" />
        </div>
      </a>
    @empty
      <div class="px-6 py-12 text-center text-slate-500">
        <p>No tickets to show yet.</p>
        <a href="{{ route('tickets.create') }}" class="mt-2 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">Create a ticket</a>
      </div>
    @endforelse
  </div>
</x-app-layout>