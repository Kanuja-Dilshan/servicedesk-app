<x-app-layout>
  <div class="max-w-5xl mx-auto p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold">Tickets</h1>
      <a href="{{ route('tickets.create') }}" class="rounded bg-indigo-600 px-4 py-2 text-white">New ticket</a>
    </div>
    <form class="flex gap-2 mb-4">
      <input name="q" value="{{ request('q') }}" placeholder="Search tickets" class="border rounded px-3 py-2 flex-1">
      <select name="status" class="border rounded px-3 py-2">
        <option value="">All statuses</option>
        @foreach($statuses as $s)<option value="{{ $s->id }}" @selected(request('status') == $s->id)>{{ $s->name }}</option>@endforeach
      </select>
      <button class="rounded border px-4">Filter</button>
    </form>
    <table class="w-full text-left bg-white shadow rounded">
      <thead class="border-b text-sm"><tr><th class="p-3">Title</th><th>Priority</th><th>Status</th><th>Assigned</th></tr></thead>
      <tbody>
      @forelse($tickets as $t)
        <tr class="border-b"><td class="p-3"><a class="text-indigo-700" href="{{ route('tickets.show', $t) }}">#{{ $t->id }} {{ $t->title }}</a></td>
        <td>{{ $t->priority->name }}</td><td>{{ $t->status->name }}</td><td>{{ $t->technician?->name ?? 'Unassigned' }}</td></tr>
      @empty
        <tr><td class="p-6 text-gray-500" colspan="4">No tickets yet. Create one to report an IT issue.</td></tr>
      @endforelse
      </tbody>
    </table>
    <div class="mt-4">{{ $tickets->links() }}</div>
  </div>
</x-app-layout>
