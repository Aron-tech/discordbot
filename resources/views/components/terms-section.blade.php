@props(['title', 'icon', 'color', 'content'])

@php
    $colorClasses = [
        'blue' => 'text-blue-400',
        'red' => 'text-red-400',
        'green' => 'text-green-400',
        'purple' => 'text-purple-400',
        'yellow' => 'text-yellow-400',
        'indigo' => 'text-indigo-400',
        'cyan' => 'text-cyan-400',
        'pink' => 'text-pink-400',
    ];

    $colorClass = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
    <h2 class="text-2xl font-bold {{ $colorClass }} mb-6 flex items-center">
        <i class="{{ $icon }} mr-3"></i>
        {{ $title }}
    </h2>
    <div class="space-y-4 text-slate-300 leading-relaxed">
        {!! $content !!}
    </div>
</section>
