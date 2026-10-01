<x-app-layout>
  <div class="max-w-xl mx-auto p-6 space-y-4">
    <h1 class="text-xl font-semibold">Ticket categories</h1>
    <form method="POST" action="{{ route('admin.categories.store') }}" class="flex gap-2">
      @csrf
      <input name="name" placeholder="New category name" class="flex-1 border rounded px-3 py-2">
      <button class="rounded bg-indigo-600 px-4 py-2 text-white">Add category</button>
    </form>
    @if($errors->any())<ul class="text-red-600 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
    <ul class="bg-white shadow rounded divide-y">
      @foreach($categories as $c)
        <li class="flex items-center justify-between p-3">
          {{ $c->name }}
          <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Delete this category?')">
            @csrf @method('DELETE')
            <button class="text-red-600 text-sm">Delete</button>
          </form>
        </li>
      @endforeach
    </ul>
  </div>
</x-app-layout>