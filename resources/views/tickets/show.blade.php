@php
  $card = 'rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_20px_40px_-12px_rgba(15,23,42,0.18),0_6px_12px_-6px_rgba(15,23,42,0.1)]';
  $field = 'w-full rounded-xl border border-slate-200 bg-slate-50 text-sm shadow-inner transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100';
  $btn3d = 'rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition hover:brightness-110 active:translate-y-1';
  $indigo = 'bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] active:shadow-[0_0_0_0_#3730a3]';
  $dark = 'bg-[linear-gradient(to_bottom,#334155,#1e293b)] shadow-[0_4px_0_0_#0f172a,0_8px_14px_-4px_rgba(15,23,42,0.5)] active:shadow-[0_0_0_0_#0f172a]';
  $green = 'bg-[linear-gradient(to_bottom,#22c55e,#16a34a)] shadow-[0_4px_0_0_#166534,0_10px_18px_-4px_rgba(22,163,74,0.5)] active:shadow-[0_0_0_0_#166534]';
  $label = 'text-xs font-semibold uppercase tracking-wide text-slate-500';
@endphp
<x-app-layout>
  <a href="{{ route('tickets.index') }}" class="text-sm text-slate-500 hover:text-indigo-600">&larr; All tickets</a>
  <h1 class="mb-6 mt-2 text-2xl font-bold">#{{ $ticket->id }} {{ $ticket->title }}</h1>

  <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">

      <div class="{{ $card }} p-6">
        <h2 class="{{ $label }} mb-3">Description</h2>
        <p class="whitespace-pre-line">{{ $ticket->description }}</p>
        @if ($ticket->attachments->count())
          <div class="mt-5 flex flex-wrap gap-2">
            @foreach ($ticket->attachments as $a)
              <a href="{{ route('attachments.show', $a) }}" class="rounded-xl bg-white px-3 py-1.5 text-sm text-indigo-700 shadow ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">📎 {{ $a->original_name }}</a>
            @endforeach
          </div>
        @endif
      </div>

      @can('respond', $ticket)
        <div class="space-y-4 rounded-2xl bg-gradient-to-br from-green-50 to-emerald-50 p-6 ring-1 ring-green-200 shadow-[0_20px_40px_-12px_rgba(22,163,74,0.3)]">
          <p class="font-semibold text-green-900">IT Support marked this ticket as resolved. Is your issue fixed?</p>
          <form method="POST" action="{{ route('tickets.close', $ticket) }}">@csrf
            <button class="{{ $btn3d }} {{ $green }}">Yes, close ticket</button>
          </form>
          <form method="POST" action="{{ route('tickets.reopen', $ticket) }}" class="space-y-3">@csrf
            <textarea name="reason" rows="2" required placeholder="Still not working? Tell us what is wrong" class="{{ $field }}">{{ old('reason') }}</textarea>
            @error('reason')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <button class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-[0_4px_0_0_#cbd5e1,0_8px_14px_-4px_rgba(15,23,42,0.2)] ring-1 ring-slate-200 transition active:translate-y-1 active:shadow-none">Reopen ticket</button>
          </form>
        </div>
      @endcan

      <div class="{{ $card }} p-6">
        <h2 class="mb-4 font-semibold">Comments</h2>
        <form method="POST" action="{{ route('comments.store', $ticket) }}" class="mb-6 space-y-3">@csrf
          <textarea name="body" rows="3" placeholder="Add a comment..." class="{{ $field }}"></textarea>
          <div class="flex items-center justify-between gap-3">
            @if (auth()->user()->isStaff())
              <label class="text-sm text-slate-600"><input type="checkbox" name="is_internal" value="1" class="rounded"> Internal note (hidden from employee)</label>
            @else<span></span>@endif
            <button class="{{ $btn3d }} {{ $indigo }}">Post comment</button>
          </div>
        </form>

        <div class="space-y-4">
          @foreach ($ticket->comments as $c)
            @continue($c->is_internal && ! auth()->user()->isStaff())
            <div class="flex gap-3">
              <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] text-sm font-semibold text-white shadow-[0_3px_8px_rgba(79,70,229,0.45)]">{{ strtoupper(substr($c->user->name, 0, 1)) }}</div>
              <div class="flex-1 rounded-2xl p-4 shadow-sm {{ $c->is_internal ? 'border border-yellow-200 bg-yellow-50' : 'bg-slate-50 ring-1 ring-slate-100' }}">
                <p class="text-xs text-slate-500"><span class="font-semibold text-slate-700">{{ $c->user->name }}</span> · {{ $c->created_at->diffForHumans() }}{{ $c->is_internal ? ' · internal note' : '' }}</p>
                <p class="mt-1 whitespace-pre-line text-sm">{{ $c->body }}</p>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <div class="{{ $card }} p-6">
        <h2 class="{{ $label }} mb-4">Details</h2>
        <dl class="space-y-3 text-sm">
          <div class="flex items-center justify-between"><dt class="text-slate-500">Status</dt><dd><x-status-badge :status="$ticket->status->name" class="shadow-sm ring-1 ring-black/5" /></dd></div>
          <div class="flex items-center justify-between"><dt class="text-slate-500">Priority</dt><dd><x-priority-badge :priority="$ticket->priority->name" class="shadow-sm ring-1 ring-black/5" /></dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Category</dt><dd>{{ $ticket->category->name }}</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Requester</dt><dd>{{ $ticket->creator->name }}</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Assigned to</dt><dd>{{ $ticket->technician?->name ?? 'Unassigned' }}</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Created</dt><dd>{{ $ticket->created_at->format('d M Y H:i') }}</dd></div>
          @if ($ticket->due_at)
            <div class="flex justify-between"><dt class="text-slate-500">Due</dt>
              <dd class="{{ $ticket->due_at->isPast() && $ticket->status_id < \App\Models\Ticket::RESOLVED ? 'font-bold text-red-600' : '' }}">{{ $ticket->due_at->format('d M H:i') }}</dd></div>
          @endif
        </dl>
      </div>

      @can('manage', $ticket)
        <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="{{ $card }} space-y-3 p-6">
          @csrf @method('PUT')
          <h2 class="{{ $label }}">Manage</h2>
          <select name="status_id" class="{{ $field }}">@foreach ($statuses as $s)<option value="{{ $s->id }}" @selected($ticket->status_id == $s->id)>{{ $s->name }}</option>@endforeach</select>
          <select name="assigned_to" class="{{ $field }}"><option value="">Unassigned</option>@foreach ($technicians as $t)<option value="{{ $t->id }}" @selected($ticket->assigned_to == $t->id)>{{ $t->name }}</option>@endforeach</select>
          <button class="{{ $btn3d }} {{ $dark }} w-full">Save changes</button>
        </form>
      @endcan

      <div class="{{ $card }} p-6">
        <h2 class="{{ $label }} mb-4">History</h2>
        <ul class="space-y-4 border-l-2 border-indigo-100 pl-5 text-sm">
          @foreach ($ticket->activities as $a)
            <li class="relative">
              <span class="absolute -left-[27px] top-1 h-3 w-3 rounded-full bg-indigo-500 ring-4 ring-white shadow"></span>
              <p>{{ $a->description }}</p>
              <p class="text-xs text-slate-500">{{ $a->user?->name ?? 'System' }} · {{ $a->created_at->format('d M Y H:i') }}</p>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
</x-app-layout>
