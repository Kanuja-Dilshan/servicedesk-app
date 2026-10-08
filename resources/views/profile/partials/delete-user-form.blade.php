@php
  $warn = 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z';
@endphp
<section class="space-y-5">
  <header class="flex items-start gap-3">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600">
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $warn }}"/></svg>
    </span>
    <div>
      <h2 class="text-lg font-semibold">{{ __('Delete Account') }}</h2>
      <p class="text-sm text-slate-500">
        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
      </p>
    </div>
  </header>

  <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
          class="rounded-xl bg-[linear-gradient(to_bottom,#ef4444,#dc2626)] px-6 py-2.5 text-sm font-semibold text-white shadow-[0_4px_0_0_#991b1b,0_10px_18px_-4px_rgba(220,38,38,0.5)] transition hover:brightness-110 active:translate-y-1 active:shadow-none">
    {{ __('Delete Account') }}
  </button>

  <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
      @csrf
      @method('delete')

      <div class="flex items-start gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $warn }}"/></svg>
        </span>
        <div>
          <h2 class="text-lg font-semibold">{{ __('Are you sure you want to delete your account?') }}</h2>
          <p class="mt-1 text-sm text-slate-500">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
          </p>
        </div>
      </div>

      <div class="mt-6">
        <label for="password" class="sr-only">{{ __('Password') }}</label>
        <input id="password" name="password" type="password" placeholder="{{ __('Password') }}"
               class="block w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 text-sm shadow-inner transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100">
        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button type="button" x-on:click="$dispatch('close')" class="rounded-xl px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">{{ __('Cancel') }}</button>
        <button class="rounded-xl bg-[linear-gradient(to_bottom,#ef4444,#dc2626)] px-5 py-2.5 text-sm font-semibold text-white shadow-[0_4px_0_0_#991b1b] transition hover:brightness-110 active:translate-y-1 active:shadow-none">{{ __('Delete Account') }}</button>
      </div>
    </form>
  </x-modal>
</section>