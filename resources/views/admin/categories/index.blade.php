<x-app-layout>
<div class="mx-auto max-w-2xl">
  <div class="mb-6"><h1 class="text-2xl font-bold">Ticket categories</h1><p class="text-slate-500">Categories employees can choose when creating a ticket.</p></div>

  <div class="space-y-5">
    <form method="POST" action="{{ route('admin.categories.store') }}" class="flex gap-3 rounded-2xl bg-white p-3 ring-1 ring-slate-900/5 shadow-[0_10px_25px_-8px_rgba(15,23,42,0.15)]">
      @csrf
      <input name="name" placeholder="New category name" class="flex-1 rounded-xl border border-slate-200 bg-slate-50 text-sm shadow-inner focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100">
      <button class="rounded-xl bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] px-5 py-2.5 text-sm font-semibold text-white shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] transition hover:brightness-110 active:translate-y-1 active:shadow-none">Add</button>
    </form>

    @if ($errors->any())<ul class="rounded-xl bg-red-50 p-3 text-sm text-red-700">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif

    <ul class="divide-y divide-slate-100 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_25px_50px_-12px_rgba(79,70,229,0.22),0_8px_16px_-8px_rgba(15,23,42,0.1)]">
      @foreach ($categories as $c)
        <li class="flex items-center justify-between p-4 transition hover:bg-indigo-50/50 hover:shadow-[inset_4px_0_0_0_#6366f1]">
          <span class="flex items-center gap-3 font-medium">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] text-xs text-white shadow-[0_3px_8px_rgba(79,70,229,0.4)]">{{ strtoupper(substr($c->name, 0, 1)) }}</span>{{ $c->name }}
          </span>
          <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Delete this category?')">
            @csrf @method('DELETE')
            <button class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-red-600 shadow-[0_2px_0_0_#fecaca] ring-1 ring-red-100 transition hover:-translate-y-0.5 active:translate-y-0.5 active:shadow-none">Delete</button>
          </form>
        </li>
      @endforeach
    </ul>
  </div>
</div>
</x-app-layout>
