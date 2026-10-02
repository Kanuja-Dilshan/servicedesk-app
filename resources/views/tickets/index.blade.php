<x-app-layout>
  <div class="max-w-5xl mx-auto p-4 sm:p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold">Tickets</h1>
      <a href="{{ route('tickets.create') }}" class="rounded bg-indigo-600 px-4 py-2 text-white">New ticket</a>
    </div>

    <form class="flex flex-col sm:flex-row gap-2 mb-4">
      <input name="q" value="{{ request('q') }}" placeholder="Search tickets" class="border rounded px-3 py-2 flex-1">
      <select name="status" class="border rounded px-3 py-2">
        <option value="">All statuses</option>
        @foreach($statuses as $s)<option value="{{ $s->id }}" @selected(request('status') == $s->id)>{{ $s->name }}</option>@endforeach
      </select>
      <button class="rounded border px-4 py-2">Filter</button>
    </form>

    <div class="overflow-x-auto bg-white shadow rounded">
      <table class="w-full text-left">
        <thead class="border-b text-sm text-gray-500">
          <tr>
            <th class="p-3">Title</th>
            <th class="p-3">Priority</th>
            <th class="p-3">Status</th>
            <th class="p-3 hidden sm:table-cell">Assigned</th>
          </tr>
        </thead>
        <tbody>
        @forelse($tickets as $t)
          <tr class="border-b last:border-0">
            <td class="p-3"><a class="text-indigo-700 hover:underline" href="{{ route('tickets.show', $t) }}">#{{ $t->id }} {{ $t->title }}</a></td>
            <td class="p-3"><x-priority-badge :priority="$t->priority->name" /></td>
            <td class="p-3 whitespace-nowrap">
              <x-status-badge :status="$t->status->name" />
              @if($t->due_at && $t->due_at->isPast() && $t->status_id < \App\Models\Ticket::RESOLVED)
                <span class="ms-1 text-xs font-medium text-red-600">Overdue</span>
              @endif
            </td>
            <td class="p-3 hidden sm:table-cell">{{ $t->technician?->name ?? 'Unassigned' }}</td>
          </tr>
        @empty
          <tr><td class="p-6 text-gray-500" colspan="4">No tickets yet. Create one to report an IT issue.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $tickets->links() }}</div>
  </div>
</x-app-layout>
