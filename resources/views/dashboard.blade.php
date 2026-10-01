<x-app-layout>
  <div class="max-w-5xl mx-auto p-6 space-y-6">
    <h1 class="text-xl font-semibold">Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white shadow rounded p-4"><p class="text-sm text-gray-500">Total tickets</p><p class="text-3xl font-semibold">{{ $total }}</p></div>
      <div class="bg-white shadow rounded p-4"><p class="text-sm text-gray-500">Overdue</p><p class="text-3xl font-semibold {{ $overdue ? 'text-red-600' : '' }}">{{ $overdue }}</p></div>
      @if($unassigned !== null)
        <div class="bg-white shadow rounded p-4"><p class="text-sm text-gray-500">Unassigned and open</p><p class="text-3xl font-semibold">{{ $unassigned }}</p></div>
      @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      @foreach(['By status' => $byStatus, 'By priority' => $byPriority] as $title => $rows)
        @php $max = max(1, $rows->max()); @endphp
        <div class="bg-white shadow rounded p-4">
          <h2 class="font-medium mb-3">{{ $title }}</h2>
          @foreach($rows as $name => $count)
            <div class="mb-2">
              <div class="flex justify-between text-sm"><span>{{ $name }}</span><span>{{ $count }}</span></div>
              <div class="h-2 rounded bg-gray-100"><div class="h-2 rounded bg-indigo-500" style="width: {{ ($count / $max) * 100 }}%"></div></div>
            </div>
          @endforeach
        </div>
      @endforeach
    </div>
  </div>
</x-app-layout>
