@props([
    'command',
    'description',
    'usage' => null,
    'example' => null,
])

<div class="bg-slate-800/50 p-4 rounded-lg border border-slate-700/50">
    <h4 class="font-medium text-white mb-2"><code class="text-purple-300">/{{ $command }}</code></h4>
    <p class="text-slate-300 text-sm">{{ $description }}</p>

    @if($usage || $example)
        <div class="mt-3 bg-slate-900 p-3 rounded text-xs text-slate-400">
            @if($usage)
                <p><span class="text-green-400">Használat:</span> {{ $usage }}</p>
            @endif
            @if($example)
                <p><span class="text-blue-400">Példa:</span> {{ $example }}</p>
            @endif
        </div>
    @endif
</div>
