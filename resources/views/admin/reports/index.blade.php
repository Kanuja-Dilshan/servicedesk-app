@php
  $card = 'rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_20px_40px_-12px_rgba(15,23,42,0.18),0_6px_12px_-6px_rgba(15,23,42,0.1)]';
  $field = 'rounded-xl border border-slate-200 bg-slate-50 text-sm shadow-inner transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100';
  $btn3d = 'rounded-xl px-4 py-2.5 text-sm font-semibold transition hover:brightness-110 active:translate-y-1';
  $indigo = 'text-white bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] active:shadow-none';
  $light = 'bg-white text-slate-700 ring-1 ring-slate-200 shadow-[0_3px_0_0_#cbd5e1,0_6px_12px_-4px_rgba(15,23,42,0.2)] active:shadow-none';
  $bar = ['Open' => 'from-blue-400 to-blue-600', 'Assigned' => 'from-indigo-400 to-indigo-600', 'In Progress' => 'from-yellow-400 to-yellow-500',
          'Waiting for User' => 'from-orange-400 to-orange-500', 'Resolved' => 'from-green-400 to-green-600', 'Closed' => 'from-slate-300 to-slate-500',
          'Low' => 'from-slate-300 to-slate-500', 'Medium' => 'from-blue-400 to-blue-600', 'High' => 'from-orange-400 to-orange-500', 'Critical' => 'from-red-400 to-red-600'];
  $range = ['from' => $from->toDateString(), 'to' => $to->toDateString()];
  $trendMax = max(1, max($trend ?: [0]));
  $trendKeys = array_keys($trend);
  $slaClass = fn ($v) => $v === null ? 'text-slate-400' : ($v >= 90 ? 'text-green-600' : ($v >= 70 ? 'text-orange-500' : 'text-red-600'));
  $kpis = [
    ['Tickets created', $kpi['total'], '', 'from-indigo-500 to-violet-500'],
    ['Resolved', $kpi['resolved'], '', 'from-green-500 to-emerald-500'],
    ['Avg resolution', $kpi['avg_hours'] !== null ? $kpi['avg_hours'] . ' h' : '-', '', 'from-blue-500 to-cyan-500'],
    ['SLA met', $kpi['sla'] !== null ? $kpi['sla'] . '%' : '-', $slaClass($kpi['sla']), 'from-yellow-400 to-orange-500'],
    ['Overdue now', $kpi['overdue'], $kpi['overdue'] ? 'text-red-600' : '', $kpi['overdue'] ? 'from-red-500 to-rose-500' : 'from-slate-400 to-slate-500'],
  ];
@endphp
<x-app-layout>
  {{-- Header --}}
  <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
      <h1 class="text-2xl font-bold">Reports &amp; analytics</h1>
      <p class="text-slate-500">{{ $from->format('d M Y') }} &ndash; {{ $to->format('d M Y') }} &middot; tickets created in this period</p>
    </div>
    <div class="flex flex-wrap gap-2 print:hidden">
      <a href="{{ route('admin.reports.export', $range + ['type' => 'tickets']) }}" class="{{ $btn3d }} {{ $indigo }}">Download tickets CSV</a>
      <a href="{{ route('admin.reports.export', $range + ['type' => 'technicians']) }}" class="{{ $btn3d }} {{ $light }}">Technicians CSV</a>
      <button type="button" onclick="window.print()" class="{{ $btn3d }} {{ $light }}">Print / Save as PDF</button>
    </div>
  </div>

  {{-- Period filter --}}
  <div class="{{ $card }} mb-6 flex flex-col gap-3 p-4 print:hidden lg:flex-row lg:items-center lg:justify-between">
    <form method="GET" class="flex flex-wrap items-center gap-3">
      <input type="date" name="from" value="{{ $range['from'] }}" class="{{ $field }}">
      <span class="text-slate-400">to</span>
      <input type="date" name="to" value="{{ $range['to'] }}" class="{{ $field }}">
      <button class="{{ $btn3d }} bg-[linear-gradient(to_bottom,#334155,#1e293b)] text-white shadow-[0_4px_0_0_#0f172a] active:shadow-none">Apply</button>
    </form>
    <div class="flex flex-wrap gap-2 text-sm">
      @foreach ([['Last 7 days', now()->subDays(6), now()], ['Last 30 days', now()->subDays(29), now()], ['Last 90 days', now()->subDays(89), now()], ['This month', now()->startOfMonth(), now()]] as [$label, $a, $b])
        <a href="{{ route('admin.reports.index', ['from' => $a->toDateString(), 'to' => $b->toDateString()]) }}"
           class="rounded-lg bg-slate-100 px-3 py-1.5 font-medium text-slate-600 transition hover:text-indigo-600">{{ $label }}</a>
      @endforeach
    </div>
  </div>

  {{-- KPI cards --}}
  <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
    @foreach ($kpis as [$label, $value, $valueClass, $grad])
      <div class="{{ $card }} relative overflow-hidden p-5">
        <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $grad }}"></span>
        <p class="text-sm text-slate-500">{{ $label }}</p>
        <p class="mt-1 text-3xl font-bold {{ $valueClass }}">{{ $value }}</p>
      </div>
    @endforeach
  </div>

  {{-- Trend --}}
  <div class="{{ $card }} mb-6 p-6">
    <h2 class="mb-1 font-semibold">Tickets created {{ $byWeek ? 'per week' : 'per day' }}</h2>
    <p class="mb-5 text-xs text-slate-500">Busiest {{ $byWeek ? 'week' : 'day' }}: {{ max($trend ?: [0]) }} tickets</p>
    <div class="flex h-40 items-end gap-1">
      @foreach ($trend as $date => $n)
        <div class="flex h-full flex-1 items-end" title="{{ \Carbon\Carbon::parse($date)->format('d M') }}: {{ $n }} {{ $n === 1 ? 'ticket' : 'tickets' }}">
          <div class="w-full rounded-t bg-gradient-to-t from-indigo-600 to-violet-400" style="height: {{ $n ? max($n / $trendMax * 100, 4) : 1 }}%"></div>
        </div>
      @endforeach
    </div>
    <div class="mt-2 flex justify-between text-xs text-slate-500">
      <span>{{ \Carbon\Carbon::parse($trendKeys[0] ?? $from)->format('d M') }}</span>
      <span>{{ \Carbon\Carbon::parse(end($trendKeys) ?: $to)->format('d M') }}</span>
    </div>
  </div>

  {{-- Breakdowns --}}
  <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
    @foreach (['By status' => $byStatus, 'By priority' => $byPriority, 'By category' => $byCategory] as $title => $rows)
      @php $max = max(1, $rows->max() ?? 0); @endphp
      <div class="{{ $card }} p-6">
        <h2 class="mb-5 font-semibold">{{ $title }}</h2>
        @forelse ($rows as $name => $n)
          <div class="mb-4 last:mb-0">
            <div class="mb-1.5 flex justify-between text-sm"><span class="truncate pe-2">{{ $name }}</span><span class="font-semibold">{{ $n }}</span></div>
            <div class="h-2.5 rounded-full bg-slate-100 shadow-inner">
              <div class="h-2.5 rounded-full bg-gradient-to-r {{ $bar[$name] ?? 'from-indigo-400 to-indigo-600' }}" style="width: {{ $n ? max($n / $max * 100, 6) : 0 }}%"></div>
            </div>
          </div>
        @empty
          <p class="text-sm text-slate-500">No data.</p>
        @endforelse
      </div>
    @endforeach
  </div>

  {{-- Technician performance --}}
  <div class="{{ $card }} overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-4"><h2 class="font-semibold">Technician performance</h2></div>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-[linear-gradient(to_bottom,#f8fafc,#eef2ff)] text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="p-4">Technician</th><th class="p-4 text-right">Assigned</th><th class="p-4 text-right">Active</th>
            <th class="p-4 text-right">Resolved</th><th class="p-4 text-right">Avg resolution</th><th class="p-4 text-right">SLA met</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($techs as $t)
            <tr>
              <td class="p-4 font-semibold text-slate-900">{{ $t['name'] }}</td>
              <td class="p-4 text-right">{{ $t['assigned'] }}</td>
              <td class="p-4 text-right">{{ $t['active'] }}</td>
              <td class="p-4 text-right">{{ $t['resolved'] }}</td>
              <td class="p-4 text-right">{{ $t['avg_hours'] !== null ? $t['avg_hours'] . ' h' : '-' }}</td>
              <td class="p-4 text-right font-semibold {{ $slaClass($t['sla']) }}">{{ $t['sla'] !== null ? $t['sla'] . '%' : '-' }}</td>
            </tr>
          @empty
            <tr><td colspan="6" class="p-10 text-center text-slate-500">No support technicians yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <p class="mt-4 text-xs text-slate-500">SLA met = resolved on or before the due time. Avg resolution = created to resolved. Only resolved tickets are counted in those two figures.</p>
</x-app-layout>