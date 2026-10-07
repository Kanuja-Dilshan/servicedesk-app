@php
  $field = 'w-full rounded-xl border border-slate-200 bg-slate-50 text-sm shadow-inner transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100';
  $lab = 'mb-1.5 block text-sm font-semibold text-slate-700';
  $roleHelp = [
    'employee' => 'Can create tickets and follow their own tickets.',
    'support' => 'IT Support: receives auto-assigned tickets and works on them.',
    'admin' => 'Full access: all tickets, users, categories and departments.',
  ];
  $currentRole = old('role', $user->role ?: 'employee');
@endphp
<x-app-layout>
<div class="mx-auto max-w-2xl">
  <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-indigo-600">&larr; All users</a>
  <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
        x-data="{ role: '{{ $currentRole }}', show: false, help: {{ \Illuminate\Support\Js::from($roleHelp) }} }"
        class="mt-3 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_25px_50px_-12px_rgba(79,70,229,0.28),0_8px_16px_-8px_rgba(15,23,42,0.12)]">
    @csrf
    @if ($user->exists) @method('PUT') @endif

    <div class="flex items-center gap-4 bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] px-6 py-6 text-white sm:px-8">
      <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-lg font-bold ring-1 ring-white/30">
        @if ($user->exists){{ strtoupper(substr($user->name, 0, 1)) }}@else
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        @endif
      </div>
      <div>
        <h1 class="text-xl font-bold">{{ $user->exists ? 'Edit user' : 'Add user' }}</h1>
        <p class="text-sm text-indigo-100">{{ $user->exists ? 'Leave the password empty to keep the current one.' : 'The user can sign in with this email and password.' }}</p>
      </div>
    </div>

    <div class="space-y-5 p-6 sm:p-8">
      <div>
        <label for="name" class="{{ $lab }}">Full name</label>
        <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="{{ $field }}">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>

      <div>
        <label for="email" class="{{ $lab }}">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="{{ $field }}">
        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>

      <div>
        <label for="password" class="{{ $lab }}">Password</label>
        <div class="relative">
          <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="new-password"
                 placeholder="{{ $user->exists ? 'New password (optional)' : 'Minimum 8 characters' }}" class="{{ $field }} pe-16">
          <button type="button" @click="show = !show" tabindex="-1"
                  class="absolute inset-y-0 end-0 flex items-center pe-3 text-xs font-semibold text-slate-400 transition hover:text-indigo-600"
                  x-text="show ? 'HIDE' : 'SHOW'"></button>
        </div>
        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label for="role" class="{{ $lab }}">Role</label>
          <select id="role" name="role" x-model="role" class="{{ $field }}">
            @foreach (['employee', 'support', 'admin'] as $role)<option value="{{ $role }}">{{ ucfirst($role) }}</option>@endforeach
          </select>
          @error('role')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
          <label for="department_id" class="{{ $lab }}">Department</label>
          <select id="department_id" name="department_id" class="{{ $field }}">
            <option value="">No department</option>
            @foreach ($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id', $user->department_id) == $d->id)>{{ $d->name }}</option>@endforeach
          </select>
          @error('department_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
      </div>
      <p class="-mt-2 rounded-lg bg-indigo-50 px-3 py-2 text-xs text-indigo-700" x-text="help[role]"></p>

      <div class="flex justify-end gap-3 border-t border-slate-100 pt-6">
        <a href="{{ route('admin.users.index') }}" class="rounded-xl px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Cancel</a>
        <button class="rounded-xl bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] px-6 py-2.5 text-sm font-semibold text-white shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] transition hover:brightness-110 active:translate-y-1 active:shadow-none">Save user</button>
      </div>
    </div>
  </form>
</div>
</x-app-layout>