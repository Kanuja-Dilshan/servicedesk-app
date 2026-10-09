@php
  $card = 'rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-[0_20px_40px_-12px_rgba(15,23,42,0.18),0_6px_12px_-6px_rgba(15,23,42,0.1)]';
  $roleBadge = ['admin' => 'bg-violet-100 text-violet-800', 'support' => 'bg-blue-100 text-blue-800', 'technician' => 'bg-yellow-100 text-yellow-800', 'employee' => 'bg-slate-100 text-slate-700'];
  $roleLabel = ['support' => 'IT Support'];
@endphp
<x-app-layout>
  <div class="mx-auto max-w-3xl space-y-6">
    <div>
      <h1 class="text-2xl font-bold">Profile</h1>
      <p class="text-slate-500">Manage your account details and security.</p>
    </div>

        {{-- Identity card --}}
    <div class="{{ $card }} overflow-hidden">
      <div class="h-24 bg-[linear-gradient(135deg,#6366f1,#8b5cf6)]"></div>
      <div class="px-6 pb-6 sm:px-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:gap-5">
          <div class="-mt-10 flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#334155,#0f172a)] text-3xl font-bold text-white shadow-[0_8px_20px_rgba(15,23,42,0.35)] ring-4 ring-white">
            {{ strtoupper(substr($user->name, 0, 1)) }}
          </div>
          <div class="min-w-0 sm:pt-3">
            <h2 class="truncate text-xl font-bold">{{ $user->name }}</h2>
            <p class="truncate text-sm text-slate-500">{{ $user->email }}</p>
          </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-slate-500">
          <span class="rounded-full px-2.5 py-0.5 text-xs font-medium shadow-sm ring-1 ring-black/5 {{ $roleBadge[$user->role] ?? '' }}">{{ $roleLabel[$user->role] ?? ucfirst($user->role) }}</span>
          @if ($user->department)<span>&middot; {{ $user->department->name }}</span>@endif
          <span>&middot; Member since {{ $user->created_at->format('F Y') }}</span>
        </div>
      </div>
    </div>

    <div class="{{ $card }} p-6 sm:p-8">
      @include('profile.partials.update-profile-information-form')
    </div>

    <div class="{{ $card }} p-6 sm:p-8">
      @include('profile.partials.update-password-form')
    </div>

    <div class="{{ $card }} border-t-4 border-red-300 p-6 sm:p-8">
      @include('profile.partials.delete-user-form')
    </div>
  </div>
</x-app-layout>