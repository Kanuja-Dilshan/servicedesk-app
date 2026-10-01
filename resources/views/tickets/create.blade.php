<x-app-layout>
  <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="max-w-2xl mx-auto p-6 space-y-4">
    @csrf
    <h1 class="text-xl font-semibold">New ticket</h1>
    <input name="title" value="{{ old('title') }}" placeholder="Short summary" class="w-full border rounded px-3 py-2">
    <textarea name="description" rows="6" placeholder="What happened? What did you expect?" class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
    <div class="flex gap-3">
      <select name="category_id" class="border rounded px-3 py-2 flex-1">@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
      <select name="priority_id" class="border rounded px-3 py-2 flex-1">@foreach($priorities as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
    </div>
    <input type="file" name="files[]" multiple>
    @if($errors->any())<ul class="text-red-600 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
    <button class="rounded bg-indigo-600 px-4 py-2 text-white">Submit ticket</button>
  </form>
</x-app-layout>
