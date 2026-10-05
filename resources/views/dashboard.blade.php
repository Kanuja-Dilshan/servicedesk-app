@php
  $bar = ['Open' => 'from-blue-400 to-blue-600', 'Assigned' => 'from-indigo-400 to-indigo-600', 'In Progress' => 'from-yellow-400 to-yellow-500',
          'Waiting for User' => 'from-orange-400 to-orange-500', 'Resolved' => 'from-green-400 to-green-600', 'Closed' => 'from-slate-300 to-slate-500',
          'Low' => 'from-slate-300 to-slate-500', 'Medium' => 'from-blue-400 to-blue-600', 'High' => 'from-orange-400 to-orange-500', 'Critical' => 'from-red-400 to-red-600'];
  $card = 'rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_20px_40px_-12px_rgba(15,23,42,0.18),0_6px_12px_-6px_rgba(15,23,42,0.1)]';
  $stats = [
    ['Total tickets', $total, 'from-indigo-500 to-violet-500', 'shadow-[0_6px_14px_rgba(99,102,241,0.55)]', 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z', ''],
    ['Overdue (past SLA)', $overdue, $overdue ? 'from-red-500 to-rose-500' : 'from-green-500 to-emerald-500', $overdue ? 'shadow-[0_6px_14px_rgba(239,68,68,0.55)]' : 'shadow-[0_6px_14px_rgba(34,197,94,0.5)]', 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', $overdue ? 'text-red-600' : ''],
  ];
  if ($unassigned !== null) {
    $stats[] = ['Unassigned and open', $unassigned, 'from-yellow-400 to-orange-500', 'shadow-[0_6px_14px_rgba(245,158,11,0.55)]', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z', ''];
  }
@endphp
<x-app-layout>
  <div class="mb-8">
    <h1 class="text-2xl font-bold">Hello, {{ auth()->user()->name }} 👋</h1>
    <p class="text-slate-500">Here is what is happening with your tickets.</p>
  </div>

  <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
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

  <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    @foreach (['By status' => $byStatus, 'By priority' => $byPriority] as $title => $rows)
      @php $max = max(1, $rows->max()); @endphp
      <div class="{{ $card }} p-6">
        <h2 class="mb-5 font-semibold">{{ $title }}</h2>
        @foreach ($rows as $name => $count)
          <div class="mb-4">
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
</x-app-layout>