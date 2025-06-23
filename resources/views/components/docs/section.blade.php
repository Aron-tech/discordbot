@props([
    'title',
    'icon',
    'color' => 'slate',
])

<section class="bg-{{ $color }}-800/30 rounded-2xl p-8 border border-{{ $color }}-700/50">
    <h2 class="text-2xl font-bold text-{{ $color }}-400 mb-6 flex items-center">
        <i class="fas {{ $icon }} mr-3"></i>
        {{ $title }}
    </h2>
    <div class="space-y-6">
        {{ $slot }}
    </div>
</section>
