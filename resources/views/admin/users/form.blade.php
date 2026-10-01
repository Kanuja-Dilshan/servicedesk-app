<x-app-layout>
  <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="max-w-xl mx-auto p-6 space-y-4">
    @csrf
    @if($user->exists) @method('PUT') @endif
    <h1 class="text-xl font-semibold">{{ $user->exists ? 'Edit user' : 'Add user' }}</h1>
    <input name="name" value="{{ old('name', $user->name) }}" placeholder="Full name" class="w-full border rounded px-3 py-2">
    <input name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="Email" class="w-full border rounded px-3 py-2">
    <input name="password" type="password" placeholder="{{ $user->exists ? 'New password (leave blank to keep current)' : 'Password (min 8 characters)' }}" class="w-full border rounded px-3 py-2">
    <div class="flex gap-3">
      <select name="role" class="border rounded px-3 py-2 flex-1">
        @foreach(['employee', 'support', 'admin'] as $role)
          <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ $role }}</option>
        @endforeach
      </select>
      <select name="department_id" class="border rounded px-3 py-2 flex-1">
        <option value="">No department</option>
        @foreach($departments as $d)
          <option value="{{ $d->id }}" @selected(old('department_id', $user->department_id) == $d->id)>{{ $d->name }}</option>
        @endforeach
      </select>
    </div>
    @if($errors->any())<ul class="text-red-600 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
    <button class="rounded bg-indigo-600 px-4 py-2 text-white">Save user</button>
  </form>
</x-app-layout>