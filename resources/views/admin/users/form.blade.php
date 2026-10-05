@php
  $field = 'w-full rounded-xl border border-slate-200 bg-slate-50 text-sm shadow-inner transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100';
  $lab = 'mb-1.5 block text-sm font-semibold text-slate-700';
@endphp
<x-app-layout>
<div class="mx-auto max-w-2xl">
  <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-indigo-600">&larr; All users</a>
  <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
        class="mt-3 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_25px_50px_-12px_rgba(79,70,229,0.28),0_8px_16px_-8px_rgba(15,23,42,0.12)]">
    @csrf
    @if ($user->exists) @method('PUT') @endif

    <div class="bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] px-6 py-6 text-white sm:px-8">
      <h1 class="text-xl font-bold">{{ $user->exists ? 'Edit user' : 'Add user' }}</h1>
      <p class="text-sm text-indigo-100">{{ $user->exists ? 'Leave the password empty to keep the current one.' : 'The user can sign in with this email and password.' }}</p>
    </div>

    <div class="space-y-5 p-6 sm:p-8">
      <div><label class="{{ $lab }}">Full name</label><input name="name" value="{{ old('name', $user->name) }}" class="{{ $field }}"></div>
      <div><label class="{{ $lab }}">Email</label><input name="email" type="email" value="{{ old('email', $user->email) }}" class="{{ $field }}"></div>
      <div><label class="{{ $lab }}">Password</label><input name="password" type="password" placeholder="{{ $user->exists ? 'New password (optional)' : 'Minimum 8 characters' }}" class="{{ $field }}"></div>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div><label class="{{ $lab }}">Role</label>
          <select name="role" class="{{ $field }}">@foreach (['employee', 'support', 'admin'] as $role)<option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ ucfirst($role) }}</option>@endforeach</select></div>
        <div><label class="{{ $lab }}">Department</label>
          <select name="department_id" class="{{ $field }}"><option value="">No department</option>@foreach ($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id', $user->department_id) == $d->id)>{{ $d->name }}</option>@endforeach</select></div>
      </div>
      @if ($errors->any())<ul class="rounded-xl bg-red-50 p-3 text-sm text-red-700">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
      <div class="flex justify-end gap-3 border-t border-slate-100 pt-6">
        <a href="{{ route('admin.users.index') }}" class="rounded-xl px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Cancel</a>
        <button class="rounded-xl bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] px-6 py-2.5 text-sm font-semibold text-white shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] transition hover:brightness-110 active:translate-y-1 active:shadow-none">Save user</button>
      </div>
    </div>
  </form>
</div>
</x-app-layout>
