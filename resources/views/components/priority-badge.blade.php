@props(['priority'])
@php
    $colors = [
        'Low' => 'bg-gray-100 text-gray-700',
        'Medium' => 'bg-blue-100 text-blue-800',
        'High' => 'bg-orange-100 text-orange-800',
        'Critical' => 'bg-red-100 text-red-800',
    ];
@endphp
<span {{ $attributes->class(['inline-block rounded-full px-2.5 py-0.5 text-xs font-medium', $colors[$priority] ?? 'bg-gray-100 text-gray-700']) }}>{{ $priority }}</span>