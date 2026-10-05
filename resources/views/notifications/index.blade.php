<x-app-layout>
<div class="mx-auto max-w-3xl">
  <div class="mb-6">
    <h1 class="text-2xl font-bold">Notifications</h1>
    <p class="text-slate-500">Updates on your tickets appear here.</p>
  </div>
  <div class="space-y-3">
    @forelse ($notifications as $n)
      <a href="{{ route('notifications.open', $n->id) }}"
         class="flex items-start gap-4 rounded-2xl bg-white p-4 ring-1 ring-slate-900/5 transition duration-200 hover:-translate-y-0.5
                {{ $n->read_at ? 'opacity-70 shadow-sm' : 'shadow-[0_16px_32px_-12px_rgba(79,70,229,0.35)] ring-indigo-200' }}">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white {{ $n->read_at ? 'bg-slate-300' : 'bg-[linear-gradient(135deg,#6366f1,#8b5cf6)] shadow-[0_4px_10px_rgba(79,70,229,0.5)]' }}">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
        </span>
        <div class="min-w-0 flex-1">
          <p class="text-sm {{ $n->read_at ? '' : 'font-semibold' }}">{{ $n->data['message'] }}</p>
          <p class="text-xs text-slate-500">{{ $n->created_at->diffForHumans() }}</p>
        </div>
        @unless ($n->read_at)<span class="mt-2 h-2.5 w-2.5 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.9)]"></span>@endunless
      </a>
    @empty
      <div class="rounded-2xl bg-white p-12 text-center text-slate-500 ring-1 ring-slate-900/5 shadow-sm">No notifications yet.</div>
    @endforelse
  </div>
  <div class="mt-5">{{ $notifications->links() }}</div>
</div>
</x-app-layout>
