@php
  $card = 'rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_20px_40px_-12px_rgba(15,23,42,0.18),0_6px_12px_-6px_rgba(15,23,42,0.1)]';
  $field = 'w-full rounded-xl border border-slate-200 bg-slate-50 text-sm shadow-inner transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100';
  $btn3d = 'rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition hover:brightness-110 active:translate-y-1';
  $indigo = 'bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] active:shadow-[0_0_0_0_#3730a3]';
  $dark = 'bg-[linear-gradient(to_bottom,#334155,#1e293b)] shadow-[0_4px_0_0_#0f172a,0_8px_14px_-4px_rgba(15,23,42,0.5)] active:shadow-[0_0_0_0_#0f172a]';
  $green = 'bg-[linear-gradient(to_bottom,#22c55e,#16a34a)] shadow-[0_4px_0_0_#166534,0_10px_18px_-4px_rgba(22,163,74,0.5)] active:shadow-[0_0_0_0_#166534]';
  $label = 'text-xs font-semibold uppercase tracking-wide text-slate-500';
  $avatar = 'flex shrink-0 items-center justify-center rounded-full bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] font-semibold text-white shadow-[0_3px_8px_rgba(79,70,229,0.45)]';

  $isOpenTicket = $ticket->status_id < \App\Models\Ticket::RESOLVED;
  $overdue = $ticket->due_at && $ticket->due_at->isPast() && $isOpenTicket;
  $visibleComments = $ticket->comments->filter(fn ($c) => ! $c->is_internal || auth()->user()->isStaff());
@endphp
<x-app-layout>
  <a href="{{ route('tickets.index') }}" class="text-sm text-slate-500 hover:text-indigo-600">&larr; All tickets</a>

  {{-- Header --}}
  <div class="mb-6 mt-2">
    <h1 class="text-2xl font-bold"><span class="text-slate-400">#{{ $ticket->id }}</span> {{ $ticket->title }}</h1>
    <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-slate-500">
      <x-status-badge :status="$ticket->status->name" class="shadow-sm ring-1 ring-black/5" />
      <x-priority-badge :priority="$ticket->priority->name" class="shadow-sm ring-1 ring-black/5" />
      @if ($overdue)
        <span class="rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white shadow">Overdue</span>
      @endif
      <span>&middot; Opened {{ $ticket->created_at->diffForHumans() }} by {{ $ticket->creator->name }}</span>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">

      {{-- Description --}}
      <div class="{{ $card }} p-6">
        <h2 class="{{ $label }} mb-3">Description</h2>
        <p class="whitespace-pre-line leading-relaxed">{{ $ticket->description }}</p>

                @if ($ticket->attachments->count())
          @php
            $isImage = fn ($a) => in_array(strtolower(pathinfo($a->path, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg']);
            $images = $ticket->attachments->filter($isImage);
            $others = $ticket->attachments->reject($isImage);
          @endphp
          <div x-data="{ img: null }" @keydown.escape.window="img = null" class="mt-6 border-t border-slate-100 pt-4">
            <h3 class="{{ $label }} mb-3">Attachments ({{ $ticket->attachments->count() }})</h3>

            @if ($images->count())
              <div class="mb-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($images as $a)
                  <button type="button"
                          @click="img = { src: '{{ route('attachments.show', $a) }}', name: @js($a->original_name) }"
                          class="group overflow-hidden rounded-xl bg-white text-left shadow ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <img src="{{ route('attachments.show', $a) }}" alt="{{ $a->original_name }}" loading="lazy"
                         class="h-28 w-full object-cover transition duration-200 group-hover:scale-105">
                    <span class="block truncate px-2 py-1.5 text-xs text-slate-600">{{ $a->original_name }}</span>
                  </button>
                @endforeach
              </div>
            @endif

            @if ($others->count())
              <div class="flex flex-wrap gap-2">
                @foreach ($others as $a)
                  <span class="inline-flex items-center overflow-hidden rounded-xl bg-white text-sm shadow ring-1 ring-slate-200">
                    <a href="{{ route('attachments.show', $a) }}" target="_blank" rel="noopener"
                       class="flex items-center gap-2 px-3 py-2 text-indigo-700 transition hover:bg-indigo-50">
                      <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                      <span class="max-w-[12rem] truncate">{{ $a->original_name }}</span>
                    </a>
                    <a href="{{ route('attachments.show', [$a, 'download' => 1]) }}" title="Download"
                       class="border-s border-slate-200 px-2.5 py-2 text-slate-400 transition hover:bg-slate-50 hover:text-indigo-600">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    </a>
                  </span>
                @endforeach
              </div>
            @endif

            {{-- Lightbox --}}
            <div x-show="img" x-cloak x-transition.opacity @click.self="img = null"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
              <div class="max-h-full w-full max-w-4xl">
                <img :src="img && img.src" :alt="img && img.name" class="mx-auto max-h-[80vh] rounded-xl shadow-2xl">
                <div class="mt-3 flex items-center justify-between gap-4 text-sm text-white">
                  <span class="truncate" x-text="img && img.name"></span>
                  <span class="flex shrink-0 gap-4">
                    <a :href="img && (img.src + '?download=1')" class="font-semibold hover:underline">Download</a>
                    <button type="button" @click="img = null" class="font-semibold hover:underline">Close</button>
                  </span>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>

            {{-- Technician finished: IT Support needs to review --}}
      @can('manage', $ticket)
        @if ($ticket->review_requested_at && $isOpenTicket)
          <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 shadow-sm">
            <p class="font-semibold text-yellow-800">The technician finished the work on this ticket.</p>
            <p class="mt-1 text-sm text-yellow-800">Read the internal notes below, then set the status to Resolved (Manage panel) so the requester can confirm.</p>
          </div>
        @endif
      @endcan

      {{-- Technician: start / finish work --}}
      @can('work', $ticket)
        <div class="{{ $card }} space-y-4 p-6">
          <h2 class="font-semibold">Your work on this ticket</h2>
          @if ((int) $ticket->status_id < \App\Models\Ticket::IN_PROGRESS)
            <form method="POST" action="{{ route('tickets.start', $ticket) }}">@csrf
              <button class="{{ $btn3d }} {{ $dark }}">Start work (set In Progress)</button>
            </form>
          @endif
          <form method="POST" action="{{ route('tickets.complete', $ticket) }}" class="space-y-3">@csrf
            <label for="note" class="block text-sm font-semibold text-slate-700">What did you do?</label>
            <textarea id="note" name="note" rows="3" required placeholder="e.g. Replaced the router, the connection is working now" class="{{ $field }}">{{ old('note') }}</textarea>
            @error('note')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <p class="text-xs text-slate-500">Saved as an internal note. The ticket goes back to IT Support for review.</p>
            <button class="{{ $btn3d }} {{ $green }}">Work done, send back to IT Support</button>
          </form>
        </div>
      @endcan

      {{-- Employee confirmation --}}
      @can('respond', $ticket)
        <div class="space-y-4 rounded-2xl bg-gradient-to-br from-green-50 to-emerald-50 p-6 ring-1 ring-green-200 shadow-[0_20px_40px_-12px_rgba(22,163,74,0.3)]">
          <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-6 w-6 shrink-0 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="font-semibold text-green-900">IT Support marked this ticket as resolved. Is your issue fixed?</p>
          </div>
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

      {{-- Comments --}}
      <div class="{{ $card }} p-6">
        <h2 class="mb-4 font-semibold">Comments <span class="ms-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">{{ $visibleComments->count() }}</span></h2>

        <form method="POST" action="{{ route('comments.store', $ticket) }}" class="mb-6 space-y-3">@csrf
          <textarea name="body" rows="3" placeholder="Add a comment..." class="{{ $field }}">{{ old('body') }}</textarea>
          @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
          <div class="flex items-center justify-between gap-3">
            @if (auth()->user()->isStaff())
              <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_internal" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Internal note (hidden from employee)
              </label>
            @else<span></span>@endif
            <button class="{{ $btn3d }} {{ $indigo }}">Post comment</button>
          </div>
        </form>

        <div class="space-y-4">
          @forelse ($visibleComments as $c)
            <div class="flex gap-3">
              <div class="{{ $avatar }} h-9 w-9 text-sm">{{ strtoupper(substr($c->user->name, 0, 1)) }}</div>
              <div class="min-w-0 flex-1 rounded-2xl p-4 shadow-sm {{ $c->is_internal ? 'border border-yellow-200 bg-yellow-50' : 'bg-slate-50 ring-1 ring-slate-100' }}">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                  <span class="font-semibold text-slate-700">{{ $c->user->name }}</span>
                  @if (in_array($c->user->role, ['admin', 'support']))
                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-indigo-700">{{ $c->user->role === 'admin' ? 'Admin' : 'IT Support' }}</span>
                  @endif
                  @if ($c->is_internal)
                    <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-yellow-800">Internal note</span>
                  @endif
                  <span>&middot; {{ $c->created_at->diffForHumans() }}</span>
                </div>
                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed">{{ $c->body }}</p>
              </div>
            </div>
          @empty
            <p class="py-6 text-center text-sm text-slate-500">No comments yet. Start the conversation above.</p>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
      <div class="{{ $card }} p-6">
        <h2 class="{{ $label }} mb-4">Details</h2>
        <dl class="space-y-3 text-sm">
          <div class="flex items-center justify-between"><dt class="text-slate-500">Status</dt><dd><x-status-badge :status="$ticket->status->name" class="shadow-sm ring-1 ring-black/5" /></dd></div>
          <div class="flex items-center justify-between"><dt class="text-slate-500">Priority</dt><dd><x-priority-badge :priority="$ticket->priority->name" class="shadow-sm ring-1 ring-black/5" /></dd></div>
          <div class="flex justify-between gap-4"><dt class="text-slate-500">Category</dt><dd class="text-right">{{ $ticket->category->name }}</dd></div>
          <div class="flex items-center justify-between gap-4">
            <dt class="text-slate-500">Requester</dt>
            <dd class="flex items-center gap-2">
              <span class="{{ $avatar }} h-6 w-6 text-[11px]">{{ strtoupper(substr($ticket->creator->name, 0, 1)) }}</span>{{ $ticket->creator->name }}
            </dd>
          </div>
          <div class="flex items-center justify-between gap-4">
            <dt class="text-slate-500">Assigned to</dt>
            <dd class="flex items-center gap-2">
              @if ($ticket->technician)
                <span class="{{ $avatar }} h-6 w-6 text-[11px]">{{ strtoupper(substr($ticket->technician->name, 0, 1)) }}</span>{{ $ticket->technician->name }}
              @else
                <span class="text-slate-400">Unassigned</span>
              @endif
            </dd>
          </div>
          <div class="flex justify-between gap-4"><dt class="text-slate-500">Created</dt><dd class="text-right">{{ $ticket->created_at->format('d M Y H:i') }}</dd></div>
          @if ($ticket->due_at)
            <div class="flex justify-between gap-4">
              <dt class="text-slate-500">Due</dt>
              <dd class="text-right {{ $overdue ? 'font-bold text-red-600' : '' }}">
                {{ $ticket->due_at->format('d M H:i') }}
                @if ($isOpenTicket)
                  <span class="block text-xs font-normal {{ $overdue ? 'text-red-600' : 'text-slate-500' }}">{{ $overdue ? 'Overdue by ' . $ticket->due_at->diffForHumans(null, true) : 'Due ' . $ticket->due_at->diffForHumans() }}</span>
                @endif
              </dd>
            </div>
          @endif
          @if ($ticket->resolved_at)
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Resolved</dt><dd class="text-right">{{ $ticket->resolved_at->format('d M Y H:i') }}</dd></div>
          @endif
        </dl>
      </div>

      @can('manage', $ticket)
        <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="{{ $card }} space-y-3 p-6">
          @csrf @method('PUT')
          <h2 class="{{ $label }}">Manage</h2>
          <div>
            <label for="status_id" class="mb-1 block text-xs text-slate-500">Status</label>
            <select id="status_id" name="status_id" class="{{ $field }}">@foreach ($statuses as $s)<option value="{{ $s->id }}" @selected($ticket->status_id == $s->id)>{{ $s->name }}</option>@endforeach</select>
          </div>
          <div>
            <label for="assigned_to" class="mb-1 block text-xs text-slate-500">Assigned to</label>
            <select id="assigned_to" name="assigned_to" class="{{ $field }}"><option value="">Unassigned</option>@foreach ($technicians as $t)<option value="{{ $t->id }}" @selected($ticket->assigned_to == $t->id)>{{ $t->name }}</option>@endforeach</select>
          </div>
          <button class="{{ $btn3d }} {{ $dark }} w-full">Save changes</button>
        </form>
      @endcan

            @can('manage', $ticket)
        @if ($isOpenTicket)
          <form method="POST" action="{{ route('tickets.delegate', $ticket) }}" class="{{ $card }} space-y-3 p-6">
            @csrf
            <h2 class="{{ $label }}">Assign to technician</h2>
            @if ($delegates->isEmpty())
              <p class="text-sm text-slate-500">No technicians yet. An admin can add users with the Technician role.</p>
            @else
              <select name="technician_id" required class="{{ $field }}">
                <option value="">Choose technician...</option>
                @foreach ($delegates as $d)<option value="{{ $d->id }}" @selected(old('technician_id') == $d->id)>{{ $d->name }}</option>@endforeach
              </select>
              @error('technician_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
              <textarea name="note" rows="3" required placeholder="Note for the technician..." class="{{ $field }}">{{ old('note') }}</textarea>
              @error('note')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
              <p class="text-xs text-slate-500">Saved as an internal note. The requester cannot see it.</p>
              <button class="{{ $btn3d }} {{ $indigo }} w-full">Assign to technician</button>
            @endif
          </form>
        @endif
      @endcan

      {{-- History --}}
      <div class="{{ $card }} p-6">
        <h2 class="{{ $label }} mb-4">History</h2>
        <ul class="space-y-5 border-l-2 border-slate-200 pl-5 text-sm">
          @foreach ($ticket->activities as $a)
            <li class="relative">
              <span class="absolute -left-[27px] top-1 h-3 w-3 rounded-full {{ $a->user ? 'bg-indigo-500 ring-indigo-500/20' : 'bg-slate-400 ring-slate-400/20' }} ring-4"></span>
              <p>{{ $a->description }}</p>
              <p class="text-xs text-slate-500">{{ $a->user?->name ?? 'System' }} &middot; {{ $a->created_at->format('d M Y H:i') }}</p>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
</x-app-layout>