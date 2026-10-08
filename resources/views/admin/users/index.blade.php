@php
  $roleBadge = ['admin' => 'bg-violet-100 text-violet-800', 'support' => 'bg-blue-100 text-blue-800', 'employee' => 'bg-slate-100 text-slate-700','technician' => 'bg-yellow-100 text-yellow-800'];
  $roleDot = ['admin' => 'bg-violet-500', 'support' => 'bg-blue-500', 'employee' => 'bg-slate-400', 'technician' => 'bg-yellow-500'];
  $total = method_exists($users, 'total') ? $users->total() : $users->count();
@endphp
<x-app-layout>
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold">Users</h1>
      <p class="text-slate-500">Manage accounts, roles and departments. {{ $total }} {{ \Illuminate\Support\Str::plural('user', $total) }} in total.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="rounded-xl bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] px-5 py-2.5 text-center text-sm font-semibold text-white shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] transition hover:brightness-110 active:translate-y-1 active:shadow-none">+ Add user</a>
  </div>

  <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_25px_50px_-12px_rgba(79,70,229,0.22),0_8px_16px_-8px_rgba(15,23,42,0.1)]">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-[linear-gradient(to_bottom,#f8fafc,#eef2ff)] text-xs uppercase tracking-wide text-slate-500">
          <tr><th class="p-4">User</th><th class="p-4">Role</th><th class="hidden p-4 sm:table-cell">Department</th><th class="p-4"></th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($users as $u)
            <tr class="transition hover:bg-indigo-50/50 hover:shadow-[inset_4px_0_0_0_#6366f1]">
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] text-sm font-semibold text-white shadow-[0_3px_8px_rgba(79,70,229,0.45)]">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                  <div class="min-w-0">
                    <p class="flex items-center gap-2 font-semibold text-slate-900">
                      <span class="truncate">{{ $u->name }}</span>
                      @if ($u->id === auth()->id())
                        <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-indigo-700">You</span>
                      @endif
                    </p>
                    <p class="truncate text-xs text-slate-500">{{ $u->email }}</p>
                  </div>
                </div>
              </td>
              <td class="p-4">
                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium capitalize shadow-sm ring-1 ring-black/5 {{ $roleBadge[$u->role] ?? '' }}">
                  <span class="h-1.5 w-1.5 rounded-full {{ $roleDot[$u->role] ?? 'bg-slate-400' }}"></span>{{ $u->role }}
                </span>
              </td>
              <td class="hidden p-4 sm:table-cell">{{ $u->department?->name ?? '-' }}</td>
              <td class="p-4 text-right"><a href="{{ route('admin.users.edit', $u) }}" class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-[0_2px_0_0_#c7d2fe] ring-1 ring-indigo-100 transition hover:-translate-y-0.5">Edit</a></td>
            </tr>
          @empty
            <tr><td colspan="4" class="p-12 text-center text-slate-500">No users yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="mt-5">{{ $users->links() }}</div>
</x-app-layout>