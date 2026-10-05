@php
  $field = 'w-full rounded-xl border border-slate-200 bg-slate-50 text-sm shadow-inner transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100';
  $lab = 'mb-1.5 block text-sm font-semibold text-slate-700';
@endphp
<x-app-layout>
<div class="mx-auto max-w-3xl">
  <a href="{{ route('tickets.index') }}" class="text-sm text-slate-500 hover:text-indigo-600">&larr; All tickets</a>

  <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" x-data="{ files: [] }"
        class="mt-3 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_25px_50px_-12px_rgba(79,70,229,0.28),0_8px_16px_-8px_rgba(15,23,42,0.12)]">
    @csrf
    <div class="bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] px-6 py-6 text-white sm:px-8">
      <h1 class="text-xl font-bold">New ticket</h1>
      <p class="text-sm text-indigo-100">Describe the problem and our IT team will pick it up.</p>
    </div>

    <div class="space-y-6 p-6 sm:p-8">
      <div>
        <label for="title" class="{{ $lab }}">Title</label>
        <input id="title" name="title" value="{{ old('title') }}" maxlength="150" placeholder="e.g. Cannot connect to VPN from home" class="{{ $field }}">
        @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>
      <div>
        <label for="description" class="{{ $lab }}">What is the problem?</label>
        <textarea id="description" name="description" rows="6" placeholder="What happened? What did you expect? Include any error message." class="{{ $field }}">{{ old('description') }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label for="category_id" class="{{ $lab }}">Category</label>
          <select id="category_id" name="category_id" class="{{ $field }}">@foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>@endforeach</select>
          @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
          <label for="priority_id" class="{{ $lab }}">Priority</label>
          <select id="priority_id" name="priority_id" class="{{ $field }}">@foreach ($priorities as $p)<option value="{{ $p->id }}" @selected(old('priority_id') ? old('priority_id') == $p->id : $p->name === 'Medium')>{{ $p->name }}</option>@endforeach</select>
          @error('priority_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
      </div>
      <p class="-mt-3 rounded-lg bg-indigo-50 px-3 py-2 text-xs text-indigo-700">Target resolution: Low 72h · Medium 24h · High 8h · Critical 4h. Use Critical only when work has completely stopped.</p>

      <div>
        <span class="{{ $lab }}">Attachments <span class="font-normal text-slate-400">(optional)</span></span>
        <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-7 text-center shadow-inner transition hover:-translate-y-0.5 hover:border-indigo-400 hover:bg-indigo-50">
          <span class="text-sm text-slate-600"><span class="font-semibold text-indigo-600">Click to upload</span> screenshots or files</span>
          <span class="text-xs text-slate-400">PNG, JPG, PDF, TXT, LOG · up to 4 MB each</span>
          <input type="file" name="files[]" multiple class="hidden" @change="files = Array.from($event.target.files).map(f => f.name)">
        </label>
        <ul x-show="files.length" class="mt-3 flex flex-wrap gap-2 text-sm">
          <template x-for="name in files" :key="name"><li class="rounded-full bg-white px-3 py-1 text-slate-600 shadow ring-1 ring-slate-200" x-text="'📎 ' + name"></li></template>
        </ul>
        @foreach ($errors->get('files.*') as $messages)@foreach ($messages as $m)<p class="mt-1 text-sm text-red-600">{{ $m }}</p>@endforeach @endforeach
      </div>

      <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
        <a href="{{ route('tickets.index') }}" class="rounded-xl px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Cancel</a>
        <button class="rounded-xl bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] px-6 py-2.5 text-sm font-semibold text-white shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] transition hover:brightness-110 active:translate-y-1 active:shadow-none">Submit ticket</button>
      </div>
    </div>
  </form>
</div>
</x-app-layout>
