@php
  $trash = 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0';
@endphp
<x-app-layout>
<div class="mx-auto max-w-2xl">
  <div class="mb-6">
    <h1 class="text-2xl font-bold">Ticket categories</h1>
    <p class="text-slate-500">Categories employees can choose when creating a ticket. {{ $categories->count() }} in total.</p>
  </div>

  <div class="space-y-5">
    <div>
      <form method="POST" action="{{ route('admin.categories.store') }}" class="flex gap-3 rounded-2xl bg-white p-3 ring-1 ring-slate-900/5 shadow-[0_10px_25px_-8px_rgba(15,23,42,0.15)]">
        @csrf
        <input name="name" value="{{ old('name') }}" placeholder="New category name" required class="flex-1 rounded-xl border border-slate-200 bg-slate-50 text-sm shadow-inner focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100">
        <button class="rounded-xl bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] px-5 py-2.5 text-sm font-semibold text-white shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] transition hover:brightness-110 active:translate-y-1 active:shadow-none">Add</button>
      </form>
      @error('name')<p class="mt-2 px-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <ul class="divide-y divide-slate-100 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_25px_50px_-12px_rgba(79,70,229,0.22),0_8px_16px_-8px_rgba(15,23,42,0.1)]">
      @forelse ($categories as $c)
        <li class="flex items-center justify-between gap-3 p-4 transition hover:bg-indigo-50/50 hover:shadow-[inset_4px_0_0_0_#6366f1]">
          <span class="flex min-w-0 items-center gap-3 font-medium">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] text-xs text-white shadow-[0_3px_8px_rgba(79,70,229,0.4)]">{{ strtoupper(substr($c->name, 0, 1)) }}</span>
            <span class="truncate">{{ $c->name }}</span>
          </span>
          <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Delete this category?')">
            @csrf @method('DELETE')
            <button class="flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-red-600 shadow-[0_2px_0_0_#fecaca] ring-1 ring-red-100 transition hover:-translate-y-0.5 active:translate-y-0.5 active:shadow-none">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $trash }}"/></svg>
              Delete
            </button>
          </form>
        </li>
      @empty
        <li class="p-10 text-center text-slate-500">No categories yet. Add the first one above.</li>
      @endforelse
    </ul>
  </div>
</div>
</x-app-layout>
