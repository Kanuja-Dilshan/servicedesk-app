@php
  $field = 'rounded-xl border border-slate-200 bg-slate-50 text-sm shadow-inner transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100';
  $btn3d = 'rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition hover:brightness-110 active:translate-y-1';
@endphp
<x-app-layout>
  <div class="mb-6 flex items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold">Tickets</h1>
      <p class="text-slate-500">Search, filter and open any ticket.</p>
    </div>
    <a href="{{ route('tickets.create') }}"
       class="{{ $btn3d }} bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] active:shadow-[0_0_0_0_#3730a3,0_4px_8px_-2px_rgba(79,70,229,0.5)]">
      + New ticket
    </a>
  </div>

  <form class="mb-5 flex flex-col gap-3 rounded-2xl bg-white p-3 ring-1 ring-slate-900/5 shadow-[0_10px_25px_-8px_rgba(15,23,42,0.15)] sm:flex-row">
    <input name="q" value="{{ request('q') }}" placeholder="Search tickets..." class="{{ $field }} flex-1">
    <select name="status" class="{{ $field }}">
      <option value="">All statuses</option>
      @foreach ($statuses as $s)<option value="{{ $s->id }}" @selected(request('status') == $s->id)>{{ $s->name }}</option>@endforeach
    </select>
    <button class="{{ $btn3d }} bg-[linear-gradient(to_bottom,#334155,#1e293b)] shadow-[0_4px_0_0_#0f172a,0_8px_14px_-4px_rgba(15,23,42,0.5)] active:shadow-[0_0_0_0_#0f172a]">
      Filter
    </button>
  </form>

  <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_25px_50px_-12px_rgba(79,70,229,0.22),0_8px_16px_-8px_rgba(15,23,42,0.1)]">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-[linear-gradient(to_bottom,#f8fafc,#eef2ff)] text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="p-4">Ticket</th>
            <th class="p-4">Priority</th>
            <th class="p-4">Status</th>
            <th class="hidden p-4 sm:table-cell">Assigned</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($tickets as $t)
          <tr class="group transition hover:bg-indigo-50/50 hover:shadow-[inset_4px_0_0_0_#6366f1]">
            <td class="p-4">
              <a href="{{ route('tickets.show', $t) }}" class="font-semibold text-slate-900 transition group-hover:text-indigo-600">{{ $t->title }}</a>
              <p class="text-xs text-slate-500">#{{ $t->id }} · {{ $t->category->name }} · {{ $t->created_at->diffForHumans() }}</p>
            </td>
            <td class="p-4"><x-priority-badge :priority="$t->priority->name" class="shadow-sm ring-1 ring-black/5" /></td>
            <td class="whitespace-nowrap p-4">
              <x-status-badge :status="$t->status->name" class="shadow-sm ring-1 ring-black/5" />
              @if ($t->due_at && $t->due_at->isPast() && $t->status_id < \App\Models\Ticket::RESOLVED)
                <span class="ms-1 rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white shadow">Overdue</span>
              @endif
            </td>
            <td class="hidden p-4 sm:table-cell">
              @if ($t->technician)
                <span class="inline-flex items-center gap-2">
                  <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] text-xs font-semibold text-white shadow-[0_2px_6px_rgba(79,70,229,0.5)]">{{ strtoupper(substr($t->technician->name, 0, 1)) }}</span>
                  {{ $t->technician->name }}
                </span>
              @else
                <span class="text-slate-400">Unassigned</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="p-12 text-center text-slate-500">No tickets yet. Create one to report an IT issue.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-5">{{ $tickets->links() }}</div>
</x-app-layout>
