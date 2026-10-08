@php
  $field = 'block w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 ps-10 text-sm shadow-inner transition placeholder:text-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100';
  $lab = 'mb-1.5 block text-sm font-semibold text-slate-700';
  $lock = 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z';
@endphp
<section x-data="{
    pw: '', conf: '', show: false,
    get score() {
      let s = 0;
      if (this.pw.length >= 8) s++;
      if (this.pw.length >= 12) s++;
      if (/[a-z]/.test(this.pw) && /[A-Z]/.test(this.pw)) s++;
      if (/\d/.test(this.pw)) s++;
      if (/[^A-Za-z0-9]/.test(this.pw)) s++;
      return s;
    },
    get label() { return ['Too short', 'Weak', 'Fair', 'Good', 'Strong', 'Very strong'][this.score]; },
  }">
  <header class="mb-6 flex items-start gap-3">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $lock }}"/></svg>
    </span>
    <div>
      <h2 class="text-lg font-semibold">{{ __('Update Password') }}</h2>
      <p class="text-sm text-slate-500">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
    </div>
  </header>

  <form method="post" action="{{ route('password.update') }}" class="space-y-5">
    @csrf
    @method('put')

    <div>
      <label for="update_password_current_password" class="{{ $lab }}">{{ __('Current Password') }}</label>
      <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $lock }}"/></svg>
        </span>
        <input id="update_password_current_password" name="current_password" :type="show ? 'text' : 'password'" autocomplete="current-password" class="{{ $field }}">
      </div>
      <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
    </div>

    <div>
      <div class="flex items-baseline justify-between">
        <label for="update_password_password" class="{{ $lab }}">{{ __('New Password') }}</label>
        <button type="button" @click="show = !show" tabindex="-1" class="mb-1.5 text-xs font-semibold text-slate-400 transition hover:text-indigo-600" x-text="show ? 'HIDE PASSWORDS' : 'SHOW PASSWORDS'"></button>
      </div>
      <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $lock }}"/></svg>
        </span>
        <input id="update_password_password" name="password" x-model="pw" :type="show ? 'text' : 'password'" autocomplete="new-password" class="{{ $field }}">
      </div>
      <div x-show="pw.length" x-cloak class="mt-2">
        <div class="flex gap-1">
          <template x-for="i in 5" :key="i">
            <span class="h-1.5 flex-1 rounded-full" :class="i <= score ? (score <= 2 ? 'bg-red-500' : (score <= 3 ? 'bg-yellow-500' : 'bg-green-500')) : 'bg-slate-200'"></span>
          </template>
        </div>
        <p class="mt-1 text-xs text-slate-500">Strength: <span class="font-semibold" x-text="label"></span> &middot; use 12+ characters with upper and lower case, numbers and symbols.</p>
      </div>
      <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
    </div>

    <div>
      <label for="update_password_password_confirmation" class="{{ $lab }}">{{ __('Confirm Password') }}</label>
      <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $lock }}"/></svg>
        </span>
        <input id="update_password_password_confirmation" name="password_confirmation" x-model="conf" :type="show ? 'text' : 'password'" autocomplete="new-password" class="{{ $field }}">
      </div>
      <p x-show="conf.length" x-cloak class="mt-1 text-xs font-medium" :class="conf === pw ? 'text-green-600' : 'text-red-600'" x-text="conf === pw ? 'Passwords match' : 'Passwords do not match'"></p>
      <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4 border-t border-slate-100 pt-5">
      <button class="rounded-xl bg-[linear-gradient(to_bottom,#6366f1,#4f46e5)] px-6 py-2.5 text-sm font-semibold text-white shadow-[0_4px_0_0_#3730a3,0_10px_18px_-4px_rgba(79,70,229,0.55)] transition hover:brightness-110 active:translate-y-1 active:shadow-none">{{ __('Save') }}</button>

      @if (session('status') === 'password-updated')
        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
           class="flex items-center gap-1.5 text-sm font-medium text-green-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
          {{ __('Saved.') }}
        </p>
      @endif
    </div>
  </form>
</section>