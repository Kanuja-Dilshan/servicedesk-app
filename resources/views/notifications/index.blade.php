<x-app-layout>
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-xl font-semibold mb-4">Notifications</h1>
    @forelse($notifications as $n)
      <a href="{{ route('notifications.open', $n->id) }}"
         class="block border rounded p-3 mb-2 bg-white {{ $n->read_at ? 'text-gray-500' : 'font-medium border-indigo-400' }}">
        {{ $n->data['message'] }}
        <span class="block text-xs text-gray-500">{{ $n->created_at->diffForHumans() }}</span>
      </a>
    @empty
      <p class="text-gray-500">No notifications yet. Updates on your tickets will show up here.</p>
    @endforelse
    {{ $notifications->links() }}
  </div>
</x-app-layout>