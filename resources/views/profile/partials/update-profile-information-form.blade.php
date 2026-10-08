@php
  $field = 'block w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 ps-10 text-sm shadow-inner transition placeholder:text-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100';
  $lab = 'mb-1.5 block text-sm font-semibold text-slate-700';
  $iconUser = 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z';
  $iconMail = 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75';
@endphp
<section>
  <header class="mb-6 flex items-start gap-3">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconUser }}"/></svg>
    </span>
    <div>
      <h2 class="text-lg font-semibold">{{ __('Profile Information') }}</h2>
      <p class="text-sm text-slate-500">{{ __("Update your account's profile information and email address.") }}</p>
    </div>
  </header>

  <form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
  </form>

  <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
    @csrf
    @method('patch')

    <div>
      <label for="name" class="{{ $lab }}">{{ __('Name') }}</label>
      <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconUser }}"/></svg>
        </span>
        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="{{ $field }}">
      </div>
      <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
      <label for="email" class="{{ $lab }}">{{ __('Email') }}</label>
      <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconMail }}"/></svg>
        </span>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="{{ $field }}">
      </div>
      <x-input-error class="mt-2" :messages="$errors->get('email')" />

      @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="mt-3 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
          {{ __('Your email address is unverified.') }}
          <button form="send-verification" class="font-semibold underline">{{ __('Click here to re-send the verification email.') }}</button>
          @if (session('status') === 'verification-link-sent')
            <p class="mt-2 font-medium text-green-700">{{ __('A new verification link has been sent to your email address.') }}</p>
          @endif
        </div>
      @endif
    </div>

    <div class="flex items-center gap-4 border-t border-slate-100 pt-5">
      <button class="rounded-xl bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] px-6 py-2.5 text-sm font-semibold text-white shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] transition hover:brightness-110 active:translate-y-1 active:shadow-none">{{ __('Save') }}</button>

      @if (session('status') === 'profile-updated')
        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
           class="flex items-center gap-1.5 text-sm font-medium text-green-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
          {{ __('Saved.') }}
        </p>
      @endif
    </div>
  </form>
</section>