<x-app-layout>
  <div class="max-w-3xl mx-auto p-6 space-y-6">
    <div>
      <h1 class="text-xl font-semibold">#{{ $ticket->id }} {{ $ticket->title }}</h1>
      <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-500">
  <span>{{ $ticket->creator->name }}</span>
  <span>·</span>
  <span>{{ $ticket->category->name }}</span>
  <x-priority-badge :priority="$ticket->priority->name" />
  <x-status-badge :status="$ticket->status->name" />
  @if($ticket->due_at)
    <span class="{{ $ticket->due_at->isPast() && $ticket->status_id < \App\Models\Ticket::RESOLVED ? 'text-red-600 font-medium' : '' }}">
      Due {{ $ticket->due_at->format('d M H:i') }}
    </span>
  @endif
</div>
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

        @can('respond', $ticket)
    <div class="rounded border border-green-300 bg-green-50 p-4 space-y-3">
      <p class="font-medium">IT Support marked this ticket as resolved. Is your issue fixed?</p>
      <form method="POST" action="{{ route('tickets.close', $ticket) }}">
        @csrf
        <button class="rounded bg-green-600 px-4 py-2 text-white">Yes, close ticket</button>
      </form>
      <form method="POST" action="{{ route('tickets.reopen', $ticket) }}" class="space-y-2">
        @csrf
        <textarea name="reason" rows="2" required placeholder="Still not working? Tell us what is wrong" class="w-full border rounded px-3 py-2">{{ old('reason') }}</textarea>
        @error('reason')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        <button class="rounded border px-4 py-2">Reopen ticket</button>
      </form>
    </div>
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

           <div>
      <h2 class="font-medium mb-2">History</h2>
      <ul class="text-sm text-gray-600 space-y-1">
        @foreach($ticket->activities as $a)
          <li>{{ $a->created_at->format('d M Y H:i') }} · {{ $a->user?->name ?? 'System' }}: {{ $a->description }}</li>
        @endforeach
      </ul>
    </div>

  </div>
</x-app-layout>
