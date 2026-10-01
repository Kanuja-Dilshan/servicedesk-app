<x-app-layout>
  <div class="max-w-4xl mx-auto p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold">Users</h1>
      <a href="{{ route('admin.users.create') }}" class="rounded bg-indigo-600 px-4 py-2 text-white">Add user</a>
    </div>
    <table class="w-full text-left bg-white shadow rounded">
      <thead class="border-b text-sm"><tr><th class="p-3">Name</th><th>Email</th><th>Role</th><th>Department</th><th></th></tr></thead>
      <tbody>
      @foreach($users as $u)
        <tr class="border-b">
          <td class="p-3">{{ $u->name }}</td><td>{{ $u->email }}</td><td>{{ $u->role }}</td>
          <td>{{ $u->department?->name ?? '-' }}</td>
          <td><a class="text-indigo-700" href="{{ route('admin.users.edit', $u) }}">Edit</a></td>
        </tr>
      @endforeach
      </tbody>
    </table>
    <div class="mt-4">{{ $users->links() }}</div>
  </div>
</x-app-layout>