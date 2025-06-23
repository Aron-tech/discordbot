@props(['title', 'subtitle' => null, 'badge' => null, 'notice' => null])

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-16">
        @if($badge)
            <div class="inline-flex items-center px-4 py-2 bg-slate-800/50 rounded-full border border-blue-500/30 mb-6">
                @if(isset($badge['icon']))
                    <i class="{{ $badge['icon'] }} text-blue-400 mr-2"></i>
                @endif
                <span class="text-sm text-slate-300">{{ $badge['text'] }}</span>
            </div>
        @endif

        <h1 class="text-4xl md:text-5xl font-bold mb-6">
            <span class="bg-gradient-to-r from-blue-400 via-purple-400 to-red-400 bg-clip-text text-transparent">
                {{ $title }}
            </span>
        </h1>

        @if($subtitle)
            <p class="text-lg text-slate-400 mb-4">
                {{ $subtitle }}
            </p>
        @endif

        @if($notice)
            <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 text-sm text-blue-300">
                <i class="fas fa-info-circle mr-2"></i>
                {{ $notice }}
            </div>
        @endif
    </div>
</div>
