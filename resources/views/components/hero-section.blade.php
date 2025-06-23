<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center py-20">
        <div class="mb-8">
            <div class="inline-flex items-center px-4 py-2 bg-slate-800/50 rounded-full border border-blue-500/30 mb-6">
                <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                <span class="text-sm text-slate-300">Aktív és üzemkész</span>
            </div>
        </div>

        <h1 class="text-5xl md:text-7xl font-bold mb-6">
            <span class="bg-gradient-to-r from-blue-400 via-purple-400 to-red-400 bg-clip-text text-transparent">
                DutyManager 2.0
            </span>
        </h1>

        <p class="text-xl md:text-2xl text-slate-300 mb-8 max-w-3xl mx-auto leading-relaxed">
            A következő generációs Discord bot szolgálati idő kezeléshez.
            Automatizált, megbízható és könnyen használható.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <a href="https://discord.com/oauth2/authorize?client_id=1377217826104016928&permissions=8&integration_type=0&scope=bot" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-blue-500/25">
                <i class="fab fa-discord mr-2"></i>
                Bot hozzáadása
            </a>
            <button class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 border border-slate-600 hover:border-slate-500">
                <i class="fas fa-book mr-2"></i>
                Dokumentáció
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-2xl mx-auto">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-400 mb-1">{{ $guildCount ?? '0' }}</div>
                <div class="text-sm text-slate-400">Szerver</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-red-400 mb-1">{{ $userCount ?? '0' }}</div>
                <div class="text-sm text-slate-400">Felhasználó</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-400 mb-1">99.9%</div>
                <div class="text-sm text-slate-400">Uptime</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-400 mb-1">24/7</div>
                <div class="text-sm text-slate-400">Támogatás</div>
            </div>
        </div>
    </div>
</div>
