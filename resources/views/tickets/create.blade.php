@php
  $field = 'w-full rounded-xl border border-slate-200 bg-slate-50 text-sm shadow-inner transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100';
  $lab = 'mb-1.5 block text-sm font-semibold text-slate-700';
  $sla = ['Low' => '72h', 'Medium' => '24h', 'High' => '8h', 'Critical' => '4h'];
  $dot = ['Low' => 'bg-slate-400', 'Medium' => 'bg-blue-500', 'High' => 'bg-orange-500', 'Critical' => 'bg-red-500'];
  $selectedPriority = old('priority_id') ?: optional($priorities->firstWhere('name', 'Medium'))->id;
@endphp
<x-app-layout>
<div class="mx-auto max-w-3xl">
  <a href="{{ route('tickets.index') }}" class="text-sm text-slate-500 hover:text-indigo-600">&larr; All tickets</a>

  <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data"
        x-data="{
          titleLen: {{ strlen(old('title', '')) }},
          files: [],
          fileErrors: [],
          dragging: false,
          submitting: false,
          allowed: ['png', 'jpg', 'jpeg', 'pdf', 'txt', 'log'],
          maxBytes: 4 * 1024 * 1024,
          add(list) {
            this.fileErrors = [];
            Array.from(list).forEach(f => {
              const ext = f.name.split('.').pop().toLowerCase();
              if (!this.allowed.includes(ext)) { this.fileErrors.push(f.name + ': file type not allowed'); return; }
              if (f.size > this.maxBytes) { this.fileErrors.push(f.name + ': larger than 4 MB'); return; }
              if (!this.files.some(x => x.name === f.name && x.size === f.size)) this.files.push(f);
            });
            this.sync();
          },
          remove(i) { this.files.splice(i, 1); this.sync(); },
          sync() {
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            this.$refs.picker.files = dt.files;
          },
          size(f) { return f.size < 1048576 ? Math.max(1, Math.round(f.size / 1024)) + ' KB' : (f.size / 1048576).toFixed(1) + ' MB'; }
        }"
        @submit="submitting = true"
        class="mt-3 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_25px_50px_-12px_rgba(79,70,229,0.28),0_8px_16px_-8px_rgba(15,23,42,0.12)]">
    @csrf

    {{-- Header --}}
    <div class="flex items-center gap-4 bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] px-6 py-6 text-white sm:px-8">
      <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20 ring-1 ring-white/30">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
      </div>
      <div>
        <h1 class="text-xl font-bold">New ticket</h1>
        <p class="text-sm text-indigo-100">Describe the problem and our IT team will pick it up.</p>
      </div>
    </div>

    <div class="space-y-6 p-6 sm:p-8">
      {{-- Title --}}
      <div>
        <div class="flex items-baseline justify-between">
          <label for="title" class="{{ $lab }}">Title</label>
          <span class="mb-1.5 text-xs text-slate-400"><span x-text="titleLen"></span>/150</span>
        </div>
        <input id="title" name="title" value="{{ old('title') }}" maxlength="150" required
               @input="titleLen = $event.target.value.length"
               placeholder="e.g. Cannot connect to VPN from home" class="{{ $field }}">
        @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>

      {{-- Description --}}
      <div>
        <label for="description" class="{{ $lab }}">What is the problem?</label>
        <textarea id="description" name="description" rows="6" required
                  placeholder="What happened? What did you expect? Include any error message." class="{{ $field }}">{{ old('description') }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>

      {{-- Category --}}
      <div>
        <label for="category_id" class="{{ $lab }}">Category</label>
        <select id="category_id" name="category_id" class="{{ $field }}">
          @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>

      {{-- Priority cards --}}
      <div>
        <span class="{{ $lab }}">Priority</span>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
          @foreach ($priorities as $p)
            <label class="cursor-pointer">
              <input type="radio" name="priority_id" value="{{ $p->id }}" class="peer sr-only" @checked($selectedPriority == $p->id)>
              <div class="rounded-xl border-2 border-slate-200 bg-slate-50 px-3 py-3 text-center transition hover:border-indigo-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-500/10 peer-checked:shadow-[0_4px_12px_-2px_rgba(99,102,241,0.35)] peer-focus-visible:ring-4 peer-focus-visible:ring-indigo-100">
                <span class="mx-auto mb-1.5 block h-2.5 w-2.5 rounded-full {{ $dot[$p->name] ?? 'bg-slate-400' }}"></span>
                <span class="block text-sm font-semibold text-slate-800">{{ $p->name }}</span>
                <span class="block text-xs text-slate-500">{{ $sla[$p->name] ?? '24h' }} target</span>
              </div>
            </label>
          @endforeach
        </div>
        <p class="mt-2 text-xs text-slate-500">Use Critical only when work has completely stopped.</p>
        @error('priority_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>

      {{-- Attachments --}}
      <div>
        <span class="{{ $lab }}">Attachments <span class="font-normal text-slate-400">(optional)</span></span>
        <label @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dragging = false; add($event.dataTransfer.files)"
               :class="dragging ? 'border-indigo-500 bg-indigo-500/10' : 'border-slate-300 bg-slate-50'"
               class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-7 text-center shadow-inner transition hover:border-indigo-400">
          <svg class="mb-2 h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
          <span class="text-sm text-slate-600"><span class="font-semibold text-indigo-600">Click to upload</span> or drag and drop</span>
          <span class="text-xs text-slate-400">PNG, JPG, PDF, TXT, LOG &middot; up to 4 MB each</span>
          <input x-ref="picker" type="file" name="files[]" multiple class="hidden" accept=".png,.jpg,.jpeg,.pdf,.txt,.log" @change="add($event.target.files)">
        </label>

        <template x-for="e in fileErrors" :key="e"><p class="mt-2 text-sm text-red-600" x-text="e"></p></template>

        <ul x-show="files.length" x-cloak class="mt-3 space-y-2">
          <template x-for="(f, i) in files" :key="f.name + f.size">
            <li class="flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2 text-sm ring-1 ring-slate-200">
              <span class="flex min-w-0 items-center gap-2">
                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                <span class="truncate text-slate-700" x-text="f.name"></span>
                <span class="shrink-0 text-xs text-slate-400" x-text="size(f)"></span>
              </span>
              <button type="button" @click="remove(i)" class="shrink-0 text-lg leading-none text-slate-400 hover:text-red-600" aria-label="Remove file">&times;</button>
            </li>
          </template>
        </ul>

        @foreach ($errors->get('files.*') as $messages)@foreach ($messages as $m)<p class="mt-1 text-sm text-red-600">{{ $m }}</p>@endforeach @endforeach
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
        <a href="{{ route('tickets.index') }}" class="rounded-xl px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Cancel</a>
        <button :disabled="submitting"
                class="rounded-xl bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] px-6 py-2.5 text-sm font-semibold text-white shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] transition hover:brightness-110 active:translate-y-1 active:shadow-none disabled:cursor-not-allowed disabled:opacity-60">
          <span x-text="submitting ? 'Submitting...' : 'Submit ticket'">Submit ticket</span>
        </button>
      </div>
    </div>
  </form>
</div>
</x-app-layout>
