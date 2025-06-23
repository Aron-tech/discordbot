@props([
    'label',
    'description',
    'color' => 'indigo',
])

<div class="bg-slate-800/50 p-4 rounded-lg border border-slate-700/50">
    <div class="flex items-center">
        <button class="bg-{{ $color }}-600 hover:bg-{{ $color }}-500 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
            {{ $label }}
        </button>
    </div>
    <p class="mt-2 text-slate-300 text-sm">{{ $description }}</p>
</div>
