@props(['icon', 'color', 'title', 'description'])

@php
    $color_classes = [
        'blue' => ['from-blue-500 to-blue-600', 'text-blue-400', 'hover:border-blue-500/50'],
        'red' => ['from-red-500 to-red-600', 'text-red-400', 'hover:border-red-500/50'],
        'purple' => ['from-purple-500 to-purple-600', 'text-purple-400', 'hover:border-purple-500/50'],
        'green' => ['from-green-500 to-green-600', 'text-green-400', 'hover:border-green-500/50'],
        'yellow' => ['from-yellow-500 to-yellow-600', 'text-yellow-400', 'hover:border-yellow-500/50'],
        'indigo' => ['from-indigo-500 to-indigo-600', 'text-indigo-400', 'hover:border-indigo-500/50'],
    ];

    [$gradient_class, $text_class, $hover_border_class] = $color_classes[$color] ?? $color_classes['blue'];
@endphp

<div @class([
        'bg-slate-800/50 rounded-2xl p-8 border border-slate-700/50 transition-all duration-300 transform hover:scale-105',
        $hover_border_class,
    ])>
    <div class="w-14 h-14 bg-gradient-to-br {{ $gradient_class }} rounded-xl flex items-center justify-center mb-6">
        <i class="{{ $icon }} text-white text-xl"></i>
    </div>
    <h3 class="text-xl font-semibold mb-4 {{ $text_class }}">{{ $title }}</h3>
    <p class="text-slate-300 leading-relaxed">
        {{ $description }}
    </p>
</div>
