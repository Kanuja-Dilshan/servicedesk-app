<x-app-layout>
  <div class="max-w-3xl mx-auto p-6 space-y-6">
    <div>
      <h1 class="text-xl font-semibold">#{{ $ticket->id }} {{ $ticket->title }}</h1>
      <p class="text-sm text-gray-500">{{ $ticket->creator->name }} · {{ $ticket->category->name }} · {{ $ticket->priority->name }} · {{ $ticket->status->name }}</p>
      <p class="mt-3 whitespace-pre-line">{{ $ticket->description }}</p>
     @foreach($ticket->attachments as $a)
  <p class="text-sm"><a class="text-indigo-700 underline" href="{{ route('attachments.show', $a) }}">{{ $a->original_name }}</a></p>
@endforeach
    </div>

    @can('manage', $ticket)
    <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="flex gap-2 items-center">
      @csrf @method('PUT')
      <select name="status_id" class="border rounded px-2 py-1">@foreach($statuses as $s)<option value="{{ $s->id }}" @selected($ticket->status_id == $s->id)>{{ $s->name }}</option>@endforeach</select>
      <select name="assigned_to" class="border rounded px-2 py-1"><option value="">Unassigned</option>@foreach($technicians as $t)<option value="{{ $t->id }}" @selected($ticket->assigned_to == $t->id)>{{ $t->name }}</option>@endforeach</select>
      <button class="rounded bg-gray-800 px-3 py-1 text-white">Save changes</button>
    </form>
    @endcan

    <form method="POST" action="{{ route('comments.store', $ticket) }}" class="space-y-2">
      @csrf
      <textarea name="body" rows="3" class="w-full border rounded px-3 py-2" placeholder="Add a comment"></textarea>
      @if(auth()->user()->isStaff())<label class="text-sm"><input type="checkbox" name="is_internal" value="1"> Internal note (hidden from employee)</label>@endif
      <button class="rounded bg-indigo-600 px-4 py-1 text-white">Post comment</button>
    </form>

    @foreach($ticket->comments as $c)
      @continue($c->is_internal && ! auth()->user()->isStaff())
      <div class="border rounded p-3 {{ $c->is_internal ? 'bg-yellow-50' : '' }}">
        <p class="text-sm text-gray-500">{{ $c->user->name }} · {{ $c->created_at->diffForHumans() }}{{ $c->is_internal ? ' · internal' : '' }}</p>
        <p class="whitespace-pre-line">{{ $c->body }}</p>
      </div>
    @endforeach
  </div>
</x-app-layout>
