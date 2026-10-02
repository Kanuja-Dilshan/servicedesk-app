@props(['status'])
@php
    $colors = [
        'Open' => 'bg-blue-100 text-blue-800',
        'Assigned' => 'bg-indigo-100 text-indigo-800',
        'In Progress' => 'bg-yellow-100 text-yellow-800',
        'Waiting for User' => 'bg-orange-100 text-orange-800',
        'Resolved' => 'bg-green-100 text-green-800',
        'Closed' => 'bg-gray-200 text-gray-700',
    ];
@endphp
<span {{ $attributes->class(['inline-block rounded-full px-2.5 py-0.5 text-xs font-medium', $colors[$status] ?? 'bg-gray-100 text-gray-700']) }}>{{ $status }}</span>